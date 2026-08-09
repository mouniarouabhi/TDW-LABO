<?php
// views/public/TeamView.php
// Point 3 du PDF: Présentation, organigramme et équipes

class TeamView {
    
    /**
     * Page principale: Présentation + Organigramme + Équipes
     */
    public static function renderIndex(array $data) {
        $settings = $data['settings'] ?? [];
        $organigram = $data['organigram'] ?? [];
        $teams = $data['teams'] ?? [];
        $allTeams = $data['allTeams'] ?? [];
        $filterEquipe = $data['filterEquipe'] ?? null;
        $filterRole = $data['filterRole'] ?? null;
        $sortBy = $data['sortBy'] ?? 'nom';
        
        HeaderView::render($settings, 'Présentation et Équipes');
        MenuView::render('teams');
        ?>
        
        <!-- Présentation du laboratoire -->
        <section class="section" style="padding-top: 40px;">
            <div class="container">
                <p class="eyebrow">Présentation</p>
                <h1>Le Laboratoire</h1>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px; margin-top: 32px;">
                    <div>
                        <p style="line-height: 1.8; color: var(--text);">
                            <?= nl2br(htmlspecialchars($settings['description_laboratoire'] ?? 'Description du laboratoire')) ?>
                        </p>
                        
                        <?php if (!empty($settings['themes_recherche'])): ?>
                            <h3 style="margin-top: 32px;">Thèmes de recherche</h3>
                            <p style="line-height: 1.8; color: var(--text);">
                                <?= nl2br(htmlspecialchars($settings['themes_recherche'])) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <img src="assets/images/<?= htmlspecialchars($settings['image_presentation'] ?? 'default.jpg') ?>" 
                             alt="Laboratoire"
                             style="width: 100%; border-radius: var(--radius); box-shadow: var(--shadow);">
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Organigramme -->
        <section class="section" id="organigram" style="background: var(--bg);">
            <div class="container">
                <p class="eyebrow">Organigramme</p>
                <h2>Structure du laboratoire</h2>
                <p style="color: var(--muted); margin-bottom: 32px;">Directeur et membres occupants différents postes</p>
                
                <?php if (!empty($organigram)): ?>
                    <div class="cards-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                        <?php foreach ($organigram as $membre): ?>
                            <div class="content-card" style="text-align: center; padding: 24px;">
                                <img src="assets/images/users/<?= htmlspecialchars($membre['photo'] ?? 'default.jpg') ?>" 
                                     alt="<?= htmlspecialchars($membre['nom']) ?>"
                                     style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin: 0 auto 16px;">
                                
                                <h4 style="margin: 0 0 4px;"><?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?></h4>
                                
                                <?php if (!empty($membre['poste'])): ?>
                                    <span class="badge primary" style="margin-bottom: 8px;">
                                        <?= htmlspecialchars($membre['poste']) ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($membre['grade'])): ?>
                                    <div style="color: var(--muted); font-size: 0.9rem; margin-bottom: 8px;">
                                        <?= htmlspecialchars($membre['grade']) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div style="margin-top: 12px;">
                                    <a href="index.php?url=team/membre/<?= $membre['id_user'] ?>" class="link">
                                        <i class="fas fa-user"></i> Voir la biographie
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>Aucun membre dans l'organigramme</p>
                <?php endif; ?>
            </div>
        </section>
        
        <!-- Équipes de recherche -->
        <section class="section" id="teams">
            <div class="container">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
                    <div>
                        <p class="eyebrow">Équipes de recherche</p>
                        <h2>Nos équipes</h2>
                    </div>
                    
                    <!-- Filtres et tri -->
                    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                        <!-- Filtre par équipe -->
                        <select name="equipe" id="filter-equipe" 
                                style="padding: 8px 12px; border: 2px solid var(--border); border-radius: 8px;">
                            <option value="">Toutes les équipes</option>
                            <?php foreach ($allTeams as $t): ?>
                                <option value="<?= $t['id_team'] ?>" <?= $filterEquipe == $t['id_team'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['nom_equipe']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <!-- Filtre par rôle -->
                        <select name="role" id="filter-role"
                                style="padding: 8px 12px; border: 2px solid var(--border); border-radius: 8px;">
                            <option value="">Tous les rôles</option>
                            <option value="enseignant" <?= $filterRole === 'enseignant' ? 'selected' : '' ?>>Enseignants</option>
                            <option value="chercheur" <?= $filterRole === 'chercheur' ? 'selected' : '' ?>>Chercheurs</option>
                            <option value="doctorant" <?= $filterRole === 'doctorant' ? 'selected' : '' ?>>Doctorants</option>
                        </select>
                        
                        <!-- Tri -->
                        <select name="sort" id="sort-by"
                                style="padding: 8px 12px; border: 2px solid var(--border); border-radius: 8px;">
                            <option value="nom" <?= $sortBy === 'nom' ? 'selected' : '' ?>>Trier par nom</option>
                            <option value="grade" <?= $sortBy === 'grade' ? 'selected' : '' ?>>Trier par grade</option>
                            <option value="date" <?= $sortBy === 'date' ? 'selected' : '' ?>>Trier par ancienneté</option>
                        </select>
                        
                        <!-- Reset filters -->
                        <?php if ($filterEquipe || $filterRole || $sortBy !== 'nom'): ?>
                            <a href="index.php?url=team" class="btn ghost" style="padding: 8px 12px;">
                                <i class="fas fa-times"></i> Réinitialiser
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($teams)): ?>
                    <?php foreach ($teams as $team): ?>
                        <div class="content-card" style="margin-bottom: 32px; padding: 32px;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
                                <div>
                                    <h3 style="margin: 0 0 8px;"><?= htmlspecialchars($team['nom_equipe']) ?></h3>
                                    <?php if (!empty($team['thematique'])): ?>
                                        <span class="badge"><?= htmlspecialchars($team['thematique']) ?></span>
                                    <?php endif; ?>
                                    <div style="color: var(--muted); margin-top: 12px; display: flex; gap: 16px; flex-wrap: wrap;">
                                        <span>
                                            <i class="fas fa-users"></i> <?= $team['total_membres'] ?> membre<?= $team['total_membres'] > 1 ? 's' : '' ?>
                                        </span>
                                        <span>
                                            <i class="fas fa-file-alt"></i> <?= $team['publication_count'] ?? 0 ?> publication<?= ($team['publication_count'] ?? 0) > 1 ? 's' : '' ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <a href="index.php?url=team/publications/<?= $team['id_team'] ?>" 
                                   class="btn primary">
                                    <i class="fas fa-file-alt"></i> Publications de l'équipe
                                </a>
                            </div>
                            
                            <?php if (!empty($team['description'])): ?>
                                <p style="line-height: 1.8; margin-bottom: 24px;">
                                    <?= nl2br(htmlspecialchars($team['description'])) ?>
                                </p>
                            <?php endif; ?>
                            
                            <!-- Chef d'équipe -->
                            <?php if (!empty($team['chef'])): ?>
                                <h4 style="margin: 24px 0 16px; padding-top: 24px; border-top: 2px solid var(--border);">
                                    <i class="fas fa-star"></i> Chef d'équipe
                                </h4>
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 16px;">
                                    <?php self::renderMemberCard($team['chef']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Membres de l'équipe -->
                            <?php if (!empty($team['membres'])): ?>
                                <h4 style="margin: 24px 0 16px; padding-top: 24px; border-top: 2px solid var(--border);">
                                    <i class="fas fa-users"></i> Membres
                                </h4>
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 16px;">
                                    <?php foreach ($team['membres'] as $membre): ?>
                                        <?php self::renderMemberCard($membre); ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state" style="text-align: center; padding: 60px 20px;">
                        <i class="fas fa-users" style="font-size: 3rem; opacity: 0.3; margin-bottom: 16px;"></i>
                        <p>Aucune équipe trouvée avec ces critères</p>
                        <a href="index.php?url=team" class="btn ghost" style="margin-top: 16px;">
                            Réinitialiser les filtres
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        
        <!-- JavaScript pour les filtres -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterEquipe = document.getElementById('filter-equipe');
            const filterRole = document.getElementById('filter-role');
            const sortBy = document.getElementById('sort-by');
            
            function applyFilters() {
                const params = new URLSearchParams();
                params.set('url', 'team');
                
                if (filterEquipe.value) {
                    params.set('equipe', filterEquipe.value);
                }
                if (filterRole.value) {
                    params.set('role', filterRole.value);
                }
                if (sortBy.value && sortBy.value !== 'nom') {
                    params.set('sort', sortBy.value);
                }
                
                window.location.href = 'index.php?' + params.toString() + '#teams';
            }
            
            filterEquipe.addEventListener('change', applyFilters);
            filterRole.addEventListener('change', applyFilters);
            sortBy.addEventListener('change', applyFilters);
        });
        </script>
        
        <?php
        FooterView::render($settings);
    }
    
    /**
     * Carte d'un membre (réutilisable)
     */
    private static function renderMemberCard($membre) {
        ?>
        <div style="background: var(--bg); padding: 16px; border-radius: 8px; border: 1px solid var(--border);">
            <div style="display: flex; gap: 12px; align-items: center;">
                <img src="assets/images/users/<?= htmlspecialchars($membre['photo'] ?? 'default.jpg') ?>" 
                     alt="<?= htmlspecialchars($membre['nom']) ?>"
                     style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                
                <div style="flex: 1;">
                    <h5 style="margin: 0 0 4px;"><?= htmlspecialchars($membre['nom'] . ' ' . $membre['prenom']) ?></h5>
                    
                    <?php if (!empty($membre['grade'])): ?>
                        <div style="font-size: 0.85rem; color: var(--muted);">
                            <?= htmlspecialchars($membre['grade']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($membre['poste'])): ?>
                        <span class="badge" style="font-size: 0.75rem; margin-top: 4px;">
                            <?= htmlspecialchars($membre['poste']) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div style="margin-top: 12px; display: flex; gap: 8px; font-size: 0.85rem;">
                <a href="index.php?url=team/membre/<?= $membre['id_user'] ?>" class="link">
                    <i class="fas fa-user"></i> Biographie et publications
                </a>
            </div>
        </div>
        <?php
    }
    
    /**
     * Publications de tous les membres d'une équipe
     */
    public static function renderTeamPublications(array $data) {
        $settings = $data['settings'] ?? [];
        $team = $data['team'] ?? [];
        $members = $data['members'] ?? [];
        $publications = $data['publications'] ?? [];
        
        HeaderView::render($settings, 'Publications - ' . ($team['nom_equipe'] ?? ''));
        MenuView::render('teams');
        ?>
        
        <section class="section" style="padding-top: 40px;">
            <div class="container">
                <!-- Breadcrumb -->
                <div style="margin-bottom: 24px; font-size: 0.9rem; color: var(--muted);">
                    <a href="index.php?url=team" class="link">Équipes</a>
                    <span> / </span>
                    <span><?= htmlspecialchars($team['nom_equipe'] ?? '') ?></span>
                    <span> / </span>
                    <span>Publications</span>
                </div>
                
                <h1><?= htmlspecialchars($team['nom_equipe'] ?? '') ?></h1>
                <p style="color: var(--muted); margin-bottom: 32px;">
                    Publications de tous les membres de l'équipe (<?= count($publications) ?>)
                </p>
                
                <?php if (!empty($publications)): ?>
                    <div class="cards-grid"
                        style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px;">

                        <?php foreach ($publications as $pub): ?>
                            <?php
                            CardView::render([
                                'badge' => $pub['type_publication'],
                                'badge_class' => 'primary',
                                'title' => $pub['titre'],
                                'text'  => mb_substr($pub['resume'] ?? '', 0, 120) . '...',
                                'meta'  => [
                                    [
                                        'icon' => 'far fa-calendar',
                                        'label' => $pub['annee_publication']
                                    ],
                                    [
                                        'icon' => 'fas fa-user',
                                        'label' => $pub['auteur_nom'] ?? '—'
                                    ]
                                ],
                                'link' => 'index.php?url=publication/detail/' . $pub['id_publication'],
                                'link_text' => 'Voir détails'
                            ]);
                            ?>
                        <?php endforeach; ?>

                    </div>

                    <!-- Pagination -->
                    <?php self::renderPagination(
                        'team/publications/' . $team['id_team'],
                        $data['currentPage'] ?? 1,
                        $data['totalPages'] ?? 1
                    ); ?>

                <?php endif; ?>
                
                <div style="margin-top: 32px;">
                    <a href="index.php?url=team" class="btn ghost">
                        <i class="fas fa-arrow-left"></i> Retour aux équipes
                    </a>
                </div>
            </div>
        </section>
        
        <?php
        FooterView::render($settings);
    }
    
    /**
     * Biographie et publications d'un membre
     */
    public static function renderMembreBiographie(array $data) {
        $settings = $data['settings'] ?? [];
        $membre = $data['membre'] ?? [];
        $roles = $data['roles'] ?? []; // Array of roles fetched from UserRoleModel
        $publications = $data['publications'] ?? [];
        $page = $data['currentPage'] ?? 1;
        $total = $data['totalPages'] ?? 1;

        HeaderView::render($settings, $membre['nom'].' '.$membre['prenom']);
        MenuView::render('teams');
        ?>

        <section class="section" style="padding-top:40px">
            <div class="container">
                <h1><?= htmlspecialchars($membre['nom'].' '.$membre['prenom']) ?></h1>

                <!-- Infos principales -->
                <div class="content-card" style="padding:32px;margin-bottom:24px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <?php if (!empty($membre['grade'])): ?>
                            <div>
                                <strong>Grade:</strong>
                                <p><?= htmlspecialchars($membre['grade']) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($membre['poste'])): ?>
                            <div>
                                <strong>Poste:</strong>
                                <p><?= htmlspecialchars($membre['poste']) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($membre['domaine_recherche'])): ?>
                            <div>
                                <strong>Domaine de recherche:</strong>
                                <p><?= htmlspecialchars($membre['domaine_recherche']) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($roles)): ?>
                            <div>
                                <strong>Rôles:</strong>
                                <p>
                                    <?= implode(', ', array_map(fn($r) => htmlspecialchars($r['nom']), $roles)) ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Biographie -->
                    <h2>Biographie</h2>
                    <?php if (!empty($membre['biographie'])): ?>
                        <p><?= nl2br(htmlspecialchars($membre['biographie'])) ?></p>
                    <?php else: ?>
                        <p style="color:var(--muted)">Aucune biographie disponible</p>
                    <?php endif; ?>
                </div>

                <!-- Publications -->
                <h2 id="publications">Publications (<?= count($publications) ?>)</h2>
                <?php if ($publications): ?>
                    <div class="cards-grid" style="grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:24px">
                        <?php foreach ($publications as $pub): ?>
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
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php self::renderPagination(
                        'team/membre/'.$membre['id_user'],
                        $page,
                        $total,
                        '#publications'
                    ); ?>
                <?php else: ?>
                    <p style="color:var(--muted)">Aucune publication</p>
                <?php endif; ?>

                <a href="index.php?url=team" class="btn ghost" style="margin-top:24px">
                    ← Retour aux équipes
                </a>
            </div>
        </section>

        <?php
        FooterView::render($settings);
    }

    private static function renderPagination(string $baseUrl, int $page, int $total, string $anchor = '')
    {
        if ($total <= 1) return;
        ?>
        <div style="margin-top: 32px; display: flex; gap: 8px; justify-content: center;">
            <?php for ($i = 1; $i <= $total; $i++): ?>
                <a href="index.php?url=<?= $baseUrl ?>&page=<?= $i ?><?= $anchor ?>"
                class="btn <?= $i === $page ? 'primary' : 'ghost' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php
    }
}
?>