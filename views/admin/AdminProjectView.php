<?php
// views/admin/AdminProjectView.php
// VERSION AMÉLIORÉE - Stats + Partenaires + Membres + Filtres

class AdminProjectView {
    
    /**
     * Affichage de la liste des projets avec filtres
     */
    public static function renderList(array $data) {
        $settings = $data['settings'] ?? [];
        $projects = $data['projects'] ?? [];
        $stats = $data['stats'] ?? [];
        $thematiques = $data['thematiques'] ?? [];
        
        // Filtres actifs
        $filterThematique = $_GET['thematique'] ?? null;
        $filterStatut = $_GET['statut'] ?? null;
        $filterAnnee = $_GET['annee'] ?? null;
        $filterEncadrant = $_GET['encadrant'] ?? null;
        
        AdminHeaderView::render($settings, 'Gestion des projets');
        AdminSidebarView::render('projects');
        ?>
        
        <div class="admin-page-header">
            <h1>Gestion des projets de recherche</h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <span>Projets</span>
            </div>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?php
                if ($_GET['success'] === 'created') echo 'Projet créé avec succès';
                elseif ($_GET['success'] === 'updated') echo 'Projet mis à jour avec succès';
                elseif ($_GET['success'] === 'deleted') echo 'Projet supprimé avec succès';
                ?>
            </div>
        <?php endif; ?>
        
        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Projets</div>
                    <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-play-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">En cours</div>
                    <div class="stat-value"><?= $stats['en_cours'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Soumis</div>
                    <div class="stat-value"><?= $stats['soumis'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon secondary">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Terminés</div>
                    <div class="stat-value"><?= $stats['termine'] ?? 0 ?></div>
                </div>
            </div>
        </div>
        
        <div class="content-card">
            <div class="card-header">
                <h2>Liste des projets (<?= count($projects) ?>)</h2>
                <a href="index.php?url=admin/addProject" class="btn primary">
                    <i class="fas fa-plus"></i> Ajouter un projet
                </a>
            </div>
            
            <!-- Filtres -->
            <div class="filters-section">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="filter-thematique">
                            <i class="fas fa-tag"></i> Thématique
                        </label>
                        <select id="filter-thematique" class="filter-select" onchange="applyFilters()">
                            <option value="">Toutes</option>
                            <?php foreach ($thematiques as $them => $count): ?>
                                <option value="<?= htmlspecialchars($them) ?>" <?= $filterThematique === $them ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($them) ?> (<?= $count ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-statut">
                            <i class="fas fa-flag"></i> Statut
                        </label>
                        <select id="filter-statut" class="filter-select" onchange="applyFilters()">
                            <option value="">Tous</option>
                            <option value="en_cours" <?= $filterStatut === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                            <option value="termine" <?= $filterStatut === 'termine' ? 'selected' : '' ?>>Terminé</option>
                            <option value="soumis" <?= $filterStatut === 'soumis' ? 'selected' : '' ?>>Soumis</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-annee">
                            <i class="fas fa-calendar"></i> Année
                        </label>
                        <select id="filter-annee" class="filter-select" onchange="applyFilters()">
                            <option value="">Toutes</option>
                            <?php
                            $years = [];
                            foreach ($projects as $p) {
                                if (!empty($p['date_debut'])) {
                                    $year = date('Y', strtotime($p['date_debut']));
                                    $years[$year] = ($years[$year] ?? 0) + 1;
                                }
                            }
                            krsort($years);
                            foreach ($years as $year => $count):
                            ?>
                                <option value="<?= $year ?>" <?= $filterAnnee == $year ? 'selected' : '' ?>>
                                    <?= $year ?> (<?= $count ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <?php if ($filterThematique || $filterStatut || $filterAnnee): ?>
                    <a href="index.php?url=admin/projects" class="btn ghost btn-sm">
                        <i class="fas fa-times"></i> Réinitialiser filtres
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="card-body">
                <?php
                $tableData = [];
                foreach ($projects as $project) {
                    $statusBadge = match ($project['statut']) {
                        'en_cours' => '<span class="badge success">En cours</span>',
                        'termine' => '<span class="badge default">Terminé</span>',
                        'soumis' => '<span class="badge warning">Soumis</span>',
                        default => '<span class="badge">-</span>'
                    };
                    
                    $tableData[] = [
                        'id' => $project['id_project'],
                        'titre_html' => '<div class="project-title">
                                            <i class="fas fa-project-diagram project-icon"></i>
                                            <strong>' . htmlspecialchars($project['titre']) . '</strong>
                                         </div>',
                        'thematique' => $project['thematique'] ?? '-',
                        'responsable' => !empty($project['resp_nom']) ? $project['resp_nom'] . ' ' . $project['resp_prenom'] : 'Non assigné',
                        'encadrant' => !empty($project['enc_nom']) ? $project['enc_nom'] . ' ' . $project['enc_prenom'] : 'Non assigné',
                        'type_financement' => $project['type_financement'] ?? '-',
                        'statut_html' => $statusBadge,
                        'budget' => !empty($project['budget']) ? number_format($project['budget'], 2, ',', ' ') . ' DA' : '-',
                        'date_debut' => (!empty($project['date_debut']) && strtotime($project['date_debut']) !== false) 
                            ? date('d/m/Y', strtotime($project['date_debut'])) 
                            : '-'
                    ];
                }
                
                TableView::render([
                    'id' => 'projects-table',
                    'columns' => [
                        'titre' => 'Titre du projet',
                        'thematique' => 'Thématique',
                        'responsable' => 'Responsable',
                        'encadrant' => 'Encadrant',
                        'type_financement' => 'Financement',
                        'statut' => 'Statut',
                        'budget' => 'Budget',
                        'date_debut' => 'Date début'
                    ],
                    'data' => $tableData,
                    'actions' => [
                        [
                            'label' => 'Voir détails',
                            'url' => 'index.php?url=admin/viewProject/{id}',
                            'icon' => 'fas fa-eye',
                            'class' => 'btn-action view'
                        ],
                        [
                            'label' => 'Modifier',
                            'url' => 'index.php?url=admin/editProject/{id}',
                            'icon' => 'fas fa-edit',
                            'class' => 'btn-action edit'
                        ],
                        [
                            'label' => 'Supprimer',
                            'url' => 'index.php?url=admin/deleteProject/{id}',
                            'icon' => 'fas fa-trash',
                            'class' => 'btn-action delete',
                            'confirm' => true
                        ]
                    ],
                    'searchable' => true,
                    'empty_message' => 'Aucun projet trouvé'
                ]);
                ?>
            </div>
        </div>
        
        <script>
        function applyFilters() {
            const thematique = document.getElementById('filter-thematique').value;
            const statut = document.getElementById('filter-statut').value;
            const annee = document.getElementById('filter-annee').value;
            
            let url = 'index.php?url=admin/projects';
            const params = [];
            
            if (thematique) params.push('thematique=' + encodeURIComponent(thematique));
            if (statut) params.push('statut=' + encodeURIComponent(statut));
            if (annee) params.push('annee=' + encodeURIComponent(annee));
            
            if (params.length > 0) {
                url += '&' + params.join('&');
            }
            
            window.location.href = url;
        }
        </script>
        
        </div><!-- .admin-main -->
        <?php
        AdminFooterView::render();
    }
    
    /**
     * Formulaire d'ajout/modification de projet
     */
    public static function renderForm(array $data) {
        $settings = $data['settings'] ?? [];
        $action = $data['action'] ?? 'add';
        $project = $data['project'] ?? null;
        $users = $data['users'] ?? [];
        $error = $data['error'] ?? null;
        
        $title = $action === 'add' ? 'Ajouter un projet' : 'Modifier un projet';
        $submitUrl = $action === 'add' ? 'index.php?url=admin/addProject' : 'index.php?url=admin/editProject/' . $project['id_project'];
        
        AdminHeaderView::render($settings, $title);
        AdminSidebarView::render('projects');
        ?>
        
        <div class="admin-page-header">
            <h1><?= $title ?></h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <a href="index.php?url=admin/projects">Projets</a>
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
                <h2>Informations du projet</h2>
            </div>
            
            <div class="card-body">
                <form method="POST" action="<?= $submitUrl ?>">
                    
                    <div class="form-group">
                        <label for="titre">Titre du projet *</label>
                        <input type="text" id="titre" name="titre" 
                               value="<?= htmlspecialchars($project['titre'] ?? '') ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" 
                                  rows="5"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="thematique">Thématique *</label>
                            <input type="text" id="thematique" name="thematique" 
                                   value="<?= htmlspecialchars($project['thematique'] ?? '') ?>"
                                   placeholder="Ex: IA, Sécurité, Cloud..."
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="type_financement">Type de financement</label>
                            <input type="text" id="type_financement" name="type_financement" 
                                   value="<?= htmlspecialchars($project['type_financement'] ?? '') ?>"
                                   placeholder="Ex: DGRSDT, International, Université...">
                        </div>
                        
                        <div class="form-group">
                            <label for="statut">Statut *</label>
                            <select id="statut" name="statut" required>
                                <option value="en_cours" <?= ($project['statut'] ?? 'en_cours') === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                                <option value="termine" <?= ($project['statut'] ?? '') === 'termine' ? 'selected' : '' ?>>Terminé</option>
                                <option value="soumis" <?= ($project['statut'] ?? '') === 'soumis' ? 'selected' : '' ?>>Soumis</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="budget">Budget (DA)</label>
                            <input type="number" id="budget" name="budget" step="0.01"
                                   value="<?= htmlspecialchars($project['budget'] ?? '') ?>"
                                   placeholder="0.00">
                        </div>
                        
                        <div class="form-group">
                            <label for="id_responsable">Responsable du projet</label>
                            <select id="id_responsable" name="id_responsable">
                                <option value="">-- Sélectionner un responsable --</option>
                                <?php foreach ($users as $user): ?>
                                    <?php
                                    $userRoles = $user['roles'] ?? [];
                                    $canManage = array_intersect(['enseignant', 'chercheur', 'doctorant'], $userRoles);
                                    
                                    if (!empty($canManage)):
                                    ?>
                                        <option value="<?= $user['id_user'] ?>" 
                                                <?= ($project['id_responsable'] ?? '') == $user['id_user'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?> 
                                            (<?= htmlspecialchars(implode(', ', $userRoles)) ?>)
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="id_encadrant">Encadrant du projet</label>
                            <select id="id_encadrant" name="id_encadrant">
                                <option value="">-- Sélectionner un encadrant --</option>
                                <?php foreach ($users as $user): ?>
                                    <?php
                                    $userRoles = $user['roles'] ?? [];
                                    $canManage = array_intersect(['enseignant', 'chercheur'], $userRoles);
                                    
                                    if (!empty($canManage)):
                                    ?>
                                        <option value="<?= $user['id_user'] ?>" 
                                                <?= ($project['id_encadrant'] ?? '') == $user['id_user'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?> 
                                            (<?= htmlspecialchars(implode(', ', $userRoles)) ?>)
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_debut">Date de début</label>
                            <input type="date" id="date_debut" name="date_debut" 
                                   value="<?= htmlspecialchars($project['date_debut'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="date_fin">Date de fin</label>
                            <input type="date" id="date_fin" name="date_fin" 
                                   value="<?= htmlspecialchars($project['date_fin'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn primary">
                            <i class="fas fa-save"></i>
                            <?= $action === 'add' ? 'Créer le projet' : 'Enregistrer les modifications' ?>
                        </button>
                        <a href="index.php?url=admin/projects" class="btn ghost">
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
    
    /**
     * Affichage COMPLET des détails d'un projet
     * Stats + Partenaires + Membres
     */
    public static function renderDetails(array $data) {
        $settings = $data['settings'] ?? [];
        $project = $data['project'] ?? null;
        $partners = $data['partners'] ?? [];
        $members = $data['members'] ?? [];
        $globalStats = $data['globalStats'] ?? [];

        if (!$project) {
            echo "<p>Projet introuvable</p>";
            return;
        }

        AdminHeaderView::render($settings, 'Détails du projet');
        AdminSidebarView::render('projects');
        
        // Badge statut
        $statusBadge = match ($project['statut']) {
            'en_cours' => '<span class="badge success">En cours</span>',
            'termine' => '<span class="badge default">Terminé</span>',
            'soumis' => '<span class="badge warning">Soumis</span>',
            default => '<span class="badge">-</span>'
        };
        
        // Calcul durée projet
        $duree = '-';
        if (!empty($project['date_debut']) && !empty($project['date_fin'])) {
            $debut = new DateTime($project['date_debut']);
            $fin = new DateTime($project['date_fin']);
            $diff = $debut->diff($fin);
            $duree = $diff->y > 0 ? $diff->y . ' an' . ($diff->y > 1 ? 's' : '') : '';
            $duree .= $diff->m > 0 ? ' ' . $diff->m . ' mois' : '';
            $duree = trim($duree) ?: $diff->days . ' jours';
        }
        ?>

        <div class="admin-page-header">
            <h1><?= htmlspecialchars($project['titre']) ?></h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <a href="index.php?url=admin/projects">Projets</a>
                <span>/</span>
                <span>Détails</span>
            </div>
        </div>

        <!-- Statistiques du projet -->
        <div class="stats-grid stats-grid-spacing">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Durée</div>
                    <div class="stat-value"><?= $duree ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Budget</div>
                    <div class="stat-value">
                        <?= !empty($project['budget']) ? number_format($project['budget'], 0, ',', ' ') . ' DA' : 'Non défini' ?>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Membres</div>
                    <div class="stat-value"><?= count($members) ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Partenaires</div>
                    <div class="stat-value"><?= count($partners) ?></div>
                </div>
            </div>
        </div>

        <!-- Informations générales -->
        <div class="content-card">
            <div class="card-header">
                <h2>Informations générales</h2>
            </div>

            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">
                            <i class="fas fa-tag"></i> Thématique
                        </span>
                        <span class="info-value">
                            <span class="project-tag">
                                <i class="fas fa-lightbulb"></i>
                                <?= htmlspecialchars($project['thematique'] ?? '-') ?>
                            </span>
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">
                            <i class="fas fa-hand-holding-usd"></i> Type de financement
                        </span>
                        <span class="info-value"><?= htmlspecialchars($project['type_financement'] ?? '-') ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">
                            <i class="fas fa-flag"></i> Statut
                        </span>
                        <span class="info-value"><?= $statusBadge ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">
                            <i class="fas fa-coins"></i> Budget
                        </span>
                        <span class="info-value">
                            <?= !empty($project['budget']) ? number_format($project['budget'], 2, ',', ' ') . ' DA' : '-' ?>
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">
                            <i class="fas fa-calendar-check"></i> Date début
                        </span>
                        <span class="info-value">
                            <?= (!empty($project['date_debut']) && strtotime($project['date_debut']) !== false) 
                                ? date('d/m/Y', strtotime($project['date_debut'])) 
                                : '-' ?>
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">
                            <i class="fas fa-calendar-times"></i> Date fin
                        </span>
                        <span class="info-value">
                            <?= (!empty($project['date_fin']) && strtotime($project['date_fin']) !== false) 
                                ? date('d/m/Y', strtotime($project['date_fin'])) 
                                : '-' ?>
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">
                            <i class="fas fa-user-tie"></i> Responsable
                        </span>
                        <span class="info-value">
                            <?= !empty($project['resp_nom']) 
                                ? htmlspecialchars($project['resp_nom'] . ' ' . $project['resp_prenom']) 
                                : 'Non assigné' ?>
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">
                            <i class="fas fa-user-graduate"></i> Encadrant
                        </span>
                        <span class="info-value">
                            <?= !empty($project['enc_nom']) 
                                ? htmlspecialchars($project['enc_nom'] . ' ' . $project['enc_prenom']) 
                                : 'Non assigné' ?>
                        </span>
                    </div>
                </div>

                <div class="description-section">
                    <h3><i class="fas fa-align-left"></i> Description</h3>
                    <div class="description-content">
                        <?= nl2br(htmlspecialchars($project['description'] ?? 'Aucune description')) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques globales -->
        <?php if (!empty($globalStats)): ?>
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-chart-pie"></i> Statistiques globales des projets</h2>
            </div>
            <div class="card-body">
                <div class="stats-breakdown">
                    <!-- Par thématique -->
                    <div class="stat-breakdown-section">
                        <h4><i class="fas fa-tag"></i> Répartition par thématique</h4>
                        <div class="breakdown-grid">
                            <?php foreach ($globalStats['byThematique'] ?? [] as $them => $count): ?>
                                <div class="breakdown-item">
                                    <span class="breakdown-label"><?= htmlspecialchars($them) ?></span>
                                    <span class="breakdown-value">
                                        <span class="count-badge"><?= $count ?></span>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Par encadrant -->
                    <div class="stat-breakdown-section">
                        <h4><i class="fas fa-user-graduate"></i> Répartition par encadrant</h4>
                        <div class="breakdown-grid">
                            <?php foreach ($globalStats['byEncadrant'] ?? [] as $enc => $count): ?>
                                <div class="breakdown-item">
                                    <span class="breakdown-label"><?= htmlspecialchars($enc) ?></span>
                                    <span class="breakdown-value">
                                        <span class="count-badge"><?= $count ?></span>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Par année -->
                    <div class="stat-breakdown-section">
                        <h4><i class="fas fa-calendar"></i> Répartition par année</h4>
                        <div class="breakdown-grid">
                            <?php foreach ($globalStats['byYear'] ?? [] as $year => $count): ?>
                                <div class="breakdown-item">
                                    <span class="breakdown-label"><?= $year ?></span>
                                    <span class="breakdown-value">
                                        <span class="count-badge"><?= $count ?></span>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Partenaires du projet -->
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-handshake"></i> Partenaires associés (<?= count($partners) ?>)</h2>
                <a href="index.php?url=admin/addProjectPartner/<?= $project['id_project'] ?>" class="btn primary btn-sm">
                    <i class="fas fa-plus"></i> Ajouter un partenaire
                </a>
            </div>
            <div class="card-body">
                <?php
                $tableData = [];
                foreach ($partners as $partner) {
                    // Logo HTML
                    $logoHtml = '';
                    if (!empty($partner['logo'])) {
                        $logoHtml = '<img src="assets/images/partners/' . htmlspecialchars($partner['logo']) . '" 
                                        alt="Logo" class="partner-logo">';
                    } else {
                        $logoHtml = '<div class="partner-logo-placeholder">
                                        <i class="fas fa-building"></i>
                                    </div>';
                    }
                    
                    // Period
                    $debut = $partner['date_debut'] ? date('d/m/Y', strtotime($partner['date_debut'])) : '?';
                    $fin = $partner['date_fin'] ? date('d/m/Y', strtotime($partner['date_fin'])) : '?';
                    $periode = "$debut - $fin";
                    
                    $tableData[] = [
                        'id' => $partner['id_partner'],
                        'nom_html' => $logoHtml . '<strong>' . htmlspecialchars($partner['nom_partenaire']) . '</strong>',
                        'type_html' => '<span class="type-badge type-' . $partner['type_partenaire'] . '">
                                            ' . htmlspecialchars($partner['type_partenaire']) . '
                                        </span>',
                        'type_collaboration' => htmlspecialchars($partner['type_collaboration'] ?? '-'),
                        'periode' => $periode
                    ];
                }

                TableView::render([
                    'id' => 'project-partners-table',
                    'columns' => [
                        'nom' => 'Partenaire',
                        'type' => 'Type',
                        'type_collaboration' => 'Collaboration',
                        'periode' => 'Période'
                    ],
                    'data' => $tableData,
                    'actions' => [
                        [
                            'label' => 'Retirer',
                            'url' => 'index.php?url=admin/removeProjectPartner/' . $project['id_project'] . '/{id}',
                            'icon' => 'fas fa-unlink',
                            'class' => 'btn-action delete',
                            'confirm' => true
                        ]
                    ],
                    'searchable' => false,
                    'empty_message' => 'Aucun partenaire associé à ce projet'
                ]);
                ?>
            </div>
        </div>

        <!-- Membres du projet -->
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-users"></i> Membres du projet (<?= count($members) ?>)</h2>
                <!-- NOUVEAU : Bouton ajouter -->
                <a href="index.php?url=admin/addProjectMember/<?= $project['id_project'] ?>" class="btn primary btn-sm">
                    <i class="fas fa-user-plus"></i> Ajouter un membre
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($members)): ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <p>Aucun membre dans ce projet</p>
                        <a href="index.php?url=admin/addProjectMember/<?= $project['id_project'] ?>" class="btn primary" style="margin-top: 16px;">
                            <i class="fas fa-user-plus"></i> Ajouter le premier membre
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Membre</th>
                                    <th>Email</th>
                                    <th>Grade</th>
                                    <th>Rôle dans le projet</th>
                                    <th>Rôles système</th>
                                    <th>Actions</th> <!-- NOUVEAU -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($members as $member): ?>
                                    <tr>
                                        <td>
                                            <img src="assets/images/users/<?= htmlspecialchars($member['photo'] ?? 'default.jpg') ?>" 
                                                alt="Photo" class="member-avatar">
                                            <strong><?= htmlspecialchars($member['nom'] . ' ' . $member['prenom']) ?></strong>
                                        </td>
                                        <td><?= htmlspecialchars($member['email']) ?></td>
                                        <td><?= htmlspecialchars($member['grade'] ?? '-') ?></td>
                                        <td>
                                            <!-- NOUVEAU : Rôle éditable inline -->
                                            <form method="POST" action="index.php?url=admin/editProjectMemberRole/<?= $project['id_project'] ?>/<?= $member['id_user'] ?>" class="inline-role-form">
                                                <select name="role_projet" class="role-select" onchange="this.form.submit()">
                                                    <option value="Chercheur" <?= ($member['role_projet'] ?? '') === 'Chercheur' ? 'selected' : '' ?>>Chercheur</option>
                                                    <option value="Doctorant" <?= ($member['role_projet'] ?? '') === 'Doctorant' ? 'selected' : '' ?>>Doctorant</option>
                                                    <option value="Post-doctorant" <?= ($member['role_projet'] ?? '') === 'Post-doctorant' ? 'selected' : '' ?>>Post-doctorant</option>
                                                    <option value="Ingénieur" <?= ($member['role_projet'] ?? '') === 'Ingénieur' ? 'selected' : '' ?>>Ingénieur</option>
                                                    <option value="Membre" <?= ($member['role_projet'] ?? 'Membre') === 'Membre' ? 'selected' : '' ?>>Membre</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td>
                                            <?php if (!empty($member['roles'])): ?>
                                                <?php foreach (explode(', ', $member['roles']) as $role): ?>
                                                    <span class="role-badge role-<?= strtolower($role) ?>">
                                                        <?= htmlspecialchars($role) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="badge default">Aucun rôle</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <!-- NOUVEAU : Actions -->
                                            <div class="action-buttons">
                                                <a href="index.php?url=admin/removeProjectMember/<?= $project['id_project'] ?>/<?= $member['id_user'] ?>" 
                                                class="btn-action delete"
                                                onclick="return confirm('Retirer <?= htmlspecialchars($member['nom']) ?> de ce projet ?')">
                                                    <i class="fas fa-user-times"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="form-actions actions-spacing">
            <a href="index.php?url=admin/editProject/<?= $project['id_project'] ?>" class="btn primary">
                <i class="fas fa-edit"></i> Modifier
            </a>
            <a href="index.php?url=admin/projects" class="btn ghost">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>

        </div><!-- .admin-main -->
        <?php
        AdminFooterView::render();
    }

    /**
     * Formulaire d'ajout de membre à un projet
     */
    public static function renderAddMemberForm(array $data) {
        $settings = $data['settings'] ?? [];
        $project = $data['project'] ?? null;
        $availableUsers = $data['availableUsers'] ?? [];
        $error = $data['error'] ?? null;
        
        if (!$project) {
            echo "<p>Projet introuvable</p>";
            return;
        }
        
        AdminHeaderView::render($settings, 'Ajouter un membre');
        AdminSidebarView::render('projects');
        ?>
        
        <div class="admin-page-header">
            <h1>Ajouter un membre au projet</h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <a href="index.php?url=admin/projects">Projets</a>
                <span>/</span>
                <a href="index.php?url=admin/viewProject/<?= $project['id_project'] ?>">
                    <?= htmlspecialchars($project['titre']) ?>
                </a>
                <span>/</span>
                <span>Ajouter membre</span>
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
                <h2><i class="fas fa-user-plus"></i> Sélectionner un membre</h2>
            </div>
            
            <div class="card-body">
                <?php if (empty($availableUsers)): ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <p>Tous les utilisateurs sont déjà membres de ce projet</p>
                    </div>
                    <div class="form-actions">
                        <a href="index.php?url=admin/viewProject/<?= $project['id_project'] ?>" class="btn ghost">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="index.php?url=admin/addProjectMember/<?= $project['id_project'] ?>">
                        
                        <div class="form-group">
                            <label for="id_user">Utilisateur *</label>
                            <select id="id_user" name="id_user" required>
                                <option value="">-- Sélectionner un utilisateur --</option>
                                <?php foreach ($availableUsers as $user): ?>
                                    <option value="<?= $user['id_user'] ?>">
                                        <?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?>
                                        <?php if (!empty($user['roles'])): ?>
                                            (<?= htmlspecialchars(implode(', ', $user['roles'])) ?>)
                                        <?php endif; ?>
                                        <?php if (!empty($user['grade'])): ?>
                                            - <?= htmlspecialchars($user['grade']) ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-help-text">
                                Choisissez l'utilisateur à ajouter au projet
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="role_projet">Rôle dans le projet *</label>
                            <select id="role_projet" name="role_projet" required>
                                <option value="Chercheur">Chercheur</option>
                                <option value="Doctorant">Doctorant</option>
                                <option value="Post-doctorant">Post-doctorant</option>
                                <option value="Ingénieur">Ingénieur</option>
                                <option value="Membre" selected>Membre</option>
                            </select>
                            <small class="form-help-text">
                                Définissez le rôle de cette personne dans le projet
                            </small>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn primary">
                                <i class="fas fa-user-plus"></i> Ajouter au projet
                            </button>
                            <a href="index.php?url=admin/viewProject/<?= $project['id_project'] ?>" class="btn ghost">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        </div><!-- .admin-main -->
        <?php
        AdminFooterView::render();
    }

    /**
     * Formulaire d'ajout de partenaire
     */
    public static function renderAddPartnerForm(array $data) {
        $settings = $data['settings'] ?? [];
        $project = $data['project'] ?? null;
        $availablePartners = $data['availablePartners'] ?? [];
        $error = $data['error'] ?? null;
        
        AdminHeaderView::render($settings, 'Ajouter un partenaire');
        AdminSidebarView::render('projects');
        ?>
        
        <div class="admin-page-header">
            <h1>Ajouter un partenaire au projet</h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <a href="index.php?url=admin/projects">Projets</a>
                <span>/</span>
                <a href="index.php?url=admin/viewProject/<?= $project['id_project'] ?>">
                    <?= htmlspecialchars($project['titre']) ?>
                </a>
                <span>/</span>
                <span>Ajouter partenaire</span>
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
                <h2><i class="fas fa-handshake"></i> Informations du partenariat</h2>
            </div>
            
            <div class="card-body">
                <?php if (empty($availablePartners)): ?>
                    <div class="empty-state">
                        <i class="fas fa-handshake"></i>
                        <p>Tous les partenaires sont déjà associés à ce projet</p>
                    </div>
                    <div class="form-actions">
                        <a href="index.php?url=admin/viewProject/<?= $project['id_project'] ?>" class="btn ghost">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                <?php else: ?>
                    <form method="POST">
                        
                        <div class="form-group">
                            <label for="id_partner">Partenaire *</label>
                            <select id="id_partner" name="id_partner" required>
                                <option value="">-- Sélectionner un partenaire --</option>
                                <?php foreach ($availablePartners as $partner): ?>
                                    <option value="<?= $partner['id_partner'] ?>">
                                        <?= htmlspecialchars($partner['nom_partenaire']) ?>
                                        (<?= htmlspecialchars($partner['type_partenaire']) ?>)
                                        <?php if (!empty($partner['pays'])): ?>
                                            - <?= htmlspecialchars($partner['pays']) ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="type_collaboration">Type de collaboration</label>
                            <input type="text" id="type_collaboration" name="type_collaboration" 
                                placeholder="Ex: Recherche conjointe, Financement, Échange...">
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="date_debut">Date début</label>
                                <input type="date" id="date_debut" name="date_debut">
                            </div>
                            
                            <div class="form-group">
                                <label for="date_fin">Date fin</label>
                                <input type="date" id="date_fin" name="date_fin">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description du partenariat</label>
                            <textarea id="description" name="description" rows="4" 
                                    placeholder="Décrivez la nature et les objectifs du partenariat..."></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn primary">
                                <i class="fas fa-handshake"></i> Ajouter le partenaire
                            </button>
                            <a href="index.php?url=admin/viewProject/<?= $project['id_project'] ?>" class="btn ghost">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        </div><!-- .admin-main -->
        <?php
        AdminFooterView::render();
    }
}
?>