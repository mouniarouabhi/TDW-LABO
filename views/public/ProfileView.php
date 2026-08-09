<?php
// views/public/ProfileView.php
// VERSION CORRIGÉE - Utilise FormComponent

class ProfileView {
    
    /**
     * Formulaire d'édition du profil
     */
    public static function renderEdit(array $data) {
        $settings = $data['settings'] ?? [];
        $user = $data['user'] ?? [];
        $success = $data['success'] ?? null;
        $error = $data['error'] ?? null;
        
        HeaderView::render($settings, 'Modifier mon profil');
        MenuView::render('dashboard');
        ?>
        
        <div class="container" style="padding: 40px 0; max-width: 900px;">
            <div style="margin-bottom: 24px;">
                <a href="index.php?url=dashboard" class="link">
                    <i class="fas fa-arrow-left"></i> Retour au dashboard
                </a>
            </div>
            
            <h1 style="margin-bottom: 24px;">Modifier mon profil</h1>
            
            <!-- Messages de succès/erreur -->
            <?php if ($success === 'updated'): ?>
                <div class="alert success" style="margin-bottom: 24px;">
                    <i class="fas fa-check-circle"></i> Votre profil a été mis à jour avec succès !
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert error" style="margin-bottom: 24px;">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php
                    switch ($error) {
                        case 'missing_fields':
                            echo 'Veuillez remplir tous les champs obligatoires';
                            break;
                        case 'email_exists':
                            echo 'Cet email est déjà utilisé par un autre utilisateur';
                            break;
                        case 'wrong_password':
                            echo 'L\'ancien mot de passe est incorrect';
                            break;
                        case 'password_mismatch':
                            echo 'Les mots de passe ne correspondent pas';
                            break;
                        case 'upload_failed':
                            echo 'Erreur lors de l\'upload de la photo';
                            break;
                        default:
                            echo 'Une erreur est survenue';
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="index.php?url=profile/update" enctype="multipart/form-data">
                
                <!-- Photo de profil -->
                <div class="content-card" style="padding: 32px; margin-bottom: 24px;">
                    <h3 style="margin: 0 0 20px; padding-bottom: 12px; border-bottom: 2px solid var(--border);">
                        Photo de profil
                    </h3>
                    
                    <div style="display: flex; align-items: center; gap: 24px;">
                        <img src="assets/images/users/<?= htmlspecialchars($user['photo'] ?? 'default.png') ?>" 
                             alt="Photo actuelle" 
                             id="preview-photo"
                             class="profile-photo-preview">
                        
                        <div style="flex: 1;">
                            <?php
                            echo FormComponent::renderFile([
                                'label' => 'Changer la photo',
                                'name' => 'photo',
                                'accept' => 'image/jpeg,image/jpg,image/png,image/gif',
                                'onchange' => 'previewImage(this)',
                                'help' => 'Formats acceptés : JPG, PNG, GIF (max 5MB)'
                            ]);
                            ?>
                        </div>
                    </div>
                </div>
                
                <!-- Informations personnelles -->
                <div class="content-card" style="padding: 32px; margin-bottom: 24px;">
                    <h3 style="margin: 0 0 20px; padding-bottom: 12px; border-bottom: 2px solid var(--border);">
                        Informations personnelles
                    </h3>
                    
                    <div class="form-grid">
                        <?php
                        echo FormComponent::renderInput([
                            'label' => 'Nom',
                            'name' => 'nom',
                            'value' => $user['nom'] ?? '',
                            'required' => true
                        ]);
                        
                        echo FormComponent::renderInput([
                            'label' => 'Prénom',
                            'name' => 'prenom',
                            'value' => $user['prenom'] ?? '',
                            'required' => true
                        ]);
                        ?>
                    </div>
                    
                    <?php
                    echo FormComponent::renderInput([
                        'label' => 'Email',
                        'name' => 'email',
                        'type' => 'email',
                        'value' => $user['email'] ?? '',
                        'required' => true
                    ]);
                    
                    echo FormComponent::renderInput([
                        'label' => 'Téléphone',
                        'name' => 'telephone',
                        'type' => 'tel',
                        'value' => $user['telephone'] ?? ''
                    ]);
                    ?>
                </div>
                
                <!-- Informations professionnelles -->
                <div class="content-card" style="padding: 32px; margin-bottom: 24px;">
                    <h3 style="margin: 0 0 20px; padding-bottom: 12px; border-bottom: 2px solid var(--border);">
                        Informations professionnelles
                    </h3>
                    
                    <div class="form-grid">
                        <?php
                        echo FormComponent::renderInput([
                            'label' => 'Grade',
                            'name' => 'grade',
                            'value' => $user['grade'] ?? '',
                            'placeholder' => 'Ex: Professeur, MCF, Doctorant...'
                        ]);
                        
                        echo FormComponent::renderInput([
                            'label' => 'Poste',
                            'name' => 'poste',
                            'value' => $user['poste'] ?? '',
                            'placeholder' => 'Ex: Directeur, Responsable équipe...'
                        ]);
                        ?>
                    </div>
                    
                    <?php
                    echo FormComponent::renderInput([
                        'label' => 'Domaine de recherche',
                        'name' => 'domaine_recherche',
                        'value' => $user['domaine_recherche'] ?? '',
                        'placeholder' => 'Ex: Intelligence Artificielle, Sécurité informatique...'
                    ]);
                    
                    echo FormComponent::renderTextarea([
                        'label' => 'Biographie',
                        'name' => 'biographie',
                        'value' => $user['biographie'] ?? '',
                        'rows' => 6,
                        'placeholder' => 'Présentez vos travaux, vos intérêts de recherche...'
                    ]);
                    ?>
                </div>
                
                <!-- Changer le mot de passe (optionnel) -->
                <div class="content-card" style="padding: 32px; margin-bottom: 24px;">
                    <h3 style="margin: 0 0 20px; padding-bottom: 12px; border-bottom: 2px solid var(--border);">
                        Changer le mot de passe (optionnel)
                    </h3>
                    
                    <p style="color: var(--muted); margin-bottom: 20px;">
                        Laissez ces champs vides si vous ne souhaitez pas changer votre mot de passe
                    </p>
                    
                    <?php
                    echo FormComponent::renderInput([
                        'label' => 'Ancien mot de passe',
                        'name' => 'old_password',
                        'type' => 'password'
                    ]);
                    ?>
                    
                    <div class="form-grid">
                        <?php
                        echo FormComponent::renderInput([
                            'label' => 'Nouveau mot de passe',
                            'name' => 'new_password',
                            'type' => 'password'
                        ]);
                        
                        echo FormComponent::renderInput([
                            'label' => 'Confirmer le nouveau mot de passe',
                            'name' => 'confirm_password',
                            'type' => 'password'
                        ]);
                        ?>
                    </div>
                </div>
                
                <!-- Boutons d'action -->
                <div class="form-actions">
                    <button type="submit" class="btn primary">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                    <a href="index.php?url=dashboard" class="btn ghost">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
        
        <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-photo').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        </script>
        
        <?php
        FooterView::render($settings);
    }
}
?>