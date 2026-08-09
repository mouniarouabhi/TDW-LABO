<?php
// views/admin/AdminOpportunitiesView.php
// NOUVELLE VUE - Gestion des opportunités (stages, emplois, bourses)

class AdminOpportunitiesView {
    
    /**
     * Affichage de la liste des opportunités
     */
    public static function renderList(array $data) {
        $settings = $data['settings'] ?? [];
        $opportunities = $data['opportunities'] ?? [];
        $stats = $data['stats'] ?? [];
        
        AdminHeaderView::render($settings, 'Gestion des opportunités');
        AdminSidebarView::render('opportunities');
        ?>
        
        <div class="admin-page-header">
            <h1>Gestion des opportunités</h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <span>Opportunités</span>
            </div>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?php
                if ($_GET['success'] === 'created') echo 'Opportunité créée avec succès';
                elseif ($_GET['success'] === 'updated') echo 'Opportunité mise à jour avec succès';
                elseif ($_GET['success'] === 'deleted') echo 'Opportunité supprimée avec succès';
                elseif ($_GET['success'] === 'published') echo 'Opportunité publiée avec succès';
                ?>
            </div>
        <?php endif; ?>
        
        <!-- ========================================
             STATISTIQUES
             ======================================== -->
        <div class="stats-grid stats-grid-spacing">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total opportunités</div>
                    <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Actives</div>
                    <div class="stat-value"><?= $stats['actives'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Stages</div>
                    <div class="stat-value"><?= $stats['stages'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Expirant bientôt</div>
                    <div class="stat-value"><?= $stats['expirant'] ?? 0 ?></div>
                </div>
            </div>
        </div>
        
        <div class="content-card">
            <div class="card-header">
                <h2>Liste des opportunités (<?= count($opportunities) ?>)</h2>
                <a href="index.php?url=admin/addOpportunity" class="btn primary">
                    <i class="fas fa-plus"></i> Ajouter une opportunité
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
                            <option value="stage">Stage</option>
                            <option value="emploi">Emploi</option>
                            <option value="these">Thèse</option>
                            <option value="postdoc">Post-doctorat</option>
                            <option value="bourse">Bourse</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-statut"><i class="fas fa-flag"></i> Statut</label>
                        <select id="filter-statut" class="filter-select">
                            <option value="">Tous</option>
                            <option value="active">Active</option>
                            <option value="expiree">Expirée</option>
                            <option value="pourvue">Pourvue</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-niveau"><i class="fas fa-graduation-cap"></i> Niveau</label>
                        <select id="filter-niveau" class="filter-select">
                            <option value="">Tous les niveaux</option>
                            <option value="licence">Licence</option>
                            <option value="master">Master</option>
                            <option value="doctorat">Doctorat</option>
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
                foreach ($opportunities as $opp) {
                    // Type avec icône
                    $typeIcons = [
                        'stage' => 'fas fa-user-graduate',
                        'emploi' => 'fas fa-briefcase',
                        'these' => 'fas fa-graduation-cap',
                        'postdoc' => 'fas fa-flask',
                        'bourse' => 'fas fa-award'
                    ];
                    
                    $typeIcon = $typeIcons[$opp['type_opportunity']] ?? 'fas fa-briefcase';
                    $typeHtml = '<span class="type-badge type-' . htmlspecialchars($opp['type_opportunity']) . '">'
                              . '<i class="' . $typeIcon . '"></i> '
                              . htmlspecialchars(ucfirst($opp['type_opportunity']))
                              . '</span>';
                    
                    // Statut avec badge
                    $now = time();
                    $dateExpiration = !empty($opp['date_limite']) ? strtotime($opp['date_limite']) : null;
                    
                    if ($opp['statut'] === 'pourvue') {
                        $statutHtml = '<span class="badge"><i class="fas fa-check-double"></i> Pourvue</span>';
                    } elseif (empty($opp['date_limite'])) {
                        $statutHtml = '<span class="badge info"><i class="fas fa-infinity"></i> Sans limite</span>';
                    } else {
                        $daysLeft = floor(($dateExpiration - $now) / 86400);
                        if ($daysLeft <= 7) {
                            $statutHtml = '<span class="badge warning"><i class="fas fa-exclamation-triangle"></i> ' . $daysLeft . ' jours</span>';
                        } else {
                            $statutHtml = '<span class="badge success"><i class="fas fa-check-circle"></i> Active</span>';
                        }
                    }
                    
                    // Entreprise/Organisation
                    $orgHtml = !empty($opp['entreprise']) 
                        ? '<span class="org-badge"><i class="fas fa-building"></i> ' . htmlspecialchars($opp['entreprise']) . '</span>'
                        : '-';
                    
                    $tableData[] = [
                        'id' => $opp['id_opportunity'],
                        'titre_html' => '<strong>' . htmlspecialchars($opp['titre']) . '</strong>',
                        'type_html' => $typeHtml,
                        'entreprise_html' => $orgHtml,
                        'niveau' => htmlspecialchars(ucfirst($opp['niveau_requis'] ?? '-')),
                        'statut_html' => $statutHtml,
                        'date_limite' => !empty($opp['date_limite']) ? date('d/m/Y', strtotime($opp['date_limite'])) : '-'
                    ];
                }
                
                TableView::render([
                    'id' => 'opportunities-table',
                    'columns' => [
                        'titre' => 'Titre',
                        'type' => 'Type',
                        'entreprise' => 'Entreprise',
                        'niveau' => 'Niveau',
                        'statut' => 'Statut',
                        'date_limite' => 'Expire le'
                    ],
                    'data' => $tableData,
                    'actions' => [
                        [
                            'label' => 'Modifier',
                            'url' => 'index.php?url=admin/editOpportunity/{id}',
                            'icon' => 'fas fa-edit',
                            'class' => 'btn-action edit'
                        ],
                        [
                            'label' => 'Publier',
                            'url' => 'index.php?url=admin/publishOpportunity/{id}',
                            'icon' => 'fas fa-paper-plane',
                            'class' => 'btn-action info',
                            'confirm' => 'Publier cette opportunité sur le site public ?'
                        ],
                        [
                            'label' => 'Supprimer',
                            'url' => 'index.php?url=admin/deleteOpportunity/{id}',
                            'icon' => 'fas fa-trash',
                            'class' => 'btn-action delete',
                            'confirm' => true
                        ]
                    ],
                    'searchable' => true,
                    'empty_message' => 'Aucune opportunité trouvée'
                ]);
                ?>
            </div>
        </div>
        
        <!-- ========================================
             JAVASCRIPT FILTRES
             ======================================== -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('opportunities-table');
            const filterType = document.getElementById('filter-type');
            const filterStatut = document.getElementById('filter-statut');
            const filterNiveau = document.getElementById('filter-niveau');
            const resetBtn = document.getElementById('reset-filters');
            
            function applyFilters() {
                const rows = table.querySelectorAll('tbody tr');
                const typeValue = filterType.value.toLowerCase();
                const statutValue = filterStatut.value.toLowerCase();
                const niveauValue = filterNiveau.value.toLowerCase();
                
                rows.forEach(row => {
                    const type = row.cells[1].textContent.toLowerCase();
                    const statut = row.cells[4].textContent.toLowerCase();
                    const niveau = row.cells[3].textContent.toLowerCase();
                    
                    const matchType = !typeValue || type.includes(typeValue);
                    const matchStatut = !statutValue || statut.includes(statutValue);
                    const matchNiveau = !niveauValue || niveau.includes(niveauValue);
                    
                    if (matchType && matchStatut && matchNiveau) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
            
            filterType.addEventListener('change', applyFilters);
            filterStatut.addEventListener('change', applyFilters);
            filterNiveau.addEventListener('change', applyFilters);
            
            resetBtn.addEventListener('click', function() {
                filterType.value = '';
                filterStatut.value = '';
                filterNiveau.value = '';
                applyFilters();
            });
        });
        </script>
        
        </div><!-- .admin-main -->
        <?php
        AdminFooterView::render();
    }
    
    /**
     * Formulaire d'ajout/modification d'opportunité
     */
    public static function renderForm(array $data) {
        $settings = $data['settings'] ?? [];
        $action = $data['action'] ?? 'add';
        $opportunity = $data['opportunity'] ?? null;
        $error = $data['error'] ?? null;
        
        $title = $action === 'add' ? 'Ajouter une opportunité' : 'Modifier une opportunité';
        $submitUrl = $action === 'add' ? 'index.php?url=admin/addOpportunity' : 'index.php?url=admin/editOpportunity/' . $opportunity['id_opportunity'];
        
        AdminHeaderView::render($settings, $title);
        AdminSidebarView::render('opportunities');
        ?>
        
        <div class="admin-page-header">
            <h1><?= $title ?></h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <a href="index.php?url=admin/opportunities">Opportunités</a>
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
                <h2>Informations de l'opportunité</h2>
            </div>
            
            <div class="card-body">
                <form method="POST" action="<?= $submitUrl ?>">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="titre">Titre *</label>
                            <input type="text" id="titre" name="titre" 
                                   value="<?= htmlspecialchars($opportunity['titre'] ?? '') ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="type_opportunity">Type *</label>
                            <select id="type_opportunity" name="type_opportunity" required>
                                <option value="stage" <?= ($opportunity['type_opportunity'] ?? '') === 'stage' ? 'selected' : '' ?>>Stage</option>
                                <option value="emploi" <?= ($opportunity['type_opportunity'] ?? '') === 'emploi' ? 'selected' : '' ?>>Emploi</option>
                                <option value="these" <?= ($opportunity['type_opportunity'] ?? '') === 'these' ? 'selected' : '' ?>>Thèse</option>
                                <option value="postdoc" <?= ($opportunity['type_opportunity'] ?? '') === 'postdoc' ? 'selected' : '' ?>>Post-doctorat</option>
                                <option value="bourse" <?= ($opportunity['type_opportunity'] ?? '') === 'bourse' ? 'selected' : '' ?>>Bourse</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="entreprise">Entreprise/Organisation</label>
                            <input type="text" id="entreprise" name="entreprise" 
                                   value="<?= htmlspecialchars($opportunity['entreprise'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="lieu">Lieu</label>
                            <input type="text" id="lieu" name="lieu" 
                                   value="<?= htmlspecialchars($opportunity['lieu'] ?? '') ?>"
                                   placeholder="Ville, Pays">
                        </div>
                        
                        <div class="form-group">
                            <label for="niveau_requis">Niveau requis *</label>
                            <select id="niveau_requis" name="niveau_requis" required>
                                <option value="licence" <?= ($opportunity['niveau_requis'] ?? '') === 'licence' ? 'selected' : '' ?>>Licence</option>
                                <option value="master" <?= ($opportunity['niveau_requis'] ?? '') === 'master' ? 'selected' : '' ?>>Master</option>
                                <option value="doctorat" <?= ($opportunity['niveau_requis'] ?? '') === 'doctorat' ? 'selected' : '' ?>>Doctorat</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="duree">Durée</label>
                            <input type="text" id="duree" name="duree" 
                                   value="<?= htmlspecialchars($opportunity['duree'] ?? '') ?>"
                                   placeholder="Ex: 6 mois, 1 an...">
                        </div>
                        
                        <div class="form-group">
                            <label for="date_debut">Date de début</label>
                            <input type="date" id="date_debut" name="date_debut" 
                                   value="<?= htmlspecialchars($opportunity['date_debut'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="date_expiration">Date limite *</label>
                            <input type="date" id="date_limite" name="date_limite" 
                                value="<?= htmlspecialchars($opportunity['date_limite'] ?? '') ?>"
                                required>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_email">Email de contact</label>
                            <input type="email" id="contact_email" name="contact_email" 
                                   value="<?= htmlspecialchars($opportunity['contact_email'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="lien_candidature">Lien de candidature</label>
                            <input type="url" id="lien_candidature" name="lien_candidature" 
                                   value="<?= htmlspecialchars($opportunity['lien_candidature'] ?? '') ?>"
                                   placeholder="https://...">
                        </div>
                        
                        <?php if ($action === 'edit'): ?>
                        <div class="form-group">
                            <label for="statut">Statut</label>
                            <select id="statut" name="statut">
                                <option value="active" <?= ($opportunity['statut'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="pourvue" <?= ($opportunity['statut'] ?? '') === 'pourvue' ? 'selected' : '' ?>>Pourvue</option>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" 
                                  rows="6" required><?= htmlspecialchars($opportunity['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="competences_requises">Compétences requises</label>
                        <textarea id="competences_requises" name="competences_requises" 
                                  rows="4"><?= htmlspecialchars($opportunity['competences_requises'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn primary">
                            <i class="fas fa-save"></i>
                            <?= $action === 'add' ? 'Créer l\'opportunité' : 'Enregistrer les modifications' ?>
                        </button>
                        <a href="index.php?url=admin/opportunities" class="btn ghost">
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