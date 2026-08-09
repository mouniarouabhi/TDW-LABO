<?php
// views/layout/AdminSidebarView.php
// VERSION FINALE - Pur PHP (styles dans CSS, JS dans admin.js)

class AdminSidebarView {
    public static function render(string $active = '') {
        $menuCategories = [
            [
                'id' => 'dashboard',
                'label' => 'Tableau de bord',
                'icon' => 'fas fa-home',
                'single' => true,
                'items' => [
                    [
                        'id' => 'dashboard',
                        'label' => 'Tableau de bord',
                        'icon' => 'fas fa-home',
                        'url' => 'index.php?url=admin'
                    ]
                ]
            ],
            [
                'id' => 'users-roles',
                'label' => 'Utilisateurs et Rôles',
                'icon' => 'fas fa-users',
                'items' => [
                    [
                        'id' => 'users',
                        'label' => 'Gestion des utilisateurs',
                        'icon' => 'fas fa-user',
                        'url' => 'index.php?url=admin/users',
                        'badge' => 'users'
                    ],
                    [
                        'id' => 'roles',
                        'label' => 'Rôles et permissions',
                        'icon' => 'fas fa-user-tag',
                        'url' => 'index.php?url=admin/roles'
                    ]
                ]
            ],
            [
                'id' => 'teams',
                'label' => 'Équipes',
                'icon' => 'fas fa-users-cog',
                'items' => [
                    [
                        'id' => 'teams',
                        'label' => 'Gestion des équipes',
                        'icon' => 'fas fa-users-cog',
                        'url' => 'index.php?url=admin/teams'
                    ]
                ]
            ],
            [
                'id' => 'projects',
                'label' => 'Projets de Recherche',
                'icon' => 'fas fa-project-diagram',
                'items' => [
                    [
                        'id' => 'projects',
                        'label' => 'Gestion des projets',
                        'icon' => 'fas fa-project-diagram',
                        'url' => 'index.php?url=admin/projects'
                    ],
                    [
                        'id' => 'partners',
                        'label' => 'Partenaires',
                        'icon' => 'fas fa-handshake',
                        'url' => 'index.php?url=admin/partners'
                    ]
                ]
            ],
            [
                'id' => 'equipments',
                'label' => 'Équipements et Ressources',
                'icon' => 'fas fa-laptop',
                'items' => [
                    [
                        'id' => 'equipments',
                        'label' => 'Gestion des équipements',
                        'icon' => 'fas fa-laptop',
                        'url' => 'index.php?url=admin/equipments'
                    ]
                ]
            ],
            [
                'id' => 'publications',
                'label' => 'Publications',
                'icon' => 'fas fa-book',
                'items' => [
                    [
                        'id' => 'publications',
                        'label' => 'Gestion des publications',
                        'icon' => 'fas fa-book',
                        'url' => 'index.php?url=admin/publications'
                    ]
                ]
            ],
            [
                'id' => 'communications',
                'label' => 'Événements et Communications',
                'icon' => 'fas fa-calendar-alt',
                'items' => [
                    [
                        'id' => 'events',
                        'label' => 'Gestion des événements',
                        'icon' => 'fas fa-calendar-alt',
                        'url' => 'index.php?url=admin/events'
                    ],
                    [
                        'id' => 'news',
                        'label' => 'Actualités',
                        'icon' => 'fas fa-newspaper',
                        'url' => 'index.php?url=admin/news'
                    ],
                    [
                        'id' => 'contacts',
                        'label' => 'Messages de contact',
                        'icon' => 'fas fa-envelope',
                        'url' => 'index.php?url=admin/contactMessages'
                    ],
                    [
                        'id' => 'opportunities',
                        'label' => 'Opportunités',
                        'icon' => 'fas fa-briefcase',
                        'url' => 'index.php?url=admin/opportunities'
                    ],
                ]
            ],
            [
                'id' => 'system',
                'label' => 'Paramètres et Rapports',
                'icon' => 'fas fa-cog',
                'items' => [
                    [
                        'id' => 'settings',
                        'label' => 'Paramètres généraux',
                        'icon' => 'fas fa-cog',
                        'url' => 'index.php?url=admin/settings'
                    ],
                    [
                        'id' => 'reports',
                        'label' => 'Rapports générés',
                        'icon' => 'fas fa-file-pdf',
                        'url' => 'index.php?url=admin/reports'
                    ]
                ]
            ]
        ];
        ?>
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-shield-alt"></i> Administration</h3>
            </div>
            
            <nav class="sidebar-nav">
                <?php foreach ($menuCategories as $category): ?>
                    <?php if (isset($category['single']) && $category['single']): ?>
                        <!-- Item unique (Dashboard) -->
                        <?php $item = $category['items'][0]; ?>
                        <div class="sidebar-item-single <?= $active === $item['id'] ? 'active' : '' ?>">
                            <a href="<?= htmlspecialchars($item['url']) ?>">
                                <i class="<?= htmlspecialchars($item['icon']) ?>"></i>
                                <span><?= htmlspecialchars($item['label']) ?></span>
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Catégorie avec sous-items -->
                        <div class="sidebar-category">
                            <div class="category-header" onclick="toggleCategory(this)">
                                <i class="<?= htmlspecialchars($category['icon']) ?>"></i>
                                <span><?= htmlspecialchars($category['label']) ?></span>
                                <i class="fas fa-chevron-down toggle-icon"></i>
                            </div>
                            
                            <ul class="category-items">
                                <?php foreach ($category['items'] as $item): ?>
                                    <li class="<?= $active === $item['id'] ? 'active' : '' ?>">
                                        <a href="<?= htmlspecialchars($item['url']) ?>">
                                            <i class="<?= htmlspecialchars($item['icon']) ?>"></i>
                                            <span><?= htmlspecialchars($item['label']) ?></span>
                                            <?php if (isset($item['badge'])): ?>
                                                <span class="badge-count" id="<?= $item['badge'] ?>-count"></span>
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
            
            <div class="sidebar-footer">
                <a href="index.php?url=auth/logout" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </div>
        </aside>
        
        <div class="admin-main">
        <?php
    }
}
?>