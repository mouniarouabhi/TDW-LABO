<?php
// controllers/ContactController.php
// Point 8 du PDF: Page de contact avec formulaire

class ContactController {
    private $contactModel;
    private $settingModel;
    
    public function __construct() {
        $this->contactModel = new ContactModel();
        $this->settingModel = new SettingModel();
    }
    
    /**
     * Page de contact avec formulaire pour visiteurs externes souhaitant collaborer
     */
    public function index() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        
        ContactView::render([
            'settings' => $settings,
            'success' => $_GET['success'] ?? null,
            'error' => $_GET['error'] ?? null
        ]);
    }
    
    /**
     * Traiter le formulaire de contact
     */
    public function envoyer() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=contact');
            exit;
        }
        
        $data = [
            'nom' => $_POST['nom'] ?? '',
            'email' => $_POST['email'] ?? '',
            'sujet' => $_POST['sujet'] ?? '',
            'message' => $_POST['message'] ?? ''
        ];
        
        // Validation
        if (empty($data['nom']) || empty($data['email']) || empty($data['sujet']) || empty($data['message'])) {
            header('Location: index.php?url=contact&error=empty_fields');
            exit;
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            header('Location: index.php?url=contact&error=invalid_email');
            exit;
        }
        
        // Enregistrer le message
        if ($this->contactModel->createMessage($data)) {
            header('Location: index.php?url=contact&success=sent');
        } else {
            header('Location: index.php?url=contact&error=failed');
        }
        exit;
    }
}


// controllers/OpportunityController.php
// Point 8 du PDF: Section "Offres et opportunités"

class OpportunityController {
    private $opportunityModel;
    private $settingModel;
    
    public function __construct() {
        $this->opportunityModel = new OpportunityModel();
        $this->settingModel = new SettingModel();
    }
    
    /**
     * Section "Offres et opportunités" (stages, thèses, bourses, collaborations)
     */
    public function index() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        
        // Filtre par type
        $filterType = $_GET['type'] ?? null;
        
        // Récupérer les opportunités ouvertes
        $opportunities = $this->opportunityModel->getOpenOpportunities();
        
        // Filtrer par type si demandé
        if ($filterType) {
            $opportunities = array_filter($opportunities, function($o) use ($filterType) {
                return $o['type_opportunity'] === $filterType;
            });
        }
        
        // Grouper par type
        $groupedOpportunities = [
            'stage' => [],
            'these' => [],
            'bourse' => [],
            'collaboration' => []
        ];
        
        foreach ($opportunities as $opp) {
            $type = $opp['type_opportunity'] ?? 'autre';
            if (isset($groupedOpportunities[$type])) {
                $groupedOpportunities[$type][] = $opp;
            }
        }
        
        OpportunityView::renderListe([
            'settings' => $settings,
            'opportunities' => $opportunities,
            'groupedOpportunities' => $groupedOpportunities,
            'filterType' => $filterType
        ]);
    }
    
    /**
     * Détail d'une opportunité
     */
    public function detail($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $opportunity = $this->opportunityModel->getOpportunityById($id);
        
        if (!$opportunity || $opportunity['statut'] !== 'ouverte') {
            header('Location: index.php?url=opportunities&error=not_found');
            exit;
        }
        
        OpportunityView::renderDetail([
            'settings' => $settings,
            'opportunity' => $opportunity
        ]);
    }
}
?>