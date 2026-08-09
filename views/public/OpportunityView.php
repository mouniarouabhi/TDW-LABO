<?php
// views/public/OpportunityView.php

class OpportunityView {
    
    public static function renderListe(array $data) {
        $settings = $data['settings'] ?? [];
        $opportunities = $data['opportunities'] ?? [];
        $groupedOpportunities = $data['groupedOpportunities'] ?? [];
        $filterType = $data['filterType'] ?? null;
        
        HeaderView::render($settings, 'Opportunités');
        MenuView::render('opportunities');
        ?>
        
        <section class="hero" style="background: linear-gradient(135deg, var(--primary), var(--nav)); padding: 60px 0; color: white;">
            <div class="container" style="text-align: center;">
                <h1 style="color: white;">Offres et Opportunités</h1>
                <p style="color: rgba(255,255,255,0.9);">Stages, thèses, bourses, collaborations</p>
            </div>
        </section>
        
        <section class="section">
            <div class="container">
                
                <h2 style="margin-bottom: 24px;">
                    <?= count($opportunities) ?> opportunité<?= count($opportunities) > 1 ? 's' : '' ?> disponible<?= count($opportunities) > 1 ? 's' : '' ?>
                </h2>
                
                <?php if (!empty($opportunities)): ?>
                    <div class="cards-grid">
                        <?php foreach ($opportunities as $opp): ?>
                            <?php
                            // Badge couleur selon le type
                            $badgeClass = '';
                            $badgeIcon = '';
                            switch ($opp['type_opportunity']) {
                                case 'stage':
                                    $badgeClass = 'primary';
                                    $badgeIcon = 'fas fa-briefcase';
                                    break;
                                case 'these':
                                    $badgeClass = 'success';
                                    $badgeIcon = 'fas fa-graduation-cap';
                                    break;
                                case 'bourse':
                                    $badgeClass = 'warning';
                                    $badgeIcon = 'fas fa-award';
                                    break;
                                case 'collaboration':
                                    $badgeClass = '';
                                    $badgeIcon = 'fas fa-handshake';
                                    break;
                            }
                            $badge_html = '<i class="' . $badgeIcon . '"></i> ' . ucfirst($opp['type_opportunity']);
                            CardView::render([
                                'badge' => $badge_html,
                                'badge_class' => $badgeClass,
                                'title' => $opp['titre'],
                                'text' => mb_substr($opp['description'] ?? '', 0, 150) . '...',
                                'link' => 'index.php?url=opportunity/detail/' . $opp['id_opportunity'],
                                'link_text' => 'Voir les détails',
                                'meta' => [
                                    [
                                        'icon' => 'far fa-calendar',
                                        'label' => 'Publié le ' . date('d/m/Y', strtotime($opp['date_creation']))
                                    ],
                                    [
                                        'icon' => 'fas fa-tag',
                                        'label' => $opp['domaine'] ?? 'Tous domaines'
                                    ],
                                    ...((!empty($opp['date_limite'])) ? [[
                                        'icon' => 'fas fa-clock',
                                        'label' => 'Limite: ' . date('d/m/Y', strtotime($opp['date_limite']))
                                    ]] : [])
                                ]
                            ]);
                            ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state" style="text-align: center; padding: 60px;">
                        <i class="fas fa-inbox" style="font-size: 4rem; opacity: 0.3; margin-bottom: 16px;"></i>
                        <h3>Aucune opportunité disponible</h3>
                        <p style="color: var(--muted);">Revenez plus tard pour découvrir de nouvelles opportunités</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        
        <?php
        FooterView::render($settings);
    }
    
    public static function renderDetail(array $data) {
        $settings = $data['settings'] ?? [];
        $opportunity = $data['opportunity'] ?? [];
        
        HeaderView::render($settings, $opportunity['titre'] ?? 'Opportunité');
        MenuView::render('opportunities');
        ?>
        
        <!-- Breadcrumb -->
        <section style="background: var(--bg); padding: 16px 0; border-bottom: 1px solid var(--border);">
            <div class="container">
                <div style="font-size: 0.9rem; color: var(--muted);">
                    <a href="index.php" class="link">Accueil</a>
                    <span> / </span>
                    <a href="index.php?url=opportunities" class="link">Opportunités</a>
                    <span> / </span>
                    <span><?= htmlspecialchars($opportunity['titre'] ?? '') ?></span>
                </div>
            </div>
        </section>
        
        <section class="section">
            <div class="container">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px;">
                    
                    <!-- Colonne principale -->
                    <div>
                        <div class="content-card" style="padding: 32px;">
                            <!-- En-tête -->
                            <div style="margin-bottom: 24px;">
                                <?php
                                $badgeIcon = '';
                                switch ($opportunity['type_opportunity']) {
                                    case 'stage': $badgeIcon = 'fas fa-briefcase'; break;
                                    case 'these': $badgeIcon = 'fas fa-graduation-cap'; break;
                                    case 'bourse': $badgeIcon = 'fas fa-award'; break;
                                    case 'collaboration': $badgeIcon = 'fas fa-handshake'; break;
                                }
                                ?>
                                <span class="badge primary">
                                    <i class="<?= $badgeIcon ?>"></i> 
                                    <?= ucfirst($opportunity['type_opportunity']) ?>
                                </span>
                                
                                <?php if (!empty($opportunity['domaine'])): ?>
                                    <span class="badge">
                                        <?= htmlspecialchars($opportunity['domaine']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <h1 style="margin: 0 0 24px;"><?= htmlspecialchars($opportunity['titre']) ?></h1>
                            
                            <!-- Description -->
                            <div style="margin-bottom: 32px;">
                                <h3 style="margin: 0 0 12px;">Description</h3>
                                <p style="white-space: pre-line; line-height: 1.8;">
                                    <?= htmlspecialchars($opportunity['description'] ?? 'Aucune description disponible') ?>
                                </p>
                            </div>
                            
                            <!-- Prérequis -->
                            <?php if (!empty($opportunity['prerequis'])): ?>
                                <div style="margin-bottom: 32px;">
                                    <h3 style="margin: 0 0 12px;">Prérequis</h3>
                                    <p style="white-space: pre-line; line-height: 1.8;">
                                        <?= htmlspecialchars($opportunity['prerequis']) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Avantages -->
                            <?php if (!empty($opportunity['avantages'])): ?>
                                <div style="margin-bottom: 32px;">
                                    <h3 style="margin: 0 0 12px;">Avantages</h3>
                                    <p style="white-space: pre-line; line-height: 1.8;">
                                        <?= htmlspecialchars($opportunity['avantages']) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Sidebar -->
                    <div>
                        <!-- Informations -->
                        <div class="content-card" style="padding: 24px; margin-bottom: 24px;">
                            <h3 style="margin: 0 0 20px; padding-bottom: 12px; border-bottom: 2px solid var(--border);">
                                Informations
                            </h3>
                            
                            <div style="display: flex; flex-direction: column; gap: 16px; font-size: 0.95rem;">
                                <div>
                                    <div style="color: var(--muted); margin-bottom: 4px;">Date de publication</div>
                                    <div style="font-weight: 600;">
                                        <?= date('d/m/Y', strtotime($opportunity['date_creation'])) ?>
                                    </div>
                                </div>
                                
                                <?php if (!empty($opportunity['date_limite'])): ?>
                                    <div>
                                        <div style="color: var(--muted); margin-bottom: 4px;">Date limite</div>
                                        <div style="font-weight: 600; color: var(--primary);">
                                            <?= date('d/m/Y', strtotime($opportunity['date_limite'])) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($opportunity['duree'])): ?>
                                    <div>
                                        <div style="color: var(--muted); margin-bottom: 4px;">Durée</div>
                                        <div style="font-weight: 600;">
                                            <?= htmlspecialchars($opportunity['duree']) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($opportunity['lieu'])): ?>
                                    <div>
                                        <div style="color: var(--muted); margin-bottom: 4px;">Lieu</div>
                                        <div style="font-weight: 600;">
                                            <?= htmlspecialchars($opportunity['lieu']) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Contact / Postuler -->
                        <?php if (!empty($opportunity['contact_email'])): ?>
                            <div class="content-card" style="background: linear-gradient(135deg, var(--primary), var(--nav)); padding: 24px; color: white;">
                                <h3 style="margin: 0 0 12px; color: white;">Postuler</h3>
                                <p style="margin: 0 0 16px; opacity: 0.9; font-size: 0.9rem;">
                                    Intéressé par cette opportunité ? Contactez-nous
                                </p>
                                <a href="mailto:<?= htmlspecialchars($opportunity['contact_email']) ?>" 
                                   class="btn" 
                                   style="background: white; color: var(--primary); width: 100%; justify-content: center; font-weight: 600;">
                                    <i class="fas fa-envelope"></i> Envoyer un email
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div style="margin-top: 24px;">
                            <a href="index.php?url=opportunities" class="btn ghost" style="width: 100%; justify-content: center;">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <?php
        FooterView::render($settings);
    }
}
?>