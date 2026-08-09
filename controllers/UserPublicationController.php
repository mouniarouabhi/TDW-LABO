<?php
// controllers/UserPublicationController.php
// Gestion des publications par l'utilisateur authentifié

class UserPublicationController {
    private $publicationModel;
    private $userModel;
    private $projectModel;
    private $settingModel;
    
    public function __construct() {
        // Vérifier que l'utilisateur est connecté
        AuthController::requireAuth();
        
        $this->publicationModel = new PublicationModel();
        $this->userModel = new UserModel();
        $this->projectModel = new ProjectModel();
        $this->settingModel = new SettingModel();
    }
    
    /**
     * Liste des publications de l'utilisateur avec gestion
     */
    public function index() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $currentUser = AuthController::getCurrentUser();
        
        // Récupérer les publications de l'utilisateur
        $publications = $this->publicationModel->getPublicationsByUser($currentUser['id_user']);
        
        // Statistiques
        $stats = [
            'total' => count($publications),
            'validees' => count(array_filter($publications, fn($p) => $p['statut'] === 'valide')),
            'en_attente' => count(array_filter($publications, fn($p) => $p['statut'] === 'en_attente')),
            'rejetees' => count(array_filter($publications, fn($p) => $p['statut'] === 'rejete'))
        ];
        
        UserPublicationView::renderList([
            'settings' => $settings,
            'publications' => $publications,
            'stats' => $stats
        ]);
    }
    
    /**
     * Supprimer une publication
     */
    public function delete($id) {
        $currentUser = AuthController::getCurrentUser();
        
        // Récupérer la publication
        $publication = $this->publicationModel->getPublicationById($id);
        
        if (!$publication) {
            header('Location: index.php?url=userpublication&error=not_found');
            exit;
        }
        
        // Vérifier que l'utilisateur est auteur
        if (!$this->publicationModel->isUserAuthor($id, $currentUser['id_user'])) {
            header('Location: index.php?url=userpublication&error=unauthorized');
            exit;
        }
        
        // Supprimer les auteurs d'abord
        $this->publicationModel->removeAllAuthors($id);
        
        // Supprimer la publication
        if ($this->publicationModel->deletePublication($id)) {
            header('Location: index.php?url=userpublication&success=deleted');
        } else {
            header('Location: index.php?url=userpublication&error=delete_failed');
        }
        exit;
    }
    
    /**
     * Ajouter une nouvelle publication
     */
    public function add() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $currentUser = AuthController::getCurrentUser();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation
            if (empty($_POST['titre']) || empty($_POST['type_publication']) || empty($_POST['annee_publication'])) {
                $error = "Veuillez remplir tous les champs obligatoires";
            } else {
                // Préparer les données
                $data = [
                    'titre' => trim($_POST['titre']),
                    'resume' => trim($_POST['resume'] ?? ''),
                    'type_publication' => $_POST['type_publication'],
                    'annee_publication' => (int)$_POST['annee_publication'],
                    'doi' => !empty($_POST['doi']) ? trim($_POST['doi']) : null,
                    'lien_telechargement' => !empty($_POST['lien_telechargement']) ? trim($_POST['lien_telechargement']) : null,
                    'id_project' => !empty($_POST['id_project']) ? (int)$_POST['id_project'] : null,
                    'domaine' => trim($_POST['domaine'] ?? ''),
                    'statut' => 'en_attente',
                    'date_soumission' => date('Y-m-d')
                ];
                
                // Créer la publication
                $publicationId = $this->publicationModel->createPublication($data);
                
                if ($publicationId) {
                    // Ajouter l'utilisateur actuel comme premier auteur
                    $this->publicationModel->addAuthorToPublication($publicationId, $currentUser['id_user'], 1);
                    
                    // Ajouter les co-auteurs sélectionnés
                    if (!empty($_POST['coauthors']) && is_array($_POST['coauthors'])) {
                        $ordre = 2; // Commencer après l'utilisateur actuel
                        foreach ($_POST['coauthors'] as $coauthorId) {
                            $coauthorId = (int)$coauthorId;
                            if ($coauthorId != $currentUser['id_user']) {
                                $this->publicationModel->addAuthorToPublication($publicationId, $coauthorId, $ordre);
                                $ordre++;
                            }
                        }
                    }
                    
                    header('Location: index.php?url=userpublication&success=created');
                    exit;
                } else {
                    $error = "Erreur lors de la création de la publication";
                }
            }
        }
        
        // Récupérer les données pour le formulaire
        $projects = $this->projectModel->getUserProjects($currentUser['id_user']);
        $users = $this->userModel->getAllUsers();
        
        UserPublicationView::renderForm([
            'settings' => $settings,
            'action' => 'add',
            'publication' => null,
            'projects' => $projects,
            'users' => $users,
            'authors' => [],
            'error' => $error ?? null
        ]);
    }

    /**
     * Modifier une publication existante
     */
    public function edit($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $currentUser = AuthController::getCurrentUser();
        
        // Récupérer la publication
        $publication = $this->publicationModel->getPublicationById($id);
        
        if (!$publication) {
            header('Location: index.php?url=userpublication&error=not_found');
            exit;
        }
        
        // Vérifier que l'utilisateur est auteur
        if (!$this->publicationModel->isUserAuthor($id, $currentUser['id_user'])) {
            header('Location: index.php?url=userpublication&error=unauthorized');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation
            if (empty($_POST['titre']) || empty($_POST['type_publication']) || empty($_POST['annee_publication'])) {
                $error = "Veuillez remplir tous les champs obligatoires";
            } else {
                // Préparer les données
                $data = [
                    'titre' => trim($_POST['titre']),
                    'resume' => trim($_POST['resume'] ?? ''),
                    'type_publication' => $_POST['type_publication'],
                    'annee_publication' => (int)$_POST['annee_publication'],
                    'doi' => !empty($_POST['doi']) ? trim($_POST['doi']) : null,
                    'lien_telechargement' => !empty($_POST['lien_telechargement']) ? trim($_POST['lien_telechargement']) : null,
                    'id_project' => !empty($_POST['id_project']) ? (int)$_POST['id_project'] : null,
                    'domaine' => trim($_POST['domaine'] ?? ''),
                    'statut' => $publication['statut'],
                    'date_soumission' => $publication['date_soumission']
                ];
                
                // Mettre à jour la publication
                if ($this->publicationModel->updatePublication($id, $data)) {
                    // Supprimer tous les auteurs existants
                    $this->publicationModel->removeAllAuthors($id);
                    
                    // Ajouter l'utilisateur actuel comme premier auteur
                    $this->publicationModel->addAuthorToPublication($id, $currentUser['id_user'], 1);
                    
                    // Ajouter les co-auteurs sélectionnés
                    if (!empty($_POST['coauthors']) && is_array($_POST['coauthors'])) {
                        $ordre = 2;
                        foreach ($_POST['coauthors'] as $coauthorId) {
                            $coauthorId = (int)$coauthorId;
                            if ($coauthorId != $currentUser['id_user']) {
                                $this->publicationModel->addAuthorToPublication($id, $coauthorId, $ordre);
                                $ordre++;
                            }
                        }
                    }
                    
                    header('Location: index.php?url=userpublication&success=updated');
                    exit;
                } else {
                    $error = "Erreur lors de la mise à jour";
                }
            }
        }
        
        // Récupérer les données pour le formulaire
        $projects = $this->projectModel->getUserProjects($currentUser['id_user']);
        $users = $this->userModel->getAllUsers();
        $authors = $this->publicationModel->getPublicationAuthors($id);
        
        UserPublicationView::renderForm([
            'settings' => $settings,
            'action' => 'edit',
            'publication' => $publication,
            'projects' => $projects,
            'users' => $users,
            'authors' => $authors,
            'error' => $error ?? null
        ]);
    }
}
?>