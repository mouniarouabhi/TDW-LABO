<?php
// controllers/AuthController.php
// VERSION CORRIGÉE - Compatible avec système RBAC (multi-rôles)
// Admin a accès UNIQUEMENT au panel admin, pas au dashboard

class AuthController {
    private $userModel;
    private $settingModel;
    private $userRoleModel;
    
    public function __construct() {
        $this->userModel = new UserModel();
        $this->settingModel = new SettingModel();
        $this->userRoleModel = new UserRoleModel();
    }
    
    /**
     * Affiche la page de connexion
     */
    public function index() {
        // Si déjà connecté, rediriger selon le rôle
        if (isset($_SESSION['user'])) {
            $this->redirectAfterLogin();
            return;
        }
        
        $settings = $this->settingModel->getAllSettingsAsArray();
        LoginView::render([
            'settings' => $settings,
            'error' => $_GET['error'] ?? null
        ]);
    }
    
    /**
     * Traitement de la connexion
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=auth');
            exit;
        }
        
        $login = $_POST['login'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Validation
        if (empty($login) || empty($password)) {
            header('Location: index.php?url=auth&error=empty_fields');
            exit;
        }
        
        // Vérifier l'utilisateur
        $user = $this->userModel->getUserByLogin($login);
        
        if (!$user) {
            header('Location: index.php?url=auth&error=invalid_credentials');
            exit;
        }
        
        // Vérifier le mot de passe
        if (!password_verify($password, $user['password'])) {
            header('Location: index.php?url=auth&error=invalid_credentials');
            exit;
        }
        
        // Vérifier le statut du compte
        if ($user['statut'] !== 'actif') {
            header('Location: index.php?url=auth&error=account_suspended');
            exit;
        }
        
        // ✅ CORRECTION : Récupérer les rôles depuis user_roles
        $roles = $this->userRoleModel->getUserRoles($user['id_user']);
        $roleNames = array_column($roles, 'nom'); // Extraire juste les noms
        
        // Créer la session avec les rôles
        $_SESSION['user'] = [
            'id_user' => $user['id_user'],
            'login' => $user['login'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'email' => $user['email'],
            'roles' => $roleNames,
            'photo' => $user['photo'],
            'grade' => $user['grade'],
            'poste' => $user['poste']
        ];
        
        // Rediriger selon le rôle
        $this->redirectAfterLogin();
    }
    
    /**
     * Déconnexion
     */
    public function logout() {
        // Détruire la session
        session_unset();
        session_destroy();
        
        // Rediriger vers la page de connexion
        header('Location: index.php?url=auth&success=logout');
        exit;
    }
    
    /**
     * Redirection après connexion selon le rôle
     * ✅ CORRECTION : Admin va UNIQUEMENT vers admin panel, jamais vers dashboard
     */
    private function redirectAfterLogin() {
        $roles = $_SESSION['user']['roles'] ?? [];
        
        // Si l'utilisateur a le rôle admin, aller UNIQUEMENT vers admin
        // L'admin n'a PAS accès au dashboard utilisateur
        if (in_array('admin', $roles)) {
            header('Location: index.php?url=admin');
        } else {
            // Tous les autres rôles (enseignant, chercheur, etc.) vont vers le dashboard
            header('Location: index.php?url=dashboard');
        }
        exit;
    }
    
    /**
     * Vérifier si l'utilisateur est connecté
     */
    public static function isAuthenticated() {
        return isset($_SESSION['user']);
    }
    
    /**
     * Vérifier si l'utilisateur est admin
     * ✅ CORRECTION : Vérifier dans l'array de rôles
     */
    public static function isAdmin() {
        if (!isset($_SESSION['user']['roles'])) {
            return false;
        }
        return in_array('admin', $_SESSION['user']['roles']);
    }
    
    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     * ✅ NOUVEAU : Méthode pour vérifier n'importe quel rôle
     */
    public static function hasRole($roleName) {
        if (!isset($_SESSION['user']['roles'])) {
            return false;
        }
        return in_array($roleName, $_SESSION['user']['roles']);
    }
    
    /**
     * Obtenir tous les rôles de l'utilisateur
     * ✅ NOUVEAU : Méthode pour récupérer tous les rôles
     */
    public static function getUserRoles() {
        return $_SESSION['user']['roles'] ?? [];
    }
    
    /**
     * Middleware pour protéger les routes admin
     */
    public static function requireAdmin() {
        if (!self::isAdmin()) {
            header('Location: index.php?url=auth&error=unauthorized');
            exit;
        }
    }
    
    /**
     * Middleware pour protéger les routes authentifiées
     */
    public static function requireAuth() {
        if (!self::isAuthenticated()) {
            header('Location: index.php?url=auth&error=login_required');
            exit;
        }
    }
    
    /**
     * Middleware pour exiger un rôle spécifique
     * ✅ NOUVEAU : Protéger par rôle personnalisé
     */
    public static function requireRole($roleName) {
        if (!self::hasRole($roleName)) {
            header('Location: index.php?url=auth&error=unauthorized');
            exit;
        }
    }
    
    /**
     * Obtenir l'utilisateur connecté
     */
    public static function getCurrentUser() {
        return $_SESSION['user'] ?? null;
    }
}
?>