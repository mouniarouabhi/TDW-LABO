<?php
// controllers/UserProjectController.php
// Gestion des projets par l'utilisateur authentifié

class UserProjectController {
    private $projectModel;
    private $userModel;
    private $publicationModel;
    private $settingModel;
    
    public function __construct() {
        AuthController::requireAuth();
        
        $this->projectModel = new ProjectModel();
        $this->userModel = new UserModel();
        $this->publicationModel = new PublicationModel();
        $this->settingModel = new SettingModel();
    }
    
    /**
     * Liste des projets de l'utilisateur
     */
    public function index() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $currentUser = AuthController::getCurrentUser();
        
        // Récupérer les projets de l'utilisateur
        $projects = $this->projectModel->getUserProjects($currentUser['id_user']);
        
        // Ajouter les membres pour chaque projet
        foreach ($projects as &$project) {
            $project['membres'] = $this->projectModel->getProjectMembers($project['id_project']);
            $project['is_chef'] = ($project['id_responsable'] == $currentUser['id_user']);
        }
        
        // Statistiques
        $stats = [
            'total' => count($projects),
            'responsable' => count(array_filter($projects, fn($p) => $p['id_responsable'] == $currentUser['id_user'])),
            'membre' => count(array_filter($projects, fn($p) => $p['id_responsable'] != $currentUser['id_user'])),
            'en_cours' => count(array_filter($projects, fn($p) => $p['statut'] === 'en_cours'))
        ];
        
        UserProjectView::renderList([
            'settings' => $settings,
            'projects' => $projects,
            'stats' => $stats,
            'currentUserId' => $currentUser['id_user']
        ]);
    }
    
    /**
     * Détails d'un projet avec gestion
     */
    public function detail($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $currentUser = AuthController::getCurrentUser();
        
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            header('Location: index.php?url=userproject&error=not_found');
            exit;
        }
        
        // Vérifier que l'utilisateur appartient au projet
        if (!$this->projectModel->isUserInProject($id, $currentUser['id_user'])) {
            header('Location: index.php?url=userproject&error=unauthorized');
            exit;
        }
        
        $isChef = ($project['id_responsable'] == $currentUser['id_user']);
        $members = $this->projectModel->getProjectMembers($id);
        $publications = $this->publicationModel->getPublicationsByProject($id);
        $allUsers = $this->userModel->getAllUsers();
        
        UserProjectView::renderDetail([
            'settings' => $settings,
            'project' => $project,
            'members' => $members,
            'publications' => $publications,
            'allUsers' => $allUsers,
            'isChef' => $isChef,
            'currentUserId' => $currentUser['id_user']
        ]);
    }
    
    /**
     * Quitter un projet (membre uniquement)
     */
    public function leave($id) {
        $currentUser = AuthController::getCurrentUser();
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            header('Location: index.php?url=userproject&error=not_found');
            exit;
        }
        
        // Interdire au chef de quitter son propre projet
        if ($project['id_responsable'] == $currentUser['id_user']) {
            header('Location: index.php?url=userproject/detail/'.$id.'&error=chef_cannot_leave');
            exit;
        }
        
        // Retirer l'utilisateur du projet
        if ($this->projectModel->removeMemberFromProject($id, $currentUser['id_user'])) {
            header('Location: index.php?url=userproject&success=left');
        } else {
            header('Location: index.php?url=userproject&error=leave_failed');
        }
        exit;
    }
    
    /**
     * Clôturer un projet (chef uniquement)
     */
    public function close($id) {
        $currentUser = AuthController::getCurrentUser();
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            header('Location: index.php?url=userproject&error=not_found');
            exit;
        }
        
        // Vérifier que c'est le chef
        if ($project['id_responsable'] != $currentUser['id_user']) {
            header('Location: index.php?url=userproject/detail/'.$id.'&error=not_chef');
            exit;
        }
        
        // Clôturer le projet
        $data = [
            'titre' => $project['titre'],
            'description' => $project['description'],
            'thematique' => $project['thematique'],
            'type_financement' => $project['type_financement'],
            'statut' => 'termine',
            'id_responsable' => $project['id_responsable'],
            'id_encadrant' => $project['id_encadrant'],
            'date_debut' => $project['date_debut'],
            'date_fin' => date('Y-m-d'),
            'budget' => $project['budget']
        ];
        
        if ($this->projectModel->updateProject($id, $data)) {
            header('Location: index.php?url=userproject/detail/'.$id.'&success=closed');
        } else {
            header('Location: index.php?url=userproject/detail/'.$id.'&error=close_failed');
        }
        exit;
    }
    
    /**
     * Ajouter un membre (chef uniquement)
     */
    public function addMember($id) {
        $currentUser = AuthController::getCurrentUser();
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            header('Location: index.php?url=userproject&error=not_found');
            exit;
        }
        
        // Vérifier que c'est le chef
        if ($project['id_responsable'] != $currentUser['id_user']) {
            header('Location: index.php?url=userproject/detail/'.$id.'&error=not_chef');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = (int)$_POST['user_id'];
            $role = trim($_POST['role_projet'] ?? 'Membre');
            
            if ($this->projectModel->addMemberToProject($id, $userId, $role)) {
                header('Location: index.php?url=userproject/detail/'.$id.'&success=member_added');
            } else {
                header('Location: index.php?url=userproject/detail/'.$id.'&error=member_exists');
            }
        }
        exit;
    }
    
    /**
     * Retirer un membre (chef uniquement)
     */
    public function removeMember($projectId, $userId) {
        $currentUser = AuthController::getCurrentUser();
        $project = $this->projectModel->getProjectById($projectId);
        
        if (!$project) {
            header('Location: index.php?url=userproject&error=not_found');
            exit;
        }
        
        // Vérifier que c'est le chef
        if ($project['id_responsable'] != $currentUser['id_user']) {
            header('Location: index.php?url=userproject/detail/'.$projectId.'&error=not_chef');
            exit;
        }
        
        // Ne pas permettre de se retirer soi-même
        if ($userId == $currentUser['id_user']) {
            header('Location: index.php?url=userproject/detail/'.$projectId.'&error=cannot_remove_self');
            exit;
        }
        
        if ($this->projectModel->removeMemberFromProject($projectId, $userId)) {
            header('Location: index.php?url=userproject/detail/'.$projectId.'&success=member_removed');
        } else {
            header('Location: index.php?url=userproject/detail/'.$projectId.'&error=remove_failed');
        }
        exit;
    }
    
    /**
     * Supprimer une publication du projet (chef uniquement)
     */
    public function removePublication($projectId, $publicationId) {
        $currentUser = AuthController::getCurrentUser();
        $project = $this->projectModel->getProjectById($projectId);
        
        if (!$project) {
            header('Location: index.php?url=userproject&error=not_found');
            exit;
        }
        
        // Vérifier que c'est le chef
        if ($project['id_responsable'] != $currentUser['id_user']) {
            header('Location: index.php?url=userproject/detail/'.$projectId.'&error=not_chef');
            exit;
        }
        
        // Supprimer les auteurs puis la publication
        $this->publicationModel->removeAllAuthors($publicationId);
        
        if ($this->publicationModel->deletePublication($publicationId)) {
            header('Location: index.php?url=userproject/detail/'.$projectId.'&success=publication_deleted');
        } else {
            header('Location: index.php?url=userproject/detail/'.$projectId.'&error=delete_failed');
        }
        exit;
    }
}
?>