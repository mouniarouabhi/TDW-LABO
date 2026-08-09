<?php
// views/layout/AdminFooterView.php
// VERSION AMÉLIORÉE - Script admin.js inclus

class AdminFooterView {
    public static function render() {
        ?>
        </div> <!-- Fermeture .admin-main ouvert dans AdminSidebarView -->
        
        <footer class="admin-footer">
            <div class="admin-footer-content">
                <span>© <?= date('Y') ?> Laboratoire - Tous droits réservés</span>
                <span>
                    <i class="fas fa-shield-alt"></i> 
                    Interface d'administration
                </span>
            </div>
        </footer>
        
        <!-- JavaScript - Chargé en dernier pour optimiser le temps de chargement -->
        <script src="assets/js/admin.js"></script>
    </body>
</html>
        <?php
    }
}
?>