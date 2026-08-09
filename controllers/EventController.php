<?php
// controllers/EventController.php
// Point 8 du PDF: Section des événements scientifiques

class EventController {
    private $eventModel;
    private $settingModel;
    
    public function __construct() {
        $this->eventModel = new EventModel();
        $this->settingModel = new SettingModel();
    }
    
    /**
     * Section des événements scientifiques (ateliers, séminaires, conférences)
     */
    public function index() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        
        // Récupérer les événements à venir
        $events = $this->eventModel->getAllEvents();
        
        EventView::renderListe([
            'settings' => $settings,
            'events' => $events
        ]);
    }
    
    /**
     * Détail d'un événement avec inscription
     */
    public function detail($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $event = $this->eventModel->getEventById($id);
        
        if (!$event) {
            header('Location: index.php?url=events&error=not_found');
            exit;
        }
        
        // Vérifier si l'utilisateur est inscrit
        $isRegistered = false;
        if (AuthController::isAuthenticated()) {
            $user = AuthController::getCurrentUser();
            $isRegistered = $this->eventModel->isUserRegistered($id, $user['id_user']);
        }
        
        EventView::renderDetail([
            'settings' => $settings,
            'event' => $event,
            'isRegistered' => $isRegistered,
            'success' => $_GET['success'] ?? null,
            'error' => $_GET['error'] ?? null
        ]);
    }
    
    /**
     * S'inscrire à un événement
     */
    public function inscrire($id) {
        AuthController::requireAuth();
        
        $user = AuthController::getCurrentUser();
        
        // Vérifier si déjà inscrit
        if ($this->eventModel->isUserRegistered($id, $user['id_user'])) {
            header('Location: index.php?url=event/detail/' . $id . '&error=already_registered');
            exit;
        }
        
        // Inscrire
        if ($this->eventModel->registerUserToEvent($id, $user['id_user'])) {
            header('Location: index.php?url=event/detail/' . $id . '&success=registered');
        } else {
            header('Location: index.php?url=event/detail/' . $id . '&error=failed');
        }

        exit;
    }
}
?>