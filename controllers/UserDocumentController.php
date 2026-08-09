<?php
// controllers/UserDocumentController.php

class UserDocumentController {
    private $documentModel;

    public function __construct() {
        AuthController::requireAuth();
        $this->documentModel = new DocumentModel();
    }

    /**
     * Liste et gestion des documents
     */
    public function index() {
        $currentUser = AuthController::getCurrentUser();
        $documents = $this->documentModel->getUserDocuments($currentUser['id_user']);
        $settings = (new SettingModel())->getAllSettingsAsArray();

        // Upload
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
            $file = $_FILES['document'];
            $titre = $_POST['titre'] ?? $file['name'];
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $taille = $file['size'];
            $nom_fichier = uniqid() . '.' . $extension;

            if (!DocumentModel::isValidExtension($extension)) {
                $error = "Extension non autorisée.";
            } elseif (!DocumentModel::isValidSize($taille)) {
                $error = "Fichier trop volumineux.";
            } elseif (!$this->documentModel->canUpload($currentUser['id_user'], $taille)) {
                $error = "Quota dépassé.";
            } else {
                $uploadDir = __DIR__ . '/../assets/documents/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                if (move_uploaded_file($file['tmp_name'], $uploadDir . $nom_fichier)) {
                    $this->documentModel->createDocument([
                        'id_user' => $currentUser['id_user'],
                        'titre' => $titre,
                        'type_document' => 'autre',
                        'nom_fichier' => $nom_fichier,
                        'nom_original' => $file['name'],
                        'taille_fichier' => $taille,
                        'extension' => $extension,
                        'visibilite' => 'prive'
                    ]);
                    header('Location: index.php?url=userDocument');
                    exit;
                } else {
                    $error = "Erreur lors de l'upload.";
                }
            }
        }

        // Suppression
        if (isset($_GET['delete'])) {
            $docId = (int)$_GET['delete'];
            $doc = $this->documentModel->getDocumentById($docId);
            if ($doc && $doc['id_user'] == $currentUser['id_user']) {
                $this->documentModel->deleteDocument($docId);
                header('Location: index.php?url=userDocument');
                exit;
            }
        }

        UserDocumentView::render([
            'settings' => $settings,
            'documents' => $documents,
            'user' => $currentUser,
            'error' => $error ?? null
        ]);
    }
}
?>
