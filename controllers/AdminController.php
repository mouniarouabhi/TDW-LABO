<?php

class AdminController {
    private $userModel;
    private $teamModel;
    private $projectModel;
    private $publicationModel;
    private $eventModel;
    private $equipmentModel;
    private $reservationModel;
    private $settingModel;
    private $roleModel;
    private $permissionModel;
    private $rolePermissionModel;
    private $userRoleModel;
    private $notificationModel;
    private $reportModel;
    private $partnerModel;
    private $projectPartnerModel;
    private $opportunityModel;
    private $contactModel;
    private $newsModel;
    private $documentModel;
    private $databaseModel;
    
    public function __construct() {
        AuthController::requireAdmin();
        
        $this->userModel = new UserModel();
        $this->teamModel = new TeamModel();
        $this->projectModel = new ProjectModel();
        $this->publicationModel = new PublicationModel();
        $this->eventModel = new EventModel();
        $this->equipmentModel = new EquipmentModel();
        $this->reservationModel = new ReservationModel();
        $this->settingModel = new SettingModel();
        $this->roleModel = new RoleModel();
        $this->permissionModel = new PermissionModel();
        $this->rolePermissionModel = new RolePermissionModel();
        $this->userRoleModel = new UserRoleModel();
        $this->notificationModel = new NotificationModel();
        $this->reportModel = new ReportModel();
        $this->partnerModel = new PartnerModel();
        $this->projectPartnerModel = new ProjectPartnerModel();
        $this->opportunityModel = new OpportunityModel();
        $this->contactModel = new ContactModel();
        $this->newsModel = new NewsModel();
        $this->documentModel = new DocumentModel();
        $this->databaseModel = new Database();
    }

    private function utf8ToIso($text) {
        if (empty($text)) return '';
        
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
        }
        
        if (function_exists('iconv')) {
            return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
        }
        
        if (function_exists('utf8_decode')) {
            return $this->utf8ToIso($text);
        }
        
        return $text;
    }
    
    /* ========================================
       DASHBOARD ADMINISTRATEUR
       ======================================== */
    
    /**
     * Tableau de bord administrateur
     */
    public function index() {
        $settings = $this->settingModel->getAllSettingsAsArray();
                $stats = [
            'total_users' => count($this->userModel->getAllUsers()),
            'total_teams' => count($this->teamModel->getAllTeams()),
            'total_projects' => count($this->projectModel->getAllProjects()),
            'total_publications' => count($this->publicationModel->getAllPublications()),
            'total_events' => count($this->eventModel->getAllEvents()),
            'total_equipments' => count($this->equipmentModel->getAllEquipments()),
            'enseignants' => $this->userModel->countUsersByRole('enseignant'),
            'chercheurs' => $this->userModel->countUsersByRole('chercheur'),
            'doctorants' => $this->userModel->countUsersByRole('doctorant'),
            'etudiants' => $this->userModel->countUsersByRole('etudiant'),
            'news' => count($this->newsModel->getAllNews())
        ];
        
        $recentUsers = array_slice($this->userModel->getAllUsers(), 0, 5);
        $recentProjects = $this->projectModel->getLatestProjects(5);
        $upcomingEvents = $this->eventModel->getUpcomingEvents();
        
        $pendingPublications = array_filter(
            $this->publicationModel->getAllPublications(),
            fn($p) => $p['statut'] === 'en_attente'
        );
        
        $activeReservations = $this->reservationModel->getActiveReservations();
        
        AdminDashboardView::render([
            'settings' => $settings,
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentProjects' => $recentProjects,
            'upcomingEvents' => array_slice($upcomingEvents, 0, 5),
            'pendingPublications' => array_slice($pendingPublications, 0, 5),
            'activeReservations' => array_slice($activeReservations, 0, 5)
        ]);
    }
    
    /* ========================================
       1. GESTION DES UTILISATEURS ET RÔLES
       ======================================== */  
    /**
     * Ajouter un utilisateur
     */
    public function addUser() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $roles = $this->roleModel->getAllRoles();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'login' => $_POST['login'] ?? '',
                'password' => $_POST['password'] ?? '',
                'nom' => $_POST['nom'] ?? '',
                'prenom' => $_POST['prenom'] ?? '',
                'email' => $_POST['email'] ?? '',
                'grade' => $_POST['grade'] ?? null,
                'poste' => $_POST['poste'] ?? null,
                'domaine_recherche' => $_POST['domaine_recherche'] ?? null,
                'photo' => 'default.jpg',
                'statut' => $_POST['statut'] ?? 'actif'
            ];
            
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
                $uploadDir = 'assets/images/users/';
                $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('user_') . '.' . $extension;
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                    $data['photo'] = $filename;
                }
            }
            
            if ($this->userModel->createUser($data)) {
                $userId = $this->userModel->getUserByLogin($data['login'])['id_user'];
                
                // Get the role ID from the role name
                if (!empty($_POST['role'])) {
                    $roleName = $_POST['role']; // e.g., "chercheur"
                    
                    // Find the role by name
                    $role = $this->roleModel->getRoleByName($roleName);
                    
                    if ($role) {
                        $this->userRoleModel->assignRole($userId, $role['id_role']);
                    }
                }
                
                header('Location: index.php?url=admin/users&success=created');
                exit;
            } else {
                $error = "Erreur lors de la création de l'utilisateur";
            }
        }
        
        AdminUserView::renderForm([
            'settings' => $settings,
            'action' => 'add',
            'roles' => $roles,
            'error' => $error ?? null
        ]);
    }

    /**
     * Afficher les détails complets d'un utilisateur avec ses documents
     */
    public function viewUser($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        
        $user = $this->userModel->getUserById($id);
        
        if (!$user) {
            header('Location: index.php?url=admin/users&error=not_found');
            exit;
        }

        $userRoles = $this->userRoleModel->getUserRoles($id);

        if (!isset($this->documentModel)) {
            $this->documentModel = new DocumentModel();
        }
        $documents = $this->documentModel->getUserDocuments($id);

        $teams = $this->teamModel->getUserTeams($id);

        $projects = $this->projectModel->getUserProjects($id);

        $publications = $this->publicationModel->getPublicationsByUser($id);

        $stats = [
            'projects' => count($projects),
            'publications' => count($publications),
            'documents' => count($documents),
            'teams' => count($teams)
        ];
        
        AdminUserView::renderDetails([
            'settings' => $settings,
            'user' => $user,
            'roles' => $userRoles,
            'documents' => $documents,
            'teams' => $teams,
            'projects' => $projects,
            'publications' => $publications,
            'stats' => $stats
        ]);
    }

    /**
     * Liste des utilisateurs avec filtres avancés
     */
    public function users() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $users = $this->userModel->getAllUsers();
        
        if (isset($_GET['role']) && !empty($_GET['role'])) {
            $users = $this->userModel->getUsersByRole($_GET['role']);
        }
        
        if (isset($_GET['statut']) && !empty($_GET['statut'])) {
            $users = array_filter($users, fn($u) => $u['statut'] === $_GET['statut']);
        }

        foreach ($users as &$user) {
            $userRoles = $this->userRoleModel->getUserRoles($user['id_user']);
            $user['roles'] = $userRoles;

            if (!empty($userRoles)) {
                $user['role'] = $userRoles[0]['nom_role'] 
                            ?? $userRoles[0]['nom'] 
                            ?? $userRoles[0]['role_name'] 
                            ?? 'utilisateur';
            } else {
                $user['role'] = $user['role'] ?? 'utilisateur';
            }
            
            $user['nb_publications'] = $this->publicationModel->countUserPublications($user['id_user']);
            $user['nb_projects'] = $this->projectModel->countUserProjects($user['id_user']);
        }

        $stats = [
            'total' => count($users),
            'actifs' => count(array_filter($users, fn($u) => $u['statut'] === 'actif')),
            'chercheurs' => count(array_filter($users, fn($u) => ($u['role'] ?? '') === 'chercheur')),
            'inactifs' => count(array_filter($users, fn($u) => $u['statut'] !== 'actif'))
        ];
        
        $roles = $this->roleModel->getAllRoles();
        
        AdminUserView::renderList([
            'settings' => $settings,
            'users' => $users,
            'stats' => $stats,
            'roles' => $roles,
            'filterRole' => $_GET['role'] ?? null,
            'filterStatut' => $_GET['statut'] ?? null
        ]);
    }

    /* ========================================
    TÉLÉCHARGER UN DOCUMENT UTILISATEUR
    ======================================== */

    public function downloadUserDocument($id) {
        if (!isset($this->documentModel)) {
            $this->documentModel = new DocumentModel();
        }
        
        $document = $this->documentModel->getDocumentById($id);
        
        if (!$document) {
            header('Location: index.php?url=admin/users&error=document_not_found');
            exit;
        }
        
        $filepath = __DIR__ . '/../assets/documents/documents/' . $document['nom_fichier'];
        
        if (!file_exists($filepath)) {
            header('Location: index.php?url=admin/viewUser/' . $document['id_user'] . '&error=file_not_found');
            exit;
        }
        
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'txt' => 'text/plain',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed'
        ];
        
        $contentType = $mimeTypes[$document['extension']] ?? 'application/octet-stream';
        
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $document['nom_original'] . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');
        
        readfile($filepath);
        exit;
    }

    /**
     * Supprimer un document d'un utilisateur
     */
    public function deleteUserDocument($id) {
        if (!isset($this->documentModel)) {
            $this->documentModel = new DocumentModel();
        }
        
        $document = $this->documentModel->getDocumentById($id);
        
        if (!$document) {
            header('Location: index.php?url=admin/users&error=document_not_found');
            exit;
        }
        
        $userId = $document['id_user'];
        
        $filepath = __DIR__ . '/../assets/documents/documents/' . $document['nom_fichier'];
        if (file_exists($filepath)) {
            @unlink($filepath);
        }
        
        if ($this->documentModel->permanentDeleteDocument($id)) {
            header('Location: index.php?url=admin/viewUser/' . $userId . '&success=document_deleted');
        } else {
            header('Location: index.php?url=admin/viewUser/' . $userId . '&error=delete_failed');
        }
        exit;
    }
    
    /**
     * Modifier un utilisateur
     */

    public function editUser($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $user = $this->userModel->getUserById($id);
        $roles = $this->roleModel->getAllRoles();
        $userRoles = $this->userRoleModel->getUserRoles($id);
        
        if (!$user) {
            header('Location: index.php?url=admin/users&error=not_found');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom' => $_POST['nom'] ?? '',
                'prenom' => $_POST['prenom'] ?? '',
                'email' => $_POST['email'] ?? '',
                'grade' => $_POST['grade'] ?? null,
                'poste' => $_POST['poste'] ?? null,
                'domaine_recherche' => $_POST['domaine_recherche'] ?? null,
                'biographie' => $_POST['biographie'] ?? null,
                'statut' => $_POST['statut'] ?? 'actif',
                'photo' => $user['photo']
            ];
            
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
                $uploadDir = 'assets/images/users/';
                $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('user_') . '.' . $extension;
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                    $data['photo'] = $filename;
                }
            }
            
            if (!empty($_POST['password'])) {
                $data['password'] = $_POST['password'];
            }
            
            if ($this->userModel->updateUser($id, $data)) {
                // ✅ CORRECTION - Gérer le rôle unique envoyé par le formulaire
                $this->userRoleModel->removeAllRoles($id);
                
                if (!empty($_POST['role'])) {
                    $roleName = $_POST['role']; // Ex: "chercheur", "enseignant"
                    
                    // Trouver l'ID du rôle par son nom
                    $role = $this->roleModel->getRoleByName($roleName);
                    
                    if ($role) {
                        $this->userRoleModel->assignRole($id, $role['id_role']);
                    }
                }
                
                header('Location: index.php?url=admin/users&success=updated');
                exit;
            } else {
                $error = "Erreur lors de la mise à jour";
            }
        }
        
        AdminUserView::renderForm([
            'settings' => $settings,
            'action' => 'edit',
            'user' => $user,
            'roles' => $roles,
            'userRoles' => array_column($userRoles, 'id_role'),
            'error' => $error ?? null
        ]);
    }
    
    /**
     * Suspendre/Activer un utilisateur
     */
    public function toggleUserStatus($id) {
        if ($id == 1) {
            header('Location: index.php?url=admin/users&error=cannot_modify_admin');
            exit;
        }
        
        $user = $this->userModel->getUserById($id);
        if ($user) {
            $newStatut = $user['statut'] === 'actif' ? 'suspendu' : 'actif';
            $data = array_merge($user, ['statut' => $newStatut]);
            
            if ($this->userModel->updateUser($id, $data)) {
                header('Location: index.php?url=admin/users&success=status_updated');
            } else {
                header('Location: index.php?url=admin/users&error=status_failed');
            }
        }
        exit;
    }
    
    /**
     * Supprimer un utilisateur
     */
    public function deleteUser($id) {
        if ($id == 1) {
            header('Location: index.php?url=admin/users&error=cannot_delete_admin');
            exit;
        }
        
        if ($this->userModel->deleteUser($id)) {
            header('Location: index.php?url=admin/users&success=deleted');
        } else {
            header('Location: index.php?url=admin/users&error=delete_failed');
        }
        exit;
    }
    
    /**
    * Gestion des rôles - Liste
    */

    public function roles() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $roles = $this->roleModel->getAllRoles();
        
        foreach ($roles as &$role) {
            $role['user_count'] = $this->userModel->countUsersByRole($role['nom']);
            $role['permissions'] = $this->rolePermissionModel->getPermissionsByRole($role['id_role']);
        }
        
        $allPermissions = $this->permissionModel->getAllPermissions();
        
        AdminRoleView::renderList([
            'settings' => $settings,
            'roles' => $roles,
            'allPermissions' => $allPermissions
        ]);
    }
    
    /**
     * Ajouter un rôle
     */
    public function addRole() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $permissions = $this->permissionModel->getAllPermissions();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = $_POST['nom'] ?? '';
            $description = $_POST['description'] ?? null;
            
            if ($this->roleModel->createRole($nom, $description)) {
                $newRole = $this->roleModel->getAllRoles();
                $roleId = end($newRole)['id_role'];
                
                if (!empty($_POST['permissions']) && is_array($_POST['permissions'])) {
                    foreach ($_POST['permissions'] as $permId) {
                        $this->rolePermissionModel->assignPermissionToRole($roleId, $permId);
                    }
                }
                
                header('Location: index.php?url=admin/roles&success=created');
                exit;
            } else {
                $error = "Erreur lors de la création du rôle";
            }
        }
        
        AdminRoleView::renderForm([
            'settings' => $settings,
            'action' => 'add',
            'permissions' => $permissions,
            'error' => $error ?? null
        ]);
    }
    
    /**
     * Modifier un rôle
     */
    public function editRole($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $role = $this->roleModel->getRoleById($id);
        $permissions = $this->permissionModel->getAllPermissions();
        $rolePermissions = $this->rolePermissionModel->getPermissionsByRole($id);
        
        if (!$role) {
            header('Location: index.php?url=admin/roles&error=not_found');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = $_POST['nom'] ?? '';
            $description = $_POST['description'] ?? null;
            
            if ($this->roleModel->updateRole($id, $nom, $description)) {
                $this->rolePermissionModel->removeAllPermissionsFromRole($id);
                if (!empty($_POST['permissions']) && is_array($_POST['permissions'])) {
                    foreach ($_POST['permissions'] as $permId) {
                        $this->rolePermissionModel->assignPermissionToRole($id, $permId);
                    }
                }
                
                header('Location: index.php?url=admin/roles&success=updated');
                exit;
            } else {
                $error = "Erreur lors de la mise à jour";
            }
        }
        
        AdminRoleView::renderForm([
            'settings' => $settings,
            'action' => 'edit',
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => array_column($rolePermissions, 'id_permission'),
            'error' => $error ?? null
        ]);
    }
    
    /**
     * Supprimer un rôle
     */
    public function deleteRole($id) {
        if ($id <= 6) {
            header('Location: index.php?url=admin/roles&error=cannot_delete_system_role');
            exit;
        }
        
        if ($this->roleModel->deleteRole($id)) {
            header('Location: index.php?url=admin/roles&success=deleted');
        } else {
            header('Location: index.php?url=admin/roles&error=delete_failed');
        }
        exit;
    }

    public function updateRolePermissions($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=admin/roles');
            exit;
        }
        
        $permissions = $_POST['permissions'] ?? [];
        
        $this->rolePermissionModel->removeAllPermissionsFromRole($id);
        
        if (!empty($permissions)) {
            foreach ($permissions as $permissionKey) {
                $permission = $this->permissionModel->getPermissionByName($permissionKey);
                
                if ($permission) {
                    $this->rolePermissionModel->assignPermissionToRole($id, $permission['id_permission']);
                } else {
                    $permId = $this->permissionModel->createPermission(
                        $permissionKey,
                        ucfirst(str_replace('_', ' ', $permissionKey))
                    );
                    
                    if ($permId) {
                        $this->rolePermissionModel->assignPermissionToRole($id, $permId);
                    }
                }
            }
        }
        
        header('Location: index.php?url=admin/roles&success=permissions_saved');
        exit;
    }

    /**
     * Mettre à jour uniquement la description d'un rôle
     */
    public function updateRoleDescription($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=admin/roles');
            exit;
        }
        
        $description = $_POST['description'] ?? '';
        
        $role = $this->roleModel->getRoleById($id);
        
        if (!$role) {
            header('Location: index.php?url=admin/roles&error=not_found');
            exit;
        }
        
        if ($this->roleModel->updateRole($id, $role['nom'], $description)) {
            header('Location: index.php?url=admin/roles&success=updated');
        } else {
            header('Location: index.php?url=admin/roles&error=update_failed');
        }
        exit;
    }

    /**
    * Gérer les permissions - Liste
    */
    public function permissions() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $permissions = $this->permissionModel->getAllPermissions();
        
        foreach ($permissions as &$perm) {
            $perm['role_count'] = $this->rolePermissionModel->countRolesWithPermission($perm['id_permission']);
        }
        
        AdminPermissionView::renderList([
            'settings' => $settings,
            'permissions' => $permissions
        ]);
    }

    /**
     * Ajouter une permission
     */
    public function addPermission() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = strtoupper(trim($_POST['code'] ?? ''));
            $description = trim($_POST['description'] ?? '');
            
            if (empty($code) || empty($description)) {
                $error = "Le code et la description sont obligatoires";
            } elseif ($this->permissionModel->getPermissionByName($code)) {
                $error = "Cette permission existe déjà";
            } else {
                if ($this->permissionModel->createPermission($code, $description)) {
                    header('Location: index.php?url=admin/permissions&success=created');
                    exit;
                } else {
                    $error = "Erreur lors de la création de la permission";
                }
            }
        }
        
        AdminPermissionView::renderForm([
            'settings' => $settings,
            'action' => 'add',
            'error' => $error ?? null
        ]);
    }

    /**
     * Modifier une permission
     */
    public function editPermission($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $permission = $this->permissionModel->getPermissionById($id);
        
        if (!$permission) {
            header('Location: index.php?url=admin/permissions&error=not_found');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $description = trim($_POST['description'] ?? '');
            
            if (empty($description)) {
                $error = "La description est obligatoire";
            } else {
                if ($this->permissionModel->updatePermission($id, $permission['code'], $description)) {
                    header('Location: index.php?url=admin/permissions&success=updated');
                    exit;
                } else {
                    $error = "Erreur lors de la mise à jour";
                }
            }
        }
        
        AdminPermissionView::renderForm([
            'settings' => $settings,
            'action' => 'edit',
            'permission' => $permission,
            'error' => $error ?? null
        ]);
    }

    /**
    * Supprimer une permission
    */
    public function deletePermission($id) {
        $permission = $this->permissionModel->getPermissionById($id);
        
        if (!$permission) {
            header('Location: index.php?url=admin/permissions&error=not_found');
            exit;
        }
        
        $roleCount = $this->rolePermissionModel->countRolesWithPermission($id);
        
        if ($roleCount > 0) {
            header('Location: index.php?url=admin/permissions&error=permission_in_use&count=' . $roleCount);
            exit;
        }
        
        if ($this->permissionModel->deletePermission($id)) {
            header('Location: index.php?url=admin/permissions&success=deleted');
        } else {
            header('Location: index.php?url=admin/permissions&error=delete_failed');
        }
        exit;
    }
    
    /* ========================================
       2. GESTION DES ÉQUIPES
       ======================================== */
    
    /**
    * Liste des équipes
    */
    public function teams() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $teams = $this->teamModel->getAllTeams();
        
        foreach ($teams as &$team) {
            $team['member_count'] = $this->teamModel->countTeamMembers($team['id_team']);
        }
        
        AdminTeamView::renderList([
            'settings' => $settings,
            'teams' => $teams
        ]);
    }
    
    /**
     * Ajouter une équipe
     */
    public function addTeam() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $users = $this->userModel->getAllUsers();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom_equipe' => $_POST['nom_equipe'] ?? '',
                'description' => $_POST['description'] ?? '',
                'thematique' => $_POST['thematique'] ?? '',
                'id_chef' => $_POST['id_chef'] ?? null
            ];
            
            if ($this->teamModel->createTeam($data)) {
                header('Location: index.php?url=admin/teams&success=created');
                exit;
            } else {
                $error = "Erreur lors de la création de l'équipe";
            }
        }
        
        AdminTeamView::renderForm([
            'settings' => $settings,
            'action' => 'add',
            'users' => $users,
            'error' => $error ?? null
        ]);
    }
    
    /**
     * Modifier une équipe
    */

    public function editTeam($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $team = $this->teamModel->getTeamById($id);
        $users = $this->userModel->getAllUsers();

        foreach ($users as &$user) {
            $userRoles = $this->userRoleModel->getUserRoles($user['id_user']);
            
            if (!empty($userRoles)) {
                $user['role'] = $userRoles[0]['nom_role'] 
                            ?? $userRoles[0]['nom'] 
                            ?? $userRoles[0]['role_name'] 
                            ?? 'utilisateur';
            } else {
                $user['role'] = $user['role'] ?? 'utilisateur';
            }
        }

        $members = $this->teamModel->getTeamMembers($id);
        
        foreach ($members as &$member) {
            if (empty($member['role'])) {
                $memberRoles = $this->userRoleModel->getUserRoles($member['id_user']);
                
                if (!empty($memberRoles)) {
                    $member['role'] = $memberRoles[0]['nom_role'] 
                                ?? $memberRoles[0]['nom'] 
                                ?? 'utilisateur';
                } else {
                    $member['role'] = 'utilisateur';
                }
            }
        }
        
        if (!$team) {
            header('Location: index.php?url=admin/teams&error=not_found');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom_equipe' => $_POST['nom_equipe'] ?? '',
                'description' => $_POST['description'] ?? '',
                'thematique' => $_POST['thematique'] ?? '',
                'id_chef' => $_POST['id_chef'] ?? null
            ];
            
            if ($this->teamModel->updateTeam($id, $data)) {
                header('Location: index.php?url=admin/teams&success=updated');
                exit;
            } else {
                $error = "Erreur lors de la mise à jour";
            }
        }
        
        AdminTeamView::renderForm([
            'settings' => $settings,
            'action' => 'edit',
            'team' => $team,
            'users' => $users,
            'members' => $members,
            'error' => $error ?? null
        ]);
    }
    
    /**
    * Ajouter un membre à une équipe
    */
    public function addTeamMember($teamId) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['id_user'] ?? null;
            
            if ($userId && $this->teamModel->addMemberToTeam($teamId, $userId)) {
                header('Location: index.php?url=admin/editTeam/' . $teamId . '&success=member_added');
            } else {
                header('Location: index.php?url=admin/editTeam/' . $teamId . '&error=add_failed');
            }
        }
        exit;
    }
    
    /**
    * Retirer un membre d'une équipe
    */
    public function removeTeamMember($teamId, $userId) {
        if ($this->teamModel->removeMemberFromTeam($teamId, $userId)) {
            header('Location: index.php?url=admin/editTeam/' . $teamId . '&success=member_removed');
        } else {
            header('Location: index.php?url=admin/editTeam/' . $teamId . '&error=remove_failed');
        }
        exit;
    }
    
    /**
    * Supprimer une équipe
    */
    public function deleteTeam($id) {
        if ($this->teamModel->deleteTeam($id)) {
            header('Location: index.php?url=admin/teams&success=deleted');
        } else {
            header('Location: index.php?url=admin/teams&error=delete_failed');
        }
        exit;
    }
    
    /* ========================================
       3. GESTION DES PROJETS
    ======================================== */
    
    /**
    * Liste des projets avec statistiques
    */
    public function projects() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $projects = $this->projectModel->getAllProjects();

        $filterThematique = $_GET['thematique'] ?? null;
        $filterStatut = $_GET['statut'] ?? null;
        $filterAnnee = $_GET['annee'] ?? null;

        if ($filterThematique) {
            $projects = array_filter($projects, fn($p) => $p['thematique'] === $filterThematique);
        }
        
        if ($filterStatut) {
            $projects = array_filter($projects, fn($p) => $p['statut'] === $filterStatut);
        }
        
        if ($filterAnnee) {
            $projects = array_filter($projects, function($p) use ($filterAnnee) {
                if (empty($p['date_debut'])) return false;
                return date('Y', strtotime($p['date_debut'])) == $filterAnnee;
            });
        }

        $stats = [
            'total' => count($projects),
            'en_cours' => count(array_filter($projects, fn($p) => $p['statut'] === 'en_cours')),
            'termine' => count(array_filter($projects, fn($p) => $p['statut'] === 'termine')),
            'soumis' => count(array_filter($projects, fn($p) => $p['statut'] === 'soumis'))
        ];

        $thematiques = [];
        foreach ($this->projectModel->getAllProjects() as $project) {
            $them = $project['thematique'] ?? 'Autre';
            $thematiques[$them] = ($thematiques[$them] ?? 0) + 1;
        }
        arsort($thematiques);
        
        AdminProjectView::renderList([
            'settings' => $settings,
            'projects' => $projects,
            'stats' => $stats,
            'thematiques' => $thematiques
        ]);
    }

    /**
    * Voir les détails COMPLETS d'un projet
    */
    public function viewProject($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            header('Location: index.php?url=admin/projects&error=not_found');
            exit;
        }

        $partners = $this->projectPartnerModel->getProjectPartners($id);

        $members = $this->projectModel->getProjectMembers($id);

        $allProjects = $this->projectModel->getAllProjects();

        $byThematique = [];
        foreach ($allProjects as $p) {
            $them = $p['thematique'] ?? 'Non spécifié';
            $byThematique[$them] = ($byThematique[$them] ?? 0) + 1;
        }
        arsort($byThematique);

        $byEncadrant = [];
        foreach ($allProjects as $p) {
            if (!empty($p['enc_nom'])) {
                $name = $p['enc_nom'] . ' ' . $p['enc_prenom'];
                $byEncadrant[$name] = ($byEncadrant[$name] ?? 0) + 1;
            }
        }
        arsort($byEncadrant);

        $byYear = [];
        foreach ($allProjects as $p) {
            if (!empty($p['date_debut'])) {
                $year = date('Y', strtotime($p['date_debut']));
                $byYear[$year] = ($byYear[$year] ?? 0) + 1;
            }
        }
        krsort($byYear);
        
        $globalStats = [
            'byThematique' => array_slice($byThematique, 0, 10), // Top 10
            'byEncadrant' => array_slice($byEncadrant, 0, 10),
            'byYear' => $byYear
        ];
        
        AdminProjectView::renderDetails([
            'settings' => $settings,
            'project' => $project,
            'partners' => $partners,
            'members' => $members,
            'globalStats' => $globalStats
        ]);
    }

    /**
     * Ajouter un projet
     * REMPLACER pour inclure id_encadrant
     */
    public function addProject() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $users = $this->userModel->getAllUsers();

        foreach ($users as &$user) {
            $userRoles = $this->userRoleModel->getUserRoles($user['id_user']);
            $user['roles'] = array_column($userRoles, 'nom_role') ?: array_column($userRoles, 'nom');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'titre' => $_POST['titre'] ?? '',
                'description' => $_POST['description'] ?? '',
                'thematique' => $_POST['thematique'] ?? '',
                'type_financement' => $_POST['type_financement'] ?? '',
                'statut' => $_POST['statut'] ?? 'en_cours',
                'id_responsable' => $_POST['id_responsable'] ?? null,
                'id_encadrant' => $_POST['id_encadrant'] ?? null,
                'date_debut' => $_POST['date_debut'] ?? null,
                'date_fin' => $_POST['date_fin'] ?? null,
                'budget' => $_POST['budget'] ?? null
            ];
            
            if ($this->projectModel->createProject($data)) {
                header('Location: index.php?url=admin/projects&success=created');
                exit;
            } else {
                $error = "Erreur lors de la création du projet";
            }
        }
        
        AdminProjectView::renderForm([
            'settings' => $settings,
            'action' => 'add',
            'users' => $users,
            'error' => $error ?? null
        ]);
    }

    /**
     * Modifier un projet
     * REMPLACER pour inclure id_encadrant
     */
    public function editProject($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $project = $this->projectModel->getProjectById($id);
        $users = $this->userModel->getAllUsers();

        foreach ($users as &$user) {
            $userRoles = $this->userRoleModel->getUserRoles($user['id_user']);
            $user['roles'] = array_column($userRoles, 'nom_role') ?: array_column($userRoles, 'nom');
        }
        
        if (!$project) {
            header('Location: index.php?url=admin/projects&error=not_found');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'titre' => $_POST['titre'] ?? '',
                'description' => $_POST['description'] ?? '',
                'thematique' => $_POST['thematique'] ?? '',
                'type_financement' => $_POST['type_financement'] ?? '',
                'statut' => $_POST['statut'] ?? 'en_cours',
                'id_responsable' => $_POST['id_responsable'] ?? null,
                'id_encadrant' => $_POST['id_encadrant'] ?? null,
                'date_debut' => $_POST['date_debut'] ?? null,
                'date_fin' => $_POST['date_fin'] ?? null,
                'budget' => $_POST['budget'] ?? null
            ];
            
            if ($this->projectModel->updateProject($id, $data)) {
                header('Location: index.php?url=admin/projects&success=updated');
                exit;
            } else {
                $error = "Erreur lors de la mise à jour";
            }
        }
        
        AdminProjectView::renderForm([
            'settings' => $settings,
            'action' => 'edit',
            'project' => $project,
            'users' => $users,
            'error' => $error ?? null
        ]);
    }

    /**
    * Supprimer un projet
    */
    public function deleteProject($id) {
        if ($this->projectModel->deleteProject($id)) {
            header('Location: index.php?url=admin/projects&success=deleted');
        } else {
            header('Location: index.php?url=admin/projects&error=delete_failed');
        }
        exit;
    }

    /**
    * Afficher le formulaire d'ajout de membre à un projet
    */
    public function addProjectMember($projectId) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $project = $this->projectModel->getProjectById($projectId);
        
        if (!$project) {
            header('Location: index.php?url=admin/projects&error=not_found');
            exit;
        }

        $currentMembers = $this->projectModel->getProjectMembers($projectId);
        $currentMemberIds = array_column($currentMembers, 'id_user');

        $allUsers = $this->userModel->getAllUsers();

        foreach ($allUsers as &$user) {
            $userRoles = $this->userRoleModel->getUserRoles($user['id_user']);
            $user['roles'] = array_column($userRoles, 'nom_role') ?: array_column($userRoles, 'nom');
        }

        $availableUsers = array_filter($allUsers, function($user) use ($currentMemberIds) {
            return !in_array($user['id_user'], $currentMemberIds);
        });
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['id_user'] ?? null;
            $roleProjet = $_POST['role_projet'] ?? 'Membre';
            
            if (empty($userId)) {
                $error = "Veuillez sélectionner un utilisateur";
            } elseif ($this->projectModel->addMemberToProject($projectId, $userId, $roleProjet)) {
                header('Location: index.php?url=admin/viewProject/' . $projectId . '&success=member_added');
                exit;
            } else {
                $error = "Erreur lors de l'ajout du membre (doublon possible)";
            }
        }
        
        AdminProjectView::renderAddMemberForm([
            'settings' => $settings,
            'project' => $project,
            'availableUsers' => $availableUsers,
            'error' => $error ?? null
        ]);
    }

    /**
    * Retirer un membre d'un projet
    */
    public function removeProjectMember($projectId, $userId) {
        if ($this->projectModel->removeMemberFromProject($projectId, $userId)) {
            header('Location: index.php?url=admin/viewProject/' . $projectId . '&success=member_removed');
        } else {
            header('Location: index.php?url=admin/viewProject/' . $projectId . '&error=remove_failed');
        }
        exit;
    }

    /**
    * Modifier le rôle d'un membre dans un projet
    */
    public function editProjectMemberRole($projectId, $userId) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $roleProjet = $_POST['role_projet'] ?? 'Membre';
            
            if ($this->projectModel->updateMemberRole($projectId, $userId, $roleProjet)) {
                header('Location: index.php?url=admin/viewProject/' . $projectId . '&success=role_updated');
            } else {
                header('Location: index.php?url=admin/viewProject/' . $projectId . '&error=role_update_failed');
            }
        }
        exit;
    }

    /* ========================================
    GESTION DES PARTENAIRES DE PROJET
    ======================================== */

    /**
     * Formulaire d'ajout de partenaire à un projet
     */
    public function addProjectPartner($projectId) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $project = $this->projectModel->getProjectById($projectId);
        
        if (!$project) {
            header('Location: index.php?url=admin/projects&error=not_found');
            exit;
        }
        
        $currentPartners = $this->projectPartnerModel->getProjectPartners($projectId);
        $partnerIds = array_column($currentPartners, 'id_partner');
        
        $allPartners = $this->partnerModel->getAllPartners();
        $availablePartners = array_filter($allPartners, function($p) use ($partnerIds) {
            return !in_array($p['id_partner'], $partnerIds);
        });
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_project' => $projectId,
                'id_partner' => $_POST['id_partner'] ?? null,
                'type_collaboration' => $_POST['type_collaboration'] ?? null,
                'date_debut' => $_POST['date_debut'] ?? null,
                'date_fin' => $_POST['date_fin'] ?? null,
                'description' => $_POST['description'] ?? null
            ];
            
            if (empty($data['id_partner'])) {
                $error = "Veuillez sélectionner un partenaire";
            } elseif ($this->projectPartnerModel->addPartnerToProject($data)) {
                header('Location: index.php?url=admin/viewProject/' . $projectId . '&success=partner_added');
                exit;
            } else {
                $error = "Erreur lors de l'ajout du partenaire";
            }
        }
        
        AdminProjectView::renderAddPartnerForm([
            'settings' => $settings,
            'project' => $project,
            'availablePartners' => $availablePartners,
            'error' => $error ?? null
        ]);
    }

    /**
     * Retirer un partenaire d'un projet
     */
    public function removeProjectPartner($projectId, $partnerId) {
        if ($this->projectPartnerModel->removePartnerFromProject($projectId, $partnerId)) {
            header('Location: index.php?url=admin/viewProject/' . $projectId . '&success=partner_removed');
        } else {
            header('Location: index.php?url=admin/viewProject/' . $projectId . '&error=remove_failed');
        }
        exit;
    }

    /**
    * Supprimer un partenariat par son ID
    */
    public function deleteProjectPartnership($id) {
        $partnership = $this->projectPartnerModel->getPartnershipById($id);
        
        if (!$partnership) {
            header('Location: index.php?url=admin/projects&error=not_found');
            exit;
        }
        
        if ($this->projectPartnerModel->deletePartnership($id)) {
            header('Location: index.php?url=admin/project/partners/' . $partnership['id_project'] . '&success=partnership_deleted');
        } else {
            header('Location: index.php?url=admin/project/partners/' . $partnership['id_project'] . '&error=delete_failed');
        }
        exit;
    }
    
    /**
    * Générer un rapport PDF des projets
    */
    public function generateProjectReport() {
        require_once 'libs/fpdf/fpdf.php';
        
        $filter = $_GET['filter'] ?? 'all';
        $value = $_GET['value'] ?? null;

        switch ($filter) {
            case 'thematique':
                $projects = $this->projectModel->getProjectsByThematic($value);
                $title = "Projets - Thématique: $value";
                break;
            case 'annee':
                $projects = array_filter(
                    $this->projectModel->getAllProjects(),
                    fn($p) => !empty($p['date_debut']) && date('Y', strtotime($p['date_debut'])) == $value
                );
                $title = "Projets - Année: $value";
                break;
            case 'responsable':
                $projects = array_filter(
                    $this->projectModel->getAllProjects(),
                    fn($p) => $p['id_responsable'] == $value
                );
                $user = $this->userModel->getUserById($value);
                $title = "Projets - Responsable: {$user['nom']} {$user['prenom']}";
                break;
            default:
                $projects = $this->projectModel->getAllProjects();
                $title = "Rapport complet des projets";
        }
        
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, $this->utf8ToIso($title), 0, 1, 'C');
        $pdf->Ln(5);
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, 'Date: ' . date('d/m/Y'), 0, 1);
        $pdf->Cell(0, 10, 'Nombre de projets: ' . count($projects), 0, 1);
        $pdf->Ln(5);
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(80, 7, 'Titre', 1);
        $pdf->Cell(40, 7, $this->utf8ToIso('Thématique'), 1);
        $pdf->Cell(30, 7, 'Statut', 1);
        $pdf->Cell(30, 7, $this->utf8ToIso('Début'), 1);
        $pdf->Ln();
        
        $pdf->SetFont('Arial', '', 9);
        foreach ($projects as $p) {
            $pdf->Cell(80, 6, $this->utf8ToIso(substr($p['titre'], 0, 40)), 1);
            $pdf->Cell(40, 6, $this->utf8ToIso($p['thematique'] ?? ''), 1);
            $pdf->Cell(30, 6, $this->utf8ToIso($p['statut']), 1);
            $pdf->Cell(30, 6, $p['date_debut'] ?? '', 1);
            $pdf->Ln();
        }
        
        $filename = 'rapport_projets_' . date('Ymd_His') . '.pdf';
        $filepath = 'assets/reports/' . $filename;
        
        if (!is_dir('assets/reports')) {
            mkdir('assets/reports', 0777, true);
        }
        
        $pdf->Output('F', $filepath);
        
        $this->reportModel->createReport([
            'type' => 'PROJETS',
            'titre' => $title,
            'contenu' => json_encode(['filter' => $filter, 'value' => $value, 'count' => count($projects)]),
            'format' => 'PDF',
            'fichier' => $filename,
            'genere_par' => AuthController::getCurrentUser()['id_user']
        ]);
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        readfile($filepath);
        exit;
    }
    
    /* ========================================
       4. GESTION DES ÉQUIPEMENTS
       ======================================== */
    
    /**
    * Liste des équipements
    */
    public function equipments() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $equipments = $this->equipmentModel->getAllEquipments();
        
        $stats = [
            'total' => count($equipments),
            'libre' => count(array_filter($equipments, fn($e) => $e['etat'] === 'libre')),
            'reserve' => count(array_filter($equipments, fn($e) => $e['etat'] === 'reserve')),
            'maintenance' => count(array_filter($equipments, fn($e) => $e['etat'] === 'en_maintenance'))
        ];
        
        AdminEquipmentView::renderList([
            'settings' => $settings,
            'equipments' => $equipments,
            'stats' => $stats
        ]);
    }
    
    public function addEquipment() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom_equipment' => $_POST['nom_equipment'] ?? '',
                'type_equipment' => $_POST['type_equipment'] ?? '',
                'description' => $_POST['description'] ?? '',
                'etat' => $_POST['etat'] ?? 'libre',
                'localisation' => $_POST['localisation'] ?? '',

                'date_acquisition' => !empty($_POST['date_acquisition']) 
                    ? $_POST['date_acquisition'] 
                    : null
            ];
            
            if ($this->equipmentModel->createEquipment($data)) {
                header('Location: index.php?url=admin/equipments&success=created');
                exit;
            } else {
                $error = "Erreur lors de la création de l'équipement";
            }
        }
        
        AdminEquipmentView::renderForm([
            'settings' => $settings,
            'action' => 'add',
            'error' => $error ?? null
        ]);
    }

    /**
    * Modifier un équipement
    */
    public function editEquipment($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $equipment = $this->equipmentModel->getEquipmentById($id);
        
        if (!$equipment) {
            header('Location: index.php?url=admin/equipments&error=not_found');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom_equipment' => $_POST['nom_equipment'] ?? '',
                'type_equipment' => $_POST['type_equipment'] ?? '',
                'description' => $_POST['description'] ?? '',
                'etat' => $_POST['etat'] ?? 'libre',
                'localisation' => $_POST['localisation'] ?? '',

                'date_acquisition' => !empty($_POST['date_acquisition']) 
                    ? $_POST['date_acquisition'] 
                    : null
            ];
            
            if ($this->equipmentModel->updateEquipment($id, $data)) {
                header('Location: index.php?url=admin/equipments&success=updated');
                exit;
            } else {
                $error = "Erreur lors de la mise à jour";
            }
        }
        
        AdminEquipmentView::renderForm([
            'settings' => $settings,
            'action' => 'edit',
            'equipment' => $equipment,
            'error' => $error ?? null
        ]);
    }
    
    /**
    * Supprimer un équipement
    */
    public function deleteEquipment($id) {
        if ($this->equipmentModel->deleteEquipment($id)) {
            header('Location: index.php?url=admin/equipments&success=deleted');
        } else {
            header('Location: index.php?url=admin/equipments&error=delete_failed');
        }
        exit;
    }
    
    /**
    * Gérer les réservations d'un équipement
    */
    public function equipmentReservations($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $equipment = $this->equipmentModel->getEquipmentById($id);
        
        if (!$equipment) {
            header('Location: index.php?url=admin/equipments&error=not_found');
            exit;
        }
        
        $reservations = $this->reservationModel->getReservationsByEquipment($id);
        
        AdminEquipmentView::renderReservations([
            'settings' => $settings,
            'equipment' => $equipment,
            'reservations' => $reservations
        ]);
    }

    public function updateReservationStatus($eqid, $stateq,$resid,$statres) {
        
        $statresValides = ['en_attente', 'confirmee', 'refusee', 'annulee'];
        $stateqValides = ['libre','reserve','en_maintenance','hors_service'];
        if (!in_array($statres, $statresValides) && !in_array($stateq, $stateqValides)) {
            $_SESSION['error'] = "Statut invalide";
            header('Location: index.php?url=admin/equipmentReservations/' . $resid);
            exit;
        }

        $result = $this->equipmentModel->updateEquipmentState($eqid, $stateq) && $this->reservationModel->updateReservationStatus($resid, $statres);

        if ($result) {
            $_SESSION['success'] = "Statut mis à jour: " . ucfirst($statres);
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour";
        }
        
        header('Location: index.php?url=admin/equipmentReservations/'.$resid);
        exit;
    }
    /**
    * Générer un rapport PDF d'utilisation
    */
    public function generateEquipmentReport() {
        require_once 'libs/fpdf/fpdf.php';
        
        $equipments = $this->equipmentModel->getAllEquipments();
        
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, $this->utf8ToIso('Rapport d\'utilisation des équipements'), 0, 1, 'C');
        $pdf->Ln(5);
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, $this->utf8ToIso('Période: 30 derniers jours'), 0, 1);
        $pdf->Ln(5);
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(60, 7, $this->utf8ToIso('Équipement'), 1);
        $pdf->Cell(30, 7, 'Type', 1);
        $pdf->Cell(30, 7, $this->utf8ToIso('État'), 1);
        $pdf->Cell(35, 7, 'Taux occupation', 1);
        $pdf->Cell(35, 7, $this->utf8ToIso('Réservations'), 1);
        $pdf->Ln();
        
        $pdf->SetFont('Arial', '', 9);
        foreach ($equipments as $eq) {
            $taux = $this->reservationModel->getTauxOccupation($eq['id_equipment'], 30);
            $nbRes = count($this->reservationModel->getReservationsByEquipment($eq['id_equipment']));
            
            $pdf->Cell(60, 6, $this->utf8ToIso(substr($eq['nom_equipment'], 0, 30)), 1);
            $pdf->Cell(30, 6, $this->utf8ToIso($eq['type_equipment']), 1);
            $pdf->Cell(30, 6, $this->utf8ToIso($eq['etat']), 1);
            $pdf->Cell(35, 6, $taux . ' %', 1);
            $pdf->Cell(35, 6, $nbRes, 1);
            $pdf->Ln();
        }
        
        $filename = 'rapport_equipements_' . date('Ymd_His') . '.pdf';
        $filepath = 'assets/reports/' . $filename;
        
        if (!is_dir('assets/reports')) {
            mkdir('assets/reports', 0777, true);
        }
        
        $pdf->Output('F', $filepath);
        
        $this->reportModel->createReport([
            'type' => 'EQUIPEMENTS',
            'titre' => 'Rapport d\'utilisation des équipements',
            'contenu' => json_encode(['period' => '30 jours', 'count' => count($equipments)]),
            'format' => 'PDF',
            'fichier' => $filename,
            'genere_par' => AuthController::getCurrentUser()['id_user']
        ]);
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        readfile($filepath);
        exit;
    }
    /* ========================================
    GESTION DES PUBLICATIONS
    ======================================== */

    /**
    * Liste des publications avec filtres et stats
    */
    public function publications() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $publications = $this->publicationModel->getAllPublications();
        
        foreach ($publications as &$pub) {
            $authors = $this->publicationModel->getPublicationAuthors($pub['id_publication']);
            
            if (!empty($authors)) {
                $auteursList = array_map(function($author) {
                    return $author['nom'] . ' ' . $author['prenom'];
                }, $authors);
                $pub['auteurs'] = implode(', ', $auteursList);
            } else {
                $pub['auteurs'] = '';
            }
            
            if (!empty($pub['id_project'])) {
                $project = $this->projectModel->getProjectById($pub['id_project']);
                $pub['projet_titre'] = $project['titre'] ?? null;
            } else {
                $pub['projet_titre'] = null;
            }
        }
        
        $stats = [
            'total' => count($publications),
            'en_attente' => count(array_filter($publications, fn($p) => $p['statut'] === 'en_attente')),
            'valide' => count(array_filter($publications, fn($p) => $p['statut'] === 'valide')),
            'rejete' => count(array_filter($publications, fn($p) => $p['statut'] === 'rejete'))
        ];
        
        AdminPublicationView::renderList([
            'settings' => $settings,
            'publications' => $publications,
            'stats' => $stats
        ]);
    }

    /**
    * Afficher le formulaire d'ajout de publication
    */
    public function addPublication() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['titre']) || empty($_POST['type_publication']) || empty($_POST['annee_publication']) || empty($_POST['domaine'])) {
                $error = "Veuillez remplir tous les champs obligatoires";
            } else {
                $data = [
                    'titre' => trim($_POST['titre']),
                    'resume' => trim($_POST['resume'] ?? ''),
                    'type_publication' => $_POST['type_publication'],
                    'annee_publication' => (int)$_POST['annee_publication'],
                    'doi' => !empty($_POST['doi']) ? trim($_POST['doi']) : null,
                    'lien_telechargement' => !empty($_POST['lien_telechargement']) ? trim($_POST['lien_telechargement']) : null,
                    'id_project' => !empty($_POST['id_project']) ? (int)$_POST['id_project'] : null,
                    'domaine' => trim($_POST['domaine']),
                    'statut' => $_POST['statut'] ?? 'en_attente',
                    'date_soumission' => !empty($_POST['date_soumission']) ? $_POST['date_soumission'] : date('Y-m-d')
                ];
                
                $publicationId = $this->publicationModel->createPublication($data);
                
                if ($publicationId) {
                    if (!empty($_POST['authors']) && is_array($_POST['authors'])) {
                        foreach ($_POST['authors'] as $ordre => $authorId) {
                            $this->publicationModel->addAuthorToPublication($publicationId, $authorId, $ordre + 1);
                        }
                    }
                    
                    header('Location: index.php?url=admin/publications&success=created');
                    exit;
                } else {
                    $error = "Erreur lors de la création de la publication";
                }
            }
        }
        
        $projects = $this->projectModel->getAllProjects();
        $users = $this->userModel->getAllUsers();
        
        foreach ($users as &$user) {
            $userRoles = $this->userRoleModel->getUserRoles($user['id_user']);
            $user['roles'] = array_column($userRoles, 'nom_role');
        }
        
        AdminPublicationView::renderForm([
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
    * Afficher le formulaire d'édition de publication
    */
    public function editPublication($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $publication = $this->publicationModel->getPublicationById($id);
        
        if (!$publication) {
            header('Location: index.php?url=admin/publications&error=not_found');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['titre']) || empty($_POST['type_publication']) || empty($_POST['annee_publication']) || empty($_POST['domaine'])) {
                $error = "Veuillez remplir tous les champs obligatoires";
            } else {
                $data = [
                    'titre' => trim($_POST['titre']),
                    'resume' => trim($_POST['resume'] ?? ''),
                    'type_publication' => $_POST['type_publication'],
                    'annee_publication' => (int)$_POST['annee_publication'],
                    'doi' => !empty($_POST['doi']) ? trim($_POST['doi']) : null,
                    'lien_telechargement' => !empty($_POST['lien_telechargement']) ? trim($_POST['lien_telechargement']) : null,
                    'id_project' => !empty($_POST['id_project']) ? (int)$_POST['id_project'] : null,
                    'domaine' => trim($_POST['domaine']),
                    'statut' => $_POST['statut'] ?? 'en_attente',
                    'date_soumission' => !empty($_POST['date_soumission']) ? $_POST['date_soumission'] : date('Y-m-d')
                ];
                
                if ($this->publicationModel->updatePublication($id, $data)) {
                    $this->publicationModel->removeAllAuthors($id);
                    
                    if (!empty($_POST['authors']) && is_array($_POST['authors'])) {
                        foreach ($_POST['authors'] as $ordre => $authorId) {
                            $this->publicationModel->addAuthorToPublication($id, $authorId, $ordre + 1);
                        }
                    }
                    
                    header('Location: index.php?url=admin/publications&success=updated');
                    exit;
                } else {
                    $error = "Erreur lors de la mise à jour";
                }
            }
        }
        
        $projects = $this->projectModel->getAllProjects();
        $users = $this->userModel->getAllUsers();
        $authors = $this->publicationModel->getPublicationAuthors($id);
        
        foreach ($users as &$user) {
            $userRoles = $this->userRoleModel->getUserRoles($user['id_user']);
            $user['roles'] = array_column($userRoles, 'nom_role');
        }
        
        AdminPublicationView::renderForm([
            'settings' => $settings,
            'action' => 'edit',
            'publication' => $publication,
            'projects' => $projects,
            'users' => $users,
            'authors' => $authors,
            'error' => $error ?? null
        ]);
    }

    /**
    * Valider une publication
    */
    public function validatePublication($id) {
        $result = $this->publicationModel->updatePublicationStatus($id, 'valide');
        
        if ($result) {
            header('Location: index.php?url=admin/publications&success=validated');
        } else {
            header('Location: index.php?url=admin/publications&error=validation_failed');
        }
        exit;
    }

    /**
    * Rejeter une publication
    */
    public function rejectPublication($id) {
        $result = $this->publicationModel->updatePublicationStatus($id, 'rejete');
        
        if ($result) {
            header('Location: index.php?url=admin/publications&success=rejected');
        } else {
            header('Location: index.php?url=admin/publications&error=rejection_failed');
        }
        exit;
    }

    /**
    * Supprimer une publication
    */
    public function deletePublication($id) {
        $this->publicationModel->removeAllAuthors($id);
        
        if ($this->publicationModel->deletePublication($id)) {
            header('Location: index.php?url=admin/publications&success=deleted');
        } else {
            header('Location: index.php?url=admin/publications&error=delete_failed');
        }
        exit;
    }
    
    /**
    * Générer un rapport bibliographique
    */
    public function generateBibliographicReport() {
        require_once 'libs/fpdf/fpdf.php';
        
        $filter = $_GET['filter'] ?? 'all';
        $value = $_GET['value'] ?? null;
        
        switch ($filter) {
            case 'annee':
                $publications = $this->publicationModel->getPublicationsByYear($value);
                $title = "Publications - Année: $value";
                break;
            case 'auteur':
                $publications = $this->publicationModel->getPublicationsByUser($value);
                $user = $this->userModel->getUserById($value);
                $title = "Publications - Auteur: {$user['nom']} {$user['prenom']}";
                break;
            default:
                $publications = $this->publicationModel->getValidatedPublications();
                $title = "Rapport bibliographique complet";
        }
        
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, $this->utf8ToIso($title), 0, 1, 'C');
        $pdf->Ln(5);
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, 'Date: ' . date('d/m/Y'), 0, 1);
        $pdf->Cell(0, 10, 'Nombre de publications: ' . count($publications), 0, 1);
        $pdf->Ln(5);
        
        $pdf->SetFont('Arial', '', 9);
        foreach ($publications as $p) {
            $ref = $p['auteurs'] . ' (' . $p['annee_publication'] . '). ';
            $ref .= $p['titre'] . '. ';
            if (!empty($p['doi'])) {
                $ref .= 'DOI: ' . $p['doi'];
            }
            
            $pdf->MultiCell(0, 5, $this->utf8ToIso($ref));
            $pdf->Ln(2);
        }
        
        $filename = 'rapport_biblio_' . date('Ymd_His') . '.pdf';
        $filepath = 'assets/reports/' . $filename;
        
        if (!is_dir('assets/reports')) {
            mkdir('assets/reports', 0777, true);
        }
        
        $pdf->Output('F', $filepath);
        
        $this->reportModel->createReport([
            'type' => 'BIBLIOGRAPHIQUE',
            'titre' => $title,
            'contenu' => json_encode(['filter' => $filter, 'value' => $value, 'count' => count($publications)]),
            'format' => 'PDF',
            'fichier' => $filename,
            'genere_par' => AuthController::getCurrentUser()['id_user']
        ]);
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        readfile($filepath);
        exit;
    }
    
    /* ========================================
       6. GESTION DES ÉVÉNEMENTS
    ======================================== */
    
    /**
    * Liste des événements
    */
    public function events() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $events = $this->eventModel->getAllEvents();
        
        AdminEventView::renderList([
            'settings' => $settings,
            'events' => $events
        ]);
    }
    
    /**
    * Ajouter un événement
    */
    public function addEvent() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $users = $this->userModel->getAllUsers();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'titre' => $_POST['titre'] ?? '',
                'description' => $_POST['description'] ?? '',
                'type_event' => $_POST['type_event'] ?? '',
                'date_debut' => $_POST['date_debut'] ?? '',
                'date_fin' => $_POST['date_fin'] ?? null,
                'lieu' => $_POST['lieu'] ?? '',
                'organisateur' => $_POST['organisateur'] ?? null,
                'statut' => $_POST['statut'] ?? 'a_venir',
                'lien_inscription' => $_POST['lien_inscription'] ?? null
            ];
            
            if ($this->eventModel->createEvent($data)) {
                header('Location: index.php?url=admin/events&success=created');
                exit;
            } else {
                $error = "Erreur lors de la création de l'événement";
            }
        }
        
        AdminEventView::renderForm([
            'settings' => $settings,
            'action' => 'add',
            'users' => $users,
            'error' => $error ?? null
        ]);
    }
    
    /**
    * Modifier un événement
    */
    public function editEvent($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $event = $this->eventModel->getEventById($id);
        $users = $this->userModel->getAllUsers();
        
        if (!$event) {
            header('Location: index.php?url=admin/events&error=not_found');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'titre' => $_POST['titre'] ?? '',
                'description' => $_POST['description'] ?? '',
                'type_event' => $_POST['type_event'] ?? '',
                'date_debut' => $_POST['date_debut'] ?? '',
                'date_fin' => $_POST['date_fin'] ?? null,
                'lieu' => $_POST['lieu'] ?? '',
                'organisateur' => $_POST['organisateur'] ?? null,
                'statut' => $_POST['statut'] ?? 'a_venir',
                'lien_inscription' => $_POST['lien_inscription'] ?? null
            ];
            
            if ($this->eventModel->updateEvent($id, $data)) {
                header('Location: index.php?url=admin/events&success=updated');
                exit;
            } else {
                $error = "Erreur lors de la mise à jour";
            }
        }
        
        AdminEventView::renderForm([
            'settings' => $settings,
            'action' => 'edit',
            'event' => $event,
            'users' => $users,
            'error' => $error ?? null
        ]);
    }
    
    /**
    * Supprimer un événement
    */
    public function deleteEvent($id) {
        if ($this->eventModel->deleteEvent($id)) {
            header('Location: index.php?url=admin/events&success=deleted');
        } else {
            header('Location: index.php?url=admin/events&error=delete_failed');
        }
        exit;
    }
    
    /**
    * Voir les inscriptions à un événement
    */
    public function eventRegistrations($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $event = $this->eventModel->getEventById($id);
        
        if (!$event) {
            header('Location: index.php?url=admin/events&error=not_found');
            exit;
        }
        
        $registrations = $this->eventModel->getEventRegistrations($id);
        
        AdminEventView::renderRegistrations([
            'settings' => $settings,
            'event' => $event,
            'registrations' => $registrations
        ]);
    }
    
    /* ========================================
       7. GESTION DES NOTIFICATIONS
    ======================================== */
    
    /**
    * Créer une notification pour un utilisateur
    */
    public function createNotification() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_user' => $_POST['id_user'] ?? null,
                'type' => $_POST['type'] ?? 'info',
                'message' => $_POST['message'] ?? '',
                'lien' => $_POST['lien'] ?? null
            ];
            
            if ($this->notificationModel->createNotification($data)) {
                header('Location: ' . ($_POST['redirect'] ?? 'index.php?url=admin') . '&success=notification_sent');
            } else {
                header('Location: ' . ($_POST['redirect'] ?? 'index.php?url=admin') . '&error=notification_failed');
            }
        }
        exit;
    }
    
    /**
    * Diffuser une notification à tous les utilisateurs
    */
    public function broadcastNotification() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = $_POST['message'] ?? '';
            $type = $_POST['type'] ?? 'info';
            $lien = $_POST['lien'] ?? null;
            
            $users = $this->userModel->getAllUsers();
            $count = 0;
            
            foreach ($users as $user) {
                if ($this->notificationModel->createNotification([
                    'id_user' => $user['id_user'],
                    'type' => $type,
                    'message' => $message,
                    'lien' => $lien
                ])) {
                    $count++;
                }
            }
            
            header('Location: index.php?url=admin&success=broadcast_sent&count=' . $count);
        }
        exit;
    }
    
    /* ========================================
    GESTION DES PARAMÈTRES
    ======================================== */

    /**
    * Afficher et gérer les paramètres généraux
    */
    public function settings() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $successCount = 0;
            $errors = [];

            $uploadDir = __DIR__ . '/../assets/images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (isset($_FILES['logo_laboratoire_file']) && $_FILES['logo_laboratoire_file']['error'] === UPLOAD_ERR_OK) {
                $uploaded = $this->handleImageUpload($_FILES['logo_laboratoire_file'], $uploadDir, 'logo_labo');
                if ($uploaded) {
                    $_POST['logo_laboratoire'] = $uploaded;
                }
            }

            if (isset($_FILES['logo_universite_file']) && $_FILES['logo_universite_file']['error'] === UPLOAD_ERR_OK) {
                $uploaded = $this->handleImageUpload($_FILES['logo_universite_file'], $uploadDir, 'logo_univ');
                if ($uploaded) {
                    $_POST['logo_universite'] = $uploaded;
                }
            }

            if (isset($_FILES['image_presentation_file']) && $_FILES['image_presentation_file']['error'] === UPLOAD_ERR_OK) {
                $uploaded = $this->handleImageUpload($_FILES['image_presentation_file'], $uploadDir, 'image_pres');
                if ($uploaded) {
                    $_POST['image_presentation'] = $uploaded;
                }
            }

            foreach ($_POST as $key => $value) {
                if (substr($key, -5) === '_file' || $key === 'submit') {
                    continue;
                }
                
                if ($this->settingModel->updateSetting($key, $value)) {
                    $successCount++;
                } else {
                    $errors[] = "Erreur pour: $key";
                }
            }

            if (empty($errors)) {
                header('Location: index.php?url=admin/settings');
            }
            exit;
        }
        
        AdminSettingView::render([
            'settings' => $settings
        ]);
    }

    /**
    * Gérer l'upload d'une image
    */
    private function handleImageUpload($file, $uploadDir, $prefix = 'image') {
        if ($file['size'] > 2 * 1024 * 1024) {
            header('Location: index.php?url=admin/settings&error=file_too_large');
            exit;
        }
        
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            header('Location: index.php?url=admin/settings&error=invalid_image');
            exit;
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $prefix . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return $filename;
        }
        
        return false;
    }

    /* ========================================
    BACKUP DATABASE - WINDOWS COMPATIBLE
    ======================================== */

    /**
    * Récupérer la configuration de la base de données
    */
    private function getDatabaseConfig() {
        return [
            'host' => $this->databaseModel->getHost(),
            'name' => $this->databaseModel->getDbname(),
            'user' => $this->databaseModel->getUsername(),
            'pass' => $this->databaseModel->getPassword()
        ];
    }

    /**
    * Détecter le chemin de mysqldump sur Windows
    */
    private function findMysqldumpPath() {
        $possiblePaths = [
            'C:/wamp64/bin/mysql/mysql8.0.27/bin/mysqldump.exe',
            'C:/wamp64/bin/mysql/mysql5.7.36/bin/mysqldump.exe',
            'C:/xampp/mysql/bin/mysqldump.exe',
            'C:/Program Files/MySQL/MySQL Server 8.0/bin/mysqldump.exe',
            'mysqldump'
        ];
        
        foreach ($possiblePaths as $path) {
            $testCommand = $path . ' --version 2>&1';
            exec($testCommand, $output, $returnCode);
            
            if ($returnCode === 0) {
                return $path;
            }
        }
        
        return null;
    }

    public function backupDatabase() {
        try {
            $backupDir = __DIR__ . '/../assets/reports/';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            $filename = 'backup_tdw_' . date('Ymd_His') . '.sql';
            $filepath = $backupDir . $filename;
            $config = $this->getDatabaseConfig();

            $pdo = new PDO(
                "mysql:host={$config['host']};dbname={$config['name']};charset=utf8",
                $config['user'],
                $config['pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $tableCount = count($tables);
            
            $mysqldumpPath = $this->findMysqldumpPath();
            $success = false;
            
            if ($mysqldumpPath) {
                $command = sprintf(
                    '"%s" --host=%s --user=%s --password=%s %s > "%s" 2>&1',
                    $mysqldumpPath,
                    escapeshellarg($config['host']),
                    escapeshellarg($config['user']),
                    escapeshellarg($config['pass']),
                    escapeshellarg($config['name']),
                    $filepath
                );
                
                exec($command, $output, $returnCode);
                
                if ($returnCode === 0 && file_exists($filepath) && filesize($filepath) > 0) {
                    $success = true;
                }
            }
            
            if (!$success) {
                $this->backupDatabasePHP($filepath, $config);
                
                if (file_exists($filepath) && filesize($filepath) > 0) {
                    $success = true;
                }
            }
            
            if ($success) {

                $fileSize = filesize($filepath);
                $fileSizeKB = round($fileSize / 1024, 2);
                
                $this->reportModel->createReport([
                    'type' => 'SAUVEGARDE',
                    'titre' => 'Sauvegarde complète BDD - ' . date('d/m/Y H:i'),
                    'contenu' => json_encode([
                        'database' => $config['name'],
                        'tables' => $tableCount,
                        'size_kb' => $fileSizeKB,
                        'method' => $mysqldumpPath ? 'mysqldump' : 'PHP'
                    ]),
                    'format' => 'SQL',
                    'fichier' => $filename,
                    'genere_par' => AuthController::getCurrentUser()['id_user']
                ]);

                header('Location: index.php?url=admin/reports&success=backup_created');
            } else {
                header('Location: index.php?url=admin/reports&error=backup_failed');
            }
        } catch (Exception $e) {
            error_log('Backup error: ' . $e->getMessage());
            header('Location: index.php?url=admin/reports&error=backup_failed');
        }
        exit;
    }

    /**
    * Sauvegarde en PHP pur (sans mysqldump)
    */
    private function backupDatabasePHP($filepath, $config) {
        try {
            $pdo = new PDO(
                "mysql:host={$config['host']};dbname={$config['name']};charset=utf8",
                $config['user'],
                $config['pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            $output = "-- Backup de la base TDW\n";
            $output .= "-- Date : " . date('Y-m-d H:i:s') . "\n\n";
            $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
            
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($tables as $table) {
                $createTable = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
                $output .= "DROP TABLE IF EXISTS `$table`;\n";
                $output .= $createTable['Create Table'] . ";\n\n";
                
                $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        $values = array_map(function($value) use ($pdo) {
                            return $value === null ? 'NULL' : $pdo->quote($value);
                        }, array_values($row));
                        
                        $output .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $output .= "\n";
                }
            }
            
            $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
            
            file_put_contents($filepath, $output);
            
        } catch (PDOException $e) {
            error_log('Backup PHP error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Restaurer la base de données
     */
    public function restoreDatabase() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['backup_file'])) {
            header('Location: index.php?url=admin/settings&error=file_not_found');
            exit;
        }
        
        try {
            $file = $_FILES['backup_file'];
            
            if (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'sql') {
                throw new Exception('Le fichier doit être au format .sql');
            }
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Erreur lors de l\'upload du fichier');
            }
            
            $config = $this->getDatabaseConfig();
            
            $pdo = new PDO(
                "mysql:host={$config['host']};dbname={$config['name']};charset=utf8",
                $config['user'],
                $config['pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            $sql = file_get_contents($file['tmp_name']);

            $filename = 'restore_' . date('Ymd_His') . '.sql';
            $filepath = __DIR__ . '/../assets/reports/' . $filename;
            copy($file['tmp_name'], $filepath);
            
            $pdo->exec($sql);

            $fileSize = filesize($filepath);
            $fileSizeKB = round($fileSize / 1024, 2);
            
            $this->reportModel->createReport([
                'type' => 'SAUVEGARDE',
                'titre' => 'Restauration BDD - ' . date('d/m/Y H:i'),
                'contenu' => json_encode([
                    'action' => 'restore',
                    'source_file' => $file['name'],
                    'size_kb' => $fileSizeKB
                ]),
                'format' => 'SQL',
                'fichier' => $filename,
                'genere_par' => AuthController::getCurrentUser()['id_user']
            ]);
            
            header('Location: index.php?url=admin/settings&success=restore');
        } catch (Exception $e) {
            error_log('Restore error: ' . $e->getMessage());
            header('Location: index.php?url=admin/settings&error=restore_failed');
        }
        exit;
    }

    /**
    * Télécharger une sauvegarde
    */
    public function downloadBackup($id = null) {

        if ($id === null && isset($_GET['file'])) {
            $filename = basename($_GET['file']);
            $filepath = __DIR__ . '/../assets/reports/' . $filename;
            
            if (file_exists($filepath)) {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize($filepath));
                readfile($filepath);
                exit;
            }
        }

        if ($id !== null) {
            $report = $this->reportModel->getReportById($id);
            
            if (!$report || empty($report['fichier'])) {
                header('Location: index.php?url=admin/reports&error=not_found');
                exit;
            }
            
            $filepath = __DIR__ . '/../assets/reports/' . $report['fichier'];
            
            if (file_exists($filepath)) {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $report['fichier'] . '"');
                header('Content-Length: ' . filesize($filepath));
                readfile($filepath);
                exit;
            }
        }
        
        header('Location: index.php?url=admin/reports&error=file_not_found');
        exit;
    }

    /**
    * Supprimer une sauvegarde
    */
    public function deleteBackup($id = null) {

        if ($id === null && isset($_GET['file'])) {
            $filename = basename($_GET['file']);
            $filepath = __DIR__ . '/../assets/reports/' . $filename;
            
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            
            header('Location: index.php?url=admin/reports');
            exit;
        }

        if ($id !== null) {
            $report = $this->reportModel->getReportById($id);
            
            if (!$report) {
                header('Location: index.php?url=admin/reports&error=not_found');
                exit;
            }

            if (!empty($report['fichier'])) {
                $filepath = __DIR__ . '/../assets/reports/' . $report['fichier'];
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }

            if ($this->reportModel->deleteReport($id)) {
                header('Location: index.php?url=admin/reports&success=backup_deleted');
            } else {
                header('Location: index.php?url=admin/reports&error=delete_failed');
            }
            exit;
        }
        
        header('Location: index.php?url=admin/reports&error=not_found');
        exit;
    }  
    /**
    * Liste des rapports générés
    */
    public function reports() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $reports = $this->reportModel->getAllReports();
        
        $stats = [
            'total' => count($reports),
            'pdf' => count(array_filter($reports, fn($r) => ($r['format'] ?? '') === 'PDF')),
            'sql' => count(array_filter($reports, fn($r) => ($r['format'] ?? '') === 'SQL')),
            'this_week' => count(array_filter($reports, function($r) {
                $weekAgo = date('Y-m-d H:i:s', strtotime('-7 days'));
                return ($r['date_generation'] ?? '') >= $weekAgo;
            }))
        ];
        
        AdminReportView::renderList([
            'settings' => $settings,
            'reports' => $reports,
            'stats' => $stats
        ]);
    }

    public function deleteReport($id) {
        $report = $this->reportModel->getReportById($id);
        
        if (!$report) {
            header('Location: index.php?url=admin/reports&error=not_found');
            exit;
        }
        
        if (!empty($report['fichier'])) {
            $filepath = 'assets/reports/' . $report['fichier'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
        
        if ($this->reportModel->deleteReport($id)) {
            header('Location: index.php?url=admin/reports&success=deleted');
        } else {
            header('Location: index.php?url=admin/reports&error=delete_failed');
        }
        exit;
    }
    
    /**
    * Télécharger un rapport
    */
    public function downloadReport($id) {
        $report = $this->reportModel->getReportById($id);
        
        if (!$report || empty($report['fichier'])) {
            header('Location: index.php?url=admin/reports&error=not_found');
            exit;
        }
        
        $filepath = 'assets/reports/' . $report['fichier'];
        
        if (file_exists($filepath)) {
            $extension = pathinfo($report['fichier'], PATHINFO_EXTENSION);
            $contentType = $extension === 'pdf' ? 'application/pdf' : 'application/sql';
            
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: attachment; filename="' . $report['fichier'] . '"');
            readfile($filepath);
            exit;
        } else {
            header('Location: index.php?url=admin/reports&error=file_not_found');
            exit;
        }
    }

    /* ========================================
    GESTION DES PARTENAIRES
    ======================================== */

    /**
     * Liste des partenaires avec stats et filtres
     */
    public function partners() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $partners = $this->partnerModel->getAllPartners();
        
        $stats = [
            'total' => count($partners),
            'entreprises' => count(array_filter($partners, fn($p) => $p['type_partenaire'] === 'entreprise')),
            'universites' => count(array_filter($partners, fn($p) => $p['type_partenaire'] === 'universite')),
            'internationaux' => count(array_filter($partners, fn($p) => !empty($p['pays']) && $p['pays'] !== 'Algérie'))
        ];
        
        AdminPartnersView::renderList([
            'settings' => $settings,
            'partners' => $partners,
            'stats' => $stats
        ]);
    }

    /**
    * Ajouter un partenaire
    */
    public function addPartner() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom_partenaire' => $_POST['nom_partenaire'] ?? '',
                'type_partenaire' => $_POST['type_partenaire'] ?? 'entreprise',
                'pays' => $_POST['pays'] ?? '',
                'ville' => $_POST['ville'] ?? null,
                'site_web' => $_POST['site_web'] ?? null,
                'email_contact' => $_POST['email_contact'] ?? null,
                'description' => $_POST['description'] ?? null,
                'date_partenariat' => $_POST['date_partenariat'] ?? date('Y-m-d')
            ];
            
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'assets/images/partners/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $filename = 'partner_' . uniqid() . '.' . $extension;
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $filename)) {
                    $data['logo'] = $filename;
                }
            }
            
            if ($this->partnerModel->createPartner($data)) {
                header('Location: index.php?url=admin/partners&success=created');
                exit;
            } else {
                $error = "Erreur lors de la création du partenaire";
            }
        }
        
        AdminPartnersView::renderForm([
            'settings' => $settings,
            'action' => 'add',
            'error' => $error ?? null
        ]);
    }

    /**
    * Modifier un partenaire
    */
    public function editPartner($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $partner = $this->partnerModel->getPartnerById($id);
        
        if (!$partner) {
            header('Location: index.php?url=admin/partners&error=not_found');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom_partenaire' => $_POST['nom_partenaire'] ?? '',
                'type_partenaire' => $_POST['type_partenaire'] ?? 'entreprise',
                'pays' => $_POST['pays'] ?? '',
                'ville' => $_POST['ville'] ?? null,
                'site_web' => $_POST['site_web'] ?? null,
                'email_contact' => $_POST['email_contact'] ?? null,
                'description' => $_POST['description'] ?? null,
                'date_partenariat' => $_POST['date_partenariat'] ?? date('Y-m-d'),
                'logo' => $partner['logo'] // Garder l'ancien logo
            ];
            
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'assets/images/partners/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $filename = 'partner_' . uniqid() . '.' . $extension;
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $filename)) {
                    // Supprimer l'ancien logo
                    if (!empty($partner['logo']) && file_exists($uploadDir . $partner['logo'])) {
                        unlink($uploadDir . $partner['logo']);
                    }
                    $data['logo'] = $filename;
                }
            }
            
            if ($this->partnerModel->updatePartner($id, $data)) {
                header('Location: index.php?url=admin/partners&success=updated');
                exit;
            } else {
                $error = "Erreur lors de la mise à jour";
            }
        }
        
        AdminPartnersView::renderForm([
            'settings' => $settings,
            'action' => 'edit',
            'partner' => $partner,
            'error' => $error ?? null
        ]);
    }

    /**
    * Supprimer un partenaire
    */
    public function deletePartner($id) {
        $partner = $this->partnerModel->getPartnerById($id);
        
        if (!$partner) {
            header('Location: index.php?url=admin/partners&error=not_found');
            exit;
        }
        
        if (!empty($partner['logo'])) {
            $logoPath = 'assets/images/partners/' . $partner['logo'];
            if (file_exists($logoPath)) {
                unlink($logoPath);
            }
        }
        
        if ($this->partnerModel->deletePartner($id)) {
            header('Location: index.php?url=admin/partners&success=deleted');
        } else {
            header('Location: index.php?url=admin/partners&error=delete_failed');
        }
        exit;
    }


    /* ========================================
    GESTION DES MESSAGES DE CONTACT
    ======================================== */

    /**
    * Liste des messages de contact
    */
    public function contactMessages() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $messages = $this->contactModel->getAllMessages();
        
        $stats = [
            'total' => count($messages),
            'non_lus' => count(array_filter($messages, fn($m) => $m['statut'] === 'non_lu')),
            'traites' => count(array_filter($messages, fn($m) => $m['statut'] === 'traite')),
            'archives' => count(array_filter($messages, fn($m) => $m['statut'] === 'archive'))
        ];
        
        AdminContactView::renderList([
            'settings' => $settings,
            'messages' => $messages,
            'stats' => $stats
        ]);
    }

    /**
    * Voir un message de contact
    */
    public function viewContactMessage($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $message = $this->contactModel->getMessageById($id);
        
        if (!$message) {
            header('Location: index.php?url=admin/contactMessages&error=not_found');
            exit;
        }
        
        if ($message['statut'] === 'non_lu') {
            $this->contactModel->updateMessageStatus($id, 'lu');
        }
        
        AdminContactView::renderView([
            'settings' => $settings,
            'message' => $message
        ]);
    }

    /**
    * Répondre à un message
    */
    public function replyContactMessage($id) {
        $message = $this->contactModel->getMessageById($id);
        
        if (!$message) {
            header('Location: index.php?url=admin/contactMessages&error=not_found');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reponse = $_POST['reponse'] ?? '';
            
            // Envoyer email (simulation)
            // TODO: Implémenter l'envoi réel d'email
            
            // Marquer comme traité
            $this->contactModel->updateMessageStatus($id, 'traite');
            
            header('Location: index.php?url=admin/contactMessages&success=replied');
            exit;
        }
        
        header('Location: mailto:' . $message['email'] . '?subject=Re: ' . urlencode($message['sujet']));
        exit;
    }

    /**
    * Supprimer un message de contact
    */
    public function deleteContactMessage($id) {
        if ($this->contactModel->deleteMessage($id)) {
            header('Location: index.php?url=admin/contactMessages&success=deleted');
        } else {
            header('Location: index.php?url=admin/contactMessages&error=delete_failed');
        }
        exit;
    }

    /**
    * Archiver un message
    */
    public function archiveContactMessage($id) {
        if ($this->contactModel->updateMessageStatus($id, 'archive')) {
            header('Location: index.php?url=admin/contactMessages&success=archived');
        } else {
            header('Location: index.php?url=admin/contactMessages&error=archive_failed');
        }
        exit;
    }


    /* ========================================
    GESTION DES OPPORTUNITÉS
    ======================================== */

    /**
    * Liste des opportunités
    */
    public function opportunities() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $opportunities = $this->opportunityModel->getAllOpportunities();
        
        $now = time();
        foreach ($opportunities as &$opp) {
            if (!empty($opp['date_limite'])) {
                $dateExpiration = strtotime($opp['date_limite']);
                
                if ($opp['statut'] !== 'pourvue' && $dateExpiration < $now) {
                    $opp['statut_reel'] = 'expiree';
                } else {
                    $opp['statut_reel'] = $opp['statut'];
                }
            } else {
                $opp['statut_reel'] = $opp['statut'];
            }
        }
        
        $stats = [
            'total' => count($opportunities),
            'actives' => count(array_filter($opportunities, fn($o) => $o['statut_reel'] === 'ouverte')),
            'stages' => count(array_filter($opportunities, fn($o) => $o['type_opportunity'] === 'stage')),
            'expirant' => count(array_filter($opportunities, function($o) use ($now) {
                if (empty($o['date_limite'])) return false;
                $dateExp = strtotime($o['date_limite']);
                return $dateExp > $now && $dateExp < ($now + 7 * 86400);
            }))
        ];
        
        AdminOpportunitiesView::renderList([
            'settings' => $settings,
            'opportunities' => $opportunities,
            'stats' => $stats
        ]);
    }

    /**
    * Ajouter une opportunité
    */
    public function addOpportunity() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'titre' => $_POST['titre'] ?? '',
                'type_opportunity' => $_POST['type_opportunity'] ?? 'stage',
                'entreprise' => $_POST['entreprise'] ?? null,
                'lieu' => $_POST['lieu'] ?? null,
                'description' => $_POST['description'] ?? '',
                'competences_requises' => $_POST['competences_requises'] ?? null,
                'niveau_requis' => $_POST['niveau_requis'] ?? 'master',
                'duree' => $_POST['duree'] ?? null,
                'date_debut' => $_POST['date_debut'] ?? null,
                'date_limite' => $_POST['date_limite'] ?? '',
                'contact_email' => $_POST['contact_email'] ?? null,
                'lien_candidature' => $_POST['lien_candidature'] ?? null,
                'statut' => 'active'
            ];
            
            if ($this->opportunityModel->createOpportunity($data)) {
                header('Location: index.php?url=admin/opportunities&success=created');
                exit;
            } else {
                $error = "Erreur lors de la création de l'opportunité";
            }
        }
        
        AdminOpportunitiesView::renderForm([
            'settings' => $settings,
            'action' => 'add',
            'error' => $error ?? null
        ]);
    }

    /**
    * Modifier une opportunité
    */
    public function editOpportunity($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $opportunity = $this->opportunityModel->getOpportunityById($id);
        
        if (!$opportunity) {
            header('Location: index.php?url=admin/opportunities&error=not_found');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'titre' => $_POST['titre'] ?? '',
                'type_opportunity' => $_POST['type_opportunity'] ?? 'stage',
                'entreprise' => $_POST['entreprise'] ?? null,
                'lieu' => $_POST['lieu'] ?? null,
                'description' => $_POST['description'] ?? '',
                'competences_requises' => $_POST['competences_requises'] ?? null,
                'niveau_requis' => $_POST['niveau_requis'] ?? 'master',
                'duree' => $_POST['duree'] ?? null,
                'date_debut' => $_POST['date_debut'] ?? null,
                'date_limite' => $_POST['date_limite'] ?? '',
                'contact_email' => $_POST['contact_email'] ?? null,
                'lien_candidature' => $_POST['lien_candidature'] ?? null,
                'statut' => $_POST['statut'] ?? 'active'
            ];
            
            if ($this->opportunityModel->updateOpportunity($id, $data)) {
                header('Location: index.php?url=admin/opportunities&success=updated');
                exit;
            } else {
                $error = "Erreur lors de la mise à jour";
            }
        }
        
        AdminOpportunitiesView::renderForm([
            'settings' => $settings,
            'action' => 'edit',
            'opportunity' => $opportunity,
            'error' => $error ?? null
        ]);
    }

    /**
    * Supprimer une opportunité
    */
    public function deleteOpportunity($id) {
        if ($this->opportunityModel->deleteOpportunity($id)) {
            header('Location: index.php?url=admin/opportunities&success=deleted');
        } else {
            header('Location: index.php?url=admin/opportunities&error=delete_failed');
        }
        exit;
    }

    /**
    * Publier une opportunité (rendre visible publiquement)
    */
    public function publishOpportunity($id) {
        if ($this->opportunityModel->updateOpportunity($id, ['statut' => 'active'])) {
            header('Location: index.php?url=admin/opportunities&success=published');
        } else {
            header('Location: index.php?url=admin/opportunities&error=publish_failed');
        }
        exit;
    }

    /* ========================================
    GESTION DES ACTUALITÉS (NEWS)
    ======================================== */

    /**
    * Liste des actualités avec filtres et statistiques
    */
    public function news() {
        $settings = $this->settingModel->getAllSettingsAsArray();

        if (!isset($this->newsModel)) {
            $this->newsModel = new NewsModel();
        }
        
        $newsList = $this->newsModel->getAllNews();

        $stats = [
            'total' => count($newsList),
            'published' => count(array_filter($newsList, fn($n) => $n['statut'] === 'publie')),
            'drafts' => count(array_filter($newsList, fn($n) => $n['statut'] === 'brouillon')),
            'slideshow' => count(array_filter($newsList, fn($n) => $n['afficher_diaporama'] == 1))
        ];
        
        AdminNewsView::renderList([
            'settings' => $settings,
            'news' => $newsList,
            'stats' => $stats
        ]);
    }

    /**
    * Afficher le formulaire d'ajout d'actualité
    */
    public function addNews() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        
        if (!isset($this->newsModel)) {
            $this->newsModel = new NewsModel();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['titre']) || empty($_POST['contenu']) || empty($_POST['type_news'])) {
                $error = "Veuillez remplir tous les champs obligatoires";
            } else {
                $imageName = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $imageName = $this->uploadImage($_FILES['image'], 'news');
                    
                    if (!$imageName) {
                        $error = "Erreur lors de l'upload de l'image (vérifiez le format et la taille)";
                    }
                }
                
                if (!isset($error)) {
                    $data = [
                        'titre' => trim($_POST['titre']),
                        'contenu' => trim($_POST['contenu']),
                        'image' => $imageName,
                        'type_news' => $_POST['type_news'],
                        'auteur' => AuthController::getCurrentUser()['id_user'],
                        'statut' => $_POST['statut'] ?? 'brouillon',
                        'afficher_diaporama' => isset($_POST['afficher_diaporama']) ? 1 : 0
                    ];
                    
                    if ($this->newsModel->createNews($data)) {
                        header('Location: index.php?url=admin/news&success=created');
                        exit;
                    } else {
                        $error = "Erreur lors de la création de l'actualité";
                    }
                }
            }
        }
        
        AdminNewsView::renderForm([
            'settings' => $settings,
            'action' => 'add',
            'error' => $error ?? null
        ]);
    }

    /**
    * Afficher le formulaire de modification d'actualité
    */
    public function editNews($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        
        if (!isset($this->newsModel)) {
            $this->newsModel = new NewsModel();
        }
        
        $news = $this->newsModel->getNewsById($id);
        
        if (!$news) {
            header('Location: index.php?url=admin/news&error=not_found');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation
            if (empty($_POST['titre']) || empty($_POST['contenu']) || empty($_POST['type_news'])) {
                $error = "Veuillez remplir tous les champs obligatoires";
            } else {
                $imageName = $news['image'];
                
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $newImage = $this->uploadImage($_FILES['image'], 'news', $news['image']);
                    
                    if ($newImage) {
                        $imageName = $newImage;
                    } else {
                        $error = "Erreur lors de l'upload de l'image (vérifiez le format et la taille)";
                    }
                }
                
                if (!isset($error)) {
                    $data = [
                        'titre' => trim($_POST['titre']),
                        'contenu' => trim($_POST['contenu']),
                        'image' => $imageName,
                        'type_news' => $_POST['type_news'],
                        'statut' => $_POST['statut'] ?? 'brouillon',
                        'afficher_diaporama' => isset($_POST['afficher_diaporama']) ? 1 : 0,
                        'date_publication' => $news['date_publication']
                    ];
                    
                    if ($this->newsModel->updateNews($id, $data)) {
                        header('Location: index.php?url=admin/news&success=updated');
                        exit;
                    } else {
                        $error = "Erreur lors de la mise à jour";
                    }
                }
            }
        }
        
        AdminNewsView::renderForm([
            'settings' => $settings,
            'action' => 'edit',
            'news' => $news,
            'error' => $error ?? null
        ]);
    }

    /**
    * Supprimer une actualité
    */
    public function deleteNews($id) {
        if (!isset($this->newsModel)) {
            $this->newsModel = new NewsModel();
        }
        
        $news = $this->newsModel->getNewsById($id);
        
        if (!$news) {
            header('Location: index.php?url=admin/news&error=not_found');
            exit;
        }
        
        if (!empty($news['image'])) {
            $imagePath = __DIR__ . '/../assets/images/news/' . $news['image'];
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }
        
        if ($this->newsModel->deleteNews($id)) {
            header('Location: index.php?url=admin/news&success=deleted');
        } else {
            header('Location: index.php?url=admin/news&error=delete_failed');
        }
        exit;
    }

    /**
    * Publier une actualité
    */
    public function publishNews($id) {
        if (!isset($this->newsModel)) {
            $this->newsModel = new NewsModel();
        }
        
        $news = $this->newsModel->getNewsById($id);
        
        if (!$news) {
            header('Location: index.php?url=admin/news&error=not_found');
            exit;
        }
        
        $data = array_merge($news, [
            'statut' => 'publie',
            'date_publication' => date('Y-m-d H:i:s')
        ]);
        
        if ($this->newsModel->updateNews($id, $data)) {
            header('Location: index.php?url=admin/news&success=published');
        } else {
            header('Location: index.php?url=admin/news&error=publish_failed');
        }
        exit;
    }

    private function uploadImage($file, $category, $oldFile = null) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        $maxSize = 2 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return false;
        }
        
        $allowedMimeTypes = [
            'image/jpeg',
            'image/jpg', 
            'image/png',
            'image/gif',
            'image/webp'
        ];
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedMimeTypes)) {
            return false;
        }
        
        $uploadDir = __DIR__ . '/../assets/images/' . $category . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        $extensionMap = [
            'jpeg' => 'jpg',
            'jpg' => 'jpg',
            'png' => 'png',
            'gif' => 'gif',
            'webp' => 'webp'
        ];
        
        $extension = $extensionMap[$extension] ?? 'jpg';

        $filename = $category . '_' 
                . date('Ymd_His') . '_' 
                . substr(uniqid(), -8) 
                . '.' . $extension;
        
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            if ($oldFile && file_exists($uploadDir . $oldFile)) {
                if ($oldFile !== 'default.jpg' && $oldFile !== 'default.png') {
                    @unlink($uploadDir . $oldFile);
                }
            }
            
            return $filename;
        }
        
        return false;
    }
}
?>