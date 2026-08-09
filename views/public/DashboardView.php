<?php
// views/public/DashboardView.php
// VERSION CORRIGÉE - Gère les rôles null

class DashboardView {
    
    public static function render(array $data = []) {
        $settings = $data['settings'] ?? [];
        $user = $data['user'] ?? [];
        $projects = $data['projects'] ?? [];
        $publications = $data['publications'] ?? [];
        $upcomingReservations = $data['upcomingReservations'] ?? [];
        $documents = $data['documents'] ?? [];
        
        HeaderView::render($settings, 'Mon Tableau de Bord');
        MenuView::render('dashboard');
        ?>
        
        <div class="container" style="padding: 40px 0;">
            <!-- En-tête -->
            <div class="dashboard-header" style="margin-bottom: 32px;">
                <h1>Bienvenue, <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h1>
                <p style="color: var(--muted); font-size: 1.1rem;">
                    <i class="fas fa-user-circle"></i> 
                    <?php
                    // ✅ FIX : Gérer le cas où role est null
                    $userRole = $user['role'] ?? 'Utilisateur';
                    echo htmlspecialchars($userRole);
                    ?>
                    <?php if (!empty($user['grade'])): ?>
                        - <?= htmlspecialchars($user['grade']) ?>
                    <?php endif; ?>
                </p>
            </div>
            
            <!-- Statistiques personnelles -->
            <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 32px;">
                
                <!-- Projets -->
                <div class="stat-card" style="background: white; border: 1px solid var(--border); border-radius: 12px; padding: 24px; box-shadow: var(--shadow);">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 56px; height: 56px; border-radius: 12px; background: rgba(46, 184, 92, 0.1); color: #2eb85c; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.8rem;"><?= count($projects) ?></h3>
                            <p style="margin: 4px 0 0; color: var(--muted);">Projets</p>
                        </div>
                    </div>
                </div>
                
                <!-- Publications -->
                <div class="stat-card" style="background: white; border: 1px solid var(--border); border-radius: 12px; padding: 24px; box-shadow: var(--shadow);">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 56px; height: 56px; border-radius: 12px; background: rgba(30, 79, 163, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                            <i class="fas fa-book"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.8rem;"><?= count($publications) ?></h3>
                            <p style="margin: 4px 0 0; color: var(--muted);">Publications</p>
                        </div>
                    </div>
                </div>
                
                <!-- Réservations -->
                <div class="stat-card" style="background: white; border: 1px solid var(--border); border-radius: 12px; padding: 24px; box-shadow: var(--shadow);">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 56px; height: 56px; border-radius: 12px; background: rgba(240, 180, 41, 0.1); color: var(--secondary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.8rem;"><?= count($upcomingReservations) ?></h3>
                            <p style="margin: 4px 0 0; color: var(--muted);">Réservations</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contenu principal -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
                
                <!-- Mes informations -->
                <div class="content-card" style="background: white; border: 1px solid var(--border); border-radius: 12px; box-shadow: var(--shadow); overflow: hidden;">
                    <div style="padding: 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                        <h2 style="margin: 0; font-size: 1.3rem;">Mes informations</h2>
                        <a href="index.php?url=profile/edit" class="btn" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: var(--primary); color: white; border-radius: 8px; font-weight: 600; font-size: 0.9rem;">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                    </div>
                    <div style="padding: 24px;">
                        <div style="display: flex; gap: 24px; margin-bottom: 24px;">
                            <img src="assets/images/users/<?= htmlspecialchars($user['photo'] ?? 'default.jpg') ?>" 
                                 alt="Photo" 
                                 style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--border);">
                            <div style="flex: 1;">
                                <h3 style="margin: 0 0 8px;"><?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?></h3>
                                <p style="margin: 4px 0; color: var(--muted);">
                                    <i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?>
                                </p>
                                <?php if (!empty($user['grade'])): ?>
                                    <p style="margin: 4px 0; color: var(--muted);">
                                        <i class="fas fa-graduation-cap"></i> <?= htmlspecialchars($user['grade']) ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($user['poste'])): ?>
                                    <p style="margin: 4px 0; color: var(--muted);">
                                        <i class="fas fa-briefcase"></i> <?= htmlspecialchars($user['poste']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($user['domaine_recherche'])): ?>
                            <div style="margin-top: 20px;">
                                <h4 style="margin: 0 0 8px; color: var(--text);">Domaine de recherche</h4>
                                <p style="margin: 0; color: var(--muted);"><?= htmlspecialchars($user['domaine_recherche']) ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($user['biographie'])): ?>
                            <div style="margin-top: 20px;">
                                <h4 style="margin: 0 0 8px; color: var(--text);">Biographie</h4>
                                <p style="margin: 0; color: var(--muted);"><?= nl2br(htmlspecialchars($user['biographie'])) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Actions rapides -->
                <div class="content-card" style="background: white; border: 1px solid var(--border); border-radius: 12px; box-shadow: var(--shadow); overflow: hidden;">
                    <div style="padding: 20px; border-bottom: 1px solid var(--border);">
                        <h2 style="margin: 0; font-size: 1.3rem;">Actions rapides</h2>
                    </div>
                    <div style="padding: 16px;">
                        <a href="index.php?url=equipment" class="quick-action" style="display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 8px; margin-bottom: 8px; transition: background 0.3s; text-decoration: none; color: inherit;">
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(30, 79, 163, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-laptop"></i>
                            </div>
                            <div>
                                <strong>Réserver équipement</strong>
                                <p style="margin: 2px 0 0; font-size: 0.85rem; color: var(--muted);">Gérer mes réservations</p>
                            </div>
                        </a>
                        
                        <a href="index.php?url=userpublication" class="quick-action" style="display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 8px; margin-bottom: 8px; transition: background 0.3s; text-decoration: none; color: inherit;">
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(240, 180, 41, 0.1); color: var(--secondary); display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-book"></i>
                            </div>
                            <div>
                                <strong>Mes publications</strong>
                                <p style="margin: 2px 0 0; font-size: 0.85rem; color: var(--muted);">Consulter et gérer</p>
                            </div>
                        </a>
                        
                        <a href="index.php?url=userproject" class="quick-action" style="display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 8px; margin-bottom: 8px; transition: background 0.3s; text-decoration: none; color: inherit;">
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(46, 184, 92, 0.1); color: #2eb85c; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-project-diagram"></i>
                            </div>
                            <div>
                                <strong>Mes projets</strong>
                                <p style="margin: 2px 0 0; font-size: 0.85rem; color: var(--muted);">Voir mes contributions</p>
                            </div>
                        </a>

                        <a href="index.php?url=userDocument" class="quick-action" style="display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 8px; margin-bottom: 8px; transition: background 0.3s; text-decoration: none; color: inherit;">
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(46, 184, 92, 0.1); color: #505effff; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-file"></i>
                            </div>
                            <div>
                                <strong>Mes documents</strong>
                                <p style="margin: 2px 0 0; font-size: 0.85rem; color: var(--muted);">Voir mes documents</p>
                            </div>
                        </a>
                        
                        <a href="index.php?url=equipment/historique" class="quick-action" style="display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 8px; margin-bottom: 8px; transition: background 0.3s; text-decoration: none; color: inherit;">
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(108, 117, 125, 0.1); color: #6c757d; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-history"></i>
                            </div>
                            <div>
                                <strong>Historique</strong>
                                <p style="margin: 2px 0 0; font-size: 0.85rem; color: var(--muted);">Mes réservations</p>
                            </div>
                        </a>
                        
                        <a href="index.php?url=auth/logout" class="quick-action" style="display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 8px; transition: background 0.3s; text-decoration: none; color: inherit;">
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(220, 53, 69, 0.1); color: #dc3545; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-sign-out-alt"></i>
                            </div>
                            <div>
                                <strong>Déconnexion</strong>
                                <p style="margin: 2px 0 0; font-size: 0.85rem; color: var(--muted);">Se déconnecter</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Mes projets -->
            <?php if (!empty($projects)): ?>
            <div class="content-card" style="background: white; border: 1px solid var(--border); border-radius: 12px; box-shadow: var(--shadow); overflow: hidden; margin-top: 24px;">
                <div style="padding: 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="margin: 0; font-size: 1.3rem;">Mes projets (<?= count($projects) ?>)</h2>
                    <a href="index.php?url=userproject" class="link">Voir tous</a>
                </div>
                <div style="padding: 20px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px;">
                        <?php foreach (array_slice($projects, 0, 4) as $project): ?>
                            <div style="padding: 16px; border: 1px solid var(--border); border-radius: 8px;">
                                <span class="badge primary" style="margin-bottom: 8px;">
                                    <?= htmlspecialchars($project['thematique'] ?? 'Projet') ?>
                                </span>
                                <h4 style="margin: 8px 0;">
                                    <a href="index.php?url=userproject/detail/<?= $project['id_project'] ?>">
                                        <?= htmlspecialchars($project['titre']) ?>
                                    </a>
                                </h4>
                                <p style="color: var(--muted); font-size: 0.9rem; margin: 8px 0;">
                                    <?= htmlspecialchars(mb_substr($project['description'] ?? '', 0, 80)) ?>...
                                </p>
                                <div style="margin-top: 12px; font-size: 0.85rem; color: var(--muted);">
                                    <?php
                                    $statusBadge = '';
                                    switch ($project['statut'] ?? '') {
                                        case 'en_cours':
                                            $statusBadge = '<span class="badge success">En cours</span>';
                                            break;
                                        case 'termine':
                                            $statusBadge = '<span class="badge">Terminé</span>';
                                            break;
                                        case 'soumis':
                                            $statusBadge = '<span class="badge warning">Soumis</span>';
                                            break;
                                    }
                                    echo $statusBadge;
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($documents)): ?>
                <div class="content-card" style="background: white; border: 1px solid var(--border); border-radius: 12px; box-shadow: var(--shadow); overflow: hidden; margin-top: 24px;">
                    <div style="padding: 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                        <h2 style="margin: 0; font-size: 1.3rem;">
                            Mes documents (<?= count($documents) ?>)
                        </h2>
                        <a href="index.php?url=userdocument" class="link">
                            Gérer
                        </a>
                    </div>

                    <div style="padding: 20px;">
                        <table style="width: 100%;">
                            <?php foreach (array_slice($documents, 0, 5) as $doc): ?>
                                <tr>
                                    <td>
                                        <i class="<?= DocumentModel::getIcon($doc['extension']) ?>"></i>
                                        <?= htmlspecialchars($doc['titre']) ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="assets/documents/<?= $doc['nom_fichier'] ?>" download class="btn ghost">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Réservations à venir -->
            <?php if (!empty($upcomingReservations)): ?>
            <div class="content-card" style="background: white; border: 1px solid var(--border); border-radius: 12px; box-shadow: var(--shadow); overflow: hidden; margin-top: 24px;">
                <div style="padding: 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="margin: 0; font-size: 1.3rem;">Réservations à venir (<?= count($upcomingReservations) ?>)</h2>
                    <a href="index.php?url=equipment/historique" class="link">Voir l'historique</a>
                </div>
                <div style="padding: 20px; overflow-x: auto;">
                    <table class="data-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--primary);">
                                <th style="padding: 12px; text-align: left; font-weight: 600; border-bottom: 2px solid var(--border);">Équipement</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600; border-bottom: 2px solid var(--border);">Date début</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600; border-bottom: 2px solid var(--border);">Date fin</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600; border-bottom: 2px solid var(--border);">Statut</th>
                                <th style="padding: 12px; text-align: center; font-weight: 600; border-bottom: 2px solid var(--border);">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingReservations as $reservation): ?>
                                <tr>
                                    <td style="padding: 12px; border-bottom: 1px solid var(--border);">
                                        <strong><?= htmlspecialchars($reservation['nom_equipment']) ?></strong>
                                        <div style="font-size: 0.85rem; color: var(--muted);">
                                            <?= htmlspecialchars($reservation['type_equipment'] ?? '') ?>
                                        </div>
                                    </td>
                                    <td style="padding: 12px; border-bottom: 1px solid var(--border);">
                                        <?= date('d/m/Y H:i', strtotime($reservation['date_debut'])) ?>
                                    </td>
                                    <td style="padding: 12px; border-bottom: 1px solid var(--border);">
                                        <?= date('d/m/Y H:i', strtotime($reservation['date_fin'])) ?>
                                    </td>
                                    <td style="padding: 12px; border-bottom: 1px solid var(--border);">
                                        <?php
                                        $statusClass = '';
                                        switch ($reservation['statut'] ?? '') {
                                            case 'validee':
                                            case 'en_cours':
                                                $statusClass = 'success';
                                                break;
                                            case 'en_attente':
                                                $statusClass = 'warning';
                                                break;
                                            case 'refusee':
                                            case 'annulee':
                                                $statusClass = '';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?= $statusClass ?>">
                                            <?= htmlspecialchars(ucfirst($reservation['statut'])) ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px; border-bottom: 1px solid var(--border); text-align: center;">
                                        <?php if (in_array($reservation['statut'] ?? '', ['en_attente', 'validee'])): ?>
                                            <a href="index.php?url=equipment/annuler/<?= $reservation['id_reservation'] ?>" 
                                               class="link"
                                               style="color: #dc3545;"
                                               onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                                                Annuler
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <style>
        .quick-action:hover {
            background: var(--bg);
        }
        </style>
        
        <?php
        FooterView::render($settings);
    }
}
?>