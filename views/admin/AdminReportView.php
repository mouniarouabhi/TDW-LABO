<?php
// views/admin/AdminReportView.php
// CORRIGÉ - Gestion des rapports sans dépendance TableView

class AdminReportView {
    
    /**
     * Affichage de la liste des rapports
     */
    public static function renderList(array $data) {
        $settings = $data['settings'] ?? [];
        $reports = $data['reports'] ?? [];
        $stats = $data['stats'] ?? [];
        
        AdminHeaderView::render($settings, 'Rapports générés');
        AdminSidebarView::render('reports');
        ?>
        
        <div class="admin-page-header">
            <h1>Rapports générés</h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <span>Rapports</span>
            </div>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?php
                if ($_GET['success'] === 'generated') echo 'Rapport généré avec succès';
                elseif ($_GET['success'] === 'deleted') echo 'Rapport supprimé avec succès';
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <?php
                if ($_GET['error'] === 'not_found') echo 'Rapport introuvable';
                elseif ($_GET['error'] === 'delete_failed') echo 'Erreur lors de la suppression';
                elseif ($_GET['error'] === 'file_not_found') echo 'Fichier introuvable';
                ?>
            </div>
        <?php endif; ?>
        
        <!-- ========================================
             STATISTIQUES
             ======================================== -->
        <div class="stats-grid stats-grid-spacing">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total rapports</div>
                    <div class="stat-value"><?= $stats['total'] ?? count($reports) ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Rapports PDF</div>
                    <div class="stat-value"><?= $stats['pdf'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-database"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Sauvegardes SQL</div>
                    <div class="stat-value"><?= $stats['sql'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-calendar-week"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Cette semaine</div>
                    <div class="stat-value"><?= $stats['this_week'] ?? 0 ?></div>
                </div>
            </div>
        </div>
        
        <!-- ========================================
             ACTIONS RAPIDES
             ======================================== -->
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-bolt"></i> Générer un nouveau rapport</h2>
            </div>
            <div class="card-body">
                <div class="report-actions-grid">
                    <!-- Rapport Projets -->
                    <div class="report-action-card">
                        <div class="report-icon primary">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        <div class="report-content">
                            <h3>Rapport Projets</h3>
                            <p>Liste complète des projets de recherche</p>
                            <a href="index.php?url=admin/generateProjectReport" class="btn primary btn-sm">
                                <i class="fas fa-file-pdf"></i> Générer PDF
                            </a>
                        </div>
                    </div>
                    
                    <!-- Rapport Publications -->
                    <div class="report-action-card">
                        <div class="report-icon success">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="report-content">
                            <h3>Rapport Bibliographique</h3>
                            <p>Publications validées</p>
                            <a href="index.php?url=admin/generateBibliographicReport" class="btn success btn-sm">
                                <i class="fas fa-file-pdf"></i> Générer PDF
                            </a>
                        </div>
                    </div>
                    
                    <!-- Rapport Équipements -->
                    <div class="report-action-card">
                        <div class="report-icon info">
                            <i class="fas fa-desktop"></i>
                        </div>
                        <div class="report-content">
                            <h3>Rapport Équipements</h3>
                            <p>Utilisation et taux d'occupation</p>
                            <a href="index.php?url=admin/generateEquipmentReport" class="btn info btn-sm">
                                <i class="fas fa-file-pdf"></i> Générer PDF
                            </a>
                        </div>
                    </div>
                    
                    <!-- Sauvegarde BD -->
                    <div class="report-action-card">
                        <div class="report-icon warning">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="report-content">
                            <h3>Sauvegarde Base de Données</h3>
                            <p>Export SQL complet</p>
                            <a href="index.php?url=admin/backupDatabase" class="btn warning btn-sm" onclick="return confirm('Créer une sauvegarde ?')">
                                <i class="fas fa-download"></i> Sauvegarder
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ========================================
             LISTE DES RAPPORTS
             ======================================== -->
        <div class="content-card">
            <div class="card-header">
                <h2>Historique des rapports (<?= count($reports) ?>)</h2>
            </div>
            
            <!-- ========================================
                 FILTRES
                 ======================================== -->
            <div class="filters-section">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="filter-type"><i class="fas fa-tag"></i> Type</label>
                        <select id="filter-type" class="filter-select">
                            <option value="">Tous les types</option>
                            <option value="projets">Projets</option>
                            <option value="bibliographique">Bibliographique</option>
                            <option value="equipements">Équipements</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-format"><i class="fas fa-file"></i> Format</label>
                        <select id="filter-format" class="filter-select">
                            <option value="">Tous les formats</option>
                            <option value="PDF">PDF</option>
                            <option value="SQL">SQL</option>
                        </select>
                    </div>
                </div>
                
                <button id="reset-filters" class="btn ghost">
                    <i class="fas fa-redo"></i> Réinitialiser
                </button>
            </div>
            
            <div class="card-body">
                <?php if (empty($reports)): ?>
                    <div class="empty-state">
                        <i class="fas fa-file-alt"></i>
                        <p>Aucun rapport généré</p>
                    </div>
                <?php else: ?>
                    <?php
                    $tableData = [];
                    foreach ($reports as $report) {
                        // Type avec icône
                        $typeIcons = [
                            'PROJETS' => 'fas fa-project-diagram',
                            'BIBLIOGRAPHIQUE' => 'fas fa-book',
                            'EQUIPEMENTS' => 'fas fa-desktop',
                            'SAUVEGARDE' => 'fas fa-database'
                        ];
                        $typeIcon = $typeIcons[$report['type']] ?? 'fas fa-file';
                        
                        // Format avec badge
                        $formatIcons = [
                            'PDF' => 'fas fa-file-pdf',
                            'SQL' => 'fas fa-database'
                        ];
                        $formatIcon = $formatIcons[$report['format']] ?? 'fas fa-file';
                        
                        // Taille fichier
                        $filepath = 'assets/reports/' . ($report['fichier'] ?? '');
                        $fileSize = file_exists($filepath) ? filesize($filepath) : 0;
                        $fileSizeKB = number_format($fileSize / 1024, 2);
                        
                        $tableData[] = [
                            'id' => $report['id_report'],
                            'titre_html' => '<strong>' . htmlspecialchars($report['titre']) . '</strong>',
                            'type_html' => '<span class="type-badge type-' . strtolower($report['type']) . '">
                                                <i class="' . $typeIcon . '"></i>
                                                ' . htmlspecialchars($report['type']) . '
                                            </span>',
                            'format_html' => '<span class="format-badge format-' . strtolower($report['format']) . '">
                                                <i class="' . $formatIcon . '"></i>
                                                ' . htmlspecialchars($report['format']) . '
                                            </span>',
                            'taille' => $fileSizeKB . ' KB',
                            'date_generation' => date('d/m/Y H:i', strtotime($report['date_generation'])),
                            'type_value' => strtolower($report['type']),
                            'format_value' => $report['format']
                        ];
                    }

                    TableView::render([
                        'id' => 'reports-table',
                        'columns' => [
                            'titre' => 'Titre',
                            'type' => 'Type',
                            'format' => 'Format',
                            'taille' => 'Taille',
                            'date_generation' => 'Date génération'
                        ],
                        'data' => $tableData,
                        'actions' => [
                            [
                                'label' => 'Télécharger',
                                'url' => 'assets/reports/' . ($report['fichier'] ?? ''),
                                'icon' => 'fas fa-download',
                                'class' => 'btn-action primary'
                            ],
                            [
                                'label' => 'Supprimer',
                                'url' => 'index.php?url=admin/deleteReport/{id}',
                                'icon' => 'fas fa-trash',
                                'class' => 'btn-action delete',
                                'confirm' => true
                            ]
                        ],
                        'searchable' => false,
                        'empty_message' => 'Aucun rapport généré'
                    ]);
                    ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- ========================================
             JAVASCRIPT FILTRES
             ======================================== -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('reports-table');
            if (!table) return;
            
            const filterType = document.getElementById('filter-type');
            const filterFormat = document.getElementById('filter-format');
            const resetBtn = document.getElementById('reset-filters');
            
            function applyFilters() {
                const rows = table.querySelectorAll('tbody tr');
                const typeValue = filterType.value.toLowerCase();
                const formatValue = filterFormat.value.toUpperCase();
                
                rows.forEach(row => {
                    const rowType = row.getAttribute('data-type');
                    const rowFormat = row.getAttribute('data-format');
                    
                    const matchType = !typeValue || rowType.includes(typeValue);
                    const matchFormat = !formatValue || rowFormat === formatValue;
                    
                    if (matchType && matchFormat) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
            
            filterType.addEventListener('change', applyFilters);
            filterFormat.addEventListener('change', applyFilters);
            
            resetBtn.addEventListener('click', function() {
                filterType.value = '';
                filterFormat.value = '';
                applyFilters();
            });
        });
        </script>
        
        </div><!-- .admin-main -->
        <?php
        AdminFooterView::render();
    }
}
?>