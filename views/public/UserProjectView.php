<?php
// views/user/UserProjectView.php
// Vues pour la gestion des projets par l'utilisateur

class UserProjectView {
    
    /**
     * Liste des projets de l'utilisateur
     */
    public static function renderList(array $data) {
        $settings = $data['settings'] ?? [];
        $projects = $data['projects'] ?? [];
        $stats = $data['stats'] ?? [];
        $currentUserId = $data['currentUserId'] ?? 0;
        
        HeaderView::render($settings, 'Mes Projets');
        MenuView::render('projects');
        ?>
        
        <div class="container" style="padding: 40px 0;">
            <!-- En-tête -->
            <div style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h1>Gérer mes projets</h1>
                    <div style="display: flex; gap: 16px; margin-top: 8px;">
                        <a href="index.php?url=dashboard" class="link">
                            <i class="fas fa-arrow-left"></i> Retour au dashboard
                        </a>
                    </div>
                </div>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert success" style="margin-bottom: 24px;">
                    <i class="fas fa-check-circle"></i>
                    <?php
                    if ($_GET['success'] === 'left') echo 'Vous avez quitté le projet';
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert error" style="margin-bottom: 24px;">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php
                    if ($_GET['error'] === 'not_found') echo 'Projet introuvable';
                    elseif ($_GET['error'] === 'unauthorized') echo 'Vous n\'appartenez pas à ce projet';
                    elseif ($_GET['error'] === 'leave_failed') echo 'Erreur lors de la sortie du projet';
                    ?>
                </div>
            <?php endif; ?>
            
            <!-- Statistiques -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 32px;">
                <div class="stat-card" style="background: white; border: 1px solid var(--border); border-radius: 12px; padding: 24px;">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 56px; height: 56px; border-radius: 12px; background: rgba(30, 79, 163, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.8rem;"><?= $stats['total'] ?></h3>
                            <p style="margin: 4px 0 0; color: var(--muted);">Total</p>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card" style="background: white; border: 1px solid var(--border); border-radius: 12px; padding: 24px;">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 56px; height: 56px; border-radius: 12px; background: rgba(46, 184, 92, 0.1); color: #2eb85c; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.8rem;"><?= $stats['responsable'] ?></h3>
                            <p style="margin: 4px 0 0; color: var(--muted);">Responsable</p>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card" style="background: white; border: 1px solid var(--border); border-radius: 12px; padding: 24px;">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 56px; height: 56px; border-radius: 12px; background: rgba(240, 180, 41, 0.1); color: var(--secondary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.8rem;"><?= $stats['membre'] ?></h3>
                            <p style="margin: 4px 0 0; color: var(--muted);">Membre</p>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card" style="background: white; border: 1px solid var(--border); border-radius: 12px; padding: 24px;">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 56px; height: 56px; border-radius: 12px; background: rgba(23, 162, 184, 0.1); color: #17a2b8; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                            <i class="fas fa-play-circle"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.8rem;"><?= $stats['en_cours'] ?></h3>
                            <p style="margin: 4px 0 0; color: var(--muted);">En cours</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Liste des projets -->
            <div class="content-card">
                <?php if (empty($projects)): ?>
                    <div style="text-align: center; padding: 60px 20px;">
                        <i class="fas fa-project-diagram" style="font-size: 4rem; color: var(--muted); opacity: 0.3; margin-bottom: 20px;"></i>
                        <h3 style="color: var(--muted);">Aucun projet</h3>
                        <p style="color: var(--muted); margin: 16px 0;">Vous ne participez à aucun projet.</p>
                    </div>
                <?php else: ?>
                    <div style="display: grid; gap: 20px; padding: 24px;">
                        <?php foreach ($projects as $project): ?>
                            <div style="border: 2px solid var(--border); border-radius: 12px; padding: 24px; transition: all 0.3s;">
                                <!-- En-tête -->
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px; gap: 16px;">
                                    <div style="flex: 1;">
                                        <div style="margin-bottom: 8px;">
                                            <span class="badge primary"><?= htmlspecialchars($project['thematique'] ?? 'Recherche') ?></span>
                                            <?php if ($project['is_chef']): ?>
                                                <span class="badge success" style="margin-left: 8px;">
                                                    <i class="fas fa-crown"></i> Responsable
                                                </span>
                                            <?php else: ?>
                                                <span class="badge" style="margin-left: 8px;">
                                                    <?= htmlspecialchars($project['role_projet'] ?? 'Membre') ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php
                                            switch ($project['statut'] ?? '') {
                                                case 'en_cours':
                                                    echo '<span class="badge success" style="margin-left: 8px;">En cours</span>';
                                                    break;
                                                case 'termine':
                                                    echo '<span class="badge" style="margin-left: 8px;">Terminé</span>';
                                                    break;
                                                case 'soumis':
                                                    echo '<span class="badge warning" style="margin-left: 8px;">Soumis</span>';
                                                    break;
                                            }
                                            ?>
                                        </div>
                                        <h3 style="margin: 0 0 8px;">
                                            <a href="index.php?url=userproject/detail/<?= $project['id_project'] ?>">
                                                <?= htmlspecialchars($project['titre']) ?>
                                            </a>
                                        </h3>
                                        <p style="color: var(--muted); margin: 0;">
                                            <?= htmlspecialchars(mb_substr($project['description'] ?? '', 0, 150)) ?><?= mb_strlen($project['description'] ?? '') > 150 ? '...' : '' ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Métadonnées -->
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin: 16px 0; padding: 16px 0; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-users" style="color: var(--primary);"></i>
                                        <span style="color: var(--muted);">
                                            <?= count($project['membres'] ?? []) ?> membre<?= count($project['membres'] ?? []) > 1 ? 's' : '' ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($project['date_debut'])): ?>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-calendar-alt" style="color: var(--primary);"></i>
                                        <span style="color: var(--muted);">
                                            Début: <?= date('d/m/Y', strtotime($project['date_debut'])) ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($project['type_financement'])): ?>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-coins" style="color: var(--primary);"></i>
                                        <span style="color: var(--muted);">
                                            <?= htmlspecialchars($project['type_financement']) ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Actions -->
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <a href="index.php?url=userproject/detail/<?= $project['id_project'] ?>" 
                                       class="btn primary">
                                        <i class="fas fa-cog"></i> Gérer
                                    </a>
                                    <?php if (!$project['is_chef']): ?>
                                        <a href="index.php?url=userproject/leave/<?= $project['id_project'] ?>" 
                                           class="btn ghost"
                                           style="color: #dc3545;"
                                           onclick="return confirm('Êtes-vous sûr de vouloir quitter ce projet ?');">
                                            <i class="fas fa-sign-out-alt"></i> Quitter
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php
        FooterView::render($settings);
    }
    
    /**
     * Détail d'un projet avec gestion
     */
    public static function renderDetail(array $data) {
        $settings = $data['settings'] ?? [];
        $project = $data['project'] ?? [];
        $members = $data['members'] ?? [];
        $publications = $data['publications'] ?? [];
        $allUsers = $data['allUsers'] ?? [];
        $isChef = $data['isChef'] ?? false;
        $currentUserId = $data['currentUserId'] ?? 0;
        
        HeaderView::render($settings, $project['titre'] ?? '');
        MenuView::render('projects');
        ?>
        
        <div class="container" style="padding: 40px 0;">
            <!-- En-tête -->
            <div style="margin-bottom: 32px;">
                <a href="index.php?url=userproject" class="link">
                    <i class="fas fa-arrow-left"></i> Retour à mes projets
                </a>
                <h1 style="margin: 16px 0 8px;"><?= htmlspecialchars($project['titre']) ?></h1>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <span class="badge primary"><?= htmlspecialchars($project['thematique'] ?? 'Recherche') ?></span>
                    <?php if ($isChef): ?>
                        <span class="badge success"><i class="fas fa-crown"></i> Vous êtes responsable</span>
                    <?php endif; ?>
                    <?php
                    switch ($project['statut'] ?? '') {
                        case 'en_cours':
                            echo '<span class="badge success">En cours</span>';
                            break;
                        case 'termine':
                            echo '<span class="badge">Terminé</span>';
                            break;
                        case 'soumis':
                            echo '<span class="badge warning">Soumis</span>';
                            break;
                    }
                    ?>
                </div>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert success" style="margin-bottom: 24px;">
                    <i class="fas fa-check-circle"></i>
                    <?php
                    if ($_GET['success'] === 'closed') echo 'Projet clôturé avec succès';
                    elseif ($_GET['success'] === 'member_added') echo 'Membre ajouté avec succès';
                    elseif ($_GET['success'] === 'member_removed') echo 'Membre retiré avec succès';
                    elseif ($_GET['success'] === 'publication_deleted') echo 'Publication supprimée avec succès';
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert error" style="margin-bottom: 24px;">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php
                    if ($_GET['error'] === 'not_chef') echo 'Action réservée au responsable du projet';
                    elseif ($_GET['error'] === 'chef_cannot_leave') echo 'Le responsable ne peut pas quitter son projet';
                    elseif ($_GET['error'] === 'member_exists') echo 'Ce membre est déjà dans le projet';
                    elseif ($_GET['error'] === 'cannot_remove_self') echo 'Vous ne pouvez pas vous retirer vous-même';
                    elseif ($_GET['error'] === 'close_failed') echo 'Erreur lors de la clôture';
                    elseif ($_GET['error'] === 'delete_failed') echo 'Erreur lors de la suppression';
                    ?>
                </div>
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px;">
                <!-- Colonne principale -->
                <div>
                    <!-- Description -->
                    <div class="content-card" style="padding: 32px; margin-bottom: 24px;">
                        <h2 style="margin: 0 0 16px;">Description</h2>
                        <p style="white-space: pre-line; line-height: 1.6; color: var(--text);">
                            <?= htmlspecialchars($project['description'] ?? 'Aucune description') ?>
                        </p>
                    </div>
                    
                    <!-- Membres -->
                    <div class="content-card" style="padding: 32px; margin-bottom: 24px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h2 style="margin: 0;">
                                <i class="fas fa-users"></i> Membres (<?= count($members) ?>)
                            </h2>
                            <?php if ($isChef): ?>
                                <button onclick="document.getElementById('addMemberForm').style.display='block'" 
                                        class="btn primary">
                                    <i class="fas fa-plus"></i> Ajouter un membre
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Formulaire d'ajout (caché par défaut) -->
                        <?php if ($isChef): ?>
                            <div id="addMemberForm" style="display: none; background: var(--bg); padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                                <h3 style="margin: 0 0 16px;">Ajouter un membre</h3>
                                <form method="POST" action="index.php?url=userproject/addMember/<?= $project['id_project'] ?>">
                                    <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 12px; align-items: end;">
                                        <div>
                                            <label>Utilisateur</label>
                                            <select name="user_id" required style="width: 100%; padding: 10px; border: 2px solid var(--border); border-radius: 8px;">
                                                <option value="">-- Sélectionner --</option>
                                                <?php 
                                                $memberIds = array_column($members, 'id_user');
                                                foreach ($allUsers as $user): 
                                                    if (in_array($user['id_user'], $memberIds)) continue;
                                                ?>
                                                    <option value="<?= $user['id_user'] ?>">
                                                        <?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?>
                                                        <?= !empty($user['grade']) ? ' (' . htmlspecialchars($user['grade']) . ')' : '' ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label>Rôle</label>
                                            <input type="text" name="role_projet" value="Membre" required 
                                                   style="width: 100%; padding: 10px; border: 2px solid var(--border); border-radius: 8px;">
                                        </div>
                                        <button type="submit" class="btn primary">
                                            <i class="fas fa-check"></i> Ajouter
                                        </button>
                                    </div>
                                </form>
                                <button onclick="document.getElementById('addMemberForm').style.display='none'" 
                                        class="btn ghost" style="margin-top: 12px;">
                                    Annuler
                                </button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Liste des membres -->
                        <div style="display: grid; gap: 12px;">
                            <?php foreach ($members as $member): ?>
                                <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: var(--bg); border-radius: 8px;">
                                    <img src="assets/images/users/<?= htmlspecialchars($member['photo'] ?? 'default.jpg') ?>" 
                                         alt="Photo" 
                                         style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;">
                                    <div style="flex: 1;">
                                        <div style="font-weight: 600;">
                                            <?= htmlspecialchars($member['nom'] . ' ' . $member['prenom']) ?>
                                        </div>
                                        <?php if (!empty($member['grade'])): ?>
                                            <div style="font-size: 0.85rem; color: var(--muted);">
                                                <?= htmlspecialchars($member['grade']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($member['role_projet'])): ?>
                                            <div style="font-size: 0.8rem; color: var(--primary);">
                                                <?= htmlspecialchars($member['role_projet']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($isChef && $member['id_user'] != $currentUserId): ?>
                                        <a href="index.php?url=userproject/removeMember/<?= $project['id_project'] ?>/<?= $member['id_user'] ?>" 
                                           class="btn ghost"
                                           style="color: #dc3545;"
                                           onclick="return confirm('Retirer ce membre du projet ?');">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Publications -->
                    <div class="content-card" style="padding: 32px;">
                        <h2 style="margin: 0 0 20px;">
                            <i class="fas fa-book"></i> Publications (<?= count($publications) ?>)
                        </h2>
                        
                        <?php if (empty($publications)): ?>
                            <p style="color: var(--muted); text-align: center; padding: 40px 0;">
                                Aucune publication associée à ce projet
                            </p>
                        <?php else: ?>
                            <div style="display: grid; gap: 16px;">
                                <?php foreach ($publications as $pub): ?>
                                    <div style="padding: 16px; background: var(--bg); border-radius: 8px; border-left: 4px solid var(--primary);">
                                        <div style="display: flex; justify-content: space-between; align-items: start; gap: 16px;">
                                            <div style="flex: 1;">
                                                <span class="badge primary" style="margin-bottom: 8px;">
                                                    <?= ucfirst(htmlspecialchars($pub['type_publication'])) ?>
                                                </span>
                                                <h4 style="margin: 8px 0;">
                                                    <a href="index.php?url=publication/detail/<?= $pub['id_publication'] ?>">
                                                        <?= htmlspecialchars($pub['titre']) ?>
                                                    </a>
                                                </h4>
                                                <?php if (!empty($pub['auteurs'])): ?>
                                                    <p style="color: var(--muted); font-size: 0.9rem; margin: 4px 0;">
                                                        <?= htmlspecialchars($pub['auteurs']) ?>
                                                    </p>
                                                <?php endif; ?>
                                                <p style="color: var(--muted); font-size: 0.85rem; margin: 4px 0;">
                                                    <i class="fas fa-calendar"></i> <?= $pub['annee_publication'] ?>
                                                </p>
                                            </div>
                                            <?php if ($isChef): ?>
                                                <a href="index.php?url=userproject/removePublication/<?= $project['id_project'] ?>/<?= $pub['id_publication'] ?>" 
                                                   class="btn ghost"
                                                   style="color: #dc3545;"
                                                   onclick="return confirm('Supprimer cette publication du projet ?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div>
                    <!-- Actions -->
                    <?php if ($isChef): ?>
                        <div class="content-card" style="padding: 24px; margin-bottom: 24px;">
                            <h3 style="margin: 0 0 16px;"><i class="fas fa-tools"></i> Actions</h3>
                            <?php if ($project['statut'] === 'en_cours'): ?>
                                <a href="index.php?url=userproject/close/<?= $project['id_project'] ?>" 
                                   class="btn primary" 
                                   style="width: 100%; justify-content: center; margin-bottom: 12px;"
                                   onclick="return confirm('Clôturer ce projet ? La date de fin sera définie à aujourd\'hui.');">
                                    <i class="fas fa-flag-checkered"></i> Clôturer le projet
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="content-card" style="padding: 24px; margin-bottom: 24px; background: #fff3cd; border-color: #ffc107;">
                            <h3 style="margin: 0 0 12px; color: #856404;">
                                <i class="fas fa-info-circle"></i> Votre rôle
                            </h3>
                            <p style="color: #856404; margin: 0 0 16px; font-size: 0.9rem;">
                                Vous êtes membre de ce projet. Seul le responsable peut gérer les membres et les publications.
                            </p>
                            <a href="index.php?url=userproject/leave/<?= $project['id_project'] ?>" 
                               class="btn ghost"
                               style="width: 100%; justify-content: center; color: #dc3545; border-color: #dc3545;"
                               onclick="return confirm('Quitter ce projet ?');">
                                <i class="fas fa-sign-out-alt"></i> Quitter le projet
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Informations -->
                    <div class="content-card" style="padding: 24px;">
                        <h3 style="margin: 0 0 16px;"><i class="fas fa-info-circle"></i> Informations</h3>
                        
                        <?php if (!empty($project['resp_nom'])): ?>
                            <div style="margin-bottom: 16px;">
                                <strong style="display: block; margin-bottom: 4px;">Responsable</strong>
                                <p style="margin: 0; color: var(--muted);">
                                    <?= htmlspecialchars($project['resp_nom'] . ' ' . $project['resp_prenom']) ?>
                                </p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($project['type_financement'])): ?>
                            <div style="margin-bottom: 16px;">
                                <strong style="display: block; margin-bottom: 4px;">Financement</strong>
                                <p style="margin: 0; color: var(--muted);">
                                    <?= htmlspecialchars($project['type_financement']) ?>
                                </p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($project['date_debut'])): ?>
                            <div style="margin-bottom: 16px;">
                                <strong style="display: block; margin-bottom: 4px;">Date de début</strong>
                                <p style="margin: 0; color: var(--muted);">
                                    <?= date('d/m/Y', strtotime($project['date_debut'])) ?>
                                </p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($project['date_fin'])): ?>
                            <div>
                                <strong style="display: block; margin-bottom: 4px;">Date de fin</strong>
                                <p style="margin: 0; color: var(--muted);">
                                    <?= date('d/m/Y', strtotime($project['date_fin'])) ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php
        FooterView::render($settings);
    }
}
?>