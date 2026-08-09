<?php
// controllers/TeamController.php
// Point 3 du PDF: Présentation, organigramme et équipes (1 pt)

class TeamController {
    private $teamModel;
    private $userModel;
    private $publicationModel;
    private $settingModel;
    private $userRoleModel;
    
    public function __construct() {
        $this->teamModel = new TeamModel();
        $this->userModel = new UserModel();

        $this->publicationModel = new PublicationModel();
        $this->settingModel = new SettingModel();
        $this->userRoleModel = new UserRoleModel();
    }
    
    /**
     * Page présentation, organigramme et équipes
     * Spécifications PDF:
     * - Présentation du laboratoire, des thèmes de recherche et de ces équipes (texte)
     * - Organigramme: directeur + membres avec postes
     * - Équipes: chef + membres avec poste, grade, photo
     * - Liens vers biographie et publications de chaque membre
     * - Lien vers publications de tous les membres de l'équipe
     * - Système de filtrage et tri
     */
    public function index() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        
        // Filtres
        $filterEquipe = isset($_GET['equipe']) && $_GET['equipe'] !== '' ? (int)$_GET['equipe'] : null;
        $filterRole = isset($_GET['role']) && $_GET['role'] !== '' ? $_GET['role'] : null;
        $sortBy = isset($_GET['sort']) && $_GET['sort'] !== '' ? $_GET['sort'] : 'nom';
        
        // Organigramme: Tous les utilisateurs avec postes, directeur en premier
        $organigram = $this->userModel->getUsersWithPoste();
        
        // Trier pour avoir le directeur en premier
        usort($organigram, function($a, $b) {
            if (stripos($a['poste'] ?? '', 'directeur') !== false) return -1;
            if (stripos($b['poste'] ?? '', 'directeur') !== false) return 1;
            return 0;
        });
        
        // Équipes de recherche
        $teams = $this->teamModel->getAllTeams();
        
        // Filtrer par équipe si demandé
        if ($filterEquipe) {
            $teams = array_filter($teams, function($t) use ($filterEquipe) {
                return $t['id_team'] == $filterEquipe;
            });
        }
        
        // Pour chaque équipe, récupérer chef, membres ET compter les publications
        foreach ($teams as &$team) {
            $members = $this->teamModel->getTeamMembers($team['id_team']);
            
            // Compter les publications de tous les membres de l'équipe
            $publicationCount = 0;
            foreach ($members as $member) {
                $publicationCount += $this->publicationModel->countUserPublications($member['id_user']);
            }
            $team['publication_count'] = $publicationCount;
            
            // Filtrer par rôle si demandé
            if ($filterRole) {
                $members = array_filter($members, function($m) use ($filterRole) {
                    $roles = strtolower($m['roles'] ?? '');
                    return stripos($roles, strtolower($filterRole)) !== false;
                });
            }
            
            // Trier les membres selon le critère choisi
            usort($members, function($a, $b) use ($sortBy) {
                switch ($sortBy) {
                    case 'grade':
                        return strcmp($a['grade'] ?? '', $b['grade'] ?? '');
                    case 'date':
                        return strcmp($b['date_adhesion'] ?? '', $a['date_adhesion'] ?? '');
                    case 'nom':
                    default:
                        $nomA = ($a['nom'] ?? '') . ' ' . ($a['prenom'] ?? '');
                        $nomB = ($b['nom'] ?? '') . ' ' . ($b['prenom'] ?? '');
                        return strcmp($nomA, $nomB);
                }
            });
            
            // Séparer le chef des autres membres
            $chef = null;
            $otherMembers = [];
            foreach ($members as $member) {
                if ($member['id_user'] == $team['id_chef']) {
                    $chef = $member;
                } else {
                    $otherMembers[] = $member;
                }
            }
            
            $team['chef'] = $chef;
            $team['membres'] = $otherMembers;
            $team['total_membres'] = count($members);
        }
        
        // Liste de toutes les équipes pour le filtre
        $allTeams = $this->teamModel->getAllTeams();
        
        TeamView::renderIndex([
            'settings' => $settings,
            'organigram' => $organigram,
            'teams' => $teams,
            'allTeams' => $allTeams,
            'filterEquipe' => $filterEquipe,
            'filterRole' => $filterRole,
            'sortBy' => $sortBy
        ]);
    }
    
    /**
     * Publications de tous les membres d'une équipe
     */
    public function publications($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $team = $this->teamModel->getTeamById($id);
        
        if (!$team) {
            header('Location: index.php?url=team');
            exit;
        }
        
        $members = $this->teamModel->getTeamMembers($id);
        
        // Récupérer toutes les publications des membres
        $allPublications = [];
        foreach ($members as $member) {
            $pubs = $this->publicationModel->getPublicationsByUser($member['id_user']);
            foreach ($pubs as $pub) {
                $pub['auteur_nom'] = $member['nom'] . ' ' . $member['prenom'];
                $allPublications[] = $pub;
            }
        }
        
        // Trier par année décroissante
        usort($allPublications, function($a, $b) {
            return $b['annee_publication'] <=> $a['annee_publication'];
        });
        
        TeamView::renderTeamPublications([
            'settings' => $settings,
            'team' => $team,
            'members' => $members,
            'publications' => $allPublications
        ]);
    }
    
    /**
     * Biographie et publications d'un membre
     */
    public function membre($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $membre = $this->userModel->getUserById($id);
        $roles = $this->userRoleModel->getUserRoles($id);
        
        if (!$membre) {
            header('Location: index.php?url=team');
            exit;
        }
        
        // Publications du membre
        $publications = $this->publicationModel->getPublicationsByUser($id);
        
        TeamView::renderMembreBiographie([
            'settings' => $settings,
            'membre' => $membre,
            'roles' => $roles,
            'publications' => $publications
        ]);
    }
}
?>