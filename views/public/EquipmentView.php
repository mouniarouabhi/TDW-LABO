<?php
// views/public/EquipmentView.php
// Point 7 du PDF: Équipements avec réservation et alertes conflits

class EquipmentView {
    
    public static function renderListe(array $data) {
        $settings = $data['settings'] ?? [];
        $equipments = $data['equipments'] ?? [];
        $types = $data['types'] ?? [];

        HeaderView::render($settings, 'Équipements');
        MenuView::render('equipments');
        ?>

        <section class="section">
            <div class="container">
                <h1>Équipements et Ressources</h1>
                <p style="color: var(--muted); margin-bottom: 32px;">Salles, serveurs, PC, robots, imprimantes, capteurs, etc.</p>

                <!-- Filtres -->
                <div class="content-card" style="padding: 24px; margin-bottom: 32px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                        <select id="filter-type" style="padding: 10px; border: 2px solid var(--border); border-radius: 8px;">
                            <option value="">Tous les types</option>
                            <?php foreach ($types as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <select id="filter-etat" style="padding: 10px; border: 2px solid var(--border); border-radius: 8px;">
                            <option value="">Tous les états</option>
                            <option value="libre">Libre</option>
                            <option value="reserve">Réservé</option>
                            <option value="en_maintenance">En maintenance</option>
                        </select>

                        <button type="button" id="reset-filters" class="btn ghost">Réinitialiser</button>
                    </div>
                </div>

                <!-- Liste -->
                <?php if (!empty($equipments)): ?>
                    <div id="equipment-cards" class="cards-grid">
                        <?php foreach ($equipments as $eq):
                            $etat = $eq['etat_actuel'] ?? $eq['etat'];
                            $badgeClass = '';
                            switch ($etat) {
                                case 'libre': $badgeClass = 'success'; break;
                                case 'reserve': $badgeClass = 'warning'; break;
                                case 'en_maintenance': $badgeClass = ''; break;
                            }
                        ?>
                            <div class="content-card equipment-card" 
                                data-type="<?= htmlspecialchars($eq['type_equipment']) ?>" 
                                data-etat="<?= $etat ?>" 
                                style="padding: 24px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                                    <span class="badge"><?= htmlspecialchars($eq['type_equipment'] ?? 'Équipement') ?></span>
                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst($etat) ?></span>
                                </div>

                                <h3><?= htmlspecialchars($eq['nom_equipment']) ?></h3>
                                <p style="color: var(--muted);"><?= htmlspecialchars(mb_substr($eq['description'] ?? '', 0, 100)) ?>...</p>

                                <?php if ($etat !== 'en_maintenance'): ?>
                                    <a href="index.php?url=equipment/reserver/<?= $eq['id_equipment'] ?>" 
                                    class="btn primary" style="margin-top: 16px; width: 100%; justify-content: center;">
                                        <i class="fas fa-calendar-check"></i> Réserver
                                    </a>
                                <?php else: ?>
                                    <button class="btn" disabled style="margin-top: 16px; width: 100%;">En maintenance</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p id="no-equipment-msg">Aucun équipement trouvé</p>
                <?php endif; ?>
            </div>
        </section>

        <script>
            const typeFilter = document.getElementById('filter-type');
            const etatFilter = document.getElementById('filter-etat');
            const resetBtn = document.getElementById('reset-filters');
            const cards = document.querySelectorAll('.equipment-card');
            const noMsg = document.getElementById('no-equipment-msg');

            function filterCards() {
                const typeValue = typeFilter.value;
                const etatValue = etatFilter.value;
                let visibleCount = 0;

                cards.forEach(card => {
                    const cardType = card.getAttribute('data-type');
                    const cardEtat = card.getAttribute('data-etat');
                    let show = true;

                    if (typeValue && cardType !== typeValue) show = false;
                    if (etatValue && cardEtat !== etatValue) show = false;

                    card.style.display = show ? 'block' : 'none';
                    if (show) visibleCount++;
                });

                if (noMsg) noMsg.style.display = visibleCount === 0 ? 'block' : 'none';
            }

            typeFilter.addEventListener('change', filterCards);
            etatFilter.addEventListener('change', filterCards);
            resetBtn.addEventListener('click', () => {
            typeFilter.value = '';
            etatFilter.value = '';
            filterCards();
    });
        </script>

        <?php
        FooterView::render($settings);
    }
    
    public static function renderReservation(array $data) {
        $settings = $data['settings'] ?? [];
        $equipment = $data['equipment'] ?? [];
        $reservations = $data['reservations'] ?? [];
        $user = $data['user'] ?? [];
        $error = $data['error'] ?? null;
        
        HeaderView::render($settings, 'Réserver - ' . ($equipment['nom_equipment'] ?? ''));
        MenuView::render('equipments');
        ?>
        
        <section class="section">
            <div class="container">
                <a href="index.php?url=equipment" class="link"><i class="fas fa-arrow-left"></i> Retour</a>
                
                <h1 style="margin-top: 24px;">Réserver: <?= htmlspecialchars($equipment['nom_equipment']) ?></h1>
                
                <?php if ($error === 'conflict'): ?>
                    <div class="alert error" style="margin-top: 16px;">
                        <i class="fas fa-exclamation-circle"></i>
                        <strong>Conflit détecté!</strong> Les dates sélectionnées chevauchent une réservation existante.
                    </div>
                <?php elseif ($error): ?>
                    <div class="alert error" style="margin-top: 16px;">Une erreur est survenue</div>
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px; margin-top: 32px;">
                    <div class="content-card" style="padding: 32px;">
                        <form method="POST" action="index.php?url=equipment/traiterReservation">
                            <input type="hidden" name="id_equipment" value="<?= $equipment['id_equipment'] ?>">
                            
                            <div style="margin-bottom: 16px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Date début *</label>
                                <input type="datetime-local" name="date_debut" required 
                                       style="width: 100%; padding: 10px; border: 2px solid var(--border); border-radius: 8px;">
                            </div>
                            
                            <div style="margin-bottom: 16px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Date fin *</label>
                                <input type="datetime-local" name="date_fin" required
                                       style="width: 100%; padding: 10px; border: 2px solid var(--border); border-radius: 8px;">
                            </div>
                            
                            <div style="margin-bottom: 24px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Motif</label>
                                <textarea name="motif" rows="3"
                                          style="width: 100%; padding: 10px; border: 2px solid var(--border); border-radius: 8px;"></textarea>
                            </div>
                            
                            <button type="submit" class="btn primary" style="width: 100%;">
                                <i class="fas fa-check"></i> Confirmer la réservation
                            </button>
                        </form>
                    </div>
                    
                    <div>
                        <div class="content-card" style="padding: 24px; margin-bottom: 16px;">
                            <h3>Réservations existantes</h3>
                            <?php if (!empty($reservations)): ?>
                                <?php foreach ($reservations as $res): ?>
                                    <div style="padding: 8px; background: var(--bg); margin-top: 8px; border-radius: 4px; font-size: 0.9rem;">
                                        <?= date('d/m/Y H:i', strtotime($res['date_debut'])) ?> - <?= date('d/m/Y H:i', strtotime($res['date_fin'])) ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="color: var(--muted); font-size: 0.9rem;">Aucune réservation</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <?php
        FooterView::render($settings);
    }
    
    public static function renderHistorique(array $data) {
        $settings = $data['settings'] ?? [];
        $reservations = $data['reservations'] ?? [];
        
        HeaderView::render($settings, 'Mes réservations');
        MenuView::render('equipments');
        ?>
        
        <section class="section">
            <div class="container">
                <h1>Historique de mes réservations</h1>
                
                <?php if (!empty($reservations)): ?>
                    <div style="margin-top: 32px;">
                        <?php foreach ($reservations as $res): ?>
                            <div class="content-card" style="padding: 24px; margin-bottom: 16px;">
                                <div style="display: flex; justify-content: space-between;">
                                    <div>
                                        <h3><?= htmlspecialchars($res['nom_equipment'] ?? 'Équipement') ?></h3>
                                        <p style="color: var(--muted);">
                                            Du <?= date('d/m/Y H:i', strtotime($res['date_debut'])) ?> 
                                            au <?= date('d/m/Y H:i', strtotime($res['date_fin'])) ?>
                                        </p>
                                        <span class="badge"><?= ucfirst($res['statut']) ?></span>
                                    </div>
                                    
                                    <?php if ($res['statut'] === 'en_attente' || $res['statut'] === 'valide'): ?>
                                        <a href="index.php?url=equipment/annuler/<?= $res['id_reservation'] ?>" 
                                           class="btn ghost">
                                            Annuler
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>Aucune réservation</p>
                <?php endif; ?>
            </div>
        </section>
        
        <?php
        FooterView::render($settings);
    }
    
    public static function renderStatistiques(array $data) {
        $settings = $data['settings'] ?? [];
        $stats = $data['stats'] ?? [];
        $equipments = $data['equipments'] ?? [];
        
        HeaderView::render($settings, 'Statistiques');
        MenuView::render('equipments');
        ?>
        
        <section class="section">
            <div class="container">
                <h1>Statistiques d'utilisation</h1>
                
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin: 32px 0;">
                    <div class="content-card" style="padding: 24px; text-align: center;">
                        <div style="font-size: 2rem; font-weight: bold; color: var(--primary);"><?= $stats['total_equipments'] ?></div>
                        <div>Équipements</div>
                    </div>
                    <div class="content-card" style="padding: 24px; text-align: center;">
                        <div style="font-size: 2rem; font-weight: bold; color: #2eb85c;"><?= $stats['equipments_libres'] ?></div>
                        <div>Libres</div>
                    </div>
                    <div class="content-card" style="padding: 24px; text-align: center;">
                        <div style="font-size: 2rem; font-weight: bold; color: #ffc107;"><?= $stats['equipments_reserves'] ?></div>
                        <div>Réservés</div>
                    </div>
                    <div class="content-card" style="padding: 24px; text-align: center;">
                        <div style="font-size: 2rem; font-weight: bold; color: #6c757d;"><?= $stats['equipments_maintenance'] ?></div>
                        <div>En maintenance</div>
                    </div>
                </div>
            </div>
        </section>
        
        <?php
        FooterView::render($settings);
    }
}
?>