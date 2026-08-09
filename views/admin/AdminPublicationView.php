<?php
// views/admin/AdminPublicationView.php
// VERSION COMPLÈTE - Actions dynamiques + Filtres + Catégorisation avec TableView

class AdminPublicationView {
    
    /**
     * Affichage de la liste des publications avec filtres
     */
    public static function renderList(array $data) {
        $settings = $data['settings'] ?? [];
        $publications = $data['publications'] ?? [];
        $stats = $data['stats'] ?? [];
        
        AdminHeaderView::render($settings, 'Gestion des publications');
        AdminSidebarView::render('publications');
        ?>
        
        <div class="admin-page-header">
            <h1>Gestion des publications et base documentaire</h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <span>Publications</span>
            </div>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?php
                if ($_GET['success'] === 'created') echo 'Publication créée avec succès';
                elseif ($_GET['success'] === 'updated') echo 'Publication mise à jour avec succès';
                elseif ($_GET['success'] === 'deleted') echo 'Publication supprimée avec succès';
                elseif ($_GET['success'] === 'validated') echo 'Publication validée avec succès';
                elseif ($_GET['success'] === 'rejected') echo 'Publication rejetée';
                ?>
            </div>
        <?php endif; ?>
        
        <!-- STATISTIQUES -->
        <div class="stats-grid stats-grid-spacing">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total</div>
                    <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">En attente</div>
                    <div class="stat-value"><?= $stats['en_attente'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Validées</div>
                    <div class="stat-value"><?= $stats['valide'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Rejetées</div>
                    <div class="stat-value"><?= $stats['rejete'] ?? 0 ?></div>
                </div>
            </div>
        </div>
        
        <div class="content-card">
            <div class="card-header">
                <h2>Liste des publications (<?= count($publications) ?>)</h2>
                <a href="index.php?url=admin/addPublication" class="btn primary">
                    <i class="fas fa-plus"></i> Ajouter une publication
                </a>
            </div>
            
            <!-- FILTRES -->
            <div class="filters-section">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="filter-year"><i class="fas fa-calendar"></i> Année</label>
                        <select id="filter-year" class="filter-select">
                            <option value="">Toutes les années</option>
                            <?php
                            $currentYear = date('Y');
                            for ($year = $currentYear; $year >= $currentYear - 10; $year--):
                            ?>
                                <option value="<?= $year ?>"><?= $year ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-type"><i class="fas fa-file"></i> Type</label>
                        <select id="filter-type" class="filter-select">
                            <option value="">Tous les types</option>
                            <option value="article">Article</option>
                            <option value="rapport">Rapport</option>
                            <option value="these">Thèse</option>
                            <option value="communication">Communication</option>
                            <option value="poster">Poster</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-status"><i class="fas fa-flag"></i> Statut</label>
                        <select id="filter-status" class="filter-select">
                            <option value="">Tous les statuts</option>
                            <option value="en_attente">En attente</option>
                            <option value="valide">Validé</option>
                            <option value="rejete">Rejeté</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-domain"><i class="fas fa-tag"></i> Domaine</label>
                        <select id="filter-domain" class="filter-select">
                            <option value="">Tous les domaines</option>
                            <?php
                            $domains = array_unique(array_filter(array_column($publications, 'domaine')));
                            sort($domains);
                            foreach ($domains as $domain):
                            ?>
                                <option value="<?= htmlspecialchars($domain) ?>"><?= htmlspecialchars($domain) ?></option>
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
                // ========================================
                // PRÉPARER LES DONNÉES POUR TABLEVIEW
                // ========================================
                $tableData = [];
                
                foreach ($publications as $pub) {
                    // Badge statut
                    $statusBadge = match ($pub['statut']) {
                        'valide' => '<span class="badge success">Validé</span>',
                        'en_attente' => '<span class="badge warning">En attente</span>',
                        'rejete' => '<span class="badge danger">Rejeté</span>',
                        default => '<span class="badge">-</span>'
                    };
                    
                    // Icônes par type
                    $typeIcons = [
                        'article' => 'fas fa-file-alt',
                        'rapport' => 'fas fa-file-pdf',
                        'these' => 'fas fa-graduation-cap',
                        'communication' => 'fas fa-comments',
                        'poster' => 'fas fa-image',
                        'autre' => 'fas fa-file'
                    ];
                    
                    $icon = $typeIcons[$pub['type_publication']] ?? 'fas fa-file';
                    
                    // Titre avec icône
                    $titreHtml = '<div class="publication-title">
                                    <i class="' . $icon . ' publication-icon"></i>
                                    <strong>' . htmlspecialchars(substr($pub['titre'], 0, 50)) 
                                    . (strlen($pub['titre']) > 50 ? '...' : '') . '</strong>
                                  </div>';
                    
                    // Auteurs
                    $auteursHtml = '-';
                    if (!empty($pub['auteurs'])) {
                        $auteursList = explode(', ', $pub['auteurs']);
                        
                        if (count($auteursList) > 3) {
                            $premiers = array_slice($auteursList, 0, 3);
                            $reste = count($auteursList) - 3;
                            $auteursHtml = '<span title="' . htmlspecialchars($pub['auteurs']) . '">' 
                                         . htmlspecialchars(implode(', ', $premiers)) 
                                         . ' <em>et ' . $reste . ' autre' . ($reste > 1 ? 's' : '') . '</em></span>';
                        } else {
                            $auteursHtml = htmlspecialchars($pub['auteurs']);
                        }
                    }
                    
                    // Projet
                    $projetHtml = '-';
                    if (!empty($pub['projet_titre'])) {
                        $projetHtml = '<span class="project-tag" title="' . htmlspecialchars($pub['projet_titre']) . '">'
                                    . '<i class="fas fa-project-diagram"></i> '
                                    . htmlspecialchars(substr($pub['projet_titre'], 0, 30))
                                    . (strlen($pub['projet_titre']) > 30 ? '...' : '')
                                    . '</span>';
                    }
                    
                    // Domaine
                    $domaineHtml = '-';
                    if (!empty($pub['domaine'])) {
                        $domaineHtml = '<span class="domain-tag">'
                                     . '<i class="fas fa-tag"></i> '
                                     . htmlspecialchars($pub['domaine'])
                                     . '</span>';
                    }
                    
                    // ========================================
                    // GÉNÉRER LES ACTIONS EN HTML
                    // ========================================
                    $actionsHtml = '<div class="action-buttons">';
                    
                    // Modifier (toujours)
                    $actionsHtml .= '<a href="index.php?url=admin/editPublication/' . $pub['id_publication'] . '" 
                                       class="btn-action edit" 
                                       title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>';
                    
                    // Actions selon statut
                    if ($pub['statut'] === 'en_attente') {
                        $actionsHtml .= '<a href="index.php?url=admin/validatePublication/' . $pub['id_publication'] . '" 
                                           class="btn-action validate" 
                                           title="Valider"
                                           onclick="return confirm(\'Voulez-vous valider cette publication ?\');">
                                            <i class="fas fa-check"></i>
                                        </a>';
                        $actionsHtml .= '<a href="index.php?url=admin/rejectPublication/' . $pub['id_publication'] . '" 
                                           class="btn-action reject" 
                                           title="Rejeter"
                                           onclick="return confirm(\'Voulez-vous rejeter cette publication ?\');">
                                            <i class="fas fa-ban"></i>
                                        </a>';
                    } elseif ($pub['statut'] === 'rejete') {
                        $actionsHtml .= '<a href="index.php?url=admin/validatePublication/' . $pub['id_publication'] . '" 
                                           class="btn-action validate" 
                                           title="Valider"
                                           onclick="return confirm(\'Voulez-vous valider cette publication ?\');">
                                            <i class="fas fa-check"></i>
                                        </a>';
                    }
                    
                    // Supprimer (toujours)
                    $actionsHtml .= '<a href="index.php?url=admin/deletePublication/' . $pub['id_publication'] . '" 
                                       class="btn-action delete" 
                                       title="Supprimer"
                                       onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer cette publication ?\');">
                                        <i class="fas fa-trash"></i>
                                    </a>';
                    
                    $actionsHtml .= '</div>';
                    
                    // Ajouter à tableData
                    $tableData[] = [
                        'id' => $pub['id_publication'],
                        'titre_html' => $titreHtml,
                        'auteurs_html' => $auteursHtml,
                        'projet_html' => $projetHtml,
                        'domaine_html' => $domaineHtml,
                        'type_publication' => ucfirst($pub['type_publication']),
                        'annee_publication' => $pub['annee_publication'] ?? '-',
                        'statut_html' => $statusBadge,
                        'date_soumission' => (!empty($pub['date_soumission']) && strtotime($pub['date_soumission']) !== false) 
                            ? date('d/m/Y', strtotime($pub['date_soumission'])) 
                            : '-',
                        'actions_html' => $actionsHtml
                    ];
                }
                
                // ========================================
                // RENDER AVEC TABLEVIEW
                // ========================================
                TableView::render([
                    'id' => 'publications-table',
                    'columns' => [
                        'titre' => 'Titre',
                        'auteurs' => 'Auteurs',
                        'projet' => 'Projet',
                        'domaine' => 'Domaine',
                        'type_publication' => 'Type',
                        'annee_publication' => 'Année',
                        'statut' => 'Statut',
                        'date_soumission' => 'Date soumission',
                        'actions' => 'Actions'
                    ],
                    'data' => $tableData,
                    'searchable' => true,
                    'empty_message' => 'Aucune publication trouvée'
                ]);
                ?>
            </div>
        </div>
        
        <!-- JAVASCRIPT FILTRES -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('publications-table');
            if (!table) return;
            
            const filterYear = document.getElementById('filter-year');
            const filterType = document.getElementById('filter-type');
            const filterStatus = document.getElementById('filter-status');
            const filterDomain = document.getElementById('filter-domain');
            const resetBtn = document.getElementById('reset-filters');
            
            function applyFilters() {
                const rows = table.querySelectorAll('tbody tr');
                const yearValue = filterYear.value.toLowerCase();
                const typeValue = filterType.value.toLowerCase();
                const statusValue = filterStatus.value.toLowerCase();
                const domainValue = filterDomain.value.toLowerCase();
                
                let visibleCount = 0;
                
                rows.forEach(row => {
                    // Skip empty state row
                    if (row.querySelector('.empty-state')) return;
                    
                    // Colonnes: 0=titre, 1=auteurs, 2=projet, 3=domaine, 4=type, 5=année, 6=statut, 7=date, 8=actions
                    const year = row.cells[5]?.textContent.trim().toLowerCase() || '';
                    const type = row.cells[4]?.textContent.trim().toLowerCase() || '';
                    const domain = row.cells[3]?.textContent.trim().toLowerCase() || '';
                    
                    // Extraire le texte du badge statut
                    const statusCell = row.cells[6];
                    const statusBadge = statusCell?.querySelector('.badge');
                    const status = statusBadge ? statusBadge.textContent.trim().toLowerCase() : '';
                    
                    // Correspondances
                    const matchYear = !yearValue || year.includes(yearValue);
                    const matchType = !typeValue || type.includes(typeValue);
                    const matchDomain = !domainValue || domain.includes(domainValue);
                    
                    // Comparaison statut
                    let matchStatus = true;
                    if (statusValue) {
                        if (statusValue === 'en_attente' && status.includes('attente')) {
                            matchStatus = true;
                        } else if (statusValue === 'valide' && status.includes('valid')) {
                            matchStatus = true;
                        } else if (statusValue === 'rejete' && status.includes('rejet')) {
                            matchStatus = true;
                        } else {
                            matchStatus = false;
                        }
                    }
                    
                    if (matchYear && matchType && matchStatus && matchDomain) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                console.log('Filtres appliqués - Lignes visibles:', visibleCount);
            }
            
            // Événements
            filterYear.addEventListener('change', applyFilters);
            filterType.addEventListener('change', applyFilters);
            filterStatus.addEventListener('change', applyFilters);
            filterDomain.addEventListener('change', applyFilters);
            
            resetBtn.addEventListener('click', function() {
                filterYear.value = '';
                filterType.value = '';
                filterStatus.value = '';
                filterDomain.value = '';
                applyFilters();
            });
        });
        </script>
        
        </div><!-- .admin-main -->
        <?php
        AdminFooterView::render();
    }
    
    /**
     * Formulaire d'ajout/modification de publication
     */
    public static function renderForm(array $data) {
        $settings = $data['settings'] ?? [];
        $action = $data['action'] ?? 'add';
        $publication = $data['publication'] ?? null;
        $projects = $data['projects'] ?? [];
        $users = $data['users'] ?? [];
        $authors = $data['authors'] ?? [];
        $error = $data['error'] ?? null;
        
        $title = $action === 'add' ? 'Ajouter une publication' : 'Modifier une publication';
        $submitUrl = $action === 'add' ? 'index.php?url=admin/addPublication' : 'index.php?url=admin/editPublication/' . $publication['id_publication'];
        
        AdminHeaderView::render($settings, $title);
        AdminSidebarView::render('publications');
        ?>
        
        <div class="admin-page-header">
            <h1><?= $title ?></h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <a href="index.php?url=admin/publications">Publications</a>
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
                <h2>Informations de la publication</h2>
            </div>
            
            <div class="card-body">
                <form method="POST" action="<?= $submitUrl ?>">
                    
                    <div class="form-group">
                        <label for="titre">Titre de la publication *</label>
                        <input type="text" id="titre" name="titre" 
                               value="<?= htmlspecialchars($publication['titre'] ?? '') ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="resume">Résumé</label>
                        <textarea id="resume" name="resume" 
                                  rows="5"><?= htmlspecialchars($publication['resume'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="type_publication">Type de publication *</label>
                            <select id="type_publication" name="type_publication" required>
                                <option value="article" <?= ($publication['type_publication'] ?? '') === 'article' ? 'selected' : '' ?>>Article</option>
                                <option value="rapport" <?= ($publication['type_publication'] ?? '') === 'rapport' ? 'selected' : '' ?>>Rapport</option>
                                <option value="these" <?= ($publication['type_publication'] ?? '') === 'these' ? 'selected' : '' ?>>Thèse</option>
                                <option value="communication" <?= ($publication['type_publication'] ?? '') === 'communication' ? 'selected' : '' ?>>Communication</option>
                                <option value="poster" <?= ($publication['type_publication'] ?? '') === 'poster' ? 'selected' : '' ?>>Poster</option>
                                <option value="autre" <?= ($publication['type_publication'] ?? '') === 'autre' ? 'selected' : '' ?>>Autre</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="annee_publication">Année de publication *</label>
                            <input type="number" id="annee_publication" name="annee_publication" 
                                   value="<?= htmlspecialchars($publication['annee_publication'] ?? date('Y')) ?>"
                                   min="1900" max="<?= date('Y') + 1 ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="doi">DOI</label>
                            <input type="text" id="doi" name="doi" 
                                   value="<?= htmlspecialchars($publication['doi'] ?? '') ?>"
                                   placeholder="10.1234/exemple">
                        </div>
                        
                        <div class="form-group">
                            <label for="lien_telechargement">Lien de téléchargement</label>
                            <input type="url" id="lien_telechargement" name="lien_telechargement" 
                                   value="<?= htmlspecialchars($publication['lien_telechargement'] ?? '') ?>"
                                   placeholder="https://...">
                        </div>
                        
                        <div class="form-group">
                            <label for="id_project">Projet associé</label>
                            <select id="id_project" name="id_project">
                                <option value="">-- Aucun projet --</option>
                                <?php foreach ($projects as $project): ?>
                                    <option value="<?= $project['id_project'] ?>" 
                                            <?= ($publication['id_project'] ?? '') == $project['id_project'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($project['titre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="statut">Statut *</label>
                            <select id="statut" name="statut" required>
                                <option value="en_attente" <?= ($publication['statut'] ?? 'en_attente') === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                <option value="valide" <?= ($publication['statut'] ?? '') === 'valide' ? 'selected' : '' ?>>Validé</option>
                                <option value="rejete" <?= ($publication['statut'] ?? '') === 'rejete' ? 'selected' : '' ?>>Rejeté</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_soumission">Date de soumission</label>
                            <input type="date" id="date_soumission" name="date_soumission" 
                                   value="<?= htmlspecialchars($publication['date_soumission'] ?? date('Y-m-d')) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="domaine">Domaine *</label>
                            <input type="text" id="domaine" name="domaine" 
                                   value="<?= htmlspecialchars($publication['domaine'] ?? '') ?>"
                                   placeholder="Ex: Intelligence Artificielle, Sécurité..."
                                   required>
                            <small class="form-text">Utilisé pour la catégorisation</small>
                        </div>
                    </div>
                    
                    <!-- Section Auteurs -->
                    <div class="form-section">
                        <h3><i class="fas fa-users"></i> Auteurs de la publication</h3>
                        <p class="form-help">Sélectionnez les auteurs de cette publication</p>
                        <div class="authors-selection">
                            <?php foreach ($users as $user): ?>
                                    <label class="checkbox-item">
                                        <input type="checkbox" 
                                               name="authors[]" 
                                               value="<?= $user['id_user'] ?>"
                                        <span><?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?> 
                                        </span>
                                    </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn primary">
                            <i class="fas fa-save"></i>
                            <?= $action === 'add' ? 'Créer la publication' : 'Enregistrer les modifications' ?>
                        </button>
                        <a href="index.php?url=admin/publications" class="btn ghost">
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