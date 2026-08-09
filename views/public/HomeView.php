<?php
// views/HomeView.php

class HomeView {

    public static function render(array $data)
    {
        $settings        = $data['settings']        ?? [];
        $slideshowNews   = $data['slideshowNews']   ?? [];
        $recentNews      = $data['recentNews']      ?? [];
        $upcomingEvents  = $data['upcomingEvents']  ?? [];
        $partners        = $data['partners']        ?? [];
        $currentPage     = $data['currentEventPage'] ?? 1;
        $totalPages      = $data['totalEventPages']  ?? 1;

        HeaderView::render($settings, 'Accueil');
        MenuView::render('home');

        self::renderHero($settings, $slideshowNews);
        self::renderNews($recentNews);
        self::renderAboutAndOrganigram($settings, $data['organigramPreview'] ?? []);
        self::renderEvents($upcomingEvents, $currentPage, $totalPages);
        self::renderPartners($partners);

        FooterView::render($settings);
    }

    private static function renderHero(array $settings, array $slides)
    {
        ?>
        <main id="hero" class="hero">
            <div class="container">
                <div class="hero-text">
                    <p class="eyebrow">Laboratoire universitaire</p>

                    <h1><?= htmlspecialchars($settings['nom_laboratoire'] ?? 'Laboratoire de Recherche') ?></h1>

                    <p class="lead">
                        <?= htmlspecialchars($settings['description_laboratoire'] ?? 'Actualités, projets, publications et ressources du laboratoire.') ?>
                    </p>

                    <div class="hero-actions">
                        <a class="btn primary" href="index.php?url=project">Découvrir les projets</a>
                        <a class="btn ghost" href="#events">Voir les événements</a>
                    </div>
                </div>

                <div class="hero-slider">
                    <div class="slideshow-container">
                        <?php if (!empty($slides)): ?>
                            <?php foreach ($slides as $i => $news): ?>
                                <div class="slide <?= $i === 0 ? 'active' : '' ?>">
                                    <div class="slide-image"
                                         style="background-image:url('assets/images/news/<?= htmlspecialchars($news['image'] ?? 'default.jpg') ?>')">
                                    </div>

                                    <div class="slide-content">
                                        <span class="badge"><?= ucfirst(htmlspecialchars($news['type_news'] ?? 'actualité')) ?></span>
                                        <h3><?= htmlspecialchars($news['titre']) ?></h3>

                                        <p><?= htmlspecialchars(mb_substr(strip_tags($news['contenu'] ?? ''), 0, 150)) ?>...</p>
                                        <a class="link"
                                           href="index.php?url=news/detail/<?= $news['id_news'] ?>">
                                           Lire la suite
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="slide-controls">
                                <button class="slide-nav prev" onclick="changeSlide(-1)">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button class="slide-nav next" onclick="changeSlide(1)">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>

                            <div class="slide-indicators">
                                <?php foreach ($slides as $i => $s): ?>
                                    <span class="indicator <?= $i === 0 ? 'active' : '' ?>"
                                          onclick="goToSlide(<?= $i ?>)"></span>
                                <?php endforeach; ?>
                            </div>

                        <?php else: ?>
                            <div class="slide active">
                                <div class="slide-image" style="background-image:url('assets/images/default.jpg')"></div>
                                <div class="slide-content">
                                    <h3>Bienvenue au laboratoire</h3>
                                    <p>Aucune actualité pour le moment.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>

        <script>
        // Automatic slideshow every 5 seconds as per PDF requirement
        let slideIndex = 0;
        let slideTimer;

        function autoSlide() {
            changeSlide(1);
            slideTimer = setTimeout(autoSlide, 5000);
        }

        function changeSlide(n) {
            clearTimeout(slideTimer);
            const slides = document.getElementsByClassName("slide");
            const indicators = document.getElementsByClassName("indicator");
            
            if (slides.length === 0) return;
            
            slides[slideIndex].classList.remove("active");
            indicators[slideIndex].classList.remove("active");
            
            slideIndex += n;
            if (slideIndex >= slides.length) slideIndex = 0;
            if (slideIndex < 0) slideIndex = slides.length - 1;
            
            slides[slideIndex].classList.add("active");
            indicators[slideIndex].classList.add("active");
            
            slideTimer = setTimeout(autoSlide, 5000);
        }

        function goToSlide(n) {
            clearTimeout(slideTimer);
            const slides = document.getElementsByClassName("slide");
            const indicators = document.getElementsByClassName("indicator");
            
            if (slides.length === 0) return;
            
            slides[slideIndex].classList.remove("active");
            indicators[slideIndex].classList.remove("active");
            
            slideIndex = n;
            
            slides[slideIndex].classList.add("active");
            indicators[slideIndex].classList.add("active");
            
            slideTimer = setTimeout(autoSlide, 5000);
        }

        // Start automatic slideshow
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementsByClassName("slide").length > 1) {
                slideTimer = setTimeout(autoSlide, 5000);
            }
        });
        </script>
        <?php
    }

    private static function renderNews(array $newsList)
    {
        ?>
        <section class="section" id="news">
            <div class="container section-header">
                <div>
                    <p class="eyebrow">Actualités</p>
                    <h2>Actualités scientifiques</h2>
                    <p>Nouveaux projets, publications et collaborations</p>
                </div>
                <a class="link" href="index.php?url=news">Voir toutes les actualités</a>
            </div>

            <div class="container cards-grid">
                <?php if (!empty($newsList)): ?>
                    <?php foreach ($newsList as $news): ?>

                        <?php
                        $card = [
                            'image' => 'assets/images/news/' . ($news['image'] ?? 'default.jpg'),
                            'badge' => ucfirst($news['type_news']),
                            'title' => $news['titre'],
                            'text'  => mb_substr(strip_tags($news['contenu']), 0, 120) . '...',
                            'link'  => "index.php?url=news/detail/" . $news['id_news'],
                            'meta'  => [
                                [
                                    'icon' => 'far fa-calendar',
                                    'label' => date('d/m/Y', strtotime($news['date_publication']))
                                ]
                            ]
                        ];
                        CardView::render($card);
                        ?>

                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="container">Aucune actualité disponible.</p>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }

    private static function renderAboutAndOrganigram(array $settings, array $organigramPreview)
    {
        ?>
        <section class="section section-combined" id="about-organigram">
            <div class="container about-layout" style="display:flex; flex-wrap:wrap; gap:2rem; align-items:center;">
                <!-- About text on the left -->
                <div class="about-content" style="flex:1; min-width:280px;">
                    <p class="eyebrow">Présentation</p>
                    <h2>Le laboratoire</h2>
                    <p><?= nl2br(htmlspecialchars($settings['description_laboratoire'] ?? '')) ?></p>

                    <?php if (!empty($settings['themes_recherche'])): ?>
                        <h3>Thèmes de recherche</h3>
                        <p><?= nl2br(htmlspecialchars($settings['themes_recherche'])) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Presentation image on the right -->
                <div class="about-visual" style="flex:1; min-width:280px; text-align:right;">
                    <img src="assets/images/<?= htmlspecialchars($settings['image_presentation'] ?? 'default.jpg') ?>"
                        alt="Laboratoire" style="max-width:100%; height:auto; border-radius:8px;">
                </div>
            </div>

            <!-- Organigram preview part -->
            <div class="container organigram-section" style="margin-top:3rem;">
                <div class="section-header">
                    <div>
                        <p class="eyebrow">Organigramme</p>
                        <h2>L'équipe dirigeante</h2>
                        <p>Directeur et responsables du laboratoire</p>
                    </div>
                </div>

                <div class="cards-grid" style="margin-top:1rem;">
                    <?php if (!empty($organigramPreview)): ?>
                        <?php foreach ($organigramPreview as $user): ?>

                            <?php
                            // Skip system admin
                            if (!empty($user['role']) && $user['role'] === 'admin') continue;

                            $card = [
                                'image' => !empty($user['photo'])
                                    ? 'assets/images/users/' . $user['photo']
                                    : 'assets/images/users/default.jpg',
                                'badge' => (string)($user['poste'] ?? ''),
                                'title' => trim((string)($user['nom'] ?? '') . ' ' . (string)($user['prenom'] ?? '')),
                                'text'  => !empty($user['domaine_recherche']) 
                                    ? htmlspecialchars(mb_substr($user['domaine_recherche'], 0, 100)) . '...'
                                    : '',
                                'meta'  => [
                                    [
                                        'icon' => 'fas fa-graduation-cap',
                                        'label' => (string)($user['grade'] ?? '')
                                    ]
                                ],
                                'link' => "index.php?url=team/membre/" . $user['id_user']
                            ];

                            CardView::render($card);
                            ?>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucun membre trouvé pour l'organigramme.</p>
                    <?php endif; ?>
                </div>

                <!-- CTA button to teams page -->
                <div class="section-cta" style="margin-top:2rem; text-align:center;">
                    <a class="btn primary" style="padding:0.5rem 1.5rem; font-size:0.95rem;" 
                    href="index.php?url=team">
                        Voir la présentation complète, l'organigramme et les équipes
                    </a>
                </div>
            </div>
        </section>
        <?php
    }



    private static function renderEvents(array $events, int $currentPage, int $totalPages)
    {
        ?>
        <section class="section" id="events">
            <div class="container section-header">
                <div>
                    <p class="eyebrow">Événements</p>
                    <h2>Événements à venir</h2>
                    <p>Conférences, ateliers, séminaires et soutenances</p>
                </div>

                <a class="link" href="index.php?url=event">
                    Voir tous les événements
                </a>
            </div>

            <div class="container cards-grid">
                <?php if (!empty($events)): ?>
                    <?php foreach ($events as $event): ?>

                        <?php
                        // Détecter le nom de la colonne date
                        $dateValue = $event['date_event'] ?? $event['date_debut'] ?? $event['date'] ?? null;
                        
                        $linkText = 'Plus d\'informations';
                        $linkUrl = "index.php?url=event/detail/" . $event['id_event'];
                        
                        if (!empty($event['lien_inscription'])) {
                            $linkText = 'S\'inscrire';
                            $linkUrl = $event['lien_inscription'];
                        }

                        $meta = [];
                        
                        if ($dateValue) {
                            $meta[] = [
                                'icon' => 'far fa-calendar',
                                'label' => date('d/m/Y', strtotime($dateValue))
                            ];
                            $meta[] = [
                                'icon' => 'far fa-clock',
                                'label' => date('H:i', strtotime($dateValue))
                            ];
                        }
                        
                        if (!empty($event['lieu'])) {
                            $meta[] = [
                                'icon' => 'fas fa-map-marker-alt',
                                'label' => $event['lieu']
                            ];
                        }

                        $card = [
                            'image' => !empty($event['image']) 
                                ? 'assets/images/events/' . $event['image']
                                : null,
                            'badge' => ucfirst($event['type_event'] ?? $event['type'] ?? 'Événement'),
                            'title' => $event['titre'] ?? $event['nom_event'] ?? 'Sans titre',
                            'text'  => mb_substr($event['description'] ?? '', 0, 120) . '...',
                            'link'  => $linkUrl,
                            'link_text' => $linkText,
                            'meta'  => $meta
                        ];
                        CardView::render($card);
                        ?>

                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="container">Aucun événement prévu pour le moment.</p>
                <?php endif; ?>
            </div>

            <!-- Pagination améliorée -->
            <?php if ($totalPages > 1): ?>
                <div class="container pagination">
                    <!-- Bouton Précédent -->
                    <?php if ($currentPage > 1): ?>
                        <a href="index.php?url=home&event_page=<?= $currentPage - 1 ?>#events" 
                           class="pagination-btn">
                            <i class="fas fa-chevron-left"></i>
                            <span>Précédent</span>
                        </a>
                    <?php else: ?>
                        <button class="pagination-btn disabled" disabled>
                            <i class="fas fa-chevron-left"></i>
                            <span>Précédent</span>
                        </button>
                    <?php endif; ?>

                    <!-- Numéros de page -->
                    <div class="pagination-numbers">
                        <?php
                        // Logique de pagination intelligente
                        $maxVisible = 7; // Nombre max de numéros visibles
                        $halfVisible = floor($maxVisible / 2);
                        
                        if ($totalPages <= $maxVisible) {
                            // Afficher tous les numéros si peu de pages
                            $startPage = 1;
                            $endPage = $totalPages;
                        } else {
                            // Calcul des pages à afficher
                            if ($currentPage <= $halfVisible) {
                                $startPage = 1;
                                $endPage = $maxVisible;
                            } elseif ($currentPage >= $totalPages - $halfVisible) {
                                $startPage = $totalPages - $maxVisible + 1;
                                $endPage = $totalPages;
                            } else {
                                $startPage = $currentPage - $halfVisible;
                                $endPage = $currentPage + $halfVisible;
                            }
                        }
                        
                        // Première page + ellipsis
                        if ($startPage > 1) {
                            ?>
                            <a href="index.php?url=home&event_page=1#events" 
                               class="pagination-number">1</a>
                            <?php if ($startPage > 2): ?>
                                <span class="pagination-ellipsis">...</span>
                            <?php endif; ?>
                        <?php }
                        
                        // Pages du milieu
                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <a href="index.php?url=home&event_page=<?= $i ?>#events" 
                               class="pagination-number <?= $i === $currentPage ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor;
                        
                        // Ellipsis + dernière page
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1): ?>
                                <span class="pagination-ellipsis">...</span>
                            <?php endif; ?>
                            <a href="index.php?url=home&event_page=<?= $totalPages ?>#events" 
                               class="pagination-number"><?= $totalPages ?></a>
                        <?php } ?>
                    </div>

                    <!-- Bouton Suivant -->
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="index.php?url=home&event_page=<?= $currentPage + 1 ?>#events" 
                           class="pagination-btn">
                            <span>Suivant</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <button class="pagination-btn disabled" disabled>
                            <span>Suivant</span>
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
        <?php
    }

    private static function renderPartners(array $partners)
    {
        ?>
        <section class="section" id="partners">
            <div class="container section-header">
                <div>
                    <p class="eyebrow">Partenaires</p>
                    <h2>Nos partenaires</h2>
                    <p>Partenaires institutionnels et industriels</p>
                </div>
                <a class="link" href="index.php?url=partner">Voir tous les partenaires</a>
            </div>

            <div class="container cards-grid partners-grid">
                <?php if (!empty($partners)): ?>

                    <?php foreach ($partners as $p): ?>
                        <?php
                        $card = [
                            'image' => !empty($p['logo'])
                                ? 'assets/images/partners/' . $p['logo']
                                : 'assets/images/partners/default.jpg',
                            'title' => $p['nom_partenaire'],
                            'badge' => ucfirst($p['type_partenaire']),
                            'text' => !empty($p['description']) 
                                ? mb_substr($p['description'], 0, 100) . '...'
                                : '',
                            'link'  => $p['site_web'] ?? null,
                            'link_text' => 'Visiter le site',
                            'external' => true,
                            'meta'  => [
                                [
                                    'icon' => 'fas fa-globe',
                                    'label' => $p['pays'] ?? ''
                                ]
                            ]
                        ];

                        CardView::render($card);
                        ?>
                    <?php endforeach; ?>

                <?php else: ?>
                    <p class="container">Aucun partenaire enregistré pour le moment.</p>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }
}
?>