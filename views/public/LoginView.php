<?php
// views/public/LoginView.php
// VERSION CORRIGÉE - Utilise correctement FormComponent

class LoginView {
    
    public static function render(array $data = []) {
        $settings = $data['settings'] ?? [];
        $error = $data['error'] ?? null;
        $success = $_GET['success'] ?? null;
        
        $labName = $settings['nom_laboratoire'] ?? 'Laboratoire';
        $logo = $settings['logo_laboratoire'] ?? 'logo.png';
        
        // Messages d'erreur
        $errorMessages = [
            'empty_fields' => 'Veuillez remplir tous les champs',
            'invalid_credentials' => 'Identifiant ou mot de passe incorrect',
            'account_suspended' => 'Votre compte a été suspendu. Contactez l\'administrateur.',
            'unauthorized' => 'Accès non autorisé. Veuillez vous connecter.',
            'login_required' => 'Vous devez être connecté pour accéder à cette page.'
        ];
        
        $successMessages = [
            'logout' => 'Vous avez été déconnecté avec succès'
        ];
        
        ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - <?= htmlspecialchars($labName) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            
            <!-- Header avec logo -->
            <div class="auth-header">
                <img src="assets/images/<?= htmlspecialchars($logo) ?>" alt="Logo" class="auth-logo">
                <h1><?= htmlspecialchars($labName) ?></h1>
                <p class="auth-subtitle">Connexion à l'espace membre</p>
            </div>
            
            <!-- Messages d'erreur/succès -->
            <?php if ($error && isset($errorMessages[$error])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($errorMessages[$error]) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success && isset($successMessages[$success])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($successMessages[$success]) ?>
                </div>
            <?php endif; ?>
            
            <!-- Formulaire de connexion -->
            <form method="POST" action="index.php?url=auth/login" class="auth-form">
                
                <?php 
                // ✅ echo obligatoire car renderInput() retourne une string
                echo FormComponent::renderInput([
                    'label' => 'Identifiant',
                    'name' => 'login',
                    'type' => 'text',
                    'icon' => 'fas fa-user',
                    'placeholder' => 'Votre identifiant',
                    'required' => true,
                    'autofocus' => true
                ]);
                ?>
                
                <?php 
                echo FormComponent::renderInput([
                    'label' => 'Mot de passe',
                    'name' => 'password',
                    'type' => 'password',
                    'icon' => 'fas fa-lock',
                    'placeholder' => 'Votre mot de passe',
                    'required' => true
                ]);
                ?>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i>
                    Se connecter
                </button>
                
            </form>
            
            <!-- Lien retour accueil -->
            <div class="auth-footer">
                <a href="index.php" class="link-back">
                    <i class="fas fa-arrow-left"></i>
                    Retour à l'accueil
                </a>
            </div>
            
        </div>
    </div>
</body>
</html>
        <?php
    }
}
?>