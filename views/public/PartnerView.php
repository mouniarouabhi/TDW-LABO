<?php
// views/public/PartnerView.php

class PartnerView {
    
    /**
     * Liste de tous les partenaires avec filtres
     */
    public static function renderList(array $data) {
        $settings = $data['settings'] ?? [];
        $partners = $data['partners'] ?? [];
        $types = $data['types'] ?? [];
        $pays = $data['pays'] ?? [];
        $filterType = $data['filterType'] ?? null;
        $filterPays = $data['filterPays'] ?? null;
        $search = $data['search'] ?? '';
        
        HeaderView::render($settings, 'Partenaires');
        MenuView::render('partners');
        ?>
        
        <!-- Hero Section -->
        <section class="hero" style="background: linear-gradient(135deg, var(--primary), var(--nav)); padding: 60px 0; color: white;">
            <div class="container" style="text-align: center;">
                <h1 style="color: white;">Nos Partenaires</h1>
                <p style="color: rgba(255,255,255,0.9); max-width: 700px; margin: 0 auto;">
                    Partenaires institutionnels et industriels du laboratoire
                </p>
            </div>
        </section>
        
        <!-- Filtres et Recherche -->
        <section class="section" style="padding-top: 40px;">
            <div class="container">
                <div class="content-card" style="padding: 24px; margin-bottom: 32px;">
                    <h3 style="margin: 0 0 16px;"><i class="fas fa-filter"></i> Filtrer les partenaires</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 16px;">
                        <!-- Recherche -->
                        <div>
                            <input type="text" 
                                   id="searchInput"
                                   value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="Rechercher un partenaire..."
                                   style="width: 100%; padding: 10px; border: 2px solid var(--border); border-radius: 8px;">
                        </div>
                        
                        <!-- Filtre Type -->
                        <div>
                            <select id="filterType"
                                    style="width: 100%; padding: 10px; border: 2px solid var(--border); border-radius: 8px;">
                                <option value="">Tous les types</option>
                                <?php foreach ($types as $type): ?>
                                    <option value="<?= htmlspecialchars($type) ?>" 
                                            <?= $filterType === $type ? 'selected' : '' ?>>
                                        <?= ucfirst(htmlspecialchars($type)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Filtre Pays -->
                        <div>
                            <select id="filterPays"
                                    style="width: 100%; padding: 10px; border: 2px solid var(--border); border-radius: 8px;">
                                <option value="">Tous les pays</option>
                                <?php foreach ($pays as $p): ?>
                                    <option value="<?= htmlspecialchars($p) ?>" 
                                            <?= $filterPays === $p ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <button type="button" id="resetFiltersBtn" class="btn ghost" style="padding: 10px 24px;">
                            <i class="fas fa-times"></i> Réinitialiser
                        </button>
                    </div>
                </div>
                
                <!-- Nombre de résultats -->
                <h2 style="margin-bottom: 24px;" id="resultsCount">
                    <?= count($partners) ?> partenaire<?= count($partners) > 1 ? 's' : '' ?> trouvé<?= count($partners) > 1 ? 's' : '' ?>
                </h2>
                
                <!-- Grille de partenaires -->
                <?php if (!empty($partners)): ?>
                    <div class="cards-grid" id="partnersGrid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
                        <?php foreach ($partners as $partner): ?>
                            <div class="partner-card content-card" 
                                 style="padding: 24px; display: flex; flex-direction: column; text-align: center;"
                                 data-type="<?= htmlspecialchars(strtolower($partner['type_partenaire'] ?? '')) ?>"
                                 data-pays="<?= htmlspecialchars(strtolower($partner['pays'] ?? '')) ?>"
                                 data-nom="<?= htmlspecialchars(strtolower($partner['nom_partenaire'] ?? '')) ?>"
                                 data-description="<?= htmlspecialchars(strtolower($partner['description'] ?? '')) ?>">
                                
                                <!-- Logo -->
                                <div style="margin-bottom: 16px;">
                                    <img src="<?= !empty($partner['logo']) 
                                            ? 'assets/images/partners/' . htmlspecialchars($partner['logo'])
                                            : 'assets/images/partners/default.jpg' ?>" 
                                         alt="<?= htmlspecialchars($partner['nom_partenaire']) ?>"
                                         style="max-width: 100%; height: 120px; object-fit: contain; border-radius: 8px;">
                                </div>
                                
                                <!-- Badge Type -->
                                <div style="margin-bottom: 12px;">
                                    <?php
                                    $badgeClass = '';
                                    switch ($partner['type_partenaire'] ?? '') {
                                        case 'institutionnel':
                                            $badgeClass = 'primary';
                                            break;
                                        case 'industriel':
                                            $badgeClass = 'success';
                                            break;
                                        case 'academique':
                                            $badgeClass = 'warning';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= ucfirst(htmlspecialchars($partner['type_partenaire'] ?? 'Partenaire')) ?>
                                    </span>
                                </div>
                                
                                <!-- Nom -->
                                <h3 style="margin: 0 0 12px; flex-grow: 0;">
                                    <?= htmlspecialchars($partner['nom_partenaire']) ?>
                                </h3>
                                
                                <!-- Pays -->
                                <?php if (!empty($partner['pays'])): ?>
                                    <div style="color: var(--muted); margin-bottom: 12px; font-size: 0.9rem;">
                                        <i class="fas fa-globe"></i> <?= htmlspecialchars($partner['pays']) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Description -->
                                <?php if (!empty($partner['description'])): ?>
                                    <p style="color: var(--text); line-height: 1.6; flex-grow: 1; margin-bottom: 16px; text-align: left;">
                                        <?= htmlspecialchars(mb_substr($partner['description'], 0, 120)) ?>...
                                    </p>
                                <?php endif; ?>
                                
                                <!-- Actions -->
                                <div style="display: flex; gap: 8px; margin-top: auto;">
                                    <?php if (!empty($partner['site_web'])): ?>
                                        <a href="<?= htmlspecialchars($partner['site_web']) ?>" 
                                           target="_blank" 
                                           rel="noopener noreferrer"
                                           class="btn primary" 
                                           style="flex: 1; justify-content: center;">
                                            <i class="fas fa-external-link-alt"></i> Site web
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="index.php?url=partner/detail/<?= $partner['id_partner'] ?>" 
                                       class="btn ghost"
                                       style="flex: 1; justify-content: center;">
                                        <i class="fas fa-info-circle"></i> Détails
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div id="noResults" style="display: none; text-align: center; padding: 60px;">
                        <i class="fas fa-handshake" style="font-size: 4rem; opacity: 0.3; margin-bottom: 16px;"></i>
                        <h3>Aucun partenaire trouvé</h3>
                        <p style="color: var(--muted);">
                            Essayez de modifier vos filtres de recherche
                        </p>
                    </div>
                <?php else: ?>
                    <div class="empty-state" style="text-align: center; padding: 60px;">
                        <i class="fas fa-handshake" style="font-size: 4rem; opacity: 0.3; margin-bottom: 16px;"></i>
                        <h3>Aucun partenaire trouvé</h3>
                        <p style="color: var(--muted);">
                            Aucun partenaire enregistré pour le moment
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        
        <!-- JavaScript pour filtrage en direct -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const filterType = document.getElementById('filterType');
            const filterPays = document.getElementById('filterPays');
            const resetBtn = document.getElementById('resetFiltersBtn');
            const partnersGrid = document.getElementById('partnersGrid');
            const resultsCount = document.getElementById('resultsCount');
            const noResults = document.getElementById('noResults');
            
            let allCards = [];
            
            // Initialiser les cartes
            if (partnersGrid) {
                allCards = Array.from(partnersGrid.querySelectorAll('.partner-card'));
                console.log('Total partenaires chargés:', allCards.length);
            }
            
            // Fonction de filtrage
            function applyFilters() {
                if (!partnersGrid || allCards.length === 0) {
                    console.log('Aucune carte à filtrer');
                    return;
                }
                
                const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
                const typeValue = filterType ? filterType.value.toLowerCase() : '';
                const paysValue = filterPays ? filterPays.value.toLowerCase() : '';
                
                console.log('Application des filtres:', {
                    recherche: searchTerm,
                    type: typeValue,
                    pays: paysValue,
                    totalCards: allCards.length
                });
                
                // Filtrer les cartes
                let visibleCards = allCards.filter(card => {
                    // Filtrage par recherche textuelle
                    if (searchTerm) {
                        const nom = (card.dataset.nom || '').toLowerCase();
                        const description = (card.dataset.description || '').toLowerCase();
                        
                        const matchSearch = nom.includes(searchTerm) || description.includes(searchTerm);
                        if (!matchSearch) {
                            return false;
                        }
                    }
                    
                    // Filtrage par type
                    if (typeValue) {
                        const cardType = (card.dataset.type || '').toLowerCase();
                        if (cardType !== typeValue) {
                            return false;
                        }
                    }
                    
                    // Filtrage par pays
                    if (paysValue) {
                        const cardPays = (card.dataset.pays || '').toLowerCase();
                        if (cardPays !== paysValue) {
                            return false;
                        }
                    }
                    
                    return true;
                });
                
                console.log('Cartes visibles après filtrage:', visibleCards.length);
                
                // Masquer toutes les cartes
                allCards.forEach(card => {
                    card.style.display = 'none';
                });
                
                // Afficher les cartes filtrées
                if (visibleCards.length > 0) {
                    visibleCards.forEach(card => {
                        card.style.display = 'flex';
                    });
                    
                    if (noResults) noResults.style.display = 'none';
                    if (partnersGrid) partnersGrid.style.display = 'grid';
                } else {
                    if (partnersGrid) partnersGrid.style.display = 'none';
                    if (noResults) noResults.style.display = 'block';
                }
                
                // Mettre à jour le compteur
                updateResultsCount(visibleCards.length);
            }
            
            // Mettre à jour le compteur de résultats
            function updateResultsCount(count) {
                if (resultsCount) {
                    const plural = count > 1 ? 's' : '';
                    resultsCount.textContent = count + ' partenaire' + plural + ' trouvé' + plural;
                }
            }
            
            // Réinitialiser les filtres
            function resetFilters() {
                console.log('Réinitialisation des filtres');
                if (searchInput) searchInput.value = '';
                if (filterType) filterType.value = '';
                if (filterPays) filterPays.value = '';
                
                applyFilters();
            }
            
            // Événements
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    console.log('Recherche modifiée:', this.value);
                    applyFilters();
                });
            }
            
            if (filterType) {
                filterType.addEventListener('change', function() {
                    console.log('Type sélectionné:', this.value);
                    applyFilters();
                });
            }
            
            if (filterPays) {
                filterPays.addEventListener('change', function() {
                    console.log('Pays sélectionné:', this.value);
                    applyFilters();
                });
            }
            
            if (resetBtn) {
                resetBtn.addEventListener('click', resetFilters);
            }
            
            // Appliquer les filtres initiaux si des paramètres URL sont présents
            const hasInitialFilters = <?= json_encode(!empty($search) || !empty($filterType) || !empty($filterPays)) ?>;
            if (hasInitialFilters) {
                console.log('Application des filtres initiaux');
                applyFilters();
            }
            
            console.log('Script de filtrage des partenaires initialisé avec succès');
        });
        </script>
        
        <?php
        FooterView::render($settings);
    }
    
    /**
     * Page détail d'un partenaire
     */
    public static function renderDetail(array $data) {
        $settings = $data['settings'] ?? [];
        $partner = $data['partner'] ?? [];
        
        HeaderView::render($settings, $partner['nom_partenaire'] ?? 'Partenaire');
        MenuView::render('partners');
        ?>
        
        <!-- Breadcrumb -->
        <section style="background: var(--bg); padding: 16px 0; border-bottom: 1px solid var(--border);">
            <div class="container">
                <div style="font-size: 0.9rem; color: var(--muted);">
                    <a href="index.php" class="link">Accueil</a>
                    <span> / </span>
                    <a href="index.php?url=partner" class="link">Partenaires</a>
                    <span> / </span>
                    <span><?= htmlspecialchars($partner['nom_partenaire'] ?? '') ?></span>
                </div>
            </div>
        </section>
        
        <section class="section">
            <div class="container">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px;">
                    
                    <!-- Colonne principale -->
                    <div>
                        <div class="content-card" style="padding: 32px;">
                            <!-- Logo et en-tête -->
                            <div style="display: flex; gap: 24px; align-items: start; margin-bottom: 32px;">
                                <div style="flex-shrink: 0;">
                                    <img src="<?= !empty($partner['logo']) 
                                            ? 'assets/images/partners/' . htmlspecialchars($partner['logo'])
                                            : 'assets/images/partners/default.jpg' ?>" 
                                         alt="<?= htmlspecialchars($partner['nom_partenaire'] ?? '') ?>"
                                         style="width: 150px; height: 150px; object-fit: contain; border-radius: 8px; border: 2px solid var(--border); padding: 12px;">
                                </div>
                                
                                <div style="flex: 1;">
                                    <?php
                                    $badgeClass = '';
                                    switch ($partner['type_partenaire'] ?? '') {
                                        case 'institutionnel':
                                            $badgeClass = 'primary';
                                            break;
                                        case 'industriel':
                                            $badgeClass = 'success';
                                            break;
                                        case 'academique':
                                            $badgeClass = 'warning';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?= $badgeClass ?>" style="margin-bottom: 12px;">
                                        <?= ucfirst(htmlspecialchars($partner['type_partenaire'] ?? 'Partenaire')) ?>
                                    </span>
                                    
                                    <h1 style="margin: 8px 0 16px;">
                                        <?= htmlspecialchars($partner['nom_partenaire'] ?? 'Sans nom') ?>
                                    </h1>
                                    
                                    <?php if (!empty($partner['pays'])): ?>
                                        <div style="color: var(--muted); font-size: 1.1rem;">
                                            <i class="fas fa-map-marker-alt"></i> 
                                            <?= htmlspecialchars($partner['pays']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <?php if (!empty($partner['description'])): ?>
                                <div style="margin-bottom: 32px;">
                                    <h3 style="margin: 0 0 12px;">À propos</h3>
                                    <p style="line-height: 1.8; white-space: pre-line;">
                                        <?= htmlspecialchars($partner['description']) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Domaines de collaboration -->
                            <?php if (!empty($partner['domaines_collaboration'])): ?>
                                <div style="margin-bottom: 32px;">
                                    <h3 style="margin: 0 0 12px;">Domaines de collaboration</h3>
                                    <p style="line-height: 1.8; white-space: pre-line;">
                                        <?= htmlspecialchars($partner['domaines_collaboration']) ?>
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
                                <?php if (!empty($partner['date_debut_partenariat'])): ?>
                                    <div>
                                        <div style="color: var(--muted); margin-bottom: 4px;">Partenariat depuis</div>
                                        <div style="font-weight: 600;">
                                            <?= date('Y', strtotime($partner['date_debut_partenariat'])) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div>
                                    <div style="color: var(--muted); margin-bottom: 4px;">Type</div>
                                    <div style="font-weight: 600;">
                                        <?= ucfirst(htmlspecialchars($partner['type_partenaire'] ?? 'Non spécifié')) ?>
                                    </div>
                                </div>
                                
                                <?php if (!empty($partner['secteur'])): ?>
                                    <div>
                                        <div style="color: var(--muted); margin-bottom: 4px;">Secteur</div>
                                        <div style="font-weight: 600;">
                                            <?= htmlspecialchars($partner['secteur']) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Contact / Site web -->
                        <?php if (!empty($partner['site_web']) || !empty($partner['email_contact'])): ?>
                            <div class="content-card" style="background: linear-gradient(135deg, var(--primary), var(--nav)); padding: 24px; color: white; margin-bottom: 24px;">
                                <h3 style="margin: 0 0 16px; color: white;">Contact</h3>
                                
                                <?php if (!empty($partner['site_web'])): ?>
                                    <a href="<?= htmlspecialchars($partner['site_web']) ?>" 
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="btn" 
                                       style="background: white; color: var(--primary); width: 100%; justify-content: center; font-weight: 600; margin-bottom: 12px;">
                                        <i class="fas fa-external-link-alt"></i> Visiter le site
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($partner['email_contact'])): ?>
                                    <a href="mailto:<?= htmlspecialchars($partner['email_contact']) ?>" 
                                       class="btn" 
                                       style="background: white; color: var(--primary); width: 100%; justify-content: center; font-weight: 600;">
                                        <i class="fas fa-envelope"></i> Envoyer un email
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div style="margin-top: 24px;">
                            <a href="index.php?url=partners" class="btn ghost" style="width: 100%; justify-content: center;">
                                <i class="fas fa-arrow-left"></i> Retour aux partenaires
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