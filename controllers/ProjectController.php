<?php
// controllers/ProjectController.php
// Point 4 du PDF: Catalogue des projets de recherche (2 pts)
// Support du filtrage par utilisateur avec ?user=me

class ProjectController {
    private $projectModel;
    private $userModel;
    private $publicationModel;
    private $settingModel;
    
    public function __construct() {
        $this->projectModel = new ProjectModel();
        $this->userModel = new UserModel();
        $this->publicationModel = new PublicationModel();
        $this->settingModel = new SettingModel();
    }
    
    /**
     * Catalogue des projets de recherche
     * Spécifications PDF:
     * - Projets classés par thématique (IA, Sécurité, Cloud, Réseaux, Systèmes embarqués, etc.)
     * - Chaque projet en CARTE: titre, responsable, membres associés, type financement, lien détail
     * - Filtrage/tri par: thématique, encadrant, statut (en cours, terminé, soumis)
     * - Publications associées au projet
     * 
     * ✅ Supporte ?user=me pour afficher uniquement les projets de l'utilisateur
     */
    public function index() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        
        // ✅ Détection du mode "mes projets"
        $filterByUser = isset($_GET['user']) && $_GET['user'] === 'me';
        $isAuthenticated = AuthController::isAuthenticated();
        
        // Redirection si non authentifié et demande "mes projets"
        if ($filterByUser && !$isAuthenticated) {
            header('Location: index.php?url=login&redirect=projects?user=me');
            exit;
        }
        
        // Filtres
        $filterThematique = $_GET['thematique'] ?? null;
        $filterEncadrant = $_GET['encadrant'] ?? null;
        $filterStatut = $_GET['statut'] ?? null;
        
        // ✅ Récupération conditionnelle des projets
        if ($filterByUser && $isAuthenticated) {
            // Uniquement les projets de l'utilisateur connecté
            $currentUser = AuthController::getCurrentUser();
            $projects = $this->projectModel->getProjectsByUser($currentUser['id_user']);
        } else {
            // Tous les projets du laboratoire
            $projects = $this->projectModel->getAllProjects();
        }
        
        // Appliquer les filtres (thématique, encadrant, statut)
       if ($filterThematique) {
            $projects = array_filter($projects, fn($p) => ($p['thematique'] ?? '') === $filterThematique);
        }

        if ($filterEncadrant) {
            $projects = array_filter($projects, fn($p) => ($p['id_encadrant'] ?? 0) == $filterEncadrant);
        }

        if ($filterStatut) {
            $projects = array_filter($projects, fn($p) => ($p['statut'] ?? '') === $filterStatut);
        }

        // Récupérer les membres associés pour chaque projet
        foreach ($projects as &$project) {
            $project['membres_associes'] = $this->projectModel->getProjectMembers($project['id_project']);
        }
        
        // Listes pour les filtres
        $allProjects = $this->projectModel->getAllProjects();
        $thematiques = array_unique(array_filter(array_column($allProjects, 'thematique')));
        sort($thematiques);
        
        $encadrants = $this->userModel->getAllUsers();
        
        ProjectView::renderCatalogue([
            'settings' => $settings,
            'projects' => $projects,
            'thematiques' => $thematiques,
            'encadrants' => $encadrants,
            'filterThematique' => $filterThematique,
            'filterEncadrant' => $filterEncadrant,
            'filterStatut' => $filterStatut,
            'filterByUser' => $filterByUser,
            'isAuthenticated' => $isAuthenticated,
            'currentUserId' => $isAuthenticated ? AuthController::getCurrentUser()['id_user'] : null
        ]);
    }
    
    /**
     * Fiche détaillée d'un projet avec publications associées
     */
    public function detail($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            header('Location: index.php?url=projects&error=not_found');
            exit;
        }
        
        // Membres associés au projet
        $members = $this->projectModel->getProjectMembers($id);
        
        // Publications associées au projet
        $publications = $this->publicationModel->getPublicationsByProject($id);
        
        ProjectView::renderDetail([
            'settings' => $settings,
            'project' => $project,
            'members' => $members,
            'publications' => $publications
        ]);
    }
}
?>