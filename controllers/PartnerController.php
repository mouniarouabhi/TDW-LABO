<?php
// controllers/PartnerController.php

class PartnerController {
    private $partnerModel;
    private $settingModel;
    
    public function __construct() {
        $this->partnerModel = new PartnerModel();
        $this->settingModel = new SettingModel();
    }
    
    /**
     * Liste de tous les partenaires
     * Affichage public des partenaires institutionnels et industriels
     */
    public function index() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        
        // Filtres
        $filterType = $_GET['type'] ?? null;
        $filterPays = $_GET['pays'] ?? null;
        $search = $_GET['search'] ?? '';
        
        // Récupérer tous les partenaires actifs
        $partners = $this->partnerModel->getAllPartners();
        
        // Appliquer les filtres
        if ($filterType) {
            $partners = array_filter($partners, function($p) use ($filterType) {
                return ($p['type_partenaire'] ?? '') === $filterType;
            });
        }
        
        if ($filterPays) {
            $partners = array_filter($partners, function($p) use ($filterPays) {
                return ($p['pays'] ?? '') === $filterPays;
            });
        }
        
        if (!empty($search)) {
            $partners = array_filter($partners, function($p) use ($search) {
                return stripos($p['nom_partenaire'] ?? '', $search) !== false || 
                       stripos($p['description'] ?? '', $search) !== false;
            });
        }
        
        // Listes pour les filtres
        $allPartners = $this->partnerModel->getAllPartners();
        $types = array_unique(array_filter(array_column($allPartners, 'type_partenaire')));
        sort($types);
        
        $pays = array_unique(array_filter(array_column($allPartners, 'pays')));
        sort($pays);
        
        PartnerView::renderList([
            'settings' => $settings,
            'partners' => $partners,
            'types' => $types,
            'pays' => $pays,
            'filterType' => $filterType,
            'filterPays' => $filterPays,
            'search' => $search
        ]);
    }
    
    /**
     * Détail d'un partenaire
     */
    public function detail($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $partner = $this->partnerModel->getPartnerById($id);
        
        if (!$partner) {
            header('Location: index.php?url=partners&error=not_found');
            exit;
        }
        
        PartnerView::renderDetail([
            'settings' => $settings,
            'partner' => $partner
        ]);
    }
}
?>