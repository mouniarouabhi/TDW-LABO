<?php
// views/admin/AdminEventView.php
// VERSION CORRIGÉE - RBAC + Styles séparés + TableView

class AdminEventView {
    
    /**
     * Affichage de la liste des événements
     */
    public static function renderList(array $data) {
        $settings = $data['settings'] ?? [];
        $events = $data['events'] ?? [];
        
        AdminHeaderView::render($settings, 'Gestion des événements');
        AdminSidebarView::render('events');
        ?>
        
        <div class="admin-page-header">
            <h1>Gestion des événements et communications</h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <span>Événements</span>
            </div>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?php
                if ($_GET['success'] === 'created') echo 'Événement créé avec succès';
                elseif ($_GET['success'] === 'updated') echo 'Événement mis à jour avec succès';
                elseif ($_GET['success'] === 'deleted') echo 'Événement supprimé avec succès';
                ?>
            </div>
        <?php endif; ?>
        
        <div class="content-card">
            <div class="card-header">
                <h2>Liste des événements (<?= count($events) ?>)</h2>
                <a href="index.php?url=admin/addEvent" class="btn primary">
                    <i class="fas fa-plus"></i> Ajouter un événement
                </a>
            </div>
            
            <div class="card-body">
                <?php
                $tableData = [];
                foreach ($events as $event) {
                    // ========================================
                    // CORRECTION - Match au lieu de switch
                    // ========================================
                    $statusBadge = match ($event['statut']) {
                        'a_venir' => '<span class="badge warning">À venir</span>',
                        'en_cours' => '<span class="badge success">En cours</span>',
                        'termine' => '<span class="badge default">Terminé</span>',
                        'annule' => '<span class="badge danger">Annulé</span>',
                        default => '<span class="badge">-</span>'
                    };
                    
                    // Icônes par type
                    $typeIcons = [
                        'conference' => 'fas fa-microphone',
                        'atelier' => 'fas fa-tools',
                        'seminaire' => 'fas fa-chalkboard-teacher',
                        'soutenance' => 'fas fa-graduation-cap',
                        'autre' => 'fas fa-calendar'
                    ];
                    
                    $icon = $typeIcons[$event['type_event']] ?? 'fas fa-calendar';
                    
                    // ========================================
                    // CORRECTION - Classes au lieu de styles inline
                    // ========================================
                    $tableData[] = [
                        'id' => $event['id_event'],
                        'titre_html' => '<div class="event-title">
                                            <i class="' . $icon . ' event-icon"></i>
                                            <strong>' . htmlspecialchars($event['titre']) . '</strong>
                                         </div>',
                        'type_event' => ucfirst($event['type_event']),
                        'date_debut' => date('d/m/Y H:i', strtotime($event['date_debut'])),
                        'lieu' => $event['lieu'] ?? '-',
                        'organisateur' => !empty($event['org_nom']) ? $event['org_nom'] . ' ' . $event['org_prenom'] : '-',
                        'statut_html' => $statusBadge
                    ];
                }
                
                TableView::render([
                    'id' => 'events-table',
                    'columns' => [
                        'titre' => 'Titre',
                        'type_event' => 'Type',
                        'date_debut' => 'Date & Heure',
                        'lieu' => 'Lieu',
                        'organisateur' => 'Organisateur',
                        'statut' => 'Statut'
                    ],
                    'data' => $tableData,
                    'actions' => [
                        [
                            'label' => 'Inscriptions',
                            'url' => 'index.php?url=admin/eventRegistrations/{id}',
                            'icon' => 'fas fa-users',
                            'class' => 'btn-action view'
                        ],
                        [
                            'label' => 'Modifier',
                            'url' => 'index.php?url=admin/editEvent/{id}',
                            'icon' => 'fas fa-edit',
                            'class' => 'btn-action edit'
                        ],
                        [
                            'label' => 'Supprimer',
                            'url' => 'index.php?url=admin/deleteEvent/{id}',
                            'icon' => 'fas fa-trash',
                            'class' => 'btn-action delete',
                            'confirm' => true
                        ]
                    ],
                    'searchable' => true,
                    'empty_message' => 'Aucun événement trouvé'
                ]);
                ?>
            </div>
        </div>
        
        </div><!-- .admin-main -->
        <?php
        AdminFooterView::render();
    }
    
    /**
     * Formulaire d'ajout/modification d'événement
     */
    public static function renderForm(array $data) {
        $settings = $data['settings'] ?? [];
        $action = $data['action'] ?? 'add';
        $event = $data['event'] ?? null;
        $users = $data['users'] ?? [];
        $error = $data['error'] ?? null;
        
        $title = $action === 'add' ? 'Ajouter un événement' : 'Modifier un événement';
        $submitUrl = $action === 'add' ? 'index.php?url=admin/addEvent' : 'index.php?url=admin/editEvent/' . $event['id_event'];
        
        AdminHeaderView::render($settings, $title);
        AdminSidebarView::render('events');
        ?>
        
        <div class="admin-page-header">
            <h1><?= $title ?></h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <a href="index.php?url=admin/events">Événements</a>
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
                <h2>Informations de l'événement</h2>
            </div>
            
            <div class="card-body">
                <form method="POST" action="<?= $submitUrl ?>">
                    
                    <div class="form-group">
                        <label for="titre">Titre de l'événement *</label>
                        <input type="text" id="titre" name="titre" 
                               value="<?= htmlspecialchars($event['titre'] ?? '') ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" 
                                  rows="5"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="type_event">Type d'événement *</label>
                            <select id="type_event" name="type_event" required>
                                <option value="conference" <?= ($event['type_event'] ?? '') === 'conference' ? 'selected' : '' ?>>Conférence</option>
                                <option value="atelier" <?= ($event['type_event'] ?? '') === 'atelier' ? 'selected' : '' ?>>Atelier</option>
                                <option value="seminaire" <?= ($event['type_event'] ?? '') === 'seminaire' ? 'selected' : '' ?>>Séminaire</option>
                                <option value="soutenance" <?= ($event['type_event'] ?? '') === 'soutenance' ? 'selected' : '' ?>>Soutenance</option>
                                <option value="autre" <?= ($event['type_event'] ?? '') === 'autre' ? 'selected' : '' ?>>Autre</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="statut">Statut *</label>
                            <select id="statut" name="statut" required>
                                <option value="a_venir" <?= ($event['statut'] ?? 'a_venir') === 'a_venir' ? 'selected' : '' ?>>À venir</option>
                                <option value="en_cours" <?= ($event['statut'] ?? '') === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                                <option value="termine" <?= ($event['statut'] ?? '') === 'termine' ? 'selected' : '' ?>>Terminé</option>
                                <option value="annule" <?= ($event['statut'] ?? '') === 'annule' ? 'selected' : '' ?>>Annulé</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_debut">Date et heure de début *</label>
                            <input type="datetime-local" id="date_debut" name="date_debut" 
                                   value="<?= htmlspecialchars(!empty($event['date_debut']) ? date('Y-m-d\TH:i', strtotime($event['date_debut'])) : '') ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_fin">Date et heure de fin</label>
                            <input type="datetime-local" id="date_fin" name="date_fin" 
                                   value="<?= htmlspecialchars(!empty($event['date_fin']) ? date('Y-m-d\TH:i', strtotime($event['date_fin'])) : '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="lieu">Lieu *</label>
                            <input type="text" id="lieu" name="lieu" 
                                   value="<?= htmlspecialchars($event['lieu'] ?? '') ?>"
                                   placeholder="Ex: Amphithéâtre A, Salle 101..."
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="organisateur">Organisateur</label>
                            <select id="organisateur" name="organisateur" required>
                                <option value=""
                                    <?= isset($event['organisateur']) && $event['organisateur'] !== '' ? 'disabled' : 'selected' ?>>
                                    -- Sélectionner un organisateur --
                                </option>

                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id_user'] ?>"
                                        <?= isset($event['organisateur']) 
                                            && (int)$event['organisateur'] === (int)$user['id_user'] 
                                            ? 'selected' 
                                            : '' ?>>
                                        <?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="lien_inscription">Lien d'inscription</label>
                            <input type="url" id="lien_inscription" name="lien_inscription" 
                                   value="<?= htmlspecialchars($event['lien_inscription'] ?? '') ?>"
                                   placeholder="https://...">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn primary">
                            <i class="fas fa-save"></i>
                            <?= $action === 'add' ? 'Créer l\'événement' : 'Enregistrer les modifications' ?>
                        </button>
                        <a href="index.php?url=admin/events" class="btn ghost">
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
     * Affichage des inscriptions à un événement
     */
    public static function renderRegistrations(array $data) {
        $settings = $data['settings'] ?? [];
        $event = $data['event'] ?? null;
        $registrations = $data['registrations'] ?? [];
        
        AdminHeaderView::render($settings, 'Inscriptions');
        AdminSidebarView::render('events');
        ?>
        
        <div class="admin-page-header">
            <h1>Inscriptions - <?= htmlspecialchars($event['titre']) ?></h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <a href="index.php?url=admin/events">Événements</a>
                <span>/</span>
                <span>Inscriptions</span>
            </div>
        </div>
        
        <div class="content-card">
            <div class="card-header">
                <h2>Liste des inscrits (<?= count($registrations) ?>)</h2>
            </div>
            
            <div class="card-body">
                <?php
                // ========================================
                // CORRECTION - TableView au lieu de HTML manuel
                // ========================================
                $tableData = [];
                foreach ($registrations as $reg) {
                    // Avatar avec classe
                    $avatar = '<img src="assets/images/users/' . htmlspecialchars($reg['photo'] ?? 'default.jpg') . '" 
                                    class="user-avatar" 
                                    alt="' . htmlspecialchars($reg['nom']) . '">';
                    
                    // Rôles (multi-rôles supportés)
                    $regRoles = $reg['roles'] ?? [$reg['role'] ?? 'Utilisateur'];
                    $rolesBadges = '';
                    foreach ($regRoles as $role) {
                        $badgeClass = match ($role) {
                            'admin' => 'badge primary',
                            'enseignant', 'chercheur' => 'badge secondary',
                            'doctorant' => 'badge success',
                            'etudiant' => 'badge warning',
                            default => 'badge default'
                        };
                        $rolesBadges .= '<span class="' . $badgeClass . '">' . htmlspecialchars(ucfirst($role)) . '</span> ';
                    }
                    
                    // Badge statut inscription
                    $statusBadge = match ($reg['registration_status']) {
                        'inscrit' => '<span class="badge success">Inscrit</span>',
                        'annule' => '<span class="badge default">Annulé</span>',
                        'present' => '<span class="badge warning">Présent</span>',
                        default => '<span class="badge">-</span>'
                    };
                    
                    $tableData[] = [
                        'id' => $reg['id_user'],
                        'photo_html' => $avatar,
                        'nom_html' => '<strong>' . htmlspecialchars($reg['nom'] . ' ' . $reg['prenom']) . '</strong>',
                        'email' => $reg['email'],
                        'roles_html' => $rolesBadges,
                        'date_inscription' => date('d/m/Y H:i', strtotime($reg['date_inscription'])),
                        'statut_html' => $statusBadge
                    ];
                }
                
                TableView::render([
                    'id' => 'registrations-table',
                    'columns' => [
                        'photo' => 'Photo',
                        'nom' => 'Nom',
                        'email' => 'Email',
                        'roles' => 'Rôles',
                        'date_inscription' => 'Date inscription',
                        'statut' => 'Statut'
                    ],
                    'data' => $tableData,
                    'searchable' => true,
                    'empty_message' => 'Aucune inscription pour cet événement'
                ]);
                ?>
            </div>
        </div>
        
        </div><!-- .admin-main -->
        <?php
        AdminFooterView::render();
    }
}
?>