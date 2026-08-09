<?php
// controllers/PublicationController.php
// Point 6 du PDF: Base documentaire et publications (1 pt)
// Support du filtrage par utilisateur avec ?user=me

class PublicationController {
    private $publicationModel;
    private $userModel;
    private $settingModel;
    
    public function __construct() {
        $this->publicationModel = new PublicationModel();
        $this->userModel = new UserModel();
        $this->settingModel = new SettingModel();
    }
    
    /**
     * Base documentaire - Publications du laboratoire
     * Spécifications PDF:
     * - Publications: articles, rapports, thèses, communications, posters, etc.
     * - Chaque document: résumé, auteurs, date, DOI, lien téléchargement
     * - Filtres: année, auteur, type de publication, domaine
     * - Recherche avancée avec tri dynamique
     * 
     * ✅ Supporte ?user=me pour afficher uniquement les publications de l'utilisateur
     */
    public function index() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        
        // ✅ Détection du mode "mes publications"
        $filterByUser = isset($_GET['user']) && $_GET['user'] === 'me';
        $isAuthenticated = AuthController::isAuthenticated();
        
        // Redirection si non authentifié et demande "mes publications"
        if ($filterByUser && !$isAuthenticated) {
            header('Location: index.php?url=login&redirect=publications?user=me');
            exit;
        }
        
        // Filtres
        $filterAnnee = $_GET['annee'] ?? null;
        $filterAuteur = $_GET['auteur'] ?? null;
        $filterType = $_GET['type'] ?? null;
        $filterDomaine = $_GET['domaine'] ?? null;
        $search = $_GET['search'] ?? '';
        $tri = $_GET['tri'] ?? 'recent'; // recent, ancien, titre
        
        // ✅ Récupération conditionnelle des publications
        if ($filterByUser && $isAuthenticated) {
            // Uniquement les publications de l'utilisateur connecté
            $currentUser = AuthController::getCurrentUser();
            $publications = $this->publicationModel->getPublicationsByUser($currentUser['id_user']);
        } else {
            // Toutes les publications validées du laboratoire
            $publications = $this->publicationModel->getValidatedPublications();
        }
        
        // Appliquer les filtres (année, type, domaine, recherche)
        if ($filterAnnee) {
            $publications = array_filter($publications, function($p) use ($filterAnnee) {
                return ($p['annee_publication'] ?? 0) == $filterAnnee;
            });
        }
        
        if ($filterType) {
            $publications = array_filter($publications, function($p) use ($filterType) {
                return ($p['type_publication'] ?? '') === $filterType;
            });
        }
        
        if ($filterDomaine) {
            $publications = array_filter($publications, function($p) use ($filterDomaine) {
                return stripos($p['domaine'] ?? '', $filterDomaine) !== false;
            });
        }
        
        // Recherche avancée (titre, résumé, auteurs)
        if (!empty($search)) {
            $publications = array_filter($publications, function($p) use ($search) {
                return stripos($p['titre'] ?? '', $search) !== false || 
                       stripos($p['resume'] ?? '', $search) !== false ||
                       stripos($p['auteurs'] ?? '', $search) !== false;
            });
        }
        
        // Filtrer par auteur (seulement si pas en mode "mes publications")
        if ($filterAuteur && !$filterByUser) {
            $filteredPubs = [];
            foreach ($publications as $pub) {
                $auteurs = $this->publicationModel->getPublicationAuthors($pub['id_publication']);
                foreach ($auteurs as $auteur) {
                    if ($auteur['id_user'] == $filterAuteur) {
                        $filteredPubs[] = $pub;
                        break;
                    }
                }
            }
            $publications = $filteredPubs;
        }
        
        // Tri dynamique
        usort($publications, function($a, $b) use ($tri) {
            switch ($tri) {
                case 'ancien':
                    return ($a['annee_publication'] ?? 0) <=> ($b['annee_publication'] ?? 0);
                case 'titre':
                    return strcasecmp($a['titre'] ?? '', $b['titre'] ?? '');
                case 'recent':
                default:
                    return ($b['annee_publication'] ?? 0) <=> ($a['annee_publication'] ?? 0);
            }
        });
        
        // Listes pour les filtres
        $allPublications = $this->publicationModel->getValidatedPublications();
        $annees = array_unique(array_filter(array_column($allPublications, 'annee_publication')));
        rsort($annees);
        
        $types = ['article', 'rapport', 'these', 'communication', 'poster', 'autre'];
        
        $domaines = array_unique(array_filter(array_column($allPublications, 'domaine')));
        sort($domaines);
        
        $auteurs = $this->userModel->getAllUsers();
        
        PublicationView::renderBase([
            'settings' => $settings,
            'publications' => $publications,
            'annees' => $annees,
            'types' => $types,
            'domaines' => $domaines,
            'auteurs' => $auteurs,
            'filterAnnee' => $filterAnnee,
            'filterAuteur' => $filterAuteur,
            'filterType' => $filterType,
            'filterDomaine' => $filterDomaine,
            'search' => $search,
            'tri' => $tri,
            'filterByUser' => $filterByUser,
            'isAuthenticated' => $isAuthenticated,
            'currentUserId' => $isAuthenticated ? AuthController::getCurrentUser()['id_user'] : null
        ]);
    }
    
    /**
     * Détail d'une publication avec DOI et téléchargement
     */
    public function detail($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $publication = $this->publicationModel->getPublicationById($id);
        $isAuthenticated = AuthController::isAuthenticated();
        
        if (!$publication || $publication['statut'] !== 'valide') {
            header('Location: index.php?url=publications&error=not_found');
            exit;
        }
        
        // Récupérer les auteurs
        $auteurs = $this->publicationModel->getPublicationAuthors($id);
        
        // Vérifier si l'utilisateur est auteur
        $isAuthor = false;
        if ($isAuthenticated) {
            $currentUser = AuthController::getCurrentUser();
            $isAuthor = $this->publicationModel->isUserAuthor($id, $currentUser['id_user']);
        }
        
        PublicationView::renderDetail([
            'settings' => $settings,
            'publication' => $publication,
            'auteurs' => $auteurs,
            'isAuthenticated' => $isAuthenticated,
            'isAuthor' => $isAuthor,
            'currentUserId' => $isAuthenticated ? AuthController::getCurrentUser()['id_user'] : null
        ]);
    }
    
    /**
     * Télécharger une publication
     */
    public function download($id) {
        $publication = $this->publicationModel->getPublicationById($id);
        
        if (!$publication || $publication['statut'] !== 'valide') {
            header('Location: index.php?url=publications&error=not_found');
            exit;
        }
        
        $file = $publication['fichier'] ?? null;
        if (!$file || !file_exists('assets/documents/publications/' . $file)) {
            header('Location: index.php?url=publication/detail/' . $id . '&error=file_not_found');
            exit;
        }
        
        // Forcer le téléchargement
        $filepath = 'assets/documents/publications/' . $file;
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }
}
?>