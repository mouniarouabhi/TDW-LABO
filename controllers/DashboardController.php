<?php
// controllers/DashboardController.php
// VERSION CORRIGÉE - Récupère les rôles de l'utilisateur

class DashboardController {
    private $userModel;
    private $projectModel;
    private $publicationModel;
    private $reservationModel;
    private $settingModel;
    private $userRoleModel;
    private $documentModel;
    
    public function __construct() {
        // Vérifier que l'utilisateur est connecté
        AuthController::requireAuth();
        
        // ✅ BLOQUER l'admin - les admins ne peuvent PAS accéder au dashboard
        if (AuthController::isAdmin()) {
            header('Location: index.php?url=admin');
            exit;
        }
        
        $this->userModel = new UserModel();
        $this->projectModel = new ProjectModel();
        $this->publicationModel = new PublicationModel();
        $this->reservationModel = new ReservationModel();
        $this->settingModel = new SettingModel();
        $this->userRoleModel = new UserRoleModel();
        $this->documentModel = new DocumentModel();
    }
    
    /**
     * Tableau de bord de l'utilisateur connecté
     * Affiche uniquement SES données personnelles
     */
    public function index() {
        $currentUser = AuthController::getCurrentUser();
        $settings = $this->settingModel->getAllSettingsAsArray();
        
        // Récupérer les détails complets de l'utilisateur
        $user = $this->userModel->getUserById($currentUser['id_user']);
        
        // ========================================
        // ✅ FIX : Ajouter les rôles à l'utilisateur
        // CORRECTION: Le champ est 'nom', pas 'nom_role'
        // ========================================
        $userRoles = $this->userRoleModel->getUserRoles($currentUser['id_user']);
        
        // Extraire le nom du premier rôle pour affichage
        if (!empty($userRoles)) {
            // Le champ de la table roles est 'nom', pas 'nom_role'
            $user['role'] = $userRoles[0]['nom'] ?? 'Utilisateur';
            $user['roles'] = $userRoles; // Tous les rôles
        } else {
            $user['role'] = 'Utilisateur';
            $user['roles'] = [];
        }
        
        // Publications de l'utilisateur UNIQUEMENT
        $publications = $this->publicationModel->getPublicationsByUser($currentUser['id_user']);
        $documents = $this->documentModel->getUserDocuments($currentUser['id_user']);
        
        // Projets auxquels l'utilisateur participe UNIQUEMENT
        $projects = [];
        if (method_exists($this->projectModel, 'getProjectsByUser')) {
            $projects = $this->projectModel->getProjectsByUser($currentUser['id_user']);
        }
        
        // Réservations à venir de l'utilisateur UNIQUEMENT
        $upcomingReservations = [];
        if (method_exists($this->reservationModel, 'getUpcomingReservations')) {
            $upcomingReservations = $this->reservationModel->getUpcomingReservations($currentUser['id_user']);
        }
        
        DashboardView::render([
            'settings' => $settings,
            'user' => $user,
            'projects' => $projects,
            'publications' => $publications,
            'documents' => $documents,
            'upcomingReservations' => $upcomingReservations
        ]);
    }
}
?>