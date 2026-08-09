<?php
// views/layout/AdminHeaderView.php
// VERSION CORRIGÉE - Compatible avec système RBAC

class AdminHeaderView {
    public static function render(array $settings = [], string $title = 'Administration') {
        $labName = $settings['nom_laboratoire'] ?? 'Laboratoire';
        $logo = $settings['logo_laboratoire'] ?? 'logo.png';
        
        // ========================================
        // CORRECTION RBAC - Vérification multi-rôles
        // ========================================
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?url=auth');
            exit;
        }
        
        // Vérifier que l'utilisateur a le rôle admin
        $roles = $_SESSION['user']['roles'] ?? [];
        if (!in_array('admin', $roles)) {
            header('Location: index.php?url=dashboard');
            exit;
        }
        
        $user = $_SESSION['user'];
        ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($labName) ?> - Admin - <?= htmlspecialchars($title) ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="admin-layout">
    <header class="admin-header">
        <div class="admin-header-left">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="admin-logo">
                <img src="assets/images/<?= htmlspecialchars($logo) ?>" alt="Logo">
                <span><?= htmlspecialchars($labName) ?></span>
            </div>
        </div>
        
        <div class="admin-header-right">
            <a href="index.php" class="btn-view-site" target="_blank">
                <i class="fas fa-external-link-alt"></i>
                Voir le site
            </a>
            
            <div class="admin-user-menu">
                <button class="user-menu-toggle" id="userMenuToggle">
                    <img src="assets/images/users/admin.png" 
                         alt="User" class="user-avatar">
                    <span><?= htmlspecialchars($user['nom']) ?></span>
                </button>
            </div>
        </div>
    </header>
<?php
    }
}
?>