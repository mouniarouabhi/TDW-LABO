<?php
// controllers/HomeController.php
// VERSION CORRIGÉE - Événements à venir EN PREMIER

class HomeController {
    private $newsModel;
    private $eventModel;
    private $partnerModel;
    private $settingModel;
    private $userModel;
    
    public function __construct() {
        $this->newsModel = new NewsModel();
        $this->eventModel = new EventModel();
        $this->partnerModel = new PartnerModel();
        $this->settingModel = new SettingModel();
        $this->userModel = new UserModel();
    }
    
    /**
     * Affiche la page d'accueil avec toutes ses sections
     */
    public function index() {
        // ========================================
        // CONFIGURATION PAGINATION
        // ========================================
        $eventsPerPage = 4;
        $currentEventPage = isset($_GET['event_page']) ? max(1, (int)$_GET['event_page']) : 1;
        
        // ========================================
        // PARAMÈTRES DU SITE
        // ========================================
        $settings = $this->settingModel->getAllSettingsAsArray();
        
        // ========================================
        // DIAPORAMA (SLIDESHOW) - Inchangé
        // ========================================
        $slideshowNews = $this->newsModel->getSlideshowNews();
        
        // ========================================
        // ACTUALITÉS RÉCENTES - Limité à 5
        // ========================================
        $allNews = $this->newsModel->getPublishedNews();
        $recentNews = array_slice($allNews, 0, 4);
        
        // ========================================
        // ÉVÉNEMENTS - TOUS avec UPCOMING EN PREMIER
        // ========================================
        // Récupérer TOUS les événements
        $allEvents = $this->eventModel->getAllEvents();
        
        // Date/heure actuelle
        $now = time();
        
        // Séparer upcoming et past
        $upcomingEvents = [];
        $pastEvents = [];
        
        foreach ($allEvents as $event) {
            $eventDate = $event['date_debut'] ?? null;
            
            if ($eventDate) {
                $eventTimestamp = strtotime($eventDate);
                
                // ✅ UPCOMING = date future ET statut 'a_venir'
                if ($eventTimestamp > $now && ($event['statut'] ?? '') === 'a_venir') {
                    $upcomingEvents[] = $event;
                } else {
                    // ✅ PAST = tous les autres (passés, terminés, en cours, annulés)
                    $pastEvents[] = $event;
                }
            } else {
                // Pas de date = mettre dans past
                $pastEvents[] = $event;
            }
        }
        
        // ✅ Trier UPCOMING par date CROISSANTE (les plus proches en premier)
        usort($upcomingEvents, function($a, $b) {
            $dateA = strtotime($a['date_debut'] ?? '9999-12-31');
            $dateB = strtotime($b['date_debut'] ?? '9999-12-31');
            return $dateA - $dateB;
        });
        
        // ✅ Trier PAST par date DÉCROISSANTE (les plus récents en premier)
        usort($pastEvents, function($a, $b) {
            $dateA = strtotime($a['date_debut'] ?? '1970-01-01');
            $dateB = strtotime($b['date_debut'] ?? '1970-01-01');
            return $dateB - $dateA;
        });
        
        // ✅ COMBINER: UPCOMING D'ABORD, puis PAST
        $sortedEvents = $upcomingEvents;
        
        // Pagination
        $totalEvents = count($sortedEvents);
        $totalEventPages = ceil($totalEvents / $eventsPerPage);
        $offset = ($currentEventPage - 1) * $eventsPerPage;
        $paginatedEvents = array_slice($sortedEvents, $offset, $eventsPerPage);
        
        // ========================================
        // ÉQUIPE (ORGANIGRAMME) - Limité à 5
        // ========================================
        $allOrganigramUsers = $this->userModel->getUsersWithPoste();
        $organigramPreview = array_slice($allOrganigramUsers, 0, 4);
        
        // ========================================
        // PARTENAIRES - Limité à 5
        // ========================================
        $allPartners = $this->partnerModel->getAllPartners();
        $partners = array_slice($allPartners, 0, 5);
        
        // ========================================
        // PASSER LES DONNÉES À LA VUE
        // ========================================
        HomeView::render([
            // Paramètres du site
            'settings' => $settings,
            
            // Diaporama (inchangé)
            'slideshowNews' => $slideshowNews,
            
            // Actualités (5 premiers)
            'recentNews' => $recentNews,
            
            // ✅ Événements (paginés, UPCOMING EN PREMIER)
            'upcomingEvents' => $paginatedEvents,
            'currentEventPage' => $currentEventPage,
            'totalEventPages' => $totalEventPages,
            
            // Équipe (5 premiers)
            'organigramPreview' => $organigramPreview,
            
            // Partenaires (5 premiers)
            'partners' => $partners
        ]);
    }
}
?>