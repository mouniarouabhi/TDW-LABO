<?php
// views/admin/AdminEquipmentView.php
// VERSION CORRIGÉE - Styles séparés

class AdminEquipmentView {
    
    /**
     * Affichage de la liste des équipements
     */
    public static function renderList(array $data) {
        $settings = $data['settings'] ?? [];
        $equipments = $data['equipments'] ?? [];
        
        AdminHeaderView::render($settings, 'Gestion des équipements');
        AdminSidebarView::render('equipments');
        ?>
        
        <div class="admin-page-header">
            <h1>Gestion des équipements et ressources</h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <span>Équipements</span>
            </div>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?php
                if ($_GET['success'] === 'created') echo 'Équipement créé avec succès';
                elseif ($_GET['success'] === 'updated') echo 'Équipement mis à jour avec succès';
                elseif ($_GET['success'] === 'deleted') echo 'Équipement supprimé avec succès';
                ?>
            </div>
        <?php endif; ?>
        
        <div class="content-card">
            <div class="card-header">
                <h2>Liste des équipements (<?= count($equipments) ?>)</h2>
                <a href="index.php?url=admin/addEquipment" class="btn primary">
                    <i class="fas fa-plus"></i> Ajouter un équipement
                </a>
            </div>
            
            <div class="card-body">
                <?php
                $tableData = [];
                foreach ($equipments as $equipment) {
                    // ========================================
                    // CORRECTION - Badge état sans style inline
                    // ========================================
                    $etatBadge = match ($equipment['etat']) {
                        'libre' => '<span class="badge success">Libre</span>',
                        'reserve' => '<span class="badge warning">Réservé</span>',
                        'en_maintenance' => '<span class="badge default">En maintenance</span>',
                        'hors_service' => '<span class="badge danger">Hors service</span>',
                        default => '<span class="badge">-</span>'
                    };
                    
                    // ========================================
                    // Icônes par type d'équipement
                    // ========================================
                    $typeIcons = [
                        'salle' => 'fas fa-door-open',
                        'serveur' => 'fas fa-server',
                        'pc' => 'fas fa-desktop',
                        'robot' => 'fas fa-robot',
                        'imprimante' => 'fas fa-print',
                        'capteur' => 'fas fa-microchip',
                        'autre' => 'fas fa-box'
                    ];
                    
                    $icon = $typeIcons[$equipment['type_equipment']] ?? 'fas fa-box';
                    
                    // ========================================
                    // CORRECTION - Sans styles inline
                    // ========================================
                    $tableData[] = [
                        'id' => $equipment['id_equipment'],
                        'nom_equipment_html' => '<div class="equipment-name">
                                                    <i class="' . $icon . ' equipment-icon"></i>
                                                    <strong>' . htmlspecialchars($equipment['nom_equipment']) . '</strong>
                                                 </div>',
                        'type_equipment' => ucfirst($equipment['type_equipment']),
                        'localisation' => $equipment['localisation'] ?? '-',
                        'etat_html' => $etatBadge,
                        // ========================================
                        // CORRECTION - Gestion date vide/invalide
                        // ========================================
                        'date_acquisition' => (!empty($equipment['date_acquisition']) && strtotime($equipment['date_acquisition']) !== false) 
                            ? date('d/m/Y', strtotime($equipment['date_acquisition'])) 
                            : '-'
                    ];
                }
                
                TableView::render([
                    'id' => 'equipments-table',
                    'columns' => [
                        'nom_equipment' => 'Nom de l\'équipement',
                        'type_equipment' => 'Type',
                        'localisation' => 'Localisation',
                        'etat' => 'État',
                        'date_acquisition' => 'Date acquisition'
                    ],
                    'data' => $tableData,
                    'actions' => [
                        [
                            'label' => 'Réservations',
                            'url' => 'index.php?url=admin/equipmentReservations/{id}',
                            'icon' => 'fas fa-calendar-check',
                            'class' => 'btn-action view'
                        ],
                        [
                            'label' => 'Modifier',
                            'url' => 'index.php?url=admin/editEquipment/{id}',
                            'icon' => 'fas fa-edit',
                            'class' => 'btn-action edit'
                        ],
                        [
                            'label' => 'Supprimer',
                            'url' => 'index.php?url=admin/deleteEquipment/{id}',
                            'icon' => 'fas fa-trash',
                            'class' => 'btn-action delete',
                            'confirm' => true
                        ]
                    ],
                    'searchable' => true,
                    'empty_message' => 'Aucun équipement trouvé'
                ]);
                ?>
            </div>
        </div>
        
        </div><!-- .admin-main -->
        <?php
        AdminFooterView::render();
    }
    
    /**
     * Formulaire d'ajout/modification d'équipement
     */
    public static function renderForm(array $data) {
        $settings = $data['settings'] ?? [];
        $action = $data['action'] ?? 'add';
        $equipment = $data['equipment'] ?? null;
        $error = $data['error'] ?? null;
        
        $title = $action === 'add' ? 'Ajouter un équipement' : 'Modifier un équipement';
        $submitUrl = $action === 'add' ? 'index.php?url=admin/addEquipment' : 'index.php?url=admin/editEquipment/' . $equipment['id_equipment'];
        
        AdminHeaderView::render($settings, $title);
        AdminSidebarView::render('equipments');
        ?>
        
        <div class="admin-page-header">
            <h1><?= $title ?></h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <a href="index.php?url=admin/equipments">Équipements</a>
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
                <h2>Informations de l'équipement</h2>
            </div>
            
            <div class="card-body">
                <form method="POST" action="<?= $submitUrl ?>">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nom_equipment">Nom de l'équipement *</label>
                            <input type="text" id="nom_equipment" name="nom_equipment" 
                                   value="<?= htmlspecialchars($equipment['nom_equipment'] ?? '') ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="type_equipment">Type d'équipement *</label>
                            <select id="type_equipment" name="type_equipment" required>
                                <option value="salle" <?= ($equipment['type_equipment'] ?? '') === 'salle' ? 'selected' : '' ?>>Salle</option>
                                <option value="serveur" <?= ($equipment['type_equipment'] ?? '') === 'serveur' ? 'selected' : '' ?>>Serveur</option>
                                <option value="pc" <?= ($equipment['type_equipment'] ?? '') === 'pc' ? 'selected' : '' ?>>PC</option>
                                <option value="robot" <?= ($equipment['type_equipment'] ?? '') === 'robot' ? 'selected' : '' ?>>Robot</option>
                                <option value="imprimante" <?= ($equipment['type_equipment'] ?? '') === 'imprimante' ? 'selected' : '' ?>>Imprimante</option>
                                <option value="capteur" <?= ($equipment['type_equipment'] ?? '') === 'capteur' ? 'selected' : '' ?>>Capteur</option>
                                <option value="autre" <?= ($equipment['type_equipment'] ?? '') === 'autre' ? 'selected' : '' ?>>Autre</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="etat">État *</label>
                            <select id="etat" name="etat" required>
                                <option value="libre" <?= ($equipment['etat'] ?? 'libre') === 'libre' ? 'selected' : '' ?>>Libre</option>
                                <option value="reserve" <?= ($equipment['etat'] ?? '') === 'reserve' ? 'selected' : '' ?>>Réservé</option>
                                <option value="en_maintenance" <?= ($equipment['etat'] ?? '') === 'en_maintenance' ? 'selected' : '' ?>>En maintenance</option>
                                <option value="hors_service" <?= ($equipment['etat'] ?? '') === 'hors_service' ? 'selected' : '' ?>>Hors service</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="localisation">Localisation</label>
                            <input type="text" id="localisation" name="localisation" 
                                   value="<?= htmlspecialchars($equipment['localisation'] ?? '') ?>"
                                   placeholder="Ex: Bâtiment A, Salle 101">
                        </div>
                        
                        <div class="form-group">
                            <label for="date_acquisition">Date d'acquisition</label>
                            <input type="date" id="date_acquisition" name="date_acquisition" 
                                   value="<?= htmlspecialchars($equipment['date_acquisition'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" 
                                  rows="4"><?= htmlspecialchars($equipment['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn primary">
                            <i class="fas fa-save"></i>
                            <?= $action === 'add' ? 'Créer l\'équipement' : 'Enregistrer les modifications' ?>
                        </button>
                        <a href="index.php?url=admin/equipments" class="btn ghost">
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
     * Affichage des réservations d'un équipement
     */
    public static function renderReservations(array $data) {
        $settings = $data['settings'] ?? [];
        $equipment = $data['equipment'] ?? null;
        $reservations = $data['reservations'] ?? [];
        
        AdminHeaderView::render($settings, 'Réservations');
        AdminSidebarView::render('equipments');
        ?>
        
        <div class="admin-page-header">
            <h1>Réservations - <?= htmlspecialchars($equipment['nom_equipment']) ?></h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <a href="index.php?url=admin/equipments">Équipements</a>
                <span>/</span>
                <span>Réservations</span>
            </div>
        </div>
        
        <div class="content-card">
            <div class="card-header">
                <h2>Historique des réservations (<?= count($reservations) ?>)</h2>
            </div>
            
            <div class="card-body">
                <?php
                // ========================================
                // UTILISATION DU COMPOSANT TABLEVIEW
                // ========================================
                $tableData = [];
                foreach ($reservations as $reservation) {
                    // Badge statut
                    $statusBadge = match ($reservation['statut']) {
                        'confirmee' => '<span class="badge success">Confirmée</span>',
                        'en_attente' => '<span class="badge warning">En attente</span>',
                        'annulee' => '<span class="badge default">Annulée</span>',
                        'terminee' => '<span class="badge default">Terminée</span>',
                        default => '<span class="badge">-</span>'
                    };
                    
                    // Actions conditionnelles
                    $actionsHtml = '';
                    if ($reservation['statut'] === 'en_attente') {
                        $actionsHtml = '<div class="action-buttons">
                                            <a href="index.php?url=admin/updateReservationStatus/' . $equipment['id_equipment'] . '/reserve/' . $reservation['id_reservation'].'/confirmee" 
                                               class="btn-action confirm"
                                               title="Confirmer">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="index.php?url=admin/updateReservationStatus/' . $equipment['id_equipment'] . '/libre/' . $reservation['id_reservation'].'/annulee" 
                                               class="btn-action delete"
                                               title="Annuler"
                                               onclick="return confirm(\'Êtes-vous sûr de vouloir annuler cette réservation ?\')">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        </div>';
                    } else {
                        $actionsHtml = '<span class="text-muted">-</span>';
                    }
                    
                    $tableData[] = [
                        'id' => $reservation['id_reservation'],
                        'utilisateur_html' => '<strong>' . htmlspecialchars($reservation['nom'] . ' ' . $reservation['prenom']) . '</strong>',
                        'date_debut' => date('d/m/Y H:i', strtotime($reservation['date_debut'])),
                        'date_fin' => date('d/m/Y H:i', strtotime($reservation['date_fin'])),
                        'motif' => htmlspecialchars(mb_substr($reservation['motif'], 0, 50)) . '...',
                        'statut_html' => $statusBadge,
                        'actions_html' => $actionsHtml
                    ];
                }
                
                TableView::render([
                    'id' => 'reservations-table',
                    'columns' => [
                        'utilisateur' => 'Utilisateur',
                        'date_debut' => 'Date début',
                        'date_fin' => 'Date fin',
                        'motif' => 'Motif',
                        'statut' => 'Statut',
                        'actions' => 'Actions'
                    ],
                    'data' => $tableData,
                    'searchable' => true,
                    'empty_message' => 'Aucune réservation pour cet équipement'
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