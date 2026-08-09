<?php
// views/public/ProjectView.php
// Point 4 du PDF: Catalogue des projets (CARTES + Filtres)
// Support du mode "mes projets" avec ?user=me

class ProjectView {
    
    /**
     * Catalogue en CARTES avec filtres thématique/encadrant/statut
     * ✅ Titre et breadcrumb conditionnels selon $filterByUser
     */
    public static function renderCatalogue(array $data) {
        $settings = $data['settings'] ?? [];
        $projects = $data['projects'] ?? [];
        $thematiques = $data['thematiques'] ?? [];
        $encadrants = $data['encadrants'] ?? [];
        $filterThematique = $data['filterThematique'] ?? null;
        $filterEncadrant = $data['filterEncadrant'] ?? null;
        $filterStatut = $data['filterStatut'] ?? null;
        $filterByUser = $data['filterByUser'] ?? false;
        $isAuthenticated = $data['isAuthenticated'] ?? false;
        $currentUserId = $data['currentUserId'] ?? null;
        
        HeaderView::render($settings, $filterByUser ? 'Mes Projets' : 'Projets');
        MenuView::render('projects');
        ?>
        
        <!-- Hero Section -->
        <section class="hero" style="background: linear-gradient(135deg, var(--primary), var(--nav)); padding: 60px 0; color: white;">
            <div class="container" style="text-align: center;">
                <h1 style="color: white;">
                    <?= $filterByUser ? 'Mes Projets' : 'Catalogue des Projets' ?>
                </h1>
                <p style="color: rgba(255,255,255,0.9);">
                    <?= $filterByUser 
                        ? 'Projets auxquels vous participez' 
                        : 'Découvrez les projets de recherche du laboratoire' ?>
                </p>
            </div>
        </section>
        
        <!-- ✅ Breadcrumb si mode "mes projets" -->
        <?php if ($filterByUser): ?>
        <section style="background: var(--bg); padding: 16px 0; border-bottom: 1px solid var(--border);">
            <div class="container">
                <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                    <a href="index.php?url=dashboard" class="link">
                        <i class="fas fa-arrow-left"></i> Retour au dashboard
                    </a>
                    <span style="color: var(--muted);">|</span>
                    <a href="index.php?url=projects" class="link">
                        Voir tous les projets du laboratoire
                    </a>
                </div>
            </div>
        </section>
        <?php endif; ?>
        
        <section class="section">
            <div class="container">
                <!-- Filtres -->
                <div class="content-card" style="padding: 24px; margin-bottom: 32px;">
                    <h3 style="margin: 0 0 16px;"><i class="fas fa-filter"></i> Filtrer les projets</h3>
                    <form id="filterForm">
                        <!-- Indiquer le contrôleur -->
                        <input type="hidden" name="url" value="projects">

                        <!-- Si mode "mes projets", garder l'info -->
                        <?php if ($filterByUser): ?>
                            <input type="hidden" name="user" value="me">
                        <?php endif; ?>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
                            <!-- Thématique -->
                            <div>
                                <label>Thématique</label>
                                <select name="thematique" style="width: 100%; padding: 10px; border: 2px solid var(--border); border-radius: 8px;">
                                    <option value="">Toutes les thématiques</option>
                                    <?php foreach ($thematiques as $them): ?>
                                        <option value="<?= htmlspecialchars($them) ?>" <?= $filterThematique === $them ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($them) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Responsable / Encadrant -->
                            <div>
                                <label>Responsable</label>
                                <select name="encadrant" style="width: 100%; padding: 10px; border: 2px solid var(--border); border-radius: 8px;">
                                    <option value="">Tous les responsables</option>
                                    <?php foreach ($encadrants as $enc): ?>
                                        <option value="<?= $enc['id_user'] ?>" <?= $filterEncadrant == $enc['id_user'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($enc['nom'] . ' ' . $enc['prenom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Statut -->
                            <div>
                                <label>Statut</label>
                                <select name="statut" style="width: 100%; padding: 10px; border: 2px solid var(--border); border-radius: 8px;">
                                    <option value="">Tous les statuts</option>
                                    <option value="en_cours" <?= $filterStatut === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                                    <option value="termine" <?= $filterStatut === 'termine' ? 'selected' : '' ?>>Terminé</option>
                                    <option value="soumis" <?= $filterStatut === 'soumis' ? 'selected' : '' ?>>Soumis</option>
                                </select>
                            </div>

                            <div style="display: flex; align-items: flex-end;">
                                <button type="button" id="applyFilter" class="btn primary" style="width: 100%; padding: 10px;">
                                    <i class="fas fa-search"></i> Filtrer
                                </button>
                            </div>
                            <div style="display: flex; align-items: flex-end;">
                                <button type="button" id="resetFilter" class="btn ghost" style="width: 100%; padding: 10px; margin-top: 8px;">
                                    <i class="fas fa-times"></i> Réinitialiser
                                </button>
                            </div>
                        </div>

                        <!-- Lien Réinitialiser -->
                        <?php if ($filterThematique || $filterEncadrant || $filterStatut): ?>
                            <div style="text-align: right;">
                                <a href="index.php?url=projects<?= $filterByUser ? '&user=me' : '' ?>" class="link">
                                    <i class="fas fa-times"></i> Réinitialiser les filtres
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- Nombre de résultats -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h2 style="margin: 0;">
                        <?= count($projects) ?> projet<?= count($projects) > 1 ? 's' : '' ?> trouvé<?= count($projects) > 1 ? 's' : '' ?>
                    </h2>
                </div>
                
                <!-- CARTES -->
                <?php if (!empty($projects)): ?>
                    <div class="cards-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px;">
                        <?php foreach ($projects as $project): ?>
                            <?php
                            $statusBadge = '';
                            $statusClass = '';
                            switch ($project['statut'] ?? '') {
                                case 'en_cours':
                                    $statusBadge = '<span class="badge success">En cours</span>';
                                    break;
                                case 'termine':
                                    $statusBadge = '<span class="badge">Terminé</span>';
                                    break;
                                case 'soumis':
                                    $statusBadge = '<span class="badge warning">Soumis</span>';
                                    break;
                            }
                            ?>
                            
                            <div class="content-card project-card" 
                            data-thematique="<?= htmlspecialchars($project['thematique'] ?? '') ?>" 
                            data-responsable="<?= $project['id_responsable'] ?? 0 ?>" 
                            data-statut="<?= $project['statut'] ?? '' ?>"
                            style="padding: 24px; display: flex; flex-direction: column; height: 100%;">
                                <!-- Badges -->
                                <div style="margin-bottom: 12px;">
                                    <span class="badge primary"><?= htmlspecialchars($project['thematique'] ?? 'Recherche') ?></span>
                                    <?= $statusBadge ?>
                                </div>
                                
                                <!-- Titre -->
                                <h3 style="margin: 0 0 12px;">
                                    <a href="index.php?url=project/detail/<?= $project['id_project'] ?>">
                                        <?= htmlspecialchars($project['titre']) ?>
                                    </a>
                                </h3>
                                
                                <!-- Description -->
                                <p style="color: var(--muted); margin: 0 0 16px; flex-grow: 1; line-height: 1.6;">
                                    <?= htmlspecialchars(mb_substr($project['description'] ?? '', 0, 120)) ?><?= mb_strlen($project['description'] ?? '') > 120 ? '...' : '' ?>
                                </p>
                                
                                <!-- Métadonnées -->
                                <div style="padding-top: 16px; border-top: 1px solid var(--border);">
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-size: 0.9rem;">
                                        <i class="fas fa-user" style="color: var(--primary); width: 20px;"></i>
                                        <span style="color: var(--muted);">
                                            <?= !empty($project['resp_nom']) ? htmlspecialchars($project['resp_nom'] . ' ' . $project['resp_prenom']) : 'Non assigné' ?>
                                        </span>
                                    </div>
                                    
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-size: 0.9rem;">
                                        <i class="fas fa-users" style="color: var(--primary); width: 20px;"></i>
                                        <span style="color: var(--muted);">
                                            <?= count($project['membres_associes'] ?? []) ?> membre<?= count($project['membres_associes'] ?? []) > 1 ? 's' : '' ?>
                                        </span>
                                    </div>
                                    
                                    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem;">
                                        <i class="fas fa-coins" style="color: var(--primary); width: 20px;"></i>
                                        <span style="color: var(--muted);">
                                            <?= htmlspecialchars($project['type_financement'] ?? 'Non spécifié') ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Lien -->
                                <a href="index.php?url=project/detail/<?= $project['id_project'] ?>" 
                                   class="btn ghost" 
                                   style="margin-top: 16px; justify-content: center;">
                                    <i class="fas fa-arrow-right"></i> Voir la fiche détaillée
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state" style="text-align: center; padding: 60px; background: var(--bg); border-radius: 12px;">
                        <i class="fas fa-project-diagram" style="font-size: 4rem; opacity: 0.3; margin-bottom: 16px; display: block;"></i>
                        <h3 style="margin: 0 0 8px;">Aucun projet trouvé</h3>
                        <p style="color: var(--muted); margin: 0;">
                            <?php if ($filterByUser): ?>
                                Vous ne participez à aucun projet pour le moment
                            <?php elseif ($filterThematique || $filterEncadrant || $filterStatut): ?>
                                Aucun projet ne correspond à vos critères de recherche
                            <?php else: ?>
                                Aucun projet disponible
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <script>
            document.getElementById('applyFilter').addEventListener('click', function() {
                const thematique = document.querySelector('select[name="thematique"]').value;
                const encadrant = document.querySelector('select[name="encadrant"]').value;
                const statut = document.querySelector('select[name="statut"]').value;

                document.querySelectorAll('.project-card').forEach(card => {
                    const matchThematique = !thematique || card.dataset.thematique === thematique;
                    const matchEncadrant = !encadrant || card.dataset.responsable === encadrant;
                    const matchStatut = !statut || card.dataset.statut === statut;

                    card.style.display = (matchThematique && matchEncadrant && matchStatut) ? 'flex' : 'none';
                });
            });

            document.getElementById('resetFilter').addEventListener('click', function() {
                document.querySelector('select[name="thematique"]').value = '';
                document.querySelector('select[name="encadrant"]').value = '';
                document.querySelector('select[name="statut"]').value = '';

                document.querySelectorAll('.project-card').forEach(card => {
                    card.style.display = 'flex';
                });
            });
            </script>
        <?php
        FooterView::render($settings);
    }
    
    /**
     * Fiche détaillée avec publications associées
     */
    public static function renderDetail(array $data) {
        $settings = $data['settings'] ?? [];
        $project = $data['project'] ?? [];
        $members = $data['members'] ?? [];
        $publications = $data['publications'] ?? [];
        $page = $data['currentPage'] ?? 1;
        $total = $data['totalPages'] ?? 1;
        
        HeaderView::render($settings, $project['titre'] ?? '');
        MenuView::render('projects');
        ?>
        
        <section class="section">
            <div class="container">
                <div style="margin-bottom: 24px;">
                    <a href="index.php?url=project" class="link">
                        <i class="fas fa-arrow-left"></i> Retour au catalogue
                    </a>
                </div>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px;">
                    <!-- Colonne principale -->
                    <div>
                        <div class="content-card" style="padding: 32px; margin-bottom: 24px;">
                            <div style="margin-bottom: 16px;">
                                <span class="badge primary"><?= htmlspecialchars($project['thematique'] ?? 'Recherche') ?></span>
                                <?php
                                switch ($project['statut'] ?? '') {
                                    case 'en_cours':
                                        echo '<span class="badge success" style="margin-left: 8px;">En cours</span>';
                                        break;
                                    case 'termine':
                                        echo '<span class="badge" style="margin-left: 8px;">Terminé</span>';
                                        break;
                                    case 'soumis':
                                        echo '<span class="badge warning" style="margin-left: 8px;">Soumis</span>';
                                        break;
                                }
                                ?>
                            </div>
                            
                            <h1 style="margin: 0 0 24px;"><?= htmlspecialchars($project['titre']) ?></h1>
                            
                            <h2 style="font-size: 1.3rem; margin: 0 0 12px;">Description</h2>
                            <p style="white-space: pre-line; line-height: 1.6;">
                                <?= htmlspecialchars($project['description'] ?? 'Aucune description disponible') ?>
                            </p>
                        </div>
                        
                        <!-- Membres du projet -->
                        <?php if (!empty($members)): ?>
                            <div class="content-card" style="padding: 32px; margin-bottom: 24px;">
                                <h2 style="margin: 0 0 20px; font-size: 1.3rem;">
                                    <i class="fas fa-users"></i> Membres du projet (<?= count($members) ?>)
                                </h2>
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 16px;">
                                    <?php foreach ($members as $member): ?>
                                        <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: var(--bg); border-radius: 8px;">
                                            <img src="assets/images/users/<?= htmlspecialchars($member['photo'] ?? 'default.jpg') ?>" 
                                                 alt="Photo" 
                                                 style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;">
                                            <div style="flex: 1; min-width: 0;">
                                                <div style="font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    <?= htmlspecialchars($member['nom'] . ' ' . $member['prenom']) ?>
                                                </div>
                                                <?php if (!empty($member['grade'])): ?>
                                                    <div style="font-size: 0.85rem; color: var(--muted);">
                                                        <?= htmlspecialchars($member['grade']) ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($member['role_projet'])): ?>
                                                    <div style="font-size: 0.8rem; color: var(--primary);">
                                                        <?= htmlspecialchars($member['role_projet']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Publications -->
                        <h2 id="publications">Publications (<?= count($publications) ?>)</h2>

                        <?php if ($publications): ?>
                            <div class="cards-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px,1fr)); gap: 24px;">
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
                                'project/'.$project['id_project'],
                                $page,
                                $total,
                                '#publications'
                            ); ?>

                        <?php else: ?>
                            <p style="color:var(--muted)">Aucune publication</p>
                        <?php endif; ?>

                        <a href="index.php?url=project" class="btn ghost" style="margin-top:24px">
                            ← Retour aux projets
                        </a>

                    </div>
                    
                    <!-- Sidebar -->
                    <div>
                        <div class="content-card" style="padding: 24px; margin-bottom: 24px;">
                            <h3 style="margin: 0 0 16px; font-size: 1.1rem;">
                                <i class="fas fa-info-circle"></i> Informations
                            </h3>
                            
                            <?php if (!empty($project['resp_nom'])): ?>
                                <div style="margin-bottom: 16px;">
                                    <strong style="display: block; margin-bottom: 4px;">Responsable</strong>
                                    <p style="margin: 0; color: var(--muted);">
                                        <?= htmlspecialchars($project['resp_nom'] . ' ' . $project['resp_prenom']) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($project['nom_equipe'])): ?>
                                <div style="margin-bottom: 16px;">
                                    <strong style="display: block; margin-bottom: 4px;">Équipe</strong>
                                    <p style="margin: 0; color: var(--muted);">
                                        <?= htmlspecialchars($project['nom_equipe']) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($project['type_financement'])): ?>
                                <div style="margin-bottom: 16px;">
                                    <strong style="display: block; margin-bottom: 4px;">Type de financement</strong>
                                    <p style="margin: 0; color: var(--muted);">
                                        <?= htmlspecialchars($project['type_financement']) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($project['budget'])): ?>
                                <div style="margin-bottom: 16px;">
                                    <strong style="display: block; margin-bottom: 4px;">Budget</strong>
                                    <p style="margin: 0; color: var(--muted);">
                                        <?= number_format($project['budget'], 2, ',', ' ') ?> DA
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($project['date_debut'])): ?>
                                <div style="margin-bottom: 16px;">
                                    <strong style="display: block; margin-bottom: 4px;">Date de début</strong>
                                    <p style="margin: 0; color: var(--muted);">
                                        <?= date('d/m/Y', strtotime($project['date_debut'])) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($project['date_fin'])): ?>
                                <div style="margin-bottom: 16px;">
                                    <strong style="display: block; margin-bottom: 4px;">Date de fin</strong>
                                    <p style="margin: 0; color: var(--muted);">
                                        <?= date('d/m/Y', strtotime($project['date_fin'])) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <div>
                                <strong style="display: block; margin-bottom: 4px;">Statut</strong>
                                <?php
                                switch ($project['statut'] ?? '') {
                                    case 'en_cours':
                                        echo '<span class="badge success">En cours</span>';
                                        break;
                                    case 'termine':
                                        echo '<span class="badge">Terminé</span>';
                                        break;
                                    case 'soumis':
                                        echo '<span class="badge warning">Soumis</span>';
                                        break;
                                    default:
                                        echo '<span class="badge">Non spécifié</span>';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <!-- Contact du responsable -->
                        <?php if (!empty($project['resp_email'])): ?>
                            <div class="content-card" style="padding: 24px; background: linear-gradient(135deg, var(--primary), var(--nav)); color: white;">
                                <h3 style="margin: 0 0 12px; color: white; font-size: 1.1rem;">
                                    <i class="fas fa-envelope"></i> Contact
                                </h3>
                                <a href="mailto:<?= htmlspecialchars($project['resp_email']) ?>" 
                                   class="btn" 
                                   style="width: 100%; background: white; color: var(--primary); justify-content: center;">
                                    <i class="fas fa-paper-plane"></i> Contacter le responsable
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
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