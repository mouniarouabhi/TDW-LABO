<?php
// views/public/PublicationView.php
// Point 6 du PDF: Base documentaire avec DOI et téléchargement
// Support du mode "mes publications" avec ?user=me et boutons d'action

class PublicationView {
    
    /**
     * Base documentaire avec filtres avancés
     * ✅ Titre et breadcrumb conditionnels + boutons d'action
     */
    public static function renderBase(array $data) {
        $settings = $data['settings'] ?? [];
        $publications = $data['publications'] ?? [];
        $annees = $data['annees'] ?? [];
        $types = $data['types'] ?? [];
        $domaines = $data['domaines'] ?? [];
        $auteurs = $data['auteurs'] ?? [];
        $filterAnnee = $data['filterAnnee'] ?? null;
        $filterAuteur = $data['filterAuteur'] ?? null;
        $filterType = $data['filterType'] ?? null;
        $filterDomaine = $data['filterDomaine'] ?? null;
        $search = $data['search'] ?? '';
        $tri = $data['tri'] ?? 'recent';
        $filterByUser = $data['filterByUser'] ?? false;
        $isAuthenticated = $data['isAuthenticated'] ?? false;
        $currentUserId = $data['currentUserId'] ?? null;
        
        HeaderView::render($settings, $filterByUser ? 'Mes Publications' : 'Publications');
        MenuView::render('publications');
        ?>
        
        <!-- Hero Section -->
        <section class="hero" style="background: linear-gradient(135deg, var(--primary), var(--nav)); padding: 60px 0; color: white;">
            <div class="container" style="text-align: center;">
                <h1 style="color: white;">
                    <?= $filterByUser ? 'Mes Publications' : 'Base Documentaire' ?>
                </h1>
                <p style="color: rgba(255,255,255,0.9);">
                    <?= $filterByUser 
                        ? 'Vos publications scientifiques' 
                        : 'Articles, rapports, thèses, communications, posters' ?>
                </p>
            </div>
        </section>
        
        <?php if ($filterByUser): ?>
        <section style="background: var(--bg); padding: 16px 0; border-bottom: 1px solid var(--border);">
            <div class="container">
                <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                    <a href="index.php?url=dashboard" class="link">
                        <i class="fas fa-arrow-left"></i> Retour au dashboard
                    </a>
                    <span style="color: var(--muted);">|</span>
                    <a href="index.php?url=publications" class="link">
                        Voir toutes les publications du laboratoire
                    </a>
                    <span style="color: var(--muted);">|</span>
                    <a href="index.php?url=userpublication" class="btn primary" style="padding: 8px 16px;">
                        <i class="fas fa-cog"></i> Gérer mes publications
                    </a>
                </div>
            </div>
        </section>
        <?php endif; ?>
        
        <section class="section">
            <div class="container">
                <!-- Filtres avancés -->
                <div class="content-card" style="padding: 24px; margin-bottom: 32px;">
                    <h3 style="margin: 0 0 16px;"><i class="fas fa-search"></i> Recherche et filtres</h3>
                    
                    <div id="filterContainer">
                        <!-- Recherche textuelle -->
                        <div style="margin-bottom: 16px;">
                            <input type="text" 
                                   id="searchInput"
                                   value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="Rechercher un titre, résumé, auteur..."
                                   style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; font-size: 1rem;">
                        </div>
                        
                        <!-- Filtres -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 16px;">
                            <!-- Année -->
                            <div>
                                <select id="filterAnnee" style="width: 100%; padding: 10px; border: 2px solid var(--border); border-radius: 8px;">
                                    <option value="">Toutes les années</option>
                                    <?php foreach ($annees as $annee): ?>
                                        <option value="<?= $annee ?>" <?= $filterAnnee == $annee ? 'selected' : '' ?>>
                                            <?= $annee ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Auteur (masqué en mode "mes publications") -->
                            <?php if (!$filterByUser): ?>
                            <div>
                                <select id="filterAuteur" style="width: 100%; padding: 10px; border: 2px solid var(--border); border-radius: 8px;">
                                    <option value="">Tous les auteurs</option>
                                    <?php foreach ($auteurs as $aut): ?>
                                        <option value="<?= $aut['id_user'] ?>" 
                                                data-name="<?= htmlspecialchars($aut['nom'] . ' ' . $aut['prenom']) ?>"
                                                <?= $filterAuteur == $aut['id_user'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($aut['nom'] . ' ' . $aut['prenom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Type -->
                            <div>
                                <select id="filterType" style="width: 100%; padding: 10px; border: 2px solid var(--border); border-radius: 8px;">
                                    <option value="">Tous les types</option>
                                    <?php foreach ($types as $type): ?>
                                        <option value="<?= htmlspecialchars($type) ?>" <?= $filterType === $type ? 'selected' : '' ?>>
                                            <?= ucfirst(htmlspecialchars($type)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Domaine -->
                            <?php if (!empty($domaines)): ?>
                            <div>
                                <select id="filterDomaine" style="width: 100%; padding: 10px; border: 2px solid var(--border); border-radius: 8px;">
                                    <option value="">Tous les domaines</option>
                                    <?php foreach ($domaines as $domaine): ?>
                                        <option value="<?= htmlspecialchars($domaine) ?>" <?= $filterDomaine === $domaine ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($domaine) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Tri -->
                            <div>
                                <select id="filterTri" style="width: 100%; padding: 10px; border: 2px solid var(--border); border-radius: 8px;">
                                    <option value="recent" <?= $tri === 'recent' ? 'selected' : '' ?>>Plus récentes</option>
                                    <option value="ancien" <?= $tri === 'ancien' ? 'selected' : '' ?>>Plus anciennes</option>
                                    <option value="titre" <?= $tri === 'titre' ? 'selected' : '' ?>>Par titre (A-Z)</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Bouton réinitialiser -->
                        <div style="display: flex; justify-content: flex-end;">
                            <button type="button" id="resetFiltersBtn" class="btn ghost">
                                <i class="fas fa-times"></i> Réinitialiser les filtres
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Nombre de résultats -->
                <h2 style="margin-bottom: 24px;" id="resultsCount">
                    <?= count($publications) ?> publication<?= count($publications) > 1 ? 's' : '' ?> trouvée<?= count($publications) > 1 ? 's' : '' ?>
                </h2>
                
                <!-- Publications -->
                <?php if ($publications): ?>
                    <div class="cards-grid" id="publicationsGrid" style="grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:24px">
                        <?php foreach ($publications as $pub): ?>
                            <div class="publication-card" 
                                 data-annee="<?= htmlspecialchars($pub['annee_publication'] ?? '') ?>"
                                 data-type="<?= htmlspecialchars($pub['type_publication'] ?? '') ?>"
                                 data-domaine="<?= htmlspecialchars($pub['domaine'] ?? '') ?>"
                                 data-titre="<?= htmlspecialchars(strtolower($pub['titre'] ?? '')) ?>"
                                 data-resume="<?= htmlspecialchars(strtolower($pub['resume'] ?? '')) ?>"
                                 data-auteurs="<?= htmlspecialchars(strtolower($pub['auteurs'] ?? '')) ?>">
                                <?php
                                CardView::render([
                                    'badge' => $pub['type_publication'] ?? '',
                                    'title' => $pub['titre'],
                                    'text'  => mb_substr($pub['resume'] ?? '', 0, 120) . '...',
                                    'meta' => [
                                        ['icon'=>'far fa-calendar','label'=>$pub['annee_publication'] ?? '—'],
                                        ['icon'=>'fas fa-hashtag','label'=>$pub['doi'] ?? '—']
                                    ],
                                    'link' => 'index.php?url=publication/detail/'.$pub['id_publication']
                                ]);
                                ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <p id="noResults" style="display: none; color: var(--muted); text-align: center; padding: 40px;">
                        Aucune publication ne correspond à vos critères de recherche.
                    </p>
                <?php else: ?>
                    <p style="color:var(--muted); text-align: center; padding: 40px;">Aucune publication disponible</p>
                <?php endif; ?>
            </div>
        </section>
        
        <!-- JavaScript pour filtrage en direct -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const filterAnnee = document.getElementById('filterAnnee');
            const filterAuteur = document.getElementById('filterAuteur');
            const filterType = document.getElementById('filterType');
            const filterDomaine = document.getElementById('filterDomaine');
            const filterTri = document.getElementById('filterTri');
            const resetBtn = document.getElementById('resetFiltersBtn');
            const publicationsGrid = document.getElementById('publicationsGrid');
            const resultsCount = document.getElementById('resultsCount');
            const noResults = document.getElementById('noResults');
            
            let allCards = [];
            
            // Initialiser les cartes
            if (publicationsGrid) {
                allCards = Array.from(publicationsGrid.querySelectorAll('.publication-card'));
                console.log('Total publications:', allCards.length);
            }
            
            // Fonction de filtrage
            function applyFilters() {
                if (!publicationsGrid || allCards.length === 0) return;
                
                const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
                const anneeValue = filterAnnee ? filterAnnee.value : '';
                const auteurValue = filterAuteur ? filterAuteur.value : '';
                const typeValue = filterType ? filterType.value : '';
                const domaineValue = filterDomaine ? filterDomaine.value : '';
                const triValue = filterTri ? filterTri.value : 'recent';
                
                console.log('Filtering with:', {searchTerm, anneeValue, auteurValue, typeValue, domaineValue, triValue});
                
                // Filtrer les cartes
                let visibleCards = allCards.filter(card => {
                    // Filtrage par recherche textuelle
                    if (searchTerm) {
                        const titre = card.dataset.titre || '';
                        const resume = card.dataset.resume || '';
                        const auteurs = card.dataset.auteurs || '';
                        
                        if (!titre.includes(searchTerm) && 
                            !resume.includes(searchTerm) && 
                            !auteurs.includes(searchTerm)) {
                            return false;
                        }
                    }
                    
                    // Filtrage par année
                    if (anneeValue && card.dataset.annee !== anneeValue) {
                        return false;
                    }
                    
                    // Filtrage par auteur (rechercher dans les noms d'auteurs)
                    if (auteurValue && filterAuteur) {
                        const selectedOption = filterAuteur.options[filterAuteur.selectedIndex];
                        const auteurName = selectedOption.dataset.name ? selectedOption.dataset.name.toLowerCase() : '';
                        const cardAuteurs = card.dataset.auteurs || '';
                        
                        if (!cardAuteurs.includes(auteurName)) {
                            return false;
                        }
                    }
                    
                    // Filtrage par type
                    if (typeValue && card.dataset.type !== typeValue) {
                        return false;
                    }
                    
                    // Filtrage par domaine
                    if (domaineValue) {
                        const cardDomaine = (card.dataset.domaine || '').toLowerCase();
                        if (!cardDomaine.includes(domaineValue.toLowerCase())) {
                            return false;
                        }
                    }
                    
                    return true;
                });
                
                console.log('Visible cards:', visibleCards.length);
                
                // Tri
                visibleCards.sort((a, b) => {
                    switch (triValue) {
                        case 'ancien':
                            return parseInt(a.dataset.annee || '0') - parseInt(b.dataset.annee || '0');
                        case 'titre':
                            return (a.dataset.titre || '').localeCompare(b.dataset.titre || '');
                        case 'recent':
                        default:
                            return parseInt(b.dataset.annee || '0') - parseInt(a.dataset.annee || '0');
                    }
                });
                
                // Masquer toutes les cartes
                allCards.forEach(card => {
                    card.style.display = 'none';
                });
                
                // Afficher et réorganiser les cartes visibles
                if (visibleCards.length > 0) {
                    visibleCards.forEach(card => {
                        card.style.display = 'block';
                        publicationsGrid.appendChild(card);
                    });
                    
                    if (noResults) noResults.style.display = 'none';
                    publicationsGrid.style.display = 'grid';
                } else {
                    publicationsGrid.style.display = 'none';
                    if (noResults) noResults.style.display = 'block';
                }
                
                // Mettre à jour le compteur
                updateResultsCount(visibleCards.length);
            }
            
            // Mettre à jour le compteur de résultats
            function updateResultsCount(count) {
                if (resultsCount) {
                    const plural = count > 1 ? 's' : '';
                    const femininePlural = count > 1 ? 's' : '';
                    resultsCount.textContent = count + ' publication' + plural + ' trouvée' + femininePlural;
                }
            }
            
            // Réinitialiser les filtres
            function resetFilters() {
                if (searchInput) searchInput.value = '';
                if (filterAnnee) filterAnnee.value = '';
                if (filterAuteur) filterAuteur.value = '';
                if (filterType) filterType.value = '';
                if (filterDomaine) filterDomaine.value = '';
                if (filterTri) filterTri.value = 'recent';
                
                applyFilters();
            }
            
            // Événements
            if (searchInput) {
                searchInput.addEventListener('input', applyFilters);
            }
            
            if (filterAnnee) {
                filterAnnee.addEventListener('change', applyFilters);
            }
            
            if (filterAuteur) {
                filterAuteur.addEventListener('change', applyFilters);
            }
            
            if (filterType) {
                filterType.addEventListener('change', applyFilters);
            }
            
            if (filterDomaine) {
                filterDomaine.addEventListener('change', applyFilters);
            }
            
            if (filterTri) {
                filterTri.addEventListener('change', applyFilters);
            }
            
            if (resetBtn) {
                resetBtn.addEventListener('click', resetFilters);
            }
            
            // Appliquer les filtres initiaux si des paramètres URL sont présents
            const hasInitialFilters = <?= json_encode(!empty($search) || $filterAnnee || $filterAuteur || $filterType || $filterDomaine) ?>;
            if (hasInitialFilters) {
                applyFilters();
            }
        });
        </script>
        
        <?php
        FooterView::render($settings);
    }
    
    /**
     * Détail d'une publication avec DOI et téléchargement
     */
    public static function renderDetail(array $data) {
        $settings = $data['settings'] ?? [];
        $publication = $data['publication'] ?? [];
        $auteurs = $data['auteurs'] ?? [];
        $isAuthenticated = $data['isAuthenticated'] ?? false;
        $isAuthor = $data['isAuthor'] ?? false;
        
        HeaderView::render($settings, $publication['titre'] ?? '');
        MenuView::render('publications');
        ?>
        
        <section class="section">
            <div class="container">
                <div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <a href="index.php?url=publication" class="link">
                        <i class="fas fa-arrow-left"></i> Retour à la base documentaire
                    </a>
                    
                    <!-- ✅ Boutons d'action si l'utilisateur est auteur -->
                    <?php if ($isAuthenticated && $isAuthor): ?>
                        <div style="display: flex; gap: 8px;">
                            <a href="index.php?url=userpublication/edit/<?= $publication['id_publication'] ?>" 
                               class="btn ghost">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <a href="index.php?url=userpublication/delete/<?= $publication['id_publication'] ?>" 
                               class="btn ghost" 
                               style="color: #e74c3c;"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette publication ?');">
                                <i class="fas fa-trash"></i> Supprimer
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px;">
                    <!-- Colonne principale -->
                    <div>
                        <div class="content-card" style="padding: 32px;">
                            <!-- Badges -->
                            <div style="margin-bottom: 16px;">
                                <span class="badge primary"><?= htmlspecialchars($publication['type_publication'] ?? 'Publication') ?></span>
                                <span class="badge" style="margin-left: 8px;"><?= $publication['annee_publication'] ?? '' ?></span>
                                <?php if (!empty($publication['domaine'])): ?>
                                    <span class="badge secondary" style="margin-left: 8px;">
                                        <?= htmlspecialchars($publication['domaine']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($publication['statut'] === 'en_attente'): ?>
                                    <span class="badge warning" style="margin-left: 8px;">En attente de validation</span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Titre -->
                            <h1 style="margin: 0 0 24px;"><?= htmlspecialchars($publication['titre']) ?></h1>
                            
                            <!-- Auteurs -->
                            <?php if (!empty($auteurs)): ?>
                                <div style="margin-bottom: 24px;">
                                    <h3 style="margin: 0 0 12px; font-size: 1.1rem;">
                                        <i class="fas fa-users"></i> Auteurs
                                    </h3>
                                    <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                                        <?php foreach ($auteurs as $aut): ?>
                                            <div style="display: flex; align-items: center; gap: 8px; padding: 8px 16px; background: var(--bg); border-radius: 20px;">
                                                <?php if (!empty($aut['photo'])): ?>
                                                    <img src="assets/images/users/<?= htmlspecialchars($aut['photo']) ?>" 
                                                         alt="Photo" 
                                                         style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                                                <?php endif; ?>
                                                <span>
                                                    <?= htmlspecialchars($aut['nom'] . ' ' . $aut['prenom']) ?>
                                                    <?php if (!empty($aut['grade'])): ?>
                                                        <span style="font-size: 0.85rem; color: var(--muted);">
                                                            (<?= htmlspecialchars($aut['grade']) ?>)
                                                        </span>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php elseif (!empty($publication['auteurs'])): ?>
                                <div style="margin-bottom: 24px;">
                                    <h3 style="margin: 0 0 8px; font-size: 1.1rem;">
                                        <i class="fas fa-users"></i> Auteurs
                                    </h3>
                                    <p style="margin: 0; color: var(--muted);">
                                        <?= htmlspecialchars($publication['auteurs']) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Résumé -->
                            <?php if (!empty($publication['resume'])): ?>
                                <div style="margin-bottom: 24px;">
                                    <h3 style="margin: 0 0 12px; font-size: 1.1rem;">Résumé</h3>
                                    <p style="white-space: pre-line; line-height: 1.7; color: var(--text);">
                                        <?= htmlspecialchars($publication['resume']) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Sidebar -->
                    <div>
                        <!-- Informations -->
                        <div class="content-card" style="padding: 24px; margin-bottom: 24px;">
                            <h3 style="margin: 0 0 16px; font-size: 1.1rem;">
                                <i class="fas fa-info-circle"></i> Informations
                            </h3>
                            
                            <?php if (!empty($publication['annee_publication'])): ?>
                                <div style="margin-bottom: 16px;">
                                    <strong style="display: block; margin-bottom: 4px;">Année</strong>
                                    <p style="margin: 0; color: var(--muted);">
                                        <?= htmlspecialchars($publication['annee_publication']) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($publication['type_publication'])): ?>
                                <div style="margin-bottom: 16px;">
                                    <strong style="display: block; margin-bottom: 4px;">Type</strong>
                                    <p style="margin: 0; color: var(--muted);">
                                        <?= ucfirst(htmlspecialchars($publication['type_publication'])) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($publication['domaine'])): ?>
                                <div style="margin-bottom: 16px;">
                                    <strong style="display: block; margin-bottom: 4px;">Domaine</strong>
                                    <p style="margin: 0; color: var(--muted);">
                                        <?= htmlspecialchars($publication['domaine']) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($publication['date_soumission'])): ?>
                                <div>
                                    <strong style="display: block; margin-bottom: 4px;">Date de soumission</strong>
                                    <p style="margin: 0; color: var(--muted);">
                                        <?= date('d/m/Y', strtotime($publication['date_soumission'])) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Actions -->
                        <div class="content-card" style="padding: 24px; background: linear-gradient(135deg, var(--primary), var(--nav)); color: white;">
                            <h3 style="margin: 0 0 16px; color: white; font-size: 1.1rem;">
                                <i class="fas fa-link"></i> Accès
                            </h3>
                            
                            <?php if (!empty($publication['doi'])): ?>
                                <a href="https://doi.org/<?= htmlspecialchars($publication['doi']) ?>" 
                                   target="_blank" 
                                   rel="noopener"
                                   class="btn" 
                                   style="width: 100%; background: white; color: var(--primary); justify-content: center; margin-bottom: 12px;">
                                    <i class="fas fa-external-link-alt"></i> Consulter via DOI
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($publication['fichier'])): ?>
                                <a href="index.php?url=publication/download/<?= $publication['id_publication'] ?>" 
                                   class="btn"
                                   style="width: 100%; background: white; color: var(--primary); justify-content: center;">
                                    <i class="fas fa-download"></i> Télécharger le document
                                </a>
                            <?php endif; ?>
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