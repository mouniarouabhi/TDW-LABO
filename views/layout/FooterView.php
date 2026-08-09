<?php
// views/layout/FooterView.php

class FooterView {
    public static function render(array $settings = []) {
        $labName = $settings['nom_laboratoire'] ?? 'Laboratoire';
        $email = $settings['email_contact'] ?? 'contact@labo.dz';
        $phone = $settings['telephone'] ?? '';
        $address = $settings['adresse_postale'] ?? '';
        $logoUniv = $settings['logo_universite'] ?? 'logo.png';
        ?>
        <footer class="site-footer" id="contact">
            <div class="container footer-grid">
                <div>
                    <h4><?= htmlspecialchars($labName) ?></h4>
                    <p><?= htmlspecialchars($settings['description_laboratoire'] ?? 'Laboratoire universitaire') ?></p>
                </div>
                <div>
                    <h4>Contact</h4>
                    <ul class="contact-list">
                        <li><i class="fas fa-envelope"></i> <a href="mailto:<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></a></li>
                        <?php if ($phone): ?><li><i class="fas fa-phone"></i> <?= htmlspecialchars($phone) ?></li><?php endif; ?>
                        <?php if ($address): ?><li><i class="fas fa-location-dot"></i> <?= htmlspecialchars($address) ?></li><?php endif; ?>
                    </ul>
                </div>
                <div>
                    <h4>Liens</h4>
                    <ul class="footer-links">
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="index.php?url=project">Projets</a></li>
                        <li><a href="index.php?url=publication">Publications</a></li>
                        <li><a href="index.php?url=event">Événements</a></li>
                        <li><a href="index.php#presentation-organigramme">Présentation</a></li>
                    </ul>
                </div>
                <div class="footer-logo">
                    <img src="assets/images/<?= htmlspecialchars($logoUniv) ?>" alt="Logo université">
                </div>
            </div>
            <div class="footer-bottom">
                <span>© <?= date('Y') ?> <?= htmlspecialchars($labName) ?> - Tous droits réservés</span>
            </div>
        </footer>
        <script src="assets/js/diaporama.js"></script>
        <script src="assets/js/main.js"></script>
    </body>
</html>
<?php
    }
}
?>

