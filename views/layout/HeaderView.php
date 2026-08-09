<?php
// views/layout/HeaderView.php

class HeaderView {
    public static function render(array $settings = [], string $title = 'Laboratoire') {
        $labName = $settings['nom_laboratoire'] ?? 'Laboratoire';
        $logo = $settings['logo_laboratoire'] ?? 'logo.png';
        $univSite = $settings['site_universite'] ?? '#';
        // Palette configurable (fallbacks = valeurs CSS existantes)
        $colors = [
            '--primary'    => $settings['theme_couleur_primaire']    ?? '#1e4fa3',
            '--secondary'  => $settings['theme_couleur_secondaire']  ?? '#f0b429',
            '--nav'        => $settings['theme_couleur_nav']         ?? '#0f2d62',
            '--footer'     => $settings['theme_couleur_footer']      ?? '#0f1e35',
            '--text'       => $settings['theme_couleur_texte']       ?? '#1f2d3d',
            '--muted'      => $settings['theme_couleur_texte_muted'] ?? '#5c6b80',
            '--bg'         => $settings['theme_couleur_fond']        ?? '#f7f9fc',
            '--card'       => $settings['theme_couleur_carte']       ?? '#ffffff',
            '--border'     => $settings['theme_couleur_bordure']     ?? '#e5e9f2',
        ];
        $socials = [
            'facebook' => $settings['reseaux_facebook'] ?? null,
            'linkedin' => $settings['reseaux_linkedin'] ?? null,
            'twitter' => $settings['reseaux_twitter'] ?? null,
            'youtube' => $settings['reseaux_youtube'] ?? null,
        ];
        ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($labName) ?> - <?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/admin.css"
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            <?php foreach ($colors as $var => $value): ?>
            <?= $var ?>: <?= htmlspecialchars($value) ?>;
            <?php endforeach; ?>
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="top-bar">
            <div class="logo">
                <a href="index.php">
                    <img src="assets/images/<?= htmlspecialchars($logo) ?>" alt="Logo labo">
                </a>
                <span class="lab-name"><?= htmlspecialchars($labName) ?></span>
            </div>
            <div class="top-right">
                <div class="social-links">
                    <?php foreach ($socials as $key => $url): ?>
                        <?php if (!empty($url)): ?>
                            <a href="<?= htmlspecialchars($url) ?>" target="_blank" aria-label="<?= $key ?>">
                                <i class="fab fa-<?= $key === 'twitter' ? 'x-twitter' : $key ?>"></i>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <a class="univ-link" href="<?= htmlspecialchars($univSite) ?>" target="_blank">
                    Site Université
                </a>
                <a class="univ-link" href="index.php?url=dashboard">
                    Mon Compte
                </a>
            </div>
        </div>
<?php
    }
}
?>

