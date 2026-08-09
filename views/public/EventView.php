<?php
// views/public/EventView.php

class EventView {
    
    public static function renderListe(array $data) {
        $settings = $data['settings'] ?? [];
        $events = $data['events'] ?? [];
        
        HeaderView::render($settings, 'Événements');
        MenuView::render('events');
        ?>
        
        <section class="hero" style="background: linear-gradient(135deg, var(--primary), var(--nav)); padding: 60px 0; color: white;">
            <div class="container" style="text-align: center;">
                <h1 style="color: white;">Événements Scientifiques</h1>
                <p style="color: rgba(255,255,255,0.9);">Ateliers, séminaires, conférences</p>
            </div>
        </section>
        
        <section class="section">
            <div class="container">
                <?php if (!empty($events)): ?>
                    <div class="cards-grid">
                        <?php foreach ($events as $event): ?>
                            <?php
                            // Détecter le nom de la colonne date (peut être date_event, date_debut, ou date)
                            $dateField = null;
                            $dateValue = null;
                            
                            if (!empty($event['date_event'])) {
                                $dateField = 'date_event';
                                $dateValue = $event['date_event'];
                            } elseif (!empty($event['date_debut'])) {
                                $dateField = 'date_debut';
                                $dateValue = $event['date_debut'];
                            } elseif (!empty($event['date'])) {
                                $dateField = 'date';
                                $dateValue = $event['date'];
                            }
                            
                            CardView::render([
                                'badge' => $event['type_event'] ?? $event['type'] ?? 'Événement',
                                'title' => $event['titre'] ?? $event['nom_event'] ?? 'Sans titre',
                                'text' => mb_substr($event['description'] ?? '', 0, 150) . '...',
                                'link' => 'index.php?url=event/detail/' . $event['id_event'],
                                'meta' => [
                                    [
                                        'icon' => 'far fa-calendar',
                                        'label' => $dateValue ? date('d/m/Y H:i', strtotime($dateValue)) : 'Date à définir'
                                    ],
                                    [
                                        'icon' => 'fas fa-map-marker-alt',
                                        'label' => $event['lieu'] ?? 'Lieu à définir'
                                    ]
                                ]
                            ]);
                            ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state" style="text-align: center; padding: 60px;">
                        <i class="far fa-calendar" style="font-size: 4rem; opacity: 0.3; margin-bottom: 16px;"></i>
                        <h3>Aucun événement à venir</h3>
                        <p style="color: var(--muted);">Revenez plus tard pour découvrir nos prochains événements</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        
        <?php
        FooterView::render($settings);
    }
    
    public static function renderDetail(array $data) {
        $settings = $data['settings'] ?? [];
        $event = $data['event'] ?? [];
        $isRegistered = $data['isRegistered'] ?? false;
        $success = $data['success'] ?? null;
        $error = $data['error'] ?? null;
        
        // Détecter le nom de la colonne date
        $dateValue = $event['date_event'] ?? $event['date_debut'] ?? $event['date'] ?? null;
        
        HeaderView::render($settings, $event['titre'] ?? $event['nom_event'] ?? 'Événement');
        MenuView::render('events');
        ?>
        
        <!-- Breadcrumb -->
        <section style="background: var(--bg); padding: 16px 0; border-bottom: 1px solid var(--border);">
            <div class="container">
                <div style="font-size: 0.9rem; color: var(--muted);">
                    <a href="index.php" class="link">Accueil</a>
                    <span> / </span>
                    <a href="index.php?url=events" class="link">Événements</a>
                    <span> / </span>
                    <span><?= htmlspecialchars($event['titre'] ?? $event['nom_event'] ?? '') ?></span>
                </div>
            </div>
        </section>
        
        <section class="section">
            <div class="container">
                <?php if ($success === 'registered'): ?>
                    <div class="alert success">
                        <i class="fas fa-check-circle"></i> Vous êtes maintenant inscrit à cet événement !
                    </div>
                <?php elseif ($error === 'already_registered'): ?>
                    <div class="alert warning">
                        <i class="fas fa-info-circle"></i> Vous êtes déjà inscrit à cet événement
                    </div>
                <?php elseif ($error): ?>
                    <div class="alert error">
                        <i class="fas fa-exclamation-circle"></i> Une erreur est survenue
                    </div>
                <?php endif; ?>
                
                <div class="content-card" style="padding: 32px; margin-top: 24px;">
                    <!-- En-tête -->
                    <div style="margin-bottom: 24px;">
                        <span class="badge primary">
                            <?= htmlspecialchars($event['type_event'] ?? $event['type'] ?? 'Événement') ?>
                        </span>
                    </div>
                    
                    <h1 style="margin: 0 0 16px;"><?= htmlspecialchars($event['titre'] ?? $event['nom_event'] ?? '') ?></h1>
                    
                    <div style="display: flex; gap: 24px; color: var(--muted); margin-bottom: 32px; flex-wrap: wrap;">
                        <?php if ($dateValue): ?>
                            <span>
                                <i class="far fa-calendar"></i> 
                                <?= date('d/m/Y à H:i', strtotime($dateValue)) ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if (!empty($event['lieu'])): ?>
                            <span>
                                <i class="fas fa-map-marker-alt"></i> 
                                <?= htmlspecialchars($event['lieu']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Description -->
                    <div style="margin-bottom: 32px;">
                        <h3>Description</h3>
                        <p style="white-space: pre-line; line-height: 1.8;">
                            <?= htmlspecialchars($event['description'] ?? 'Aucune description disponible') ?>
                        </p>
                    </div>
                    
                    <!-- Inscription -->
                    <?php if (AuthController::isAuthenticated()): ?>
                        <div style="padding: 24px; background: var(--bg); border-radius: 8px; border: 2px solid var(--border);">
                            <?php if ($isRegistered): ?>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <i class="fas fa-check-circle" style="color: #2eb85c; font-size: 1.5rem;"></i>
                                    <div>
                                        <strong>Vous êtes inscrit à cet événement</strong>
                                        <div style="color: var(--muted); font-size: 0.9rem;">
                                            Nous vous enverrons un rappel avant l'événement
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong>Participer à cet événement</strong>
                                        <div style="color: var(--muted); font-size: 0.9rem;">
                                            Inscrivez-vous pour recevoir les détails et rappels
                                        </div>
                                    </div>
                                    <a href="index.php?url=event/inscrire/<?= $event['id_event'] ?>" 
                                       class="btn primary">
                                        <i class="fas fa-user-plus"></i> S'inscrire
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div style="padding: 24px; background: var(--bg); border-radius: 8px; border: 2px solid var(--border); text-align: center;">
                            <p style="margin-bottom: 16px;">Connectez-vous pour vous inscrire à cet événement</p>
                            <a href="index.php?url=auth" class="btn primary">
                                <i class="fas fa-sign-in-alt"></i> Se connecter
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div style="margin-top: 24px;">
                    <a href="index.php?url=event" class="btn ghost">
                        <i class="fas fa-arrow-left"></i> Retour aux événements
                    </a>
                </div>
            </div>
        </section>
        
        <?php
        FooterView::render($settings);
    }
}
?>