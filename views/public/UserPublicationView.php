<?php
// views/user/UserPublicationView.php
// Vues pour la gestion des publications par l'utilisateur

class UserPublicationView {
    
    /**
     * Liste des publications de l'utilisateur
     */
    public static function renderList(array $data) {
        $settings = $data['settings'] ?? [];
        $publications = $data['publications'] ?? [];
        $stats = $data['stats'] ?? [];
        
        HeaderView::render($settings, 'Mes Publications');
        MenuView::render('publications');
        ?>
        
        <div class="container" style="padding: 40px 0;">
            <!-- En-tête -->
            <div style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h1>Gérer mes publications</h1>
                    <div style="display: flex; gap: 16px; margin-top: 8px;">
                        <a href="index.php?url=dashboard" class="link">
                            <i class="fas fa-arrow-left"></i> Retour au dashboard
                        </a>
                    </div>
                </div>
                <a href="index.php?url=userpublication/add" class="btn primary">
                    <i class="fas fa-plus"></i> Nouvelle publication
                </a>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert success" style="margin-bottom: 24px;">
                    <i class="fas fa-check-circle"></i>
                    <?php
                    if ($_GET['success'] === 'created') echo 'Publication créée avec succès (en attente de validation)';
                    elseif ($_GET['success'] === 'updated') echo 'Publication mise à jour avec succès';
                    elseif ($_GET['success'] === 'deleted') echo 'Publication supprimée avec succès';
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert error" style="margin-bottom: 24px;">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php
                    if ($_GET['error'] === 'not_found') echo 'Publication introuvable';
                    elseif ($_GET['error'] === 'unauthorized') echo 'Vous n\'êtes pas autorisé à modifier cette publication';
                    elseif ($_GET['error'] === 'delete_failed') echo 'Erreur lors de la suppression';
                    ?>
                </div>
            <?php endif; ?>
            
            <!-- Statistiques -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 32px;">
                <div class="stat-card" style="background: white; border: 1px solid var(--border); border-radius: 12px; padding: 24px;">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 56px; height: 56px; border-radius: 12px; background: rgba(30, 79, 163, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                            <i class="fas fa-book"></i>
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
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.8rem;"><?= $stats['validees'] ?></h3>
                            <p style="margin: 4px 0 0; color: var(--muted);">Validées</p>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card" style="background: white; border: 1px solid var(--border); border-radius: 12px; padding: 24px;">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 56px; height: 56px; border-radius: 12px; background: rgba(240, 180, 41, 0.1); color: var(--secondary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.8rem;"><?= $stats['en_attente'] ?></h3>
                            <p style="margin: 4px 0 0; color: var(--muted);">En attente</p>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card" style="background: white; border: 1px solid var(--border); border-radius: 12px; padding: 24px;">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 56px; height: 56px; border-radius: 12px; background: rgba(220, 53, 69, 0.1); color: #dc3545; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.8rem;"><?= $stats['rejetees'] ?></h3>
                            <p style="margin: 4px 0 0; color: var(--muted);">Rejetées</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Liste des publications -->
            <div class="content-card">
                <?php if (empty($publications)): ?>
                    <div style="text-align: center; padding: 60px 20px;">
                        <i class="fas fa-book" style="font-size: 4rem; color: var(--muted); opacity: 0.3; margin-bottom: 20px;"></i>
                        <h3 style="color: var(--muted);">Aucune publication</h3>
                        <p style="color: var(--muted); margin: 16px 0;">Vous n'avez pas encore de publication.</p>
                        <a href="index.php?url=userpublication/add" class="btn primary">
                            <i class="fas fa-plus"></i> Créer ma première publication
                        </a>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: var(--bg); border-bottom: 2px solid var(--border);">
                                    <th style="padding: 16px; text-align: left; font-weight: 600;">Titre</th>
                                    <th style="padding: 16px; text-align: left; font-weight: 600;">Type</th>
                                    <th style="padding: 16px; text-align: center; font-weight: 600;">Année</th>
                                    <th style="padding: 16px; text-align: center; font-weight: 600;">Statut</th>
                                    <th style="padding: 16px; text-align: center; font-weight: 600;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($publications as $pub): ?>
                                    <tr style="border-bottom: 1px solid var(--border);">
                                        <td style="padding: 16px;">
                                            <strong><?= htmlspecialchars($pub['titre']) ?></strong>
                                            <?php if (!empty($pub['auteurs'])): ?>
                                                <div style="font-size: 0.85rem; color: var(--muted); margin-top: 4px;">
                                                    <?= htmlspecialchars($pub['auteurs']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 16px;">
                                            <span class="badge primary">
                                                <?= ucfirst(htmlspecialchars($pub['type_publication'])) ?>
                                            </span>
                                        </td>
                                        <td style="padding: 16px; text-align: center;">
                                            <?= htmlspecialchars($pub['annee_publication']) ?>
                                        </td>
                                        <td style="padding: 16px; text-align: center;">
                                            <?php
                                            $statutClass = '';
                                            $statutLabel = '';
                                            switch ($pub['statut']) {
                                                case 'valide':
                                                    $statutClass = 'success';
                                                    $statutLabel = 'Validée';
                                                    break;
                                                case 'en_attente':
                                                    $statutClass = 'warning';
                                                    $statutLabel = 'En attente';
                                                    break;
                                                case 'rejete':
                                                    $statutClass = '';
                                                    $statutLabel = 'Rejetée';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?= $statutClass ?>">
                                                <?= $statutLabel ?>
                                            </span>
                                        </td>
                                        <td style="padding: 16px; text-align: center;">
                                            <div style="display: flex; gap: 8px; justify-content: center;">
                                                <a href="index.php?url=publication/detail/<?= $pub['id_publication'] ?>" 
                                                   class="btn ghost" 
                                                   style="padding: 8px 12px; font-size: 0.9rem;"
                                                   title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="index.php?url=userpublication/edit/<?= $pub['id_publication'] ?>" 
                                                   class="btn ghost" 
                                                   style="padding: 8px 12px; font-size: 0.9rem;"
                                                   title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="index.php?url=userpublication/delete/<?= $pub['id_publication'] ?>" 
                                                   class="btn ghost" 
                                                   style="padding: 8px 12px; font-size: 0.9rem; color: #dc3545;"
                                                   title="Supprimer"
                                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette publication ?');">
                                                    <i class="fas fa-trash"></i>
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
        
        <?php
        FooterView::render($settings);
    }
    
    /**
     * Formulaire d'ajout/modification de publication
     */
    public static function renderForm(array $data) {
        $settings = $data['settings'] ?? [];
        $action = $data['action'] ?? 'add';
        $publication = $data['publication'] ?? null;
        $projects = $data['projects'] ?? [];
        $users = $data['users'] ?? [];
        $authors = $data['authors'] ?? [];
        $error = $data['error'] ?? null;
        
        $isEdit = $action === 'edit';
        $pageTitle = $isEdit ? 'Modifier une publication' : 'Nouvelle publication';
        
        // Créer un tableau des IDs des auteurs actuels pour le mode édition
        $currentAuthorIds = array_column($authors, 'id_user');
        
        HeaderView::render($settings, $pageTitle);
        MenuView::render('publications');
        ?>
        
        <div class="container" style="padding: 40px 0;">
            <!-- En-tête -->
            <div style="margin-bottom: 32px;">
                <h1><?= $pageTitle ?></h1>
                <a href="index.php?url=userpublication" class="link" style="margin-top: 8px; display: inline-block;">
                    <i class="fas fa-arrow-left"></i> Retour à mes publications
                </a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert error" style="margin-bottom: 24px;">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <div class="content-card" style="padding: 32px;">
                <form method="POST" enctype="multipart/form-data">
                    
                    <!-- Titre -->
                    <div style="margin-bottom: 24px;">
                        <label for="titre" style="display: block; margin-bottom: 8px; font-weight: 600;">
                            Titre <span style="color: #dc3545;">*</span>
                        </label>
                        <input type="text" 
                            id="titre" 
                            name="titre" 
                            value="<?= htmlspecialchars($publication['titre'] ?? '') ?>"
                            required
                            style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; font-size: 1rem;">
                    </div>
                    
                    <!-- Type et Année -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                        <div>
                            <label for="type_publication" style="display: block; margin-bottom: 8px; font-weight: 600;">
                                Type <span style="color: #dc3545;">*</span>
                            </label>
                            <select id="type_publication" 
                                    name="type_publication" 
                                    required
                                    style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; font-size: 1rem;">
                                <option value="">-- Sélectionner --</option>
                                <option value="article" <?= ($publication['type_publication'] ?? '') === 'article' ? 'selected' : '' ?>>Article</option>
                                <option value="rapport" <?= ($publication['type_publication'] ?? '') === 'rapport' ? 'selected' : '' ?>>Rapport</option>
                                <option value="these" <?= ($publication['type_publication'] ?? '') === 'these' ? 'selected' : '' ?>>Thèse</option>
                                <option value="communication" <?= ($publication['type_publication'] ?? '') === 'communication' ? 'selected' : '' ?>>Communication</option>
                                <option value="poster" <?= ($publication['type_publication'] ?? '') === 'poster' ? 'selected' : '' ?>>Poster</option>
                                <option value="autre" <?= ($publication['type_publication'] ?? '') === 'autre' ? 'selected' : '' ?>>Autre</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="annee_publication" style="display: block; margin-bottom: 8px; font-weight: 600;">
                                Année <span style="color: #dc3545;">*</span>
                            </label>
                            <input type="number" 
                                id="annee_publication" 
                                name="annee_publication" 
                                value="<?= htmlspecialchars($publication['annee_publication'] ?? date('Y')) ?>"
                                required
                                min="1900"
                                max="<?= date('Y') + 1 ?>"
                                style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; font-size: 1rem;">
                        </div>
                    </div>
                    
                    <!-- Domaine -->
                    <div style="margin-bottom: 24px;">
                        <label for="domaine" style="display: block; margin-bottom: 8px; font-weight: 600;">
                            Domaine
                        </label>
                        <input type="text" 
                            id="domaine" 
                            name="domaine" 
                            value="<?= htmlspecialchars($publication['domaine'] ?? '') ?>"
                            placeholder="Ex: Intelligence Artificielle, Sécurité Informatique..."
                            style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; font-size: 1rem;">
                    </div>
                    
                    <!-- Résumé -->
                    <div style="margin-bottom: 24px;">
                        <label for="resume" style="display: block; margin-bottom: 8px; font-weight: 600;">
                            Résumé
                        </label>
                        <textarea id="resume" 
                                name="resume" 
                                rows="6"
                                style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; font-size: 1rem; resize: vertical;"><?= htmlspecialchars($publication['resume'] ?? '') ?></textarea>
                    </div>
                    
                    <!-- Co-auteurs -->
                    <div style="margin-bottom: 24px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                        <label style="display: block; margin-bottom: 12px; font-weight: 600;">
                            <i class="fas fa-users"></i> Co-auteurs
                        </label>
                        <p style="color: var(--muted); font-size: 0.9rem; margin-bottom: 16px;">
                            Vous êtes automatiquement ajouté comme premier auteur. Sélectionnez les co-auteurs :
                        </p>
                        
                        <div style="max-height: 300px; overflow-y: auto; border: 2px solid var(--border); border-radius: 8px; background: white;">
                            <?php if (!empty($users)): ?>
                                <?php 
                                $currentUserId = AuthController::getCurrentUser()['id_user'];
                                foreach ($users as $user): 
                                    // Ne pas afficher l'utilisateur actuel dans la liste
                                    if ($user['id_user'] == $currentUserId) continue;
                                    
                                    $isSelected = in_array($user['id_user'], $currentAuthorIds);
                                ?>
                                    <label style="display: flex; align-items: center; padding: 12px 16px; border-bottom: 1px solid var(--border); cursor: pointer; transition: background 0.2s;"
                                        onmouseover="this.style.background='#f8f9fa'"
                                        onmouseout="this.style.background='white'">
                                        <input type="checkbox" 
                                            name="coauthors[]" 
                                            value="<?= $user['id_user'] ?>"
                                            <?= $isSelected ? 'checked' : '' ?>
                                            style="margin-right: 12px; width: 18px; height: 18px; cursor: pointer;">
                                        <div style="flex: 1;">
                                            <strong><?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?></strong>
                                            <?php if (!empty($user['grade'])): ?>
                                                <span style="color: var(--muted); font-size: 0.85rem; margin-left: 8px;">
                                                    (<?= htmlspecialchars($user['grade']) ?>)
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="padding: 20px; text-align: center; color: var(--muted);">
                                    Aucun utilisateur disponible
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <p style="color: var(--muted); font-size: 0.85rem; margin-top: 8px;">
                            <i class="fas fa-info-circle"></i> L'ordre des auteurs peut être modifié après la création
                        </p>
                    </div>
                    
                    <!-- DOI et Lien -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                        <div>
                            <label for="doi" style="display: block; margin-bottom: 8px; font-weight: 600;">
                                DOI
                            </label>
                            <input type="text" 
                                id="doi" 
                                name="doi" 
                                value="<?= htmlspecialchars($publication['doi'] ?? '') ?>"
                                placeholder="10.1234/example"
                                style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; font-size: 1rem;">
                        </div>
                        
                        <div>
                            <label for="lien_telechargement" style="display: block; margin-bottom: 8px; font-weight: 600;">
                                Lien de téléchargement
                            </label>
                            <input type="url" 
                                id="lien_telechargement" 
                                name="lien_telechargement" 
                                value="<?= htmlspecialchars($publication['lien_telechargement'] ?? '') ?>"
                                placeholder="https://..."
                                style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; font-size: 1rem;">
                        </div>
                    </div>
                    
                    <!-- Projet associé -->
                    <?php if (!empty($projects)): ?>
                    <div style="margin-bottom: 24px;">
                        <label for="id_project" style="display: block; margin-bottom: 8px; font-weight: 600;">
                            Projet associé
                        </label>
                        <select id="id_project" 
                                name="id_project"
                                style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; font-size: 1rem;">
                            <option value="">-- Aucun --</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?= $project['id_project'] ?>" 
                                        <?= ($publication['id_project'] ?? '') == $project['id_project'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($project['titre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Actions -->
                    <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 24px; border-top: 1px solid var(--border);">
                        <a href="index.php?url=userpublication" class="btn ghost">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                        <button type="submit" class="btn primary">
                            <i class="fas fa-save"></i> <?= $isEdit ? 'Mettre à jour' : 'Créer' ?>
                        </button>
                    </div>
                    
                </form>
            </div>
        </div>
        
        <?php
        FooterView::render($settings);
    }
}
?>