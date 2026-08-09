<?php
// views/admin/AdminRolesView.php
// COMPLÈTE - Permissions dynamiques depuis BDD avec checkboxes auto-submit

class AdminRoleView {
    
    /**
     * Affichage de la liste des rôles avec permissions inline
     */
    public static function renderList(array $data) {
        $settings = $data['settings'] ?? [];
        $roles = $data['roles'] ?? [];
        
        AdminHeaderView::render($settings, 'Gestion des rôles');
        AdminSidebarView::render('roles');
        ?>
        
        <div class="admin-page-header">
            <h1>Gestion des rôles et permissions</h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <span>Rôles</span>
            </div>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?php
                if ($_GET['success'] === 'created') echo 'Rôle créé avec succès';
                elseif ($_GET['success'] === 'updated') echo 'Rôle mis à jour avec succès';
                elseif ($_GET['success'] === 'deleted') echo 'Rôle supprimé avec succès';
                elseif ($_GET['success'] === 'permissions_saved') echo 'Permissions enregistrées avec succès';
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <?php
                if ($_GET['error'] === 'cannot_delete_system_role') echo 'Impossible de supprimer un rôle système';
                elseif ($_GET['error'] === 'delete_failed') echo 'Erreur lors de la suppression';
                else echo 'Une erreur est survenue';
                ?>
            </div>
        <?php endif; ?>
        
        <!-- ========================================
             STATISTIQUES
             ======================================== -->
        <div class="stats-grid stats-grid-spacing">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-user-tag"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total rôles</div>
                    <div class="stat-value"><?= count($roles) ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Utilisateurs</div>
                    <div class="stat-value">
                        <?= array_sum(array_column($roles, 'user_count')) ?>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Permissions totales</div>
                    <div class="stat-value">
                        <?php
                        $totalPerms = 0;
                        foreach ($roles as $role) {
                            if (!empty($role['permissions'])) {
                                $perms = is_array($role['permissions']) 
                                    ? $role['permissions'] 
                                    : explode(',', $role['permissions']);
                                $totalPerms += count($perms);
                            }
                        }
                        echo $totalPerms;
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Rôles système</div>
                    <div class="stat-value">6</div>
                </div>
            </div>
        </div>
        
        <!-- ========================================
            BOUTON AJOUTER
        ======================================== -->

        <div class="content-card">
            <div class="card-header">
                <h2>Liste des rôles (<?= count($roles) ?>)</h2>
                <div style="display: flex; gap: 10px;">
                    <a href="index.php?url=admin/permissions" class="btn info">
                        <i class="fas fa-shield-alt"></i> Gérer les permissions
                    </a>
                    <a href="index.php?url=admin/addRole" class="btn primary">
                        <i class="fas fa-plus"></i> Ajouter un rôle
                    </a>
                </div>
            </div>
        </div>
        
        <!-- ========================================
             LISTE DES RÔLES AVEC PERMISSIONS
             ======================================== -->
        <div class="roles-grid">
            <?php
            // ========================================
            // ✅ CHARGER TOUTES LES PERMISSIONS DEPUIS LA BDD
            // ========================================
            $allPermissions = $data['allPermissions'] ?? [];
            
            // ✅ Grouper les permissions par catégorie pour l'affichage
            $groupedPermissions = [
                'Utilisateurs' => [],
                'Équipes' => [],
                'Projets' => [],
                'Publications' => [],
                'Équipements' => [],
                'Événements' => [],
                'Administration' => []
            ];
            
            // ✅ Catégoriser automatiquement selon le code
            foreach ($allPermissions as $perm) {
                $code = strtoupper($perm['code']);
                
                if (strpos($code, 'USER') !== false) {
                    $groupedPermissions['Utilisateurs'][] = $perm;
                } elseif (strpos($code, 'TEAM') !== false) {
                    $groupedPermissions['Équipes'][] = $perm;
                } elseif (strpos($code, 'PROJECT') !== false) {
                    $groupedPermissions['Projets'][] = $perm;
                } elseif (strpos($code, 'PUBLICATION') !== false) {
                    $groupedPermissions['Publications'][] = $perm;
                } elseif (strpos($code, 'EQUIPMENT') !== false || strpos($code, 'RESERVE') !== false) {
                    $groupedPermissions['Équipements'][] = $perm;
                } elseif (strpos($code, 'EVENT') !== false) {
                    $groupedPermissions['Événements'][] = $perm;
                } else {
                    $groupedPermissions['Administration'][] = $perm;
                }
            }
            
            // ✅ Icônes par catégorie
            $categoryIcons = [
                'Utilisateurs' => 'fas fa-users',
                'Équipes' => 'fas fa-users-cog',
                'Projets' => 'fas fa-project-diagram',
                'Publications' => 'fas fa-book',
                'Équipements' => 'fas fa-desktop',
                'Événements' => 'fas fa-calendar',
                'Administration' => 'fas fa-cog'
            ];
            
            foreach ($roles as $role):
                // Nom du rôle
                $roleName = $role['nom_role'] ?? $role['nom'] ?? 'inconnu';
                
                // Icône du rôle
                $roleIcons = [
                    'admin' => 'fas fa-crown',
                    'enseignant' => 'fas fa-chalkboard-teacher',
                    'chercheur' => 'fas fa-flask',
                    'doctorant' => 'fas fa-user-graduate',
                    'etudiant' => 'fas fa-graduation-cap',
                    'invite' => 'fas fa-user'
                ];
                $icon = $roleIcons[$roleName] ?? 'fas fa-user-tag';
                
                // ========================================
                // ✅ PERMISSIONS ACTUELLES (utiliser 'code')
                // ========================================
                if (!empty($role['permissions'])) {
                    $currentPermissions = is_array($role['permissions']) 
                        ? array_column($role['permissions'], 'code')
                        : explode(',', $role['permissions']);
                } else {
                    $currentPermissions = [];
                }
                
                // Vérifier si c'est un rôle système (ID <= 6)
                $isSystemRole = $role['id_role'] <= 6;
            ?>
                
                <div class="role-card">
                    <!-- En-tête du rôle -->
                    <div class="role-card-header">
                        <div class="role-info">
                            <div class="role-icon-badge role-<?= htmlspecialchars($roleName) ?>">
                                <i class="<?= $icon ?>"></i>
                            </div>
                            <div>
                                <h3><?= htmlspecialchars(ucfirst($roleName)) ?></h3>
                                <p class="role-description">
                                    <?= htmlspecialchars($role['description'] ?? 'Aucune description') ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="role-stats">
                            <span class="role-stat">
                                <i class="fas fa-users"></i>
                                <?= $role['user_count'] ?? 0 ?> utilisateur<?= ($role['user_count'] ?? 0) > 1 ? 's' : '' ?>
                            </span>
                            <?php if ($isSystemRole): ?>
                                <span class="badge-system">
                                    <i class="fas fa-lock"></i> Système
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- ========================================
                         PERMISSIONS DYNAMIQUES
                         ======================================== -->
                    <form method="POST" 
                          action="index.php?url=admin/updateRolePermissions/<?= $role['id_role'] ?>" 
                          class="role-permissions-form">
                        
                        <div class="permissions-section">
                            <h4>
                                <i class="fas fa-shield-alt"></i>
                                Permissions (<?= count($currentPermissions) ?>)
                            </h4>
                            
                            <div class="permissions-grid">
                                <?php foreach ($groupedPermissions as $categoryName => $permissions): ?>
                                    <?php if (!empty($permissions)): ?>
                                        <div class="permission-group">
                                            <div class="permission-group-header">
                                                <i class="<?= $categoryIcons[$categoryName] ?? 'fas fa-shield-alt' ?>"></i>
                                                <strong><?= $categoryName ?></strong>
                                            </div>
                                            
                                            <div class="permission-checkboxes">
                                                <?php foreach ($permissions as $perm): ?>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" 
                                                               name="permissions[]" 
                                                               value="<?= htmlspecialchars($perm['code']) ?>"
                                                               <?= in_array($perm['code'], $currentPermissions) ? 'checked' : '' ?>
                                                               onchange="this.form.submit()">
                                                        <span><?= htmlspecialchars($perm['description'] ?? $perm['code']) ?></span>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Actions -->
                    <div class="role-card-actions">
                        <button onclick="editRoleDescription(<?= $role['id_role'] ?>, '<?= htmlspecialchars(addslashes($roleName)) ?>', '<?= htmlspecialchars(addslashes($role['description'] ?? '')) ?>')" 
                                class="btn-action edit"
                                title="Modifier la description">
                            <i class="fas fa-edit"></i>
                            Modifier description
                        </button>
                        
                        <?php if (!$isSystemRole): ?>
                            <a href="index.php?url=admin/deleteRole/<?= $role['id_role'] ?>" 
                               class="btn-action delete"
                               onclick="return confirm('Supprimer le rôle <?= htmlspecialchars($roleName) ?> ?')"
                               title="Supprimer le rôle">
                                <i class="fas fa-trash"></i>
                                Supprimer
                            </a>
                        <?php else: ?>
                            <button class="btn-action disabled" disabled title="Rôle système protégé">
                                <i class="fas fa-lock"></i>
                                Protégé
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                
            <?php endforeach; ?>
        </div>
        
        <!-- ========================================
             MODAL MODIFICATION DESCRIPTION
             ======================================== -->
        <div id="editDescriptionModal" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Modifier la description</h3>
                    <button type="button" class="modal-close" onclick="closeEditModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form method="POST" id="editDescriptionForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="role_name_display">Rôle</label>
                            <input type="text" id="role_name_display" readonly disabled>
                        </div>
                        
                        <div class="form-group">
                            <label for="role_description">Description</label>
                            <textarea id="role_description" 
                                      name="description" 
                                      rows="4" 
                                      placeholder="Description du rôle..."
                                      required></textarea>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="submit" class="btn primary">
                            <i class="fas fa-save"></i>
                            Enregistrer
                        </button>
                        <button type="button" class="btn ghost" onclick="closeEditModal()">
                            <i class="fas fa-times"></i>
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- ========================================
             JAVASCRIPT
             ======================================== -->
        <script>
        function editRoleDescription(roleId, roleName, currentDescription) {
            const modal = document.getElementById('editDescriptionModal');
            const form = document.getElementById('editDescriptionForm');
            const nameInput = document.getElementById('role_name_display');
            const descTextarea = document.getElementById('role_description');
            
            // Définir l'action du formulaire
            form.action = 'index.php?url=admin/updateRoleDescription/' + roleId;
            
            // Remplir les champs
            nameInput.value = roleName;
            descTextarea.value = currentDescription;
            
            // Afficher le modal
            modal.style.display = 'flex';
        }
        
        function closeEditModal() {
            document.getElementById('editDescriptionModal').style.display = 'none';
        }
        
        // Fermer si clic en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('editDescriptionModal');
            if (event.target === modal) {
                closeEditModal();
            }
        }
        
        // Fermer avec Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeEditModal();
            }
        });
        </script>
        
        </div><!-- .admin-main -->
        <?php
        AdminFooterView::render();
    }
    
    /**
     * Formulaire d'ajout de rôle
     */
    public static function renderForm(array $data) {
        $settings = $data['settings'] ?? [];
        $action = $data['action'] ?? 'add';
        $role = $data['role'] ?? null;
        $error = $data['error'] ?? null;
        
        $title = 'Ajouter un rôle';
        $submitUrl = 'index.php?url=admin/addRole';
        
        AdminHeaderView::render($settings, $title);
        AdminSidebarView::render('roles');
        ?>
        
        <div class="admin-page-header">
            <h1><?= $title ?></h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <a href="index.php?url=admin/roles">Rôles</a>
                <span>/</span>
                <span>Ajouter</span>
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
                <h2>Informations du rôle</h2>
            </div>
            
            <div class="card-body">
                <form method="POST" action="<?= $submitUrl ?>">
                    
                    <div class="form-group">
                        <label for="nom">Nom du rôle *</label>
                        <input type="text" 
                               id="nom" 
                               name="nom" 
                               placeholder="Ex: gestionnaire, moderateur..."
                               required>
                        <small class="form-help-text">
                            Nom unique pour identifier le rôle (minuscules, sans espaces)
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="4"
                                  placeholder="Description du rôle et de ses responsabilités..."
                                  required></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn primary">
                            <i class="fas fa-save"></i>
                            Créer le rôle
                        </button>
                        <a href="index.php?url=admin/roles" class="btn ghost">
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