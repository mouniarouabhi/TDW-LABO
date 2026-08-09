<?php
// views/admin/AdminContactView.php
// NOUVELLE VUE - Gestion des messages de contact

class AdminContactView {
    
    /**
     * Affichage de la liste des messages
     */
    public static function renderList(array $data) {
        $settings = $data['settings'] ?? [];
        $messages = $data['messages'] ?? [];
        $stats = $data['stats'] ?? [];
        
        AdminHeaderView::render($settings, 'Messages de contact');
        AdminSidebarView::render('contact');
        ?>
        
        <div class="admin-page-header">
            <h1>Messages de contact</h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <span>Contact</span>
            </div>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?php
                if ($_GET['success'] === 'replied') echo 'Réponse envoyée avec succès';
                elseif ($_GET['success'] === 'deleted') echo 'Message supprimé avec succès';
                elseif ($_GET['success'] === 'archived') echo 'Message archivé avec succès';
                ?>
            </div>
        <?php endif; ?>
        
        <!-- ========================================
             STATISTIQUES
             ======================================== -->
        <div class="stats-grid stats-grid-spacing">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total messages</div>
                    <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-envelope-open"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Non lus</div>
                    <div class="stat-value"><?= $stats['non_lus'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-reply"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Traités</div>
                    <div class="stat-value"><?= $stats['traites'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-archive"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Archivés</div>
                    <div class="stat-value"><?= $stats['archives'] ?? 0 ?></div>
                </div>
            </div>
        </div>
        
        <div class="content-card">
            <div class="card-header">
                <h2>Liste des messages (<?= count($messages) ?>)</h2>
            </div>
            
            <!-- ========================================
                 FILTRES
                 ======================================== -->
            <div class="filters-section">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="filter-statut"><i class="fas fa-flag"></i> Statut</label>
                        <select id="filter-statut" class="filter-select">
                            <option value="">Tous</option>
                            <option value="non_lu">Non lu</option>
                            <option value="lu">Lu</option>
                            <option value="traite">Traité</option>
                            <option value="archive">Archivé</option>
                        </select>
                    </div>
                </div>
                
                <button id="reset-filters" class="btn ghost">
                    <i class="fas fa-redo"></i> Réinitialiser
                </button>
            </div>
            
            <div class="card-body">
                <?php
                $tableData = [];
                foreach ($messages as $msg) {
                    // Statut avec badge
                    $statutBadges = [
                        'non_lu' => '<span class="badge warning"><i class="fas fa-envelope"></i> Non lu</span>',
                        'lu' => '<span class="badge info"><i class="fas fa-envelope-open"></i> Lu</span>',
                        'traite' => '<span class="badge success"><i class="fas fa-check"></i> Traité</span>',
                        'archive' => '<span class="badge"><i class="fas fa-archive"></i> Archivé</span>'
                    ];
                    
                    $statutHtml = $statutBadges[$msg['statut']] ?? '<span class="badge">-</span>';
                                        
                    // Message tronqué
                    $messagePreview = htmlspecialchars(substr($msg['message'], 0, 60));
                    if (strlen($msg['message']) > 60) $messagePreview .= '...';
                    
                    $tableData[] = [
                        'id' => $msg['id_contact'],
                        'nom_html' => '<strong>' . htmlspecialchars($msg['nom'] . ' ' . $msg['prenom']) . '</strong>',
                        'email_html' => '<a href="mailto:' . htmlspecialchars($msg['email']) . '">' . htmlspecialchars($msg['email']) . '</a>',
                        'message' => $messagePreview,
                        'statut_html' => $statutHtml,
                        'statut_value' => $msg['statut'], // Pour filtrage
                        'date_envoi' => date('d/m/Y H:i', strtotime($msg['date_envoi']))
                    ];
                }
                
                TableView::render([
                    'id' => 'contact-table',
                    'columns' => [
                        'nom' => 'Nom',
                        'email' => 'Email',
                        'message' => 'Message',
                        'statut' => 'Statut',
                        'date_envoi' => 'Date'
                    ],
                    'data' => $tableData,
                    'actions' => [
                        [
                            'label' => 'Voir',
                            'url' => 'index.php?url=admin/viewContactMessage/{id}',
                            'icon' => 'fas fa-eye',
                            'class' => 'btn-action view'
                        ],
                        [
                            'label' => 'Répondre',
                            'url' => 'index.php?url=admin/replyContactMessage/{id}',
                            'icon' => 'fas fa-reply',
                            'class' => 'btn-action info'
                        ],
                        [
                            'label' => 'Supprimer',
                            'url' => 'index.php?url=admin/deleteContactMessage/{id}',
                            'icon' => 'fas fa-trash',
                            'class' => 'btn-action delete',
                            'confirm' => true
                        ]
                    ],
                    'searchable' => true,
                    'empty_message' => 'Aucun message trouvé'
                ]);
                ?>
            </div>
        </div>
        
        <!-- ========================================
             JAVASCRIPT FILTRES
             ======================================== -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('contact-table');
            const filterStatut = document.getElementById('filter-statut');
            const resetBtn = document.getElementById('reset-filters');
            
            function applyFilters() {
                const rows = table.querySelectorAll('tbody tr');
                const statutValue = filterStatut.value.toLowerCase();
                
                rows.forEach(row => {
                    const statut = row.cells[4].textContent.toLowerCase();
                    
                    const matchStatut = !statutValue || statut.includes(statutValue.replace('_', ' '));
                    
                    if (matchStatut) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
            
            filterStatut.addEventListener('change', applyFilters);
            
            resetBtn.addEventListener('click', function() {
                filterStatut.value = '';
                applyFilters();
            });
        });
        </script>
        
        </div><!-- .admin-main -->
        <?php
        AdminFooterView::render();
    }
    
    /**
     * Affichage détaillé d'un message
     */
    public static function renderView(array $data) {
        $settings = $data['settings'] ?? [];
        $message = $data['message'] ?? null;
        
        AdminHeaderView::render($settings, 'Détails du message');
        AdminSidebarView::render('contact');
        ?>
        
        <div class="admin-page-header">
            <h1>Message de contact</h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <a href="index.php?url=admin/contact">Contact</a>
                <span>/</span>
                <span>Détails</span>
            </div>
        </div>
        
        <div class="content-card">
            <div class="card-header">
                <h2>Détails du message</h2>
                <div class="card-actions">
                    <a href="index.php?url=admin/replyContactMessage/<?= $message['id_contact'] ?>" class="btn primary">
                        <i class="fas fa-reply"></i> Répondre
                    </a>
                    <a href="index.php?url=admin/contact" class="btn ghost">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <div class="message-details">
                    <div class="detail-row">
                        <strong>De :</strong>
                        <span><?= htmlspecialchars($message['nom'] . ' ' . $message['prenom']) ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <strong>Email :</strong>
                        <span><a href="mailto:<?= htmlspecialchars($message['email']) ?>"><?= htmlspecialchars($message['email']) ?></a></span>
                    </div>
                    
                    <div class="detail-row">
                        <strong>Téléphone :</strong>
                        <span><?= htmlspecialchars($message['telephone'] ?? '-') ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <strong>Sujet :</strong>
                        <span><?= htmlspecialchars(ucfirst($message['sujet'])) ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <strong>Date d'envoi :</strong>
                        <span><?= date('d/m/Y à H:i', strtotime($message['date_envoi'])) ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <strong>Statut :</strong>
                        <span><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $message['statut']))) ?></span>
                    </div>
                    
                    <div class="detail-message">
                        <strong>Message :</strong>
                        <div class="message-content">
                            <?= nl2br(htmlspecialchars($message['message'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        </div><!-- .admin-main -->
        <?php
        AdminFooterView::render();
    }
}
?>