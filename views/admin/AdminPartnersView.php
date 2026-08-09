<?php
// views/admin/AdminPartnersView.php
// NOUVELLE VUE - Gestion des partenaires

class AdminPartnersView {
    
    /**
     * Affichage de la liste des partenaires
     */
    public static function renderList(array $data) {
        $settings = $data['settings'] ?? [];
        $partners = $data['partners'] ?? [];
        $stats = $data['stats'] ?? [];
        
        AdminHeaderView::render($settings, 'Gestion des partenaires');
        AdminSidebarView::render('partners');
        ?>
        
        <div class="admin-page-header">
            <h1>Gestion des partenaires</h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <span>Partenaires</span>
            </div>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?php
                if ($_GET['success'] === 'created') echo 'Partenaire ajouté avec succès';
                elseif ($_GET['success'] === 'updated') echo 'Partenaire mis à jour avec succès';
                elseif ($_GET['success'] === 'deleted') echo 'Partenaire supprimé avec succès';
                ?>
            </div>
        <?php endif; ?>
        
        <!-- ========================================
             STATISTIQUES
             ======================================== -->
        <div class="stats-grid stats-grid-spacing">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total partenaires</div>
                    <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Entreprises</div>
                    <div class="stat-value"><?= $stats['entreprises'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-university"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Universités</div>
                    <div class="stat-value"><?= $stats['universites'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-globe"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Internationaux</div>
                    <div class="stat-value"><?= $stats['internationaux'] ?? 0 ?></div>
                </div>
            </div>
        </div>
        
        <div class="content-card">
            <div class="card-header">
                <h2>Liste des partenaires (<?= count($partners) ?>)</h2>
                <a href="index.php?url=admin/addPartner" class="btn primary">
                    <i class="fas fa-plus"></i> Ajouter un partenaire
                </a>
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
                            <option value="entreprise">Entreprise</option>
                            <option value="universite">Université</option>
                            <option value="laboratoire">Laboratoire</option>
                            <option value="institution">Institution</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-pays"><i class="fas fa-globe"></i> Pays</label>
                        <select id="filter-pays" class="filter-select">
                            <option value="">Tous les pays</option>
                            <?php
                            $pays = array_unique(array_filter(array_column($partners, 'pays')));
                            sort($pays);
                            foreach ($pays as $p):
                            ?>
                                <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option>
                            <?php endforeach; ?>
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
                foreach ($partners as $partner) {
                    // Logo
                    $logoHtml = !empty($partner['logo']) 
                        ? '<img src="assets/images/partners/' . htmlspecialchars($partner['logo']) . '" class="partner-logo" alt="Logo">'
                        : '<div class="partner-logo-placeholder"><i class="fas fa-building"></i></div>';
                    
                    // Type avec icône
                    $typeIcons = [
                        'entreprise' => 'fas fa-building',
                        'universite' => 'fas fa-university',
                        'laboratoire' => 'fas fa-flask',
                        'institution' => 'fas fa-landmark'
                    ];
                    
                    $typeIcon = $typeIcons[$partner['type_partenaire']] ?? 'fas fa-handshake';
                    $typeHtml = '<span class="type-badge type-' . htmlspecialchars($partner['type_partenaire']) . '">'
                              . '<i class="' . $typeIcon . '"></i> '
                              . htmlspecialchars(ucfirst($partner['type_partenaire']))
                              . '</span>';
                    
                    // Pays avec drapeau
                    $paysHtml = '<span class="country-badge">'
                              . '<i class="fas fa-globe"></i> '
                              . htmlspecialchars($partner['pays'] ?? '-')
                              . '</span>';
                    
                    $tableData[] = [
                        'id' => $partner['id_partner'],
                        'logo_html' => $logoHtml,
                        'nom_partenaire_html' => '<strong>' . htmlspecialchars($partner['nom_partenaire']) . '</strong>',
                        'type_html' => $typeHtml,
                        'pays_html' => $paysHtml,
                        'site_web_html' => !empty($partner['site_web']) 
                            ? '<a href="' . htmlspecialchars($partner['site_web']) . '" target="_blank" class="link-external">'
                            . '<i class="fas fa-external-link-alt"></i></a>'
                            : '-',
                        'date_partenariat' => !empty($partner['date_partenariat']) ? date('d/m/Y', strtotime($partner['date_partenariat'])) : '-'
                    ];
                }
                
                TableView::render([
                    'id' => 'partners-table',
                    'columns' => [
                        'logo' => 'Logo',
                        'nom_partenaire' => 'Nom',
                        'type' => 'Type',
                        'pays' => 'Pays',
                        'site_web' => 'Site web',
                        'date_partenariat' => 'Date partenariat'
                    ],
                    'data' => $tableData,
                    'actions' => [
                        [
                            'label' => 'Modifier',
                            'url' => 'index.php?url=admin/editPartner/{id}',
                            'icon' => 'fas fa-edit',
                            'class' => 'btn-action edit'
                        ],
                        [
                            'label' => 'Supprimer',
                            'url' => 'index.php?url=admin/deletePartner/{id}',
                            'icon' => 'fas fa-trash',
                            'class' => 'btn-action delete',
                            'confirm' => true
                        ]
                    ],
                    'searchable' => true,
                    'empty_message' => 'Aucun partenaire trouvé'
                ]);
                ?>
            </div>
        </div>
        
        <!-- ========================================
             JAVASCRIPT FILTRES
             ======================================== -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('partners-table');
            const filterType = document.getElementById('filter-type');
            const filterPays = document.getElementById('filter-pays');
            const resetBtn = document.getElementById('reset-filters');
            
            function applyFilters() {
                const rows = table.querySelectorAll('tbody tr');
                const typeValue = filterType.value.toLowerCase();
                const paysValue = filterPays.value.toLowerCase();
                
                rows.forEach(row => {
                    const type = row.cells[2].textContent.toLowerCase();
                    const pays = row.cells[3].textContent.toLowerCase();
                    
                    const matchType = !typeValue || type.includes(typeValue);
                    const matchPays = !paysValue || pays.includes(paysValue);
                    
                    if (matchType && matchPays) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
            
            filterType.addEventListener('change', applyFilters);
            filterPays.addEventListener('change', applyFilters);
            
            resetBtn.addEventListener('click', function() {
                filterType.value = '';
                filterPays.value = '';
                applyFilters();
            });
        });
        </script>
        
        </div><!-- .admin-main -->
        <?php
        AdminFooterView::render();
    }
    
    /**
     * Formulaire d'ajout/modification de partenaire
     */
    public static function renderForm(array $data) {
        $settings = $data['settings'] ?? [];
        $action = $data['action'] ?? 'add';
        $partner = $data['partner'] ?? null;
        $error = $data['error'] ?? null;
        
        $title = $action === 'add' ? 'Ajouter un partenaire' : 'Modifier un partenaire';
        $submitUrl = $action === 'add' ? 'index.php?url=admin/addPartner' : 'index.php?url=admin/editPartner/' . $partner['id_partner'];
        
        AdminHeaderView::render($settings, $title);
        AdminSidebarView::render('partners');
        ?>
        
        <div class="admin-page-header">
            <h1><?= $title ?></h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <a href="index.php?url=admin/partners">Partenaires</a>
                <span>/</span>
                <span><?= $action === 'add' ? 'Ajouter' : 'Modifier' ?></span>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <div class="content-card">
            <div class="card-header">
                <h2>Informations du partenaire</h2>
            </div>
            
            <div class="card-body">
                <form method="POST" action="<?= $submitUrl ?>" enctype="multipart/form-data">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nom_partenaire">Nom du partenaire *</label>
                            <input type="text" id="nom_partenaire" name="nom_partenaire" 
                                   value="<?= htmlspecialchars($partner['nom_partenaire'] ?? '') ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="type_partenaire">Type *</label>
                            <select id="type_partenaire" name="type_partenaire" required>
                                <option value="entreprise" <?= ($partner['type_partenaire'] ?? '') === 'entreprise' ? 'selected' : '' ?>>Entreprise</option>
                                <option value="universite" <?= ($partner['type_partenaire'] ?? '') === 'universite' ? 'selected' : '' ?>>Université</option>
                                <option value="laboratoire" <?= ($partner['type_partenaire'] ?? '') === 'laboratoire' ? 'selected' : '' ?>>Laboratoire</option>
                                <option value="institution" <?= ($partner['type_partenaire'] ?? '') === 'institution' ? 'selected' : '' ?>>Institution</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="pays">Pays *</label>
                            <input type="text" id="pays" name="pays" 
                                   value="<?= htmlspecialchars($partner['pays'] ?? '') ?>"
                                   placeholder="Ex: France, Algérie..."
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="ville">Ville</label>
                            <input type="text" id="ville" name="ville" 
                                   value="<?= htmlspecialchars($partner['ville'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="site_web">Site web</label>
                            <input type="url" id="site_web" name="site_web" 
                                   value="<?= htmlspecialchars($partner['site_web'] ?? '') ?>"
                                   placeholder="https://...">
                        </div>
                        
                        <div class="form-group">
                            <label for="email_contact">Email de contact</label>
                            <input type="email" id="email_contact" name="email_contact" 
                                   value="<?= htmlspecialchars($partner['email_contact'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="date_partenariat">Date du partenariat</label>
                            <input type="date" id="date_partenariat" name="date_partenariat" 
                                   value="<?= htmlspecialchars($partner['date_partenariat'] ?? date('Y-m-d')) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="logo">Logo</label>
                            <input type="file" id="logo" name="logo" accept="image/*">
                            <?php if ($action === 'edit' && !empty($partner['logo'])): ?>
                                <img src="assets/images/partners/<?= htmlspecialchars($partner['logo']) ?>" 
                                     class="logo-preview"
                                     alt="Logo actuel">
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" 
                                  rows="5"><?= htmlspecialchars($partner['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn primary">
                            <i class="fas fa-save"></i>
                            <?= $action === 'add' ? 'Ajouter le partenaire' : 'Enregistrer les modifications' ?>
                        </button>
                        <a href="index.php?url=admin/partners" class="btn ghost">
                            <i class="fas fa-times"></i>
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        </div><!-- .admin-main -->
        <?php
        AdminFooterView::render();
    }
}
?>