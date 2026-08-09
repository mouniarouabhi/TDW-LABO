<?php
// views/admin/AdminDashboardView.php
// VERSION CORRIGÉE - RBAC compatible + styles séparés

class AdminDashboardView {
    public static function render(array $data) {
        $settings = $data['settings'] ?? [];
        $stats = $data['stats'] ?? [];
        $recentUsers = $data['recentUsers'] ?? [];
        $recentProjects = $data['recentProjects'] ?? [];
        $upcomingEvents = $data['upcomingEvents'] ?? [];
        
        AdminHeaderView::render($settings, 'Tableau de bord');
        AdminSidebarView::render('dashboard');
        ?>
        
        <div class="admin-page-header">
            <h1>Tableau de bord</h1>
            <div class="breadcrumb">
                <span>Administration</span>
                <span>/</span>
                <span>Tableau de bord</span>
            </div>
        </div>
        
        <!-- Statistiques principales -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['total_users'] ?? 0 ?></h3>
                    <p>Utilisateurs total</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon secondary">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['total_projects'] ?? 0 ?></h3>
                    <p>Projets de recherche</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['total_publications'] ?? 0 ?></h3>
                    <p>Publications</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['total_events'] ?? 0 ?></h3>
                    <p>Événements</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-laptop"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['total_equipments'] ?? 0 ?></h3>
                    <p>Équipements</p>
                </div> 
            </div>

            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['news'] ?? 0 ?></h3>
                    <p>Actualités</p>
                </div>
            </div>
        </div>
        <!-- Statistiques par rôle -->
        <div class="content-card">
            <div class="card-header">
                <h2>Répartition des utilisateurs</h2>
            </div>
            <div class="card-body">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon primary">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $stats['enseignants'] ?? 0 ?></h3>
                            <p>Enseignants</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon secondary">
                            <i class="fas fa-flask"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $stats['chercheurs'] ?? 0 ?></h3>
                            <p>Chercheurs</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon success">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $stats['doctorants'] ?? 0 ?></h3>
                            <p>Doctorants</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon warning">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $stats['etudiants'] ?? 0 ?></h3>
                            <p>Étudiants</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Grid pour les sections récentes -->
        <div class="dashboard-recent-grid">
            
            <!-- Utilisateurs récents -->
            <div class="content-card">
                <div class="card-header">
                    <h2>Utilisateurs récents</h2>
                    <a href="index.php?url=admin/users" class="link">Voir tout</a>
                </div>
                <div class="card-body">
                    <?php
                    $tableData = [];
                    foreach (array_slice($recentUsers, 0, 5) as $user) {
                        // Build roles HTML
                        $rolesHtml = '';
                        $userRoles = $user['roles'] ?? [];
                        if (empty($userRoles)) {
                            $rolesHtml = '<span class="badge default">Utilisateur</span>';
                        } else {
                            foreach ($userRoles as $role) {
                                $badgeClass = match ($role) {
                                    'admin' => 'badge primary',
                                    'enseignant', 'chercheur' => 'badge secondary',
                                    'doctorant' => 'badge success',
                                    'etudiant' => 'badge warning',
                                    default => 'badge default'
                                };
                                $rolesHtml .= '<span class="' . $badgeClass . '">' . htmlspecialchars(ucfirst($role)) . '</span> ';
                            }
                        }
                        
                        $tableData[] = [
                            'nom_html' => '<strong>' . htmlspecialchars($user['nom'] . ' ' . $user['prenom']) . '</strong>',
                            'roles_html' => $rolesHtml,
                            'date_creation' => date('d/m/Y', strtotime($user['date_creation']))
                        ];
                    }

                    TableView::render([
                        'id' => 'recent-users-table',
                        'columns' => [
                            'nom' => 'Nom',
                            'roles' => 'Rôles',
                            'date_creation' => 'Date'
                        ],
                        'data' => $tableData,
                        'searchable' => false,
                        'empty_message' => 'Aucun utilisateur récent'
                    ]);
                    ?>
                </div>
            </div>
            
            <!-- Événements à venir -->
            <div class="content-card">
                <div class="card-header">
                    <h2>Événements à venir</h2>
                    <a href="index.php?url=admin/events" class="link">Voir tout</a>
                </div>
                <div class="card-body">
                    <?php
                    $tableData = [];
                    foreach (array_slice($upcomingEvents, 0, 5) as $event) {
                        $eventBadge = match ($event['type_event']) {
                            'conference' => 'badge primary',
                            'atelier' => 'badge secondary',
                            'seminaire' => 'badge success',
                            'soutenance' => 'badge warning',
                            default => 'badge default'
                        };
                        
                        $tableData[] = [
                            'titre_html' => '<strong>' . htmlspecialchars($event['titre']) . '</strong>',
                            'type_html' => '<span class="' . $eventBadge . '">' . htmlspecialchars(ucfirst($event['type_event'])) . '</span>',
                            'date_debut' => date('d/m/Y', strtotime($event['date_debut']))
                        ];
                    }

                    TableView::render([
                        'id' => 'upcoming-events-table',
                        'columns' => [
                            'titre' => 'Titre',
                            'type' => 'Type',
                            'date_debut' => 'Date'
                        ],
                        'data' => $tableData,
                        'searchable' => false,
                        'empty_message' => 'Aucun événement à venir'
                    ]);
                    ?>
                </div>
            </div>
            
        </div>
        
        </div><!-- .admin-main -->
        <?php
        AdminFooterView::render();
    }
}
?>