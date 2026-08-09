<?php
// views/admin/AdminSettingView.php

class AdminSettingView {
    
    public static function render(array $data) {
        $settings = $data['settings'] ?? [];
        
        AdminHeaderView::render($settings, 'Paramètres généraux');
        AdminSidebarView::render('settings');
        ?>
        
        <div class="admin-page-header">
            <h1>Paramètres généraux de l'application</h1>
            <div class="breadcrumb">
                <a href="index.php?url=admin">Administration</a>
                <span>/</span>
                <span>Paramètres</span>
            </div>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?php
                if ($_GET['success'] === 'saved') {
                    $count = $_GET['count'] ?? 0;
                    echo "$count paramètres enregistrés avec succès";
                } elseif ($_GET['success'] === 'backup') {
                    echo 'Sauvegarde créée avec succès';
                } elseif ($_GET['success'] === 'restore') {
                    echo 'Base de données restaurée avec succès';
                }
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <?php
                if ($_GET['error'] === 'partial_save') {
                    $count = $_GET['count'] ?? 0;
                    echo "Sauvegarde partielle: $count paramètres enregistrés";
                } elseif ($_GET['error'] === 'backup_failed') {
                    echo 'Erreur lors de la sauvegarde';
                } elseif ($_GET['error'] === 'restore_failed') {
                    echo 'Erreur lors de la restauration';
                } else {
                    echo 'Une erreur est survenue';
                }
                ?>
            </div>
        <?php endif; ?>
        
        <!-- FORMULAIRE -->
        <form method="POST" action="index.php?url=admin/settings" enctype="multipart/form-data">
            
            <!-- Informations générales -->
            <div class="content-card">
                <div class="card-header">
                    <h2><i class="fas fa-info-circle"></i> Informations générales</h2>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nom_laboratoire">Nom du laboratoire</label>
                            <input type="text" id="nom_laboratoire" name="nom_laboratoire" 
                                   value="<?= htmlspecialchars($settings['nom_laboratoire'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email_contact">Email de contact</label>
                            <input type="email" id="email_contact" name="email_contact" 
                                   value="<?= htmlspecialchars($settings['email_contact'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="telephone">Téléphone</label>
                            <input type="text" id="telephone" name="telephone" 
                                   value="<?= htmlspecialchars($settings['telephone'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="adresse_postale">Adresse postale</label>
                            <input type="text" id="adresse_postale" name="adresse_postale" 
                                   value="<?= htmlspecialchars($settings['adresse_postale'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="site_universite">Site de l'université</label>
                            <input type="url" id="site_universite" name="site_universite" 
                                   value="<?= htmlspecialchars($settings['site_universite'] ?? '') ?>"
                                   placeholder="https://...">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description_laboratoire">Description du laboratoire</label>
                        <textarea id="description_laboratoire" name="description_laboratoire" 
                                  rows="4"><?= htmlspecialchars($settings['description_laboratoire'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Images et logos -->
            <div class="content-card">
                <div class="card-header">
                    <h2><i class="fas fa-images"></i> Images et logos</h2>
                </div>
                <div class="card-body">
                    <p class="form-help-text">
                        <i class="fas fa-info-circle"></i> 
                        Formats acceptés : PNG, JPG, JPEG, GIF. Taille max : 2 MB
                    </p>
                    
                    <!-- Logo Laboratoire -->
                    <div class="image-upload-section">
                        <h3>Logo du laboratoire</h3>
                        <div class="image-upload-container">
                            <div class="image-preview-box">
                                <?php
                                $logoLabo = $settings['logo_laboratoire'] ?? '';
                                $logoLaboPath = !empty($logoLabo) ? 'assets/images/' . $logoLabo : '';
                                if (!empty($logoLabo) && file_exists($logoLaboPath)):
                                ?>
                                    <img src="<?= $logoLaboPath ?>" alt="Logo laboratoire" class="preview-image">
                                    <div class="image-info">
                                        <span class="image-filename"><?= htmlspecialchars($logoLabo) ?></span>
                                    </div>
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-image"></i>
                                        <p>Aucune image</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="image-upload-controls">
                                <label for="logo_laboratoire_file" class="btn primary btn-file">
                                    <i class="fas fa-upload"></i> Choisir une image
                                </label>
                                <input type="file" 
                                       id="logo_laboratoire_file" 
                                       name="logo_laboratoire_file" 
                                       accept="image/png,image/jpeg,image/jpg,image/gif"
                                       class="file-input-hidden"
                                       onchange="previewImage(this)">
                                
                                <!-- Hidden field to preserve current value if no new file -->
                                <input type="hidden" name="logo_laboratoire" value="<?= htmlspecialchars($logoLabo) ?>">
                                
                                <small class="form-help-text">
                                    Image actuelle : <?= !empty($logoLabo) ? htmlspecialchars($logoLabo) : 'Aucune' ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Logo Université -->
                    <div class="image-upload-section">
                        <h3>Logo de l'université</h3>
                        <div class="image-upload-container">
                            <div class="image-preview-box">
                                <?php
                                $logoUniv = $settings['logo_universite'] ?? '';
                                $logoUnivPath = !empty($logoUniv) ? 'assets/images/' . $logoUniv : '';
                                if (!empty($logoUniv) && file_exists($logoUnivPath)):
                                ?>
                                    <img src="<?= $logoUnivPath ?>" alt="Logo université" class="preview-image">
                                    <div class="image-info">
                                        <span class="image-filename"><?= htmlspecialchars($logoUniv) ?></span>
                                    </div>
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-image"></i>
                                        <p>Aucune image</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="image-upload-controls">
                                <label for="logo_universite_file" class="btn primary btn-file">
                                    <i class="fas fa-upload"></i> Choisir une image
                                </label>
                                <input type="file" 
                                       id="logo_universite_file" 
                                       name="logo_universite_file" 
                                       accept="image/png,image/jpeg,image/jpg,image/gif"
                                       class="file-input-hidden"
                                       onchange="previewImage(this)">
                                
                                <input type="hidden" name="logo_universite" value="<?= htmlspecialchars($logoUniv) ?>">
                                
                                <small class="form-help-text">
                                    Image actuelle : <?= !empty($logoUniv) ? htmlspecialchars($logoUniv) : 'Aucune' ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Image de présentation -->
                    <div class="image-upload-section">
                        <h3>Image de présentation</h3>
                        <div class="image-upload-container">
                            <div class="image-preview-box large">
                                <?php
                                $imagePres = $settings['image_presentation'] ?? '';
                                $imagePresPath = !empty($imagePres) ? 'assets/images/' . $imagePres : '';
                                if (!empty($imagePres) && file_exists($imagePresPath)):
                                ?>
                                    <img src="<?= $imagePresPath ?>" alt="Image présentation" class="preview-image">
                                    <div class="image-info">
                                        <span class="image-filename"><?= htmlspecialchars($imagePres) ?></span>
                                    </div>
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-image"></i>
                                        <p>Aucune image</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="image-upload-controls">
                                <label for="image_presentation_file" class="btn primary btn-file">
                                    <i class="fas fa-upload"></i> Choisir une image
                                </label>
                                <input type="file" 
                                       id="image_presentation_file" 
                                       name="image_presentation_file" 
                                       accept="image/png,image/jpeg,image/jpg,image/gif"
                                       class="file-input-hidden"
                                       onchange="previewImage(this)">
                                
                                <input type="hidden" name="image_presentation" value="<?= htmlspecialchars($imagePres) ?>">
                                
                                <small class="form-help-text">
                                    Image actuelle : <?= !empty($imagePres) ? htmlspecialchars($imagePres) : 'Aucune' ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Réseaux sociaux -->
            <div class="content-card">
                <div class="card-header">
                    <h2><i class="fas fa-share-alt"></i> Réseaux sociaux</h2>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="reseaux_facebook">
                                <i class="fab fa-facebook social-icon facebook"></i> Facebook
                            </label>
                            <input type="url" id="reseaux_facebook" name="reseaux_facebook" 
                                   value="<?= htmlspecialchars($settings['reseaux_facebook'] ?? '') ?>"
                                   placeholder="https://facebook.com/...">
                        </div>
                        
                        <div class="form-group">
                            <label for="reseaux_linkedin">
                                <i class="fab fa-linkedin social-icon linkedin"></i> LinkedIn
                            </label>
                            <input type="url" id="reseaux_linkedin" name="reseaux_linkedin" 
                                   value="<?= htmlspecialchars($settings['reseaux_linkedin'] ?? '') ?>"
                                   placeholder="https://linkedin.com/company/...">
                        </div>
                        
                        <div class="form-group">
                            <label for="reseaux_twitter">
                                <i class="fab fa-x-twitter social-icon twitter"></i> Twitter / X
                            </label>
                            <input type="url" id="reseaux_twitter" name="reseaux_twitter" 
                                   value="<?= htmlspecialchars($settings['reseaux_twitter'] ?? '') ?>"
                                   placeholder="https://x.com/...">
                        </div>
                        
                        <div class="form-group">
                            <label for="reseaux_youtube">
                                <i class="fab fa-youtube social-icon youtube"></i> YouTube
                            </label>
                            <input type="url" id="reseaux_youtube" name="reseaux_youtube" 
                                   value="<?= htmlspecialchars($settings['reseaux_youtube'] ?? '') ?>"
                                   placeholder="https://youtube.com/@...">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Personnalisation du thème -->
            <div class="content-card">
                <div class="card-header">
                    <h2><i class="fas fa-palette"></i> Personnalisation du thème</h2>
                </div>
                <div class="card-body">
                    <p class="theme-info">
                        <i class="fas fa-info-circle"></i> 
                        Personnalisez les couleurs de votre interface administrative
                    </p>
                    
                    <div class="form-grid">
                        <!-- Couleur Primaire -->
                        <div class="form-group">
                            <div class="color-picker-card">
                                <label class="color-label">
                                    <i class="fas fa-circle"></i>
                                    Couleur primaire
                                </label>
                                <div class="color-input-group">
                                    <div class="color-preview-square" 
                                         id="preview_theme_couleur_primaire" 
                                         style="background-color: <?= htmlspecialchars($settings['theme_couleur_primaire'] ?? '#1e4fb3') ?>">
                                    </div>
                                    
                                    <div class="color-input-wrapper">
                                        <input type="text" 
                                               id="theme_couleur_primaire" 
                                               name="theme_couleur_primaire" 
                                               class="color-text-input"
                                               value="<?= htmlspecialchars($settings['theme_couleur_primaire'] ?? '#1e4fb3') ?>"
                                               placeholder="1e4fb3"
                                               pattern="^#?[0-9A-Fa-f]{6}$">
                                    </div>
                                    
                                    <button type="button" class="color-picker-btn" onclick="document.getElementById('color_picker_primaire').click()">
                                        <i class="fas fa-palette"></i>
                                        Choisir
                                    </button>
                                    <input type="color" 
                                           id="color_picker_primaire" 
                                           class="color-picker-hidden"
                                           value="<?= htmlspecialchars($settings['theme_couleur_primaire'] ?? '#1e4fb3') ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Couleur Secondaire -->
                        <div class="form-group">
                            <div class="color-picker-card">
                                <label class="color-label">
                                    <i class="fas fa-circle"></i>
                                    Couleur secondaire
                                </label>
                                <div class="color-input-group">
                                    <div class="color-preview-square" 
                                         id="preview_theme_couleur_secondaire" 
                                         style="background-color: <?= htmlspecialchars($settings['theme_couleur_secondaire'] ?? '#f0b429') ?>">
                                    </div>
                                    
                                    <div class="color-input-wrapper">
                                        <input type="text" 
                                               id="theme_couleur_secondaire" 
                                               name="theme_couleur_secondaire" 
                                               class="color-text-input"
                                               value="<?= htmlspecialchars($settings['theme_couleur_secondaire'] ?? '#f0b429') ?>"
                                               placeholder="f0b429"
                                               pattern="^#?[0-9A-Fa-f]{6}$">
                                    </div>
                                    
                                    <button type="button" class="color-picker-btn" onclick="document.getElementById('color_picker_secondaire').click()">
                                        <i class="fas fa-palette"></i>
                                        Choisir
                                    </button>
                                    <input type="color" 
                                           id="color_picker_secondaire" 
                                           class="color-picker-hidden"
                                           value="<?= htmlspecialchars($settings['theme_couleur_secondaire'] ?? '#f0b429') ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Couleur Navigation -->
                        <div class="form-group">
                            <div class="color-picker-card">
                                <label class="color-label">
                                    <i class="fas fa-circle"></i>
                                    Couleur navigation
                                </label>
                                <div class="color-input-group">
                                    <div class="color-preview-square" 
                                         id="preview_theme_couleur_nav" 
                                         style="background-color: <?= htmlspecialchars($settings['theme_couleur_nav'] ?? '#0f2d62') ?>">
                                    </div>
                                    
                                    <div class="color-input-wrapper">
                                        <input type="text" 
                                               id="theme_couleur_nav" 
                                               name="theme_couleur_nav" 
                                               class="color-text-input"
                                               value="<?= htmlspecialchars($settings['theme_couleur_nav'] ?? '#0f2d62') ?>"
                                               placeholder="0f2d62"
                                               pattern="^#?[0-9A-Fa-f]{6}$">
                                    </div>
                                    
                                    <button type="button" class="color-picker-btn" onclick="document.getElementById('color_picker_nav').click()">
                                        <i class="fas fa-palette"></i>
                                        Choisir
                                    </button>
                                    <input type="color" 
                                           id="color_picker_nav" 
                                           class="color-picker-hidden"
                                           value="<?= htmlspecialchars($settings['theme_couleur_nav'] ?? '#0f2d62') ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Couleur Footer -->
                        <div class="form-group">
                            <div class="color-picker-card">
                                <label class="color-label">
                                    <i class="fas fa-circle"></i>
                                    Couleur footer
                                </label>
                                <div class="color-input-group">
                                    <div class="color-preview-square" 
                                         id="preview_theme_couleur_footer" 
                                         style="background-color: <?= htmlspecialchars($settings['theme_couleur_footer'] ?? '#0f1e35') ?>">
                                    </div>
                                    
                                    <div class="color-input-wrapper">
                                        <input type="text" 
                                               id="theme_couleur_footer" 
                                               name="theme_couleur_footer" 
                                               class="color-text-input"
                                               value="<?= htmlspecialchars($settings['theme_couleur_footer'] ?? '#0f1e35') ?>"
                                               placeholder="0f1e35"
                                               pattern="^#?[0-9A-Fa-f]{6}$">
                                    </div>
                                    
                                    <button type="button" class="color-picker-btn" onclick="document.getElementById('color_picker_footer').click()">
                                        <i class="fas fa-palette"></i>
                                        Choisir
                                    </button>
                                    <input type="color" 
                                           id="color_picker_footer" 
                                           class="color-picker-hidden"
                                           value="<?= htmlspecialchars($settings['theme_couleur_footer'] ?? '#0f1e35') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Live Preview -->
                    <div class="theme-live-preview">
                        <h3>Aperçu en direct</h3>
                        <div class="preview-interface">
                            <div class="preview-element" id="preview_primary" style="background-color: <?= htmlspecialchars($settings['theme_couleur_primaire'] ?? '#1e4fb3') ?>">
                                <div class="preview-element-label">Primaire</div>
                                <div class="preview-element-sample">Boutons principaux</div>
                            </div>
                            <div class="preview-element" id="preview_secondary" style="background-color: <?= htmlspecialchars($settings['theme_couleur_secondaire'] ?? '#f0b429') ?>">
                                <div class="preview-element-label">Secondaire</div>
                                <div class="preview-element-sample">Accents & badges</div>
                            </div>
                            <div class="preview-element" id="preview_nav" style="background-color: <?= htmlspecialchars($settings['theme_couleur_nav'] ?? '#0f2d62') ?>">
                                <div class="preview-element-label">Navigation</div>
                                <div class="preview-element-sample">Barre de menu</div>
                            </div>
                            <div class="preview-element" id="preview_footer" style="background-color: <?= htmlspecialchars($settings['theme_couleur_footer'] ?? '#0f1e35') ?>">
                                <div class="preview-element-label">Footer</div>
                                <div class="preview-element-sample">Pied de page</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Boutons d'action -->
            <div class="form-actions">
                <button type="submit" name="submit" class="btn primary btn-large">
                    <i class="fas fa-save"></i>
                    Enregistrer tous les paramètres
                </button>
                <a href="index.php?url=admin" class="btn ghost">
                    <i class="fas fa-times"></i>
                    Annuler
                </a>
            </div>
            
        </form>
        
        </div><!-- .admin-main -->
        <?php
        AdminFooterView::render();
    }
}
?>