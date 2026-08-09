<?php
// views/public/UserDocumentView.php
// VERSION CORRIGÉE - Utilise TableView et FormComponent

class UserDocumentView {
    public static function render(array $data) {
        $settings = $data['settings'] ?? [];
        $documents = $data['documents'] ?? [];
        $user = $data['user'] ?? [];
        $error = $data['error'] ?? null;
        $success = $data['success'] ?? null;

        HeaderView::render($settings, 'Mes Documents');
        MenuView::render('dashboard');
        ?>
        <div class="container" style="padding: 40px 0;">
            <!-- En-tête -->
            <div class="section-header">
                <div>
                    <h1>Gérer mes documents</h1>
                    <p style="color: var(--muted);">Téléchargez et organisez vos documents personnels</p>
                </div>
                <a href="index.php?url=dashboard" class="btn ghost">
                    <i class="fas fa-arrow-left"></i> Retour au dashboard
                </a>
            </div>

            <?php if ($success): ?>
                <div class="alert success">
                    <i class="fas fa-check-circle"></i>
                    <?php
                    switch ($success) {
                        case 'uploaded':
                            echo 'Document ajouté avec succès';
                            break;
                        case 'deleted':
                            echo 'Document supprimé avec succès';
                            break;
                        default:
                            echo 'Opération réussie';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Formulaire upload -->
            <div class="content-card" style="padding: 32px; margin-bottom: 24px;">
                <h2 style="margin: 0 0 24px;">Ajouter un document</h2>
                <form method="POST" enctype="multipart/form-data" class="upload-form">
                    <div class="form-grid">
                        <?php
                        echo FormComponent::renderInput([
                            'label' => 'Titre du document',
                            'name' => 'titre',
                            'required' => true,
                            'placeholder' => 'Ex: CV, Diplôme, Attestation...'
                        ]);
                        
                        echo FormComponent::renderSelect([
                            'label' => 'Type de document',
                            'name' => 'type_document',
                            'required' => true,
                            'options' => [
                                'cv' => 'CV',
                                'diplome' => 'Diplôme',
                                'certificat' => 'Certificat',
                                'rapport' => 'Rapport',
                                'article' => 'Article',
                                'presentation' => 'Présentation',
                                'these' => 'Thèse',
                                'memoire' => 'Mémoire',
                                'attestation' => 'Attestation',
                                'autre' => 'Autre'
                            ]
                        ]);
                        ?>
                    </div>
                    
                    <?php
                    echo FormComponent::renderTextarea([
                        'label' => 'Description (optionnelle)',
                        'name' => 'description',
                        'rows' => 3,
                        'placeholder' => 'Décrivez brièvement le contenu du document...'
                    ]);
                    
                    echo FormComponent::renderFile([
                        'label' => 'Fichier',
                        'name' => 'document',
                        'required' => true,
                        'help' => 'Formats acceptés : PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, ZIP (Max 10MB)'
                    ]);
                    ?>
                    
                    <button type="submit" class="btn primary">
                        <i class="fas fa-upload"></i> Télécharger le document
                    </button>
                </form>
            </div>

            <!-- Liste des documents -->
            <div class="content-card">
                <div class="card-header">
                    <h2>Mes documents (<?= count($documents) ?>)</h2>
                </div>
                
                <div class="card-body">
                    <?php if (!empty($documents)): ?>
                        <?php
                        // Préparer les données pour TableView
                        $tableData = [];
                        foreach ($documents as $doc) {
                            $iconClass = DocumentModel::getIcon($doc['extension']);
                            
                            $tableData[] = [
                                'id' => $doc['id_document'],
                                'fichier_html' => '<div style="display: flex; align-items: center; gap: 8px;">
                                                        <i class="' . $iconClass . '" style="font-size: 1.5rem;"></i>
                                                        <div>
                                                            <div style="font-weight: 600;">' . htmlspecialchars($doc['titre']) . '</div>
                                                            <div style="color: var(--muted); font-size: 0.9rem;">' . htmlspecialchars($doc['nom_original']) . '</div>
                                                        </div>
                                                    </div>',
                                'type' => ucfirst(str_replace('_', ' ', $doc['type_document'])),
                                'taille' => DocumentModel::formatSize($doc['taille_fichier']),
                                'date_upload' => date('d/m/Y', strtotime($doc['date_upload']))
                            ];
                        }
                        
                        TableView::render([
                            'id' => 'documents-table',
                            'columns' => [
                                'fichier' => 'Document',
                                'type' => 'Type',
                                'taille' => 'Taille',
                                'date_upload' => 'Date'
                            ],
                            'data' => $tableData,
                            'actions' => [
                                [
                                    'label' => 'Télécharger',
                                    'url' => 'assets/documents/documents/{id}',
                                    'icon' => 'fas fa-download',
                                    'class' => 'btn-action primary',
                                    'download' => true
                                ],
                                [
                                    'label' => 'Supprimer',
                                    'url' => 'index.php?url=userDocument&delete={id}',
                                    'icon' => 'fas fa-trash',
                                    'class' => 'btn-action delete',
                                    'confirm' => true
                                ]
                            ],
                            'searchable' => true,
                            'empty_message' => 'Aucun document'
                        ]);
                        ?>
                        
                        <!-- Quota d'espace utilisé -->
                        <?php
                        $totalSize = array_sum(array_column($documents, 'taille_fichier'));
                        $maxQuota = 50 * 1024 * 1024; // 50 MB
                        $percentUsed = ($totalSize / $maxQuota) * 100;
                        ?>
                        <div class="storage-info" style="margin-top: 24px; padding: 20px; background: var(--bg); border-radius: 8px;">
                            <h4 style="margin: 0 0 12px;">
                                <i class="fas fa-hdd"></i> Espace de stockage utilisé
                            </h4>
                            <div class="progress-bar" style="height: 24px; background: var(--border); border-radius: 12px; overflow: hidden;">
                                <div class="progress-fill" 
                                     style="width: <?= min($percentUsed, 100) ?>%; 
                                            height: 100%; 
                                            background: linear-gradient(90deg, var(--primary), var(--secondary)); 
                                            transition: width 0.3s ease;
                                            display: flex;
                                            align-items: center;
                                            justify-content: center;
                                            color: white;
                                            font-size: 0.85rem;
                                            font-weight: 600;">
                                    <?= number_format($percentUsed, 1) ?>%
                                </div>
                            </div>
                            <p style="margin: 8px 0 0; color: var(--muted); font-size: 0.9rem;">
                                <?= DocumentModel::formatSize($totalSize) ?> utilisé sur <?= DocumentModel::formatSize($maxQuota) ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-file-alt"></i>
                            <p>Aucun document téléchargé</p>
                            <p style="color: var(--muted); font-size: 0.9rem;">
                                Utilisez le formulaire ci-dessus pour ajouter vos premiers documents
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php
        FooterView::render($settings);
    }
}
?>