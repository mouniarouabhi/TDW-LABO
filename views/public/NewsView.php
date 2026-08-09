<?php
// views/public/NewsView.php

class NewsView {

    public static function renderList(array $data) {
        extract($data);

        HeaderView::render($settings, 'Actualités');
        MenuView::render('news');
        ?>

        <!-- Hero -->
        <section class="hero" style="background: linear-gradient(135deg, var(--primary), var(--nav)); padding:60px 0;">
            <div class="container" style="text-align:center; color:white;">
                <h1>Actualités</h1>
                <p>Publications, projets et activités scientifiques</p>
            </div>
        </section>

        <!-- Filtres -->
        <section class="section">
            <div class="container content-card" style="padding:24px; margin-bottom: 24px;">
                <h3 style="margin: 0 0 16px;"><i class="fas fa-search"></i> Recherche et filtres</h3>
                
                <div style="display:grid; grid-template-columns:1fr 1fr auto; gap:16px; margin-bottom: 16px;">
                    <input type="text" 
                           id="searchInput"
                           placeholder="Rechercher une actualité..."
                           value="<?= htmlspecialchars($search) ?>"
                           style="padding: 10px; border: 2px solid var(--border); border-radius: 8px;">

                    <select id="filterType" style="padding: 10px; border: 2px solid var(--border); border-radius: 8px;">
                        <option value="">Tous les types</option>
                        <?php foreach ($types as $t): ?>
                            <option value="<?= htmlspecialchars($t) ?>" <?= $filterType === $t ? 'selected' : '' ?>>
                                <?= ucfirst(htmlspecialchars($t)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="button" id="resetFiltersBtn" class="btn ghost">
                        <i class="fas fa-times"></i> Réinitialiser
                    </button>
                </div>
            </div>

            <div class="container" style="margin: 24px;">
                <h2 id="resultsCount">
                    <?= $totalNews ?> actualité<?= $totalNews > 1 ? 's' : '' ?>
                </h2>
            </div>

            <!-- News grid -->
            <div class="container cards-grid" id="newsGrid">
                <?php foreach ($news as $n): ?>
                    <div class="news-card"
                         data-type="<?= htmlspecialchars($n['type_news'] ?? '') ?>"
                         data-titre="<?= htmlspecialchars(strtolower($n['titre'] ?? '')) ?>"
                         data-contenu="<?= htmlspecialchars(strtolower(strip_tags($n['contenu'] ?? ''))) ?>"
                         data-date="<?= htmlspecialchars($n['date_publication'] ?? '') ?>">
                        <?php
                        CardView::render([
                            'image' => 'assets/images/news/' . ($n['image'] ?? 'default.jpg'),
                            'badge' => !empty($n['type_news']) ? ucfirst($n['type_news']) : 'Actualité',
                            'title' => $n['titre'] ?? 'Sans titre',
                            'text'  => mb_substr(strip_tags($n['contenu'] ?? ''), 0, 140) . '...',
                            'link'  => "index.php?url=news/detail/{$n['id_news']}",
                            'meta'  => [[
                                'icon' => 'far fa-calendar',
                                'label' => !empty($n['date_publication']) ? date('d/m/Y', strtotime($n['date_publication'])) : 'Date inconnue'
                            ]]
                        ]);
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <p id="noResults" style="display: none; color: var(--muted); text-align: center; padding: 40px;">
                Aucune actualité ne correspond à vos critères de recherche.
            </p>

            <!-- Pagination (hidden when filtering) -->
            <?php if ($totalPages > 1): ?>
                <div class="container pagination" id="paginationContainer">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a class="pagination-number <?= $i === $currentPage ? 'active' : '' ?>"
                           href="index.php?url=news&page=<?= $i ?><?= $filterType ? '&type=' . urlencode($filterType) : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- JavaScript pour filtrage en direct -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const filterType = document.getElementById('filterType');
            const resetBtn = document.getElementById('resetFiltersBtn');
            const newsGrid = document.getElementById('newsGrid');
            const resultsCount = document.getElementById('resultsCount');
            const noResults = document.getElementById('noResults');
            const paginationContainer = document.getElementById('paginationContainer');
            
            let allCards = [];
            
            // Initialiser les cartes
            if (newsGrid) {
                allCards = Array.from(newsGrid.querySelectorAll('.news-card'));
                console.log('Total actualités:', allCards.length);
            }
            
            // Fonction de filtrage
            function applyFilters() {
                if (!newsGrid || allCards.length === 0) return;
                
                const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
                const typeValue = filterType ? filterType.value : '';
                
                console.log('Filtering with:', {searchTerm, typeValue});
                
                // Filtrer les cartes
                let visibleCards = allCards.filter(card => {
                    // Filtrage par recherche textuelle
                    if (searchTerm) {
                        const titre = card.dataset.titre || '';
                        const contenu = card.dataset.contenu || '';
                        
                        if (!titre.includes(searchTerm) && !contenu.includes(searchTerm)) {
                            return false;
                        }
                    }
                    
                    // Filtrage par type
                    if (typeValue && card.dataset.type !== typeValue) {
                        return false;
                    }
                    
                    return true;
                });
                
                console.log('Visible cards:', visibleCards.length);
                
                // Masquer toutes les cartes
                allCards.forEach(card => {
                    card.style.display = 'none';
                });
                
                // Afficher les cartes filtrées
                if (visibleCards.length > 0) {
                    visibleCards.forEach(card => {
                        card.style.display = 'block';
                    });
                    
                    if (noResults) noResults.style.display = 'none';
                    newsGrid.style.display = 'grid';
                } else {
                    newsGrid.style.display = 'none';
                    if (noResults) noResults.style.display = 'block';
                }
                
                // Masquer la pagination quand on filtre
                if (paginationContainer) {
                    if (searchTerm || typeValue) {
                        paginationContainer.style.display = 'none';
                    } else {
                        paginationContainer.style.display = 'flex';
                    }
                }
                
                // Mettre à jour le compteur
                updateResultsCount(visibleCards.length);
            }
            
            // Mettre à jour le compteur de résultats
            function updateResultsCount(count) {
                if (resultsCount) {
                    const plural = count > 1 ? 's' : '';
                    resultsCount.textContent = count + ' actualité' + plural;
                }
            }
            
            // Réinitialiser les filtres
            function resetFilters() {
                if (searchInput) searchInput.value = '';
                if (filterType) filterType.value = '';
                
                applyFilters();
            }
            
            // Événements
            if (searchInput) {
                searchInput.addEventListener('input', applyFilters);
            }
            
            if (filterType) {
                filterType.addEventListener('change', applyFilters);
            }
            
            if (resetBtn) {
                resetBtn.addEventListener('click', resetFilters);
            }
            
            // Appliquer les filtres initiaux si des paramètres URL sont présents
            const hasInitialFilters = <?= json_encode(!empty($search) || !empty($filterType)) ?>;
            if (hasInitialFilters) {
                applyFilters();
            }
        });
        </script>

        <?php FooterView::render($settings);
    }

    public static function renderDetail(array $data)
    {
        $settings = $data['settings'] ?? [];
        $news     = $data['news'] ?? [];

        HeaderView::render($settings, $news['titre'] ?? 'Actualité');
        MenuView::render('news');
        ?>

        <section class="section">
            <div class="container">

                <!-- Section header -->
                <div class="section-header">
                    <div>
                        <p class="eyebrow">Actualité</p>
                        <h1><?= htmlspecialchars($news['titre']) ?></h1>
                        <p>
                            <?= date('d/m/Y', strtotime($news['date_publication'])) ?>
                            <?php if (!empty($news['type_news'])): ?>
                                · <?= ucfirst(htmlspecialchars($news['type_news'])) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <!-- Image -->
                <?php if (!empty($news['image'])): ?>
                    <div style="margin-bottom:1.5rem;">
                        <img
                            src="assets/images/news/<?= htmlspecialchars($news['image']) ?>"
                            alt="<?= htmlspecialchars($news['titre']) ?>"
                            style="max-width:100%; border-radius:8px;">
                    </div>
                <?php endif; ?>

                <!-- Content -->
                <div style="max-width:780px; line-height: 1.7;">
                    <?= nl2br(htmlspecialchars($news['contenu'])) ?>
                </div>

                <!-- Back -->
                <div style="margin-top:2rem;">
                    <a href="index.php?url=news" class="link">
                        ← Retour aux actualités
                    </a>
                </div>

            </div>
        </section>

        <?php
        FooterView::render($settings);
    }
}
?>