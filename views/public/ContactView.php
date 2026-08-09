<?php
// views/public/ContactView.php
// VERSION CORRIGÉE - Utilise FormComponent

class ContactView {
    
    public static function render(array $data) {
        $settings = $data['settings'] ?? [];
        $success = $data['success'] ?? null;
        $error = $data['error'] ?? null;
        
        HeaderView::render($settings, 'Contact');
        MenuView::render('contact');
        ?>
        
        <section class="section">
            <div class="container">
                <h1>Contactez-nous</h1>
                <p style="color: var(--muted); margin-bottom: 32px;">
                    Vous souhaitez collaborer avec le laboratoire ? Envoyez-nous un message
                </p>
                
                <?php if ($success === 'sent'): ?>
                    <div class="alert success">
                        <i class="fas fa-check-circle"></i> Votre message a été envoyé avec succès !
                    </div>
                <?php elseif ($error === 'empty_fields'): ?>
                    <div class="alert error">
                        <i class="fas fa-exclamation-circle"></i> Veuillez remplir tous les champs obligatoires
                    </div>
                <?php elseif ($error === 'invalid_email'): ?>
                    <div class="alert error">
                        <i class="fas fa-exclamation-circle"></i> Adresse email invalide
                    </div>
                <?php elseif ($error): ?>
                    <div class="alert error">
                        <i class="fas fa-exclamation-circle"></i> Une erreur est survenue
                    </div>
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px; margin-top: 32px;">
                    <!-- Formulaire -->
                    <div class="content-card" style="padding: 32px;">
                        <h3 style="margin: 0 0 24px;">Formulaire de contact</h3>
                        
                        <form method="POST" action="index.php?url=contact/envoyer">
                            <?php
                            // ✅ Utilisation de FormComponent
                            echo FormComponent::renderInput([
                                'label' => 'Nom complet',
                                'name' => 'nom',
                                'type' => 'text',
                                'required' => true,
                                'placeholder' => 'Votre nom complet'
                            ]);
                            
                            echo FormComponent::renderInput([
                                'label' => 'Email',
                                'name' => 'email',
                                'type' => 'email',
                                'required' => true,
                                'placeholder' => 'votre.email@exemple.com'
                            ]);
                            
                            echo FormComponent::renderInput([
                                'label' => 'Sujet',
                                'name' => 'sujet',
                                'type' => 'text',
                                'required' => true,
                                'placeholder' => 'Objet de votre message'
                            ]);
                            
                            echo FormComponent::renderTextarea([
                                'label' => 'Message',
                                'name' => 'message',
                                'rows' => 6,
                                'required' => true,
                                'placeholder' => 'Votre message...'
                            ]);
                            ?>
                            
                            <button type="submit" class="btn primary" style="width: 100%;">
                                <i class="fas fa-paper-plane"></i> Envoyer le message
                            </button>
                        </form>
                    </div>
                    
                    <!-- Informations de contact -->
                    <div>
                        <div class="content-card" style="padding: 24px; margin-bottom: 16px;">
                            <h3 style="margin: 0 0 16px;">Informations de contact</h3>
                            
                            <?php if (!empty($settings['adresse_postale'])): ?>
                                <div style="margin-bottom: 16px;">
                                    <div style="color: var(--muted); font-size: 0.9rem; margin-bottom: 4px;">
                                        <i class="fas fa-map-marker-alt"></i> Adresse
                                    </div>
                                    <div style="font-weight: 600;">
                                        <?= nl2br(htmlspecialchars($settings['adresse_postale'])) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($settings['email_contact'])): ?>
                                <div style="margin-bottom: 16px;">
                                    <div style="color: var(--muted); font-size: 0.9rem; margin-bottom: 4px;">
                                        <i class="fas fa-envelope"></i> Email
                                    </div>
                                    <div style="font-weight: 600;">
                                        <a href="mailto:<?= htmlspecialchars($settings['email_contact']) ?>">
                                            <?= htmlspecialchars($settings['email_contact']) ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($settings['telephone'])): ?>
                                <div style="margin-bottom: 16px;">
                                    <div style="color: var(--muted); font-size: 0.9rem; margin-bottom: 4px;">
                                        <i class="fas fa-phone"></i> Téléphone
                                    </div>
                                    <div style="font-weight: 600;">
                                        <?= htmlspecialchars($settings['telephone']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($settings['reseaux_facebook']) || !empty($settings['reseaux_twitter']) || !empty($settings['reseaux_linkedin'])): ?>
                            <div class="content-card" style="padding: 24px;">
                                <h3 style="margin: 0 0 16px;">Suivez-nous</h3>
                                <div style="display: flex; gap: 12px;">
                                    <?php if (!empty($settings['reseaux_facebook'])): ?>
                                        <a href="<?= htmlspecialchars($settings['reseaux_facebook']) ?>" 
                                           target="_blank" 
                                           class="btn ghost"
                                           style="padding: 8px 12px;">
                                            <i class="fab fa-facebook"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($settings['reseaux_twitter'])): ?>
                                        <a href="<?= htmlspecialchars($settings['reseaux_twitter']) ?>" 
                                           target="_blank" 
                                           class="btn ghost"
                                           style="padding: 8px 12px;">
                                            <i class="fab fa-twitter"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($settings['reseaux_linkedin'])): ?>
                                        <a href="<?= htmlspecialchars($settings['reseaux_linkedin']) ?>" 
                                           target="_blank" 
                                           class="btn ghost"
                                           style="padding: 8px 12px;">
                                            <i class="fab fa-linkedin"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        
        <?php
        FooterView::render($settings);
    }
}
?>