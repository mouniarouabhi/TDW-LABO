<?php
// controllers/ProfileController.php
// VERSION CORRIGÉE - Préserve la photo existante

class ProfileController {
    private $userModel;
    private $settingModel;
    
    
    public function __construct() {
        // Vérifier que l'utilisateur est connecté
        AuthController::requireAuth();
        
        $this->userModel = new UserModel();
        $this->settingModel = new SettingModel();
    }
    
    /**
     * Afficher le formulaire d'édition du profil
     */
    public function edit() {
        $currentUser = AuthController::getCurrentUser();
        $settings = $this->settingModel->getAllSettingsAsArray();
        
        // Récupérer les détails complets
        $user = $this->userModel->getUserById($currentUser['id_user']);
        
        ProfileView::renderEdit([
            'settings' => $settings,
            'user' => $user,
            'success' => $_GET['success'] ?? null,
            'error' => $_GET['error'] ?? null
        ]);
    }
    
    /**
     * Mettre à jour le profil
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=profile/edit&error=invalid_method');
            exit;
        }
        
        $currentUser = AuthController::getCurrentUser();
        
        // ✅ Récupérer l'utilisateur actuel pour préserver les données existantes
        $existingUser = $this->userModel->getUserById($currentUser['id_user']);
        
        // Données à mettre à jour
        $data = [
            'nom' => $_POST['nom'] ?? '',
            'prenom' => $_POST['prenom'] ?? '',
            'email' => $_POST['email'] ?? '',
            'grade' => $_POST['grade'] ?? '',
            'poste' => $_POST['poste'] ?? '',
            'domaine_recherche' => $_POST['domaine_recherche'] ?? '',
            'biographie' => $_POST['biographie'] ?? '',
            'telephone' => $_POST['telephone'] ?? '',
            'photo' => $existingUser['photo'], // ✅ Préserver la photo existante par défaut
            'statut' => $existingUser['statut'] // ✅ Préserver le statut
        ];
        
        // Validation basique
        if (empty($data['nom']) || empty($data['prenom']) || empty($data['email'])) {
            header('Location: index.php?url=profile/edit&error=missing_fields');
            exit;
        }
        
        // Vérifier que l'email n'est pas déjà utilisé par un autre utilisateur
        if ($data['email'] !== $currentUser['email']) {
            $existingUserByEmail = $this->userModel->getUserByEmail($data['email']);
            if ($existingUserByEmail && $existingUserByEmail['id_user'] != $currentUser['id_user']) {
                header('Location: index.php?url=profile/edit&error=email_exists');
                exit;
            }
        }
        
        // Gestion de l'upload de photo (seulement si une nouvelle photo est uploadée)
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload = $this->handlePhotoUpload($_FILES['photo']);
            if ($upload['success']) {
                $data['photo'] = $upload['filename'];
                
                // ✅ Supprimer l'ancienne photo si ce n'est pas default.png
                if (!empty($existingUser['photo']) && $existingUser['photo'] !== 'default.png') {
                    $oldPhotoPath = 'assets/images/users/' . $existingUser['photo'];
                    if (file_exists($oldPhotoPath)) {
                        @unlink($oldPhotoPath);
                    }
                }
            } else {
                header('Location: index.php?url=profile/edit&error=upload_failed');
                exit;
            }
        }
        
        // Gestion du changement de mot de passe (optionnel)
        if (!empty($_POST['new_password'])) {
            if ($_POST['new_password'] === $_POST['confirm_password']) {
                // Vérifier l'ancien mot de passe
                if (password_verify($_POST['old_password'] ?? '', $existingUser['password'])) {
                    $data['password'] = $_POST['new_password']; // Le UserModel le hashera
                } else {
                    header('Location: index.php?url=profile/edit&error=wrong_password');
                    exit;
                }
            } else {
                header('Location: index.php?url=profile/edit&error=password_mismatch');
                exit;
            }
        }
        
        // Mettre à jour le profil
        $success = $this->userModel->updateUser($currentUser['id_user'], $data);
        
        if ($success) {
            // Mettre à jour la session
            $_SESSION['user']['nom'] = $data['nom'];
            $_SESSION['user']['prenom'] = $data['prenom'];
            $_SESSION['user']['email'] = $data['email'];
            $_SESSION['user']['photo'] = $data['photo']; // ✅ Mettre à jour la photo dans la session
            
            header('Location: index.php?url=profile/edit&success=updated');
        } else {
            header('Location: index.php?url=profile/edit&error=update_failed');
        }
        exit;
    }
    
    /**
     * Gérer l'upload de la photo de profil
     */
    private function handlePhotoUpload($file) {
        $uploadDir = 'assets/images/users/';
        
        // Créer le dossier s'il n'existe pas
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Vérifier le type de fichier
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            return ['success' => false, 'error' => 'invalid_type'];
        }
        
        // Vérifier la taille (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'error' => 'file_too_large'];
        }
        
        // Générer un nom unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('user_') . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Déplacer le fichier
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => true, 'filename' => $filename];
        } else {
            return ['success' => false, 'error' => 'upload_failed'];
        }
    }
}
?>