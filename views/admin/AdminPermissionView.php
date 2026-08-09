<?php
class AdminPermissionView {
    
    public static function renderList(array $data) {
        $settings = $data['settings'] ?? [];
        $permissions = $data['permissions'] ?? [];
        
        AdminHeaderView::render($settings, 'Gestion des permissions');
        AdminSidebarView::render('roles');
        ?>
        
        <div class="admin-page-header">
            <h1>Gestion des permissions</h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <a href="index.php?url=admin/roles">Rôles</a>
                <span>/</span>
                <span>Permissions</span>
            </div>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?php
                if ($_GET['success'] === 'created') echo 'Permission créée avec succès';
                elseif ($_GET['success'] === 'updated') echo 'Permission mise à jour';
                elseif ($_GET['success'] === 'deleted') echo 'Permission supprimée';
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <?php
                if ($_GET['error'] === 'permission_in_use') {
                    echo 'Impossible de supprimer cette permission : utilisée par ' . ($_GET['count'] ?? 0) . ' rôle(s)';
                } elseif ($_GET['error'] === 'delete_failed') {
                    echo 'Erreur lors de la suppression';
                } else {
                    echo 'Une erreur est survenue';
                }
                ?>
            </div>
        <?php endif; ?>
        
        <div class="content-card">
            <div class="card-header">
                <h2>Liste des permissions (<?= count($permissions) ?>)</h2>
                <a href="index.php?url=admin/addPermission" class="btn primary">
                    <i class="fas fa-plus"></i> Ajouter une permission
                </a>
            </div>
            
            <div class="card-body">
                <?php
                $tableData = [];
                foreach ($permissions as $perm) {
                    $actionsHtml = '<div class="action-buttons">';
                    $actionsHtml .= '<a href="index.php?url=admin/editPermission/' . $perm['id_permission'] . '" 
                                       class="btn-action edit" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>';
                    
                    if ($perm['role_count'] == 0) {
                        $actionsHtml .= '<a href="index.php?url=admin/deletePermission/' . $perm['id_permission'] . '" 
                                           class="btn-action delete" 
                                           title="Supprimer"
                                           onclick="return confirm(\'Supprimer cette permission ?\');">
                                            <i class="fas fa-trash"></i>
                                        </a>';
                    } else {
                        $actionsHtml .= '<button class="btn-action disabled" disabled 
                                                title="Utilisée par ' . $perm['role_count'] . ' rôle(s)">
                                            <i class="fas fa-lock"></i>
                                        </button>';
                    }
                    
                    $actionsHtml .= '</div>';
                    
                    $tableData[] = [
                        'code_html' => '<code>' . htmlspecialchars($perm['code']) . '</code>',
                        'description' => htmlspecialchars($perm['description']),
                        'role_count_html' => '<span class="badge ' . ($perm['role_count'] > 0 ? 'info' : 'secondary') . '">' 
                                      . $perm['role_count'] . ' rôle(s)</span>',
                        'actions_html' => $actionsHtml
                    ];
                }
                
                TableView::render([
                    'id' => 'permissions-table',
                    'columns' => [
                        'code' => 'Code',
                        'description' => 'Description',
                        'role_count' => 'Utilisation',
                        'actions' => 'Actions'
                    ],
                    'data' => $tableData,
                    'searchable' => true,
                    'empty_message' => 'Aucune permission trouvée'
                ]);
                ?>
            </div>
        </div>
        
        </div>
        <?php
        AdminFooterView::render();
    }
    
    public static function renderForm(array $data) {
        $settings = $data['settings'] ?? [];
        $action = $data['action'] ?? 'add';
        $permission = $data['permission'] ?? null;
        $error = $data['error'] ?? null;
        
        $title = $action === 'add' ? 'Ajouter une permission' : 'Modifier une permission';
        $submitUrl = $action === 'add' 
            ? 'index.php?url=admin/addPermission' 
            : 'index.php?url=admin/editPermission/' . $permission['id_permission'];
        
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
                <a href="index.php?url=admin/permissions">Permissions</a>
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
                <h2>Informations de la permission</h2>
            </div>
            
            <div class="card-body">
                <form method="POST" action="<?= $submitUrl ?>">
                    
                    <?php if ($action === 'add'): ?>
                        <div class="form-group">
                            <label for="code">Code de la permission *</label>
                            <input type="text" 
                                   id="code" 
                                   name="code" 
                                   placeholder="Ex: MANAGE_NOTIFICATIONS"
                                   style="text-transform: uppercase;"
                                   required>
                            <small class="form-help-text">
                                Code unique en MAJUSCULES, avec underscores (ex: CREATE_REPORT)
                            </small>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label>Code de la permission</label>
                            <input type="text" 
                                   value="<?= htmlspecialchars($permission['code']) ?>" 
                                   disabled
                                   style="background: #f5f5f5;">
                            <small class="form-help-text">
                                Le code ne peut pas être modifié
                            </small>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="3"
                                  placeholder="Description claire de la permission..."
                                  required><?= htmlspecialchars($permission['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn primary">
                            <i class="fas fa-save"></i>
                            <?= $action === 'add' ? 'Créer la permission' : 'Enregistrer' ?>
                        </button>
                        <a href="index.php?url=admin/permissions" class="btn ghost">
                            <i class="fas fa-times"></i>
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        </div>
        <?php
        AdminFooterView::render();
    }
}
?>