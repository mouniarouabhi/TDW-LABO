<?php
// views/admin/AdminNewsView.php
// Vue complète pour la gestion des actualités

class AdminNewsView {
    
    /**
     * Liste des actualités avec filtres et stats
     */
    public static function renderList(array $data) {
        $settings = $data['settings'] ?? [];
        $newsList = $data['news'] ?? [];
        $stats = $data['stats'] ?? [];
        
        AdminHeaderView::render($settings, 'Gestion des actualités');
        AdminSidebarView::render('news');
        ?>
        
        <div class="admin-page-header">
            <h1>Gestion des actualités</h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <span>Actualités</span>
            </div>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?php
                if ($_GET['success'] === 'created') echo 'Actualité créée avec succès';
                elseif ($_GET['success'] === 'updated') echo 'Actualité mise à jour avec succès';
                elseif ($_GET['success'] === 'deleted') echo 'Actualité supprimée avec succès';
                elseif ($_GET['success'] === 'published') echo 'Actualité publiée avec succès';
                ?>
            </div>
        <?php endif; ?>
        
        <!-- ========================================
             STATISTIQUES
             ======================================== -->
        <div class="stats-grid stats-grid-spacing">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total actualités</div>
                    <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Publiées</div>
                    <div class="stat-value"><?= $stats['published'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-edit"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Brouillons</div>
                    <div class="stat-value"><?= $stats['drafts'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-images"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Diaporama</div>
                    <div class="stat-value"><?= $stats['slideshow'] ?? 0 ?></div>
                </div>
            </div>
        </div>
        
        <div class="content-card">
            <div class="card-header">
                <h2>Liste des actualités (<?= count($newsList) ?>)</h2>
                <div class="card-actions">
                    <a href="index.php?url=admin/addNews" class="btn primary">
                        <i class="fas fa-plus"></i> Nouvelle actualité
                    </a>
                </div>
            </div>
            
            <!-- ========================================
                 FILTRES
                 ======================================== -->
            <div class="filters-section">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="filter-type"><i class="fas fa-tag"></i> Type</label>
                        <select id="filter-type" class="filter-select">
                            <option value="">Tous</option>
                            <option value="evenement">Événement</option>
                            <option value="projet">Projet</option>
                            <option value="collaboration">Collaboration</option>
                            <option value="publication">Publication</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-statut"><i class="fas fa-flag"></i> Statut</label>
                        <select id="filter-statut" class="filter-select">
                            <option value="">Tous</option>
                            <option value="publie">Publié</option>
                            <option value="brouillon">Brouillon</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-diaporama"><i class="fas fa-images"></i> Diaporama</label>
                        <select id="filter-diaporama" class="filter-select">
                            <option value="">Tous</option>
                            <option value="1">Oui</option>
                            <option value="0">Non</option>
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
                foreach ($newsList as $news) {
                    // Type badge
                    $typeBadges = [
                        'evenement' => '<span class="badge info"><i class="fas fa-calendar"></i> Événement</span>',
                        'publication' => '<span class="badge primary"><i class="fas fa-newspaper"></i> Publication</span>',
                        'projet' => '<span class="badge warning"><i class="fas fa-project-diagram"></i> Projet</span>',
                        'collaboration' => '<span class="badge"><i class="fas fa-handshake"></i> Collaboration</span>',
                        'autre' => '<span class="badge"><i class="fas fa-tag"></i> Autre</span>'
                    ];
                    
                    $typeHtml = $typeBadges[$news['type_news']] ?? '<span class="badge">-</span>';
                    
                    // Statut badge
                    $statutBadges = [
                        'publie' => '<span class="badge success"><i class="fas fa-check"></i> Publié</span>',
                        'brouillon' => '<span class="badge warning"><i class="fas fa-edit"></i> Brouillon</span>'
                    ];
                    
                    $statutHtml = $statutBadges[$news['statut']] ?? '<span class="badge">-</span>';
                    
                    // Image miniature
                    $imageHtml = !empty($news['image']) 
                        ? '<img src="assets/images/news/' . htmlspecialchars($news['image']) . '" alt="Image" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">'
                        : '<span class="text-muted"><i class="fas fa-image"></i> Aucune</span>';
                    
                    // Diaporama
                    $diaporamaHtml = $news['afficher_diaporama'] 
                        ? '<span class="badge success"><i class="fas fa-check"></i></span>'
                        : '<span class="badge"><i class="fas fa-times"></i></span>';
                    
                    // Auteur
                    $auteurHtml = !empty($news['nom']) 
                        ? htmlspecialchars($news['nom'] . ' ' . $news['prenom'])
                        : '<span class="text-muted">Non défini</span>';
                    
                    $tableData[] = [
                        'id' => $news['id_news'],
                        'image_html' => $imageHtml,
                        'titre_html' => '<strong>' . htmlspecialchars(substr($news['titre'], 0, 50)) . '</strong>' . (strlen($news['titre']) > 50 ? '...' : ''),
                        'type_html' => $typeHtml,
                        'type_value' => $news['type_news'],
                        'statut_html' => $statutHtml,
                        'statut_value' => $news['statut'],
                        'diaporama_html' => $diaporamaHtml,
                        'diaporama_value' => $news['afficher_diaporama'],
                        'auteur' => $auteurHtml,
                        'date_publication' => !empty($news['date_publication']) ? date('d/m/Y', strtotime($news['date_publication'])) : '-'
                    ];
                }
                
                TableView::render([
                    'id' => 'news-table',
                    'columns' => [
                        'image' => 'Image',
                        'titre' => 'Titre',
                        'type' => 'Type',
                        'statut' => 'Statut',
                        'diaporama' => 'Diaporama',
                        'auteur' => 'Auteur',
                        'date_publication' => 'Date publication'
                    ],
                    'data' => $tableData,
                    'actions' => [
                        [
                            'label' => 'Modifier',
                            'url' => 'index.php?url=admin/editNews/{id}',
                            'icon' => 'fas fa-edit',
                            'class' => 'btn-action primary'
                        ],
                        [
                            'label' => 'Supprimer',
                            'url' => 'index.php?url=admin/deleteNews/{id}',
                            'icon' => 'fas fa-trash',
                            'class' => 'btn-action delete',
                            'confirm' => true
                        ]
                    ],
                    'searchable' => true,
                    'empty_message' => 'Aucune actualité trouvée'
                ]);
                ?>
            </div>
        </div>
        
        <!-- ========================================
             JAVASCRIPT FILTRES
             ======================================== -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('news-table');
            const filterType = document.getElementById('filter-type');
            const filterStatut = document.getElementById('filter-statut');
            const filterDiaporama = document.getElementById('filter-diaporama');
            const resetBtn = document.getElementById('reset-filters');
            
            function applyFilters() {
                const rows = table.querySelectorAll('tbody tr');
                const typeValue = filterType.value.toLowerCase();
                const statutValue = filterStatut.value.toLowerCase();
                const diaporamaValue = filterDiaporama.value;
                
                rows.forEach(row => {
                    const type = row.cells[2].textContent.toLowerCase();
                    const statut = row.cells[3].textContent.toLowerCase();
                    const diaporama = row.cells[4].querySelector('.badge');
                    const hasDiaporama = diaporama && diaporama.classList.contains('success') ? '1' : '0';
                    
                    const matchType = !typeValue || type.includes(typeValue);
                    const matchStatut = !statutValue || statut.includes(statutValue);
                    const matchDiaporama = !diaporamaValue || hasDiaporama === diaporamaValue;
                    
                    if (matchType && matchStatut && matchDiaporama) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
            
            filterType.addEventListener('change', applyFilters);
            filterStatut.addEventListener('change', applyFilters);
            filterDiaporama.addEventListener('change', applyFilters);
            
            resetBtn.addEventListener('click', function() {
                filterType.value = '';
                filterStatut.value = '';
                filterDiaporama.value = '';
                applyFilters();
            });
        });
        </script>
        
        </div><!-- .admin-main -->
        <?php
        AdminFooterView::render();
    }
    
    /**
     * Formulaire d'ajout/modification d'actualité
     */
    public static function renderForm(array $data) {
        $settings = $data['settings'] ?? [];
        $news = $data['news'] ?? null;
        $action = $data['action'] ?? 'add';
        $error = $data['error'] ?? null;
        
        $isEdit = $action === 'edit';
        $pageTitle = $isEdit ? 'Modifier une actualité' : 'Nouvelle actualité';
        
        AdminHeaderView::render($settings, $pageTitle);
        AdminSidebarView::render('news');
        ?>
        
        <div class="admin-page-header">
            <h1><?= $pageTitle ?></h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <a href="index.php?url=admin/news">Actualités</a>
                <span>/</span>
                <span><?= $isEdit ? 'Modifier' : 'Nouvelle' ?></span>
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
                <h2><?= $pageTitle ?></h2>
                <div class="card-actions">
                    <a href="index.php?url=admin/news" class="btn ghost">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" class="admin-form">
                    
                    <!-- ========================================
                         INFORMATIONS PRINCIPALES
                         ======================================== -->
                    <div class="form-section">
                        <h3><i class="fas fa-info-circle"></i> Informations principales</h3>
                        
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="titre" class="required">Titre</label>
                                <input 
                                    type="text" 
                                    id="titre" 
                                    name="titre" 
                                    value="<?= htmlspecialchars($news['titre'] ?? '') ?>"
                                    required
                                    maxlength="255"
                                    placeholder="Titre de l'actualité">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="type_news" class="required">Type</label>
                                <select id="type_news" name="type_news" required>
                                    <option value="">-- Sélectionner --</option>
                                    <option value="evenement" <?= ($news['type_news'] ?? '') === 'evenement' ? 'selected' : '' ?>>Événement</option>
                                    <option value="projet" <?= ($news['type_news'] ?? '') === 'projet' ? 'selected' : '' ?>>Projet</option>
                                    <option value="communique" <?= ($news['type_news'] ?? '') === 'publication' ? 'selected' : '' ?>>Publication</option>
                                    <option value="annonce" <?= ($news['type_news'] ?? '') === 'collaboration' ? 'selected' : '' ?>>Collaboration</option>
                                    <option value="autre" <?= ($news['type_news'] ?? '') === 'autre' ? 'selected' : '' ?>>Autre</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="statut" class="required">Statut</label>
                                <select id="statut" name="statut" required>
                                    <option value="brouillon" <?= ($news['statut'] ?? 'brouillon') === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
                                    <option value="publie" <?= ($news['statut'] ?? '') === 'publie' ? 'selected' : '' ?>>Publié</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="contenu" class="required">Contenu</label>
                                <textarea 
                                    id="contenu" 
                                    name="contenu" 
                                    rows="15"
                                    required
                                    placeholder="Contenu complet de l'actualité"><?= htmlspecialchars($news['contenu'] ?? '') ?></textarea>
                                <small>Vous pouvez utiliser Markdown pour la mise en forme</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ========================================
                         IMAGE
                         ======================================== -->
                    <div class="form-section">
                        <h3><i class="fas fa-image"></i> Image</h3>
                        
                        <?php if ($isEdit && !empty($news['image'])): ?>
                            <div class="current-image">
                                <label>Image actuelle :</label>
                                <img 
                                    src="assets/images/news/<?= htmlspecialchars($news['image']) ?>" 
                                    alt="Image actuelle"
                                    style="max-width: 300px; max-height: 200px; border-radius: 8px; margin-top: 10px;">
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="image">
                                    <?= $isEdit ? 'Changer l\'image' : 'Image' ?>
                                </label>
                                <input 
                                    type="file" 
                                    id="image" 
                                    name="image"
                                    accept="image/jpeg,image/jpg,image/png,image/gif">
                                <small>Formats acceptés : JPG, PNG, GIF (max 2 MB)</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ========================================
                         OPTIONS
                         ======================================== -->
                    <div class="form-section">
                        <h3><i class="fas fa-cog"></i> Options</h3>
                        
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label class="checkbox-label">
                                    <input 
                                        type="checkbox" 
                                        name="afficher_diaporama" 
                                        value="1"
                                        <?= ($news['afficher_diaporama'] ?? 0) ? 'checked' : '' ?>>
                                    <span>Afficher dans le diaporama de la page d'accueil</span>
                                </label>
                                <small>Les 5 actualités les plus récentes avec cette option seront affichées</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ========================================
                         ACTIONS
                         ======================================== -->
                    <div class="form-actions">
                        <button type="submit" class="btn primary">
                            <i class="fas fa-save"></i>
                            <?= $isEdit ? 'Mettre à jour' : 'Créer' ?>
                        </button>
                        <a href="index.php?url=admin/news" class="btn ghost">
                            <i class="fas fa-times"></i> Annuler
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