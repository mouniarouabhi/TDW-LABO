<?php
// views/admin/AdminTeamView.php
// VERSION AMÉLIORÉE - Stats + Filtres + 0 styles inline

class AdminTeamView {
    
    /**
     * Affichage de la liste des équipes
     */
    public static function renderList(array $data) {
        $settings = $data['settings'] ?? [];
        $teams = $data['teams'] ?? [];
        $stats = $data['stats'] ?? [];
        
        AdminHeaderView::render($settings, 'Gestion des équipes');
        AdminSidebarView::render('teams');
        ?>
        
        <div class="admin-page-header">
            <h1>Gestion des équipes</h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <span>Équipes</span>
            </div>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?php
                if ($_GET['success'] === 'created') echo 'Équipe créée avec succès';
                elseif ($_GET['success'] === 'updated') echo 'Équipe mise à jour avec succès';
                elseif ($_GET['success'] === 'deleted') echo 'Équipe supprimée avec succès';
                elseif ($_GET['success'] === 'member_added') echo 'Membre ajouté à l\'équipe';
                elseif ($_GET['success'] === 'member_removed') echo 'Membre retiré de l\'équipe';
                ?>
            </div>
        <?php endif; ?>
        
        <!-- ========================================
             STATISTIQUES
             ======================================== -->
        <div class="stats-grid stats-grid-spacing">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total équipes</div>
                    <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Avec chef</div>
                    <div class="stat-value"><?= $stats['with_chef'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total membres</div>
                    <div class="stat-value"><?= $stats['total_members'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Moyenne membres/équipe</div>
                    <div class="stat-value"><?= $stats['avg_members'] ?? 0 ?></div>
                </div>
            </div>
        </div>
        
        <div class="content-card">
            <div class="card-header">
                <h2>Liste des équipes (<?= count($teams) ?>)</h2>
                <a href="index.php?url=admin/addTeam" class="btn primary">
                    <i class="fas fa-plus"></i> Ajouter une équipe
                </a>
            </div>
            
            <!-- ========================================
                 FILTRES
                 ======================================== -->
            <div class="filters-section">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="filter-chef"><i class="fas fa-user-tie"></i> Chef</label>
                        <select id="filter-chef" class="filter-select">
                            <option value="">Tous</option>
                            <option value="oui">Avec chef</option>
                            <option value="non">Sans chef</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-thematique"><i class="fas fa-tag"></i> Thématique</label>
                        <select id="filter-thematique" class="filter-select">
                            <option value="">Toutes</option>
                            <?php
                            $thematiques = array_unique(array_filter(array_column($teams, 'thematique')));
                            sort($thematiques);
                            foreach ($thematiques as $them):
                            ?>
                                <option value="<?= htmlspecialchars($them) ?>"><?= htmlspecialchars($them) ?></option>
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
                foreach ($teams as $team) {
                    // Badge chef
                    $chefBadge = !empty($team['chef_nom']) 
                        ? '<span class="user-badge">' . htmlspecialchars($team['chef_nom'] . ' ' . $team['chef_prenom']) . '</span>'
                        : '<span class="badge warning">Non assigné</span>';
                    
                    // Badge membres
                    $memberCount = $team['member_count'] ?? 0;
                    $memberBadge = '<span class="count-badge">' 
                                 . '<i class="fas fa-user-friends"></i> ' 
                                 . $memberCount 
                                 . '</span>';
                    
                    $tableData[] = [
                        'id' => $team['id_team'],
                        'nom_equipe_html' => '<strong>' . htmlspecialchars($team['nom_equipe']) . '</strong>',
                        'thematique_html' => !empty($team['thematique']) 
                            ? '<span class="domain-tag"><i class="fas fa-tag"></i> ' . htmlspecialchars($team['thematique']) . '</span>'
                            : '-',
                        'chef_html' => $chefBadge,
                        'membres_html' => $memberBadge,
                        'date_creation' => date('d/m/Y', strtotime($team['date_creation']))
                    ];
                }
                
                TableView::render([
                    'id' => 'teams-table',
                    'columns' => [
                        'nom_equipe' => 'Nom de l\'équipe',
                        'thematique' => 'Thématique',
                        'chef' => 'Chef d\'équipe',
                        'membres' => 'Membres',
                        'date_creation' => 'Date création'
                    ],
                    'data' => $tableData,
                    'actions' => [
                        [
                            'label' => 'Modifier',
                            'url' => 'index.php?url=admin/editTeam/{id}',
                            'icon' => 'fas fa-edit',
                            'class' => 'btn-action edit'
                        ],
                        [
                            'label' => 'Supprimer',
                            'url' => 'index.php?url=admin/deleteTeam/{id}',
                            'icon' => 'fas fa-trash',
                            'class' => 'btn-action delete',
                            'confirm' => true
                        ]
                    ],
                    'searchable' => true,
                    'empty_message' => 'Aucune équipe trouvée'
                ]);
                ?>
            </div>
        </div>
        
        <!-- ========================================
             JAVASCRIPT FILTRES
             ======================================== -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('teams-table');
            const filterChef = document.getElementById('filter-chef');
            const filterThematique = document.getElementById('filter-thematique');
            const resetBtn = document.getElementById('reset-filters');
            
            function applyFilters() {
                const rows = table.querySelectorAll('tbody tr');
                const chefValue = filterChef.value.toLowerCase();
                const thematValue = filterThematique.value.toLowerCase();
                
                rows.forEach(row => {
                    const chef = row.cells[2].textContent.toLowerCase();
                    const themat = row.cells[1].textContent.toLowerCase();
                    
                    const matchChef = !chefValue || 
                        (chefValue === 'oui' && !chef.includes('non assigné')) ||
                        (chefValue === 'non' && chef.includes('non assigné'));
                    const matchThemat = !thematValue || themat.includes(thematValue);
                    
                    if (matchChef && matchThemat) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
            
            filterChef.addEventListener('change', applyFilters);
            filterThematique.addEventListener('change', applyFilters);
            
            resetBtn.addEventListener('click', function() {
                filterChef.value = '';
                filterThematique.value = '';
                applyFilters();
            });
        });
        </script>
        
        </div><!-- .admin-main -->
        <?php
        AdminFooterView::render();
    }
    
    /**
     * Formulaire d'ajout/modification d'équipe
     */
    public static function renderForm(array $data) {
        $settings = $data['settings'] ?? [];
        $action = $data['action'] ?? 'add';
        $team = $data['team'] ?? null;
        $users = $data['users'] ?? [];
        $members = $data['members'] ?? [];
        $error = $data['error'] ?? null;
        
        $title = $action === 'add' ? 'Ajouter une équipe' : 'Modifier une équipe';
        $submitUrl = $action === 'add' ? 'index.php?url=admin/addTeam' : 'index.php?url=admin/editTeam/' . $team['id_team'];
        
        AdminHeaderView::render($settings, $title);
        AdminSidebarView::render('teams');
        ?>
        
        <div class="admin-page-header">
            <h1><?= $title ?></h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <a href="index.php?url=admin/teams">Équipes</a>
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
                <h2>Informations de l'équipe</h2>
            </div>
            
            <div class="card-body">
                <form method="POST" action="<?= $submitUrl ?>">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nom_equipe">Nom de l'équipe *</label>
                            <input type="text" id="nom_equipe" name="nom_equipe" 
                                   value="<?= htmlspecialchars($team['nom_equipe'] ?? '') ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="thematique">Thématique</label>
                            <input type="text" id="thematique" name="thematique" 
                                   value="<?= htmlspecialchars($team['thematique'] ?? '') ?>"
                                   placeholder="Ex: Intelligence Artificielle, Sécurité...">
                        </div>

                        <div class="form-group full-width">
                            <label for="id_chef">Chef d'équipe</label>
                            <select id="id_chef" name="id_chef">
                                <option value="">-- Sélectionner un chef --</option>
                                <?php foreach ($users as $user): ?>
                                    <?php if ($user['role'] === 'enseignant' || $user['role'] === 'chercheur'): ?>
                                        <option value="<?= $user['id_user'] ?>" 
                                                <?= ($team['id_chef'] ?? '') == $user['id_user'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?> 
                                            (<?= htmlspecialchars($user['grade'] ?? $user['role']) ?>)
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" 
                                  rows="5"><?= htmlspecialchars($team['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn primary">
                            <i class="fas fa-save"></i>
                            <?= $action === 'add' ? 'Créer l\'équipe' : 'Enregistrer les modifications' ?>
                        </button>
                        <a href="index.php?url=admin/teams" class="btn ghost">
                            <i class="fas fa-times"></i>
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if ($action === 'edit'): ?>
        <!-- Gestion des membres -->
        <div class="content-card">
            <div class="card-header">
                <h2>Membres de l'équipe (<?= count($members) ?>)</h2>
            </div>
            
            <div class="card-body">
                <!-- Ajouter un membre -->
                <form method="POST" action="index.php?url=admin/addTeamMember/<?= $team['id_team'] ?>" class="add-member-form">
                    <!-- ========================================
                         CORRECTION - Classe au lieu de style inline
                         ======================================== -->
                    <div class="add-member-container justify-between align-center display-flex">
                        <div class="form-group flex-1">
                            <label for="user_id">Ajouter un membre</label>
                            <select id="user_id" name="id_user" required>
                                <option value="">-- Sélectionner un utilisateur --</option>
                                <?php 
                                $memberIds = array_column($members, 'id_user');
                                foreach ($users as $user): 
                                    if (!in_array($user['id_user'], $memberIds)):
                                ?>
                                    <option value="<?= $user['id_user'] ?>">
                                        <?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?> 
                                        (<?= htmlspecialchars($user['role']) ?>)
                                    </option>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </select>
                        </div>
                        <button type="submit" class="btn primary">
                            <i class="fas fa-plus"></i> Ajouter
                        </button>
                    </div>
                </form>
                
                <!-- Liste des membres -->
                <?php
                $tableData = [];
                foreach ($members as $member) {
                    $tableData[] = [
                        'id' => $member['id_user'],
                        'photo_html' => '<img src="assets/images/users/' . htmlspecialchars($member['photo']) . '" 
                                            class="member-avatar"
                                            alt="' . htmlspecialchars($member['nom']) . '">',
                        'nom_html' => '<strong>' . htmlspecialchars($member['nom'] . ' ' . $member['prenom']) . '</strong>',
                        'role_html' => '<span class="badge">' . htmlspecialchars($member['role']) . '</span>',
                        'grade' => htmlspecialchars($member['grade'] ?? '-'),
                        'date_adhesion' => date('d/m/Y', strtotime($member['date_adhesion']))
                    ];
                }

                TableView::render([
                    'id' => 'team-members-table',
                    'columns' => [
                        'photo' => 'Photo',
                        'nom' => 'Nom',
                        'role' => 'Rôle',
                        'grade' => 'Grade',
                        'date_adhesion' => 'Date adhésion'
                    ],
                    'data' => $tableData,
                    'actions' => [
                        [
                            'label' => 'Retirer',
                            'url' => 'index.php?url=admin/removeTeamMember/' . $team['id_team'] . '/{id}',
                            'icon' => 'fas fa-times',
                            'class' => 'btn-action delete',
                            'confirm' => true
                        ]
                    ],
                    'searchable' => false,
                    'empty_message' => 'Aucun membre dans cette équipe'
                ]);
                ?>
            </div>
        </div>
        <?php endif; ?>
        
        </div><!-- .admin-main -->
        <?php
        AdminFooterView::render();
    }
}
?>