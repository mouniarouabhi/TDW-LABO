<?php
// controllers/OpportunityController.php

class OpportunityController {
    private $opportunityModel;
    private $settingModel;
    
    public function __construct() {
        $this->opportunityModel = new OpportunityModel();
        $this->settingModel = new SettingModel();
    }
    
    /**
     * Affichage public des partenaires institutionnels et industriels
     */
    public function index() {
        $settings = $this->settingModel->getAllSettingsAsArray();

        $opportunities = $this->opportunityModel->getAllOpportunities();
        
        OpportunityView::renderListe([
            'settings' => $settings,
            'opportunities' => $opportunities,
        ]);
    }
    
    /**
     * Détail d'un partenaire
     */
    public function detail($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $opportunity = $this->opportunityModel->getOpportunityById($id);
        
        if (!$opportunity) {
            header('Location: index.php?url=opportunity&error=not_found');
            exit;
        }
        
        OpportunityView::renderDetail([
            'settings' => $settings,
            'opportunity' => $opportunity
        ]);
    }
}
?>