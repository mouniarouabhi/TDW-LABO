<?php
// views/admin/AdminUserView.php
// VERSION AMÉLIORÉE - Stats + Filtres + 0 styles inline

class AdminUserView {
    
    /**
     * Affichage de la liste des utilisateurs
     */
    public static function renderList(array $data) {
        $settings = $data['settings'] ?? [];
        $users = $data['users'] ?? [];
        $stats = $data['stats'] ?? [];
        
        AdminHeaderView::render($settings, 'Gestion des utilisateurs');
        AdminSidebarView::render('users');
        ?>
        
        <div class="admin-page-header">
            <h1>Gestion des utilisateurs</h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <span>Utilisateurs</span>
            </div>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?php
                if ($_GET['success'] === '1' || $_GET['success'] === 'created') echo 'Utilisateur créé avec succès';
                elseif ($_GET['success'] === 'updated') echo 'Utilisateur mis à jour avec succès';
                elseif ($_GET['success'] === 'deleted') echo 'Utilisateur supprimé avec succès';
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <?php
                if ($_GET['error'] === 'not_found') echo 'Utilisateur introuvable';
                elseif ($_GET['error'] === 'delete_failed') echo 'Erreur lors de la suppression';
                elseif ($_GET['error'] === 'cannot_delete_admin') echo 'Impossible de supprimer l\'administrateur principal';
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
                    <div class="stat-label">Total utilisateurs</div>
                    <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Actifs</div>
                    <div class="stat-value"><?= $stats['actifs'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Chercheurs</div>
                    <div class="stat-value"><?= $stats['chercheurs'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Inactifs</div>
                    <div class="stat-value"><?= $stats['inactifs'] ?? 0 ?></div>
                </div>
            </div>
        </div>
        
        <div class="content-card">
            <div class="card-header">
                <h2>Liste des utilisateurs (<?= count($users) ?>)</h2>
                <a href="index.php?url=admin/addUser" class="btn primary">
                    <i class="fas fa-plus"></i> Ajouter un utilisateur
                </a>
            </div>
            
            <!-- ========================================
                 FILTRES
                 ======================================== -->
            <div class="filters-section">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="filter-role"><i class="fas fa-user-tag"></i> Rôle</label>
                        <select id="filter-role" class="filter-select">
                            <option value="">Tous les rôles</option>
                            <option value="etudiant">Étudiant</option>
                            <option value="doctorant">Doctorant</option>
                            <option value="chercheur">Chercheur</option>
                            <option value="enseignant">Enseignant</option>
                            <option value="invite">Invité</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-statut"><i class="fas fa-flag"></i> Statut</label>
                        <select id="filter-statut" class="filter-select">
                            <option value="">Tous les statuts</option>
                            <option value="actif">Actif</option>
                            <option value="suspendu">Suspendu</option>
                            <option value="inactif">Inactif</option>
                        </select>
                    </div>
                </div>
                
                <button id="reset-filters" class="btn ghost">
                    <i class="fas fa-redo"></i> Réinitialiser
                </button>
            </div>
            
            <div class="card-body">
                <?php
                // Préparation des données pour le tableau
                $tableData = [];
                foreach ($users as $user) {
                    // Photo avec classe
                    $photoHtml = '<img src="assets/images/users/' . htmlspecialchars($user['photo']) . '" '
                               . 'class="user-avatar" '
                               . 'alt="' . htmlspecialchars($user['nom']) . '">';
                    
                    // ========================================
                    // CORRECTION - Gérer role principal
                    // ========================================
                    // Récupérer le rôle principal (soit direct, soit premier du tableau roles)
                    $mainRole = 'utilisateur'; // Défaut
                    if (!empty($user['role'])) {
                        $mainRole = $user['role'];
                    } elseif (!empty($user['roles']) && is_array($user['roles'])) {
                        $mainRole = $user['roles'][0]['nom'] ?? 'utilisateur';
                    }
                    
                    // Badges avec icônes
                    $roleIcons = [
                        'admin' => 'fas fa-crown',
                        'enseignant' => 'fas fa-chalkboard-teacher',
                        'chercheur' => 'fas fa-flask',
                        'doctorant' => 'fas fa-user-graduate',
                        'etudiant' => 'fas fa-graduation-cap',
                        'invite' => 'fas fa-user'
                    ];
                    
                    $roleIcon = $roleIcons[$mainRole] ?? 'fas fa-user';
                    $roleHtml = '<span class="role-badge role-' . htmlspecialchars($mainRole) . '">'
                              . '<i class="' . $roleIcon . '"></i> '
                              . htmlspecialchars(ucfirst($mainRole))
                              . '</span>';
                    
                    $statutClass = $user['statut'] === 'actif' ? 'success' : ($user['statut'] === 'suspendu' ? 'warning' : 'danger');
                    $statutHtml = '<span class="badge ' . $statutClass . '">' . htmlspecialchars(ucfirst($user['statut'])) . '</span>';
                    
                    $tableData[] = [
                        'id' => $user['id_user'],
                        'photo_html' => $photoHtml,
                        'nom' => $user['nom'] . ' ' . $user['prenom'],
                        'email' => $user['email'],
                        'role_html' => $roleHtml,
                        'role_value' => $mainRole, // Pour filtrage
                        'grade' => $user['grade'] ?? '-',
                        'statut_html' => $statutHtml,
                        'statut_value' => $user['statut'], // Pour filtrage
                        'date_creation' => date('d/m/Y', strtotime($user['date_creation']))
                    ];
                }
                
                TableView::render([
                    'id' => 'users-table',
                    'columns' => [
                        'photo' => 'Photo',
                        'nom' => 'Nom complet',
                        'email' => 'Email',
                        'role' => 'Rôle',
                        'grade' => 'Grade',
                        'statut' => 'Statut',
                        'date_creation' => 'Date création'
                    ],
                    'data' => $tableData,
                    'actions' => [
                        [
                        'label' => 'Voir',
                        'url' => 'index.php?url=admin/viewUser/{id}',
                            'icon' => 'fas fa-eye',
                            'class' => 'btn-action view'
                        ],
                        [
                            'label' => 'Modifier',
                            'url' => 'index.php?url=admin/editUser/{id}',
                            'icon' => 'fas fa-edit',
                            'class' => 'btn-action edit'
                        ],
                        [
                            'label' => 'Supprimer',
                            'url' => 'index.php?url=admin/deleteUser/{id}',
                            'icon' => 'fas fa-trash',
                            'class' => 'btn-action delete',
                            'confirm' => true
                        ]
                    ],
                    'searchable' => true,
                    'empty_message' => 'Aucun utilisateur trouvé'
                ]);
                ?>
            </div>
        </div>
        
        <!-- ========================================
             JAVASCRIPT FILTRES
             ======================================== -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('users-table');
            const filterRole = document.getElementById('filter-role');
            const filterStatut = document.getElementById('filter-statut');
            const resetBtn = document.getElementById('reset-filters');
            
            function applyFilters() {
                const rows = table.querySelectorAll('tbody tr');
                const roleValue = filterRole.value.toLowerCase();
                const statutValue = filterStatut.value.toLowerCase();
                
                rows.forEach(row => {
                    const role = row.cells[3].textContent.toLowerCase();
                    const statut = row.cells[5].textContent.toLowerCase();
                    
                    const matchRole = !roleValue || role.includes(roleValue);
                    const matchStatut = !statutValue || statut.includes(statutValue);
                    
                    if (matchRole && matchStatut) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
            
            filterRole.addEventListener('change', applyFilters);
            filterStatut.addEventListener('change', applyFilters);
            
            resetBtn.addEventListener('click', function() {
                filterRole.value = '';
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
     * Formulaire d'ajout/modification d'utilisateur
     */
    public static function renderForm(array $data) {
        $settings = $data['settings'] ?? [];
        $action = $data['action'] ?? 'add';
        $user = $data['user'] ?? null;
        $roles = $data['roles'];
        $userRoles=$data['userRoles'];
        $error = $data['error'] ?? null;
        
        $title = $action === 'add' ? 'Ajouter un utilisateur' : 'Modifier un utilisateur';
        $submitUrl = $action === 'add' ? 'index.php?url=admin/addUser' : 'index.php?url=admin/editUser/' . $user['id_user'];
        
        AdminHeaderView::render($settings, $title);
        AdminSidebarView::render('users');
        ?>
        
        <div class="admin-page-header">
            <h1><?= $title ?></h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <a href="index.php?url=admin/users">Utilisateurs</a>
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
                <h2><?= $title ?></h2>
            </div>
            
            <div class="card-body">
                <form method="POST" action="<?= $submitUrl ?>" enctype="multipart/form-data">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nom">Nom *</label>
                            <input type="text" id="nom" name="nom" 
                                   value="<?= htmlspecialchars($user['nom'] ?? '') ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="prenom">Prénom *</label>
                            <input type="text" id="prenom" name="prenom" 
                                   value="<?= htmlspecialchars($user['prenom'] ?? '') ?>" 
                                   required>
                        </div>
                        
                        <?php if ($action === 'add'): ?>
                        <div class="form-group">
                            <label for="login">Login *</label>
                            <input type="text" id="login" name="login" required>
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" 
                                   value="<?= htmlspecialchars($user['email'] ?? '') ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Mot de passe <?= $action === 'add' ? '*' : '(laisser vide pour ne pas changer)' ?></label>
                            <input type="password" id="password" name="password" 
                                   <?= $action === 'add' ? 'required' : '' ?>>
                        </div>
                        
                        <div class="form-group">
                            <label for="role">Rôle *</label>
                            <select id="role" name="role" required>
                                <?php
                                $currentRoleId = null;
                                if ($action === 'edit' && !empty($userRoles)) {
                                    // userRoles contient les IDs des rôles : [1] ou [2] ou [3], etc.
                                    $currentRoleId = $userRoles[0];
                                }
                                ?>
                                <option value="etudiant" <?= $currentRoleId == 5 ? 'selected' : '' ?>>Étudiant</option>
                                <option value="doctorant" <?= $currentRoleId == 4 ? 'selected' : '' ?>>Doctorant</option>
                                <option value="chercheur" <?= $currentRoleId == 3 ? 'selected' : '' ?>>Chercheur</option>
                                <option value="enseignant" <?= $currentRoleId == 2 ? 'selected' : '' ?>>Enseignant</option>
                                <option value="invite" <?= $currentRoleId == 6 ? 'selected' : '' ?>>Invité</option>
                                <option value="admin" <?= $currentRoleId == 1 ? 'selected' : '' ?>>Administrateur</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="grade">Grade</label>
                            <input type="text" id="grade" name="grade" 
                                   value="<?= htmlspecialchars($user['grade'] ?? '') ?>"
                                   placeholder="Ex: Professeur, Maître de conférences...">
                        </div>
                        
                        <div class="form-group">
                            <label for="poste">Poste</label>
                            <input type="text" id="poste" name="poste" 
                                   value="<?= htmlspecialchars($user['poste'] ?? '') ?>"
                                   placeholder="Ex: Directeur, Responsable équipe...">
                        </div>
                        
                        <?php if ($action === 'edit'): ?>
                        <div class="form-group">
                            <label for="statut">Statut</label>
                            <select id="statut" name="statut">
                                <option value="actif" <?= ($user['statut'] ?? 'actif') === 'actif' ? 'selected' : '' ?>>Actif</option>
                                <option value="suspendu" <?= ($user['statut'] ?? '') === 'suspendu' ? 'selected' : '' ?>>Suspendu</option>
                                <option value="inactif" <?= ($user['statut'] ?? '') === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="photo">Photo de profil</label>
                            <input type="file" id="photo" name="photo" accept="image/*">
                            <?php if ($action === 'edit' && !empty($user['photo'])): ?>
                                <!-- ========================================
                                     CORRECTION - Classe au lieu de style inline
                                     ======================================== -->
                                <img src="assets/images/users/<?= htmlspecialchars($user['photo']) ?>" 
                                     class="profile-photo-preview"
                                     alt="Photo actuelle">
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="domaine_recherche">Domaine de recherche</label>
                        <textarea id="domaine_recherche" name="domaine_recherche" 
                                  rows="3"><?= htmlspecialchars($user['domaine_recherche'] ?? '') ?></textarea>
                    </div>
                    
                    <?php if ($action === 'edit'): ?>
                    <div class="form-group full-width">
                        <label for="biographie">Biographie</label>
                        <textarea id="biographie" name="biographie" 
                                  rows="5"><?= htmlspecialchars($user['biographie'] ?? '') ?></textarea>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn primary">
                            <i class="fas fa-save"></i>
                            <?= $action === 'add' ? 'Créer l\'utilisateur' : 'Enregistrer les modifications' ?>
                        </button>
                        <a href="index.php?url=admin/users" class="btn ghost">
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
     * Vue détaillée d'un utilisateur avec ses documents
     */
    public static function renderDetails(array $data) {
        $settings = $data['settings'] ?? [];
        $user = $data['user'] ?? null;
        $documents = $data['documents'] ?? [];
        $stats = $data['stats'] ?? [];
        $roles = $data['roles'] ?? [];
        $teams = $data['teams'] ?? [];
        $projects = $data['projects'] ?? [];
        $publications = $data['publications'] ?? [];
        
        if (!$user) {
            echo "Utilisateur introuvable";
            return;
        }
        
        AdminHeaderView::render($settings, 'Détails utilisateur');
        AdminSidebarView::render('users');
        ?>
        
        <div class="admin-page-header">
            <h1>Profil de <?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?></h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <a href="index.php?url=admin/users">Utilisateurs</a>
                <span>/</span>
                <span>Détails</span>
            </div>
        </div>
        
        <!-- ========================================
            INFORMATIONS PRINCIPALES
            ======================================== -->
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-user"></i> Informations principales</h2>
                <div class="card-actions">
                    <a href="index.php?url=admin/editUser/<?= $user['id_user'] ?>" class="btn primary">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                    <a href="index.php?url=admin/users" class="btn ghost">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <div class="user-profile-header">
                    <div class="user-avatar-large">
                        <img src="assets/images/users/<?= htmlspecialchars($user['photo']) ?>" 
                            alt="<?= htmlspecialchars($user['nom']) ?>">
                    </div>
                    
                    <div class="user-main-info">
                        <h3><?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?></h3>
                        
                        <div class="user-badges">
                            <?php
                            // Afficher tous les rôles
                            if (!empty($roles)) {
                                foreach ($roles as $role) {
                                    $roleIcons = [
                                        'admin' => 'fas fa-crown',
                                        'enseignant' => 'fas fa-chalkboard-teacher',
                                        'chercheur' => 'fas fa-flask',
                                        'doctorant' => 'fas fa-user-graduate',
                                        'etudiant' => 'fas fa-graduation-cap',
                                        'invite' => 'fas fa-user'
                                    ];
                                    $roleName = $role['nom_role'] ?? $role['nom'] ?? 'utilisateur';
                                    $roleIcon = $roleIcons[$roleName] ?? 'fas fa-user';
                                    ?>
                                    <span class="role-badge role-<?= htmlspecialchars($roleName) ?>">
                                        <i class="<?= $roleIcon ?>"></i>
                                        <?= htmlspecialchars(ucfirst($roleName)) ?>
                                    </span>
                                    <?php
                                }
                            }
                            
                            // Statut
                            $statutClass = $user['statut'] === 'actif' ? 'success' : ($user['statut'] === 'suspendu' ? 'warning' : 'danger');
                            ?>
                            <span class="badge <?= $statutClass ?>">
                                <?= htmlspecialchars(ucfirst($user['statut'])) ?>
                            </span>
                        </div>
                        
                        <div class="user-contact-info">
                            <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                            <?php if (!empty($user['grade'])): ?>
                                <p><i class="fas fa-graduation-cap"></i> <?= htmlspecialchars($user['grade']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($user['poste'])): ?>
                                <p><i class="fas fa-briefcase"></i> <?= htmlspecialchars($user['poste']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($user['domaine_recherche'])): ?>
                    <div class="user-section">
                        <h4><i class="fas fa-flask"></i> Domaine de recherche</h4>
                        <p><?= nl2br(htmlspecialchars($user['domaine_recherche'])) ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($user['biographie'])): ?>
                    <div class="user-section">
                        <h4><i class="fas fa-info-circle"></i> Biographie</h4>
                        <p><?= nl2br(htmlspecialchars($user['biographie'])) ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="user-meta">
                    <p><i class="fas fa-calendar-plus"></i> <strong>Compte créé :</strong> <?= date('d/m/Y à H:i', strtotime($user['date_creation'])) ?></p>
                    <p><i class="fas fa-calendar-check"></i> <strong>Dernière modification :</strong> <?= date('d/m/Y à H:i', strtotime($user['date_modification'])) ?></p>
                </div>
            </div>
        </div>
        
        <!-- ========================================
            STATISTIQUES D'ACTIVITÉ
            ======================================== -->
        <div class="stats-grid stats-grid-spacing">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Projets</div>
                    <div class="stat-value"><?= $stats['projects'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Publications</div>
                    <div class="stat-value"><?= $stats['publications'] ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-folder"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Documents</div>
                    <div class="stat-value"><?= count($documents) ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Équipes</div>
                    <div class="stat-value"><?= count($teams) ?></div>
                </div>
            </div>
        </div>
        
        <!-- ========================================
            ÉQUIPES
            ======================================== -->
        <?php if (!empty($teams)): ?>
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-users"></i> Équipes (<?= count($teams) ?>)</h2>
            </div>
            
            <div class="card-body">
                <div class="teams-grid">
                    <?php foreach ($teams as $team): ?>
                        <div class="team-card">
                            <h4><?= htmlspecialchars($team['nom_equipe']) ?></h4>
                            <?php if (!empty($team['thematique'])): ?>
                                <p class="team-thematique">
                                    <i class="fas fa-tag"></i>
                                    <?= htmlspecialchars($team['thematique']) ?>
                                </p>
                            <?php endif; ?>
                            <a href="index.php?url=admin/team/detail/<?= $team['id_team'] ?>" class="btn-link">
                                Voir l'équipe <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- ========================================
            PROJETS
            ======================================== -->
        <?php if (!empty($projects)): ?>
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-project-diagram"></i> Projets (<?= count($projects) ?>)</h2>
            </div>
            
            <div class="card-body">
                <div class="projects-list">
                    <?php foreach ($projects as $project): ?>
                        <div class="project-item">
                            <div class="project-header">
                                <h4><?= htmlspecialchars($project['titre']) ?></h4>
                                <span class="badge <?= $project['statut'] === 'en_cours' ? 'primary' : ($project['statut'] === 'termine' ? 'success' : 'warning') ?>">
                                    <?= ucfirst(str_replace('_', ' ', $project['statut'])) ?>
                                </span>
                            </div>
                            <?php if (!empty($project['thematique'])): ?>
                                <p class="project-thematique">
                                    <i class="fas fa-tag"></i>
                                    <?= htmlspecialchars($project['thematique']) ?>
                                </p>
                            <?php endif; ?>
                            <p class="project-role">
                                <i class="fas fa-user-tag"></i>
                                <strong>Rôle :</strong> <?= htmlspecialchars($project['role_projet'] ?? 'Membre') ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- ========================================
            PUBLICATIONS
            ======================================== -->
        <?php if (!empty($publications)): ?>
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-book"></i> Publications (<?= count($publications) ?>)</h2>
            </div>
            
            <div class="card-body">
                <div class="publications-list">
                    <?php foreach ($publications as $pub): ?>
                        <div class="publication-item">
                            <h4><?= htmlspecialchars($pub['titre']) ?></h4>
                            <div class="publication-meta">
                                <span><i class="fas fa-calendar"></i> <?= $pub['annee_publication'] ?></span>
                                <span><i class="fas fa-tag"></i> <?= ucfirst($pub['type_publication']) ?></span>
                                <?php if (!empty($pub['doi'])): ?>
                                    <span><i class="fas fa-fingerprint"></i> DOI: <?= htmlspecialchars($pub['doi']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- ========================================
            DOCUMENTS
            ======================================== -->
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-folder-open"></i> Documents (<?= count($documents) ?>)</h2>
            </div>
            
            <div class="card-body">
                <?php if (empty($documents)): ?>
                    <div class="empty-state">
                        <i class="fas fa-file-alt"></i>
                        <p>Aucun document uploadé</p>
                    </div>
                <?php else: ?>
                    <div class="documents-grid">
                        <?php foreach ($documents as $doc): ?>
                            <div class="document-card">
                                <div class="document-icon-large">
                                    <i class="<?= DocumentModel::getIcon($doc['extension']) ?>"></i>
                                </div>
                                
                                <div class="document-details">
                                    <h4><?= htmlspecialchars($doc['titre']) ?></h4>
                                    
                                    <div class="document-badges">
                                        <span class="badge">
                                            <i class="fas fa-tag"></i>
                                            <?= ucfirst(str_replace('_', ' ', $doc['type_document'])) ?>
                                        </span>
                                        
                                        <span class="badge <?= $doc['visibilite'] === 'public' ? 'success' : ($doc['visibilite'] === 'equipe' ? 'info' : '') ?>">
                                            <i class="fas fa-<?= $doc['visibilite'] === 'public' ? 'globe' : ($doc['visibilite'] === 'equipe' ? 'users' : 'lock') ?>"></i>
                                            <?= ucfirst($doc['visibilite']) ?>
                                        </span>
                                    </div>
                                    
                                    <?php if (!empty($doc['description'])): ?>
                                        <p class="document-description">
                                            <?= nl2br(htmlspecialchars(substr($doc['description'], 0, 100))) ?>
                                            <?= strlen($doc['description']) > 100 ? '...' : '' ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="document-meta-grid">
                                        <span>
                                            <i class="fas fa-file"></i>
                                            .<?= htmlspecialchars($doc['extension']) ?>
                                        </span>
                                        <span>
                                            <i class="fas fa-weight"></i>
                                            <?= DocumentModel::formatSize($doc['taille_fichier']) ?>
                                        </span>
                                        <span>
                                            <i class="fas fa-calendar"></i>
                                            <?= date('d/m/Y', strtotime($doc['date_upload'])) ?>
                                        </span>
                                    </div>
                                    
                                    <div class="document-filename">
                                        <i class="fas fa-paperclip"></i>
                                        <small><?= htmlspecialchars($doc['nom_original']) ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Quota d'espace utilisé -->
                    <?php
                    $totalSize = array_sum(array_column($documents, 'taille_fichier'));
                    $maxQuota = 50 * 1024 * 1024; // 50 MB
                    $percentUsed = ($totalSize / $maxQuota) * 100;
                    ?>
                    <div class="storage-info">
                        <h4><i class="fas fa-hdd"></i> Espace de stockage utilisé</h4>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= min($percentUsed, 100) ?>%"></div>
                        </div>
                        <p>
                            <?= DocumentModel::formatSize($totalSize) ?> / <?= DocumentModel::formatSize($maxQuota) ?>
                            (<?= number_format($percentUsed, 1) ?>%)
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        </div><!-- .admin-main -->
        <?php
        AdminFooterView::render();
    }
}
?>