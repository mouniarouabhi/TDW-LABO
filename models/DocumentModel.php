<?php
/* ===============================================
   MODÈLE DocumentModel.php
   =============================================== */

// models/DocumentModel.php

class DocumentModel {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnexion();
    }

    /* =============================
       RÉCUPÉRATION
       ============================= */

    /**
     * Récupérer tous les documents d'un utilisateur
     */
    public function getUserDocuments($userId, $includeArchived = false) {
        $sql = "
            SELECT 
                d.*,
                u.nom,
                u.prenom
            FROM documents d
            LEFT JOIN users u ON d.id_user = u.id_user
            WHERE d.id_user = ?
        ";
        
        if (!$includeArchived) {
            $sql .= " AND d.statut = 'actif'";
        }
        
        $sql .= " ORDER BY d.date_upload DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer tous les documents publics
     */
    public function getPublicDocuments() {
        $sql = "
            SELECT 
                d.*,
                u.nom,
                u.prenom,
                u.poste
            FROM documents d
            LEFT JOIN users u ON d.id_user = u.id_user
            WHERE d.visibilite = 'public'
              AND d.statut = 'actif'
            ORDER BY d.date_upload DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer un document par ID
     */
    public function getDocumentById($id) {
        $sql = "
            SELECT 
                d.*,
                u.nom,
                u.prenom,
                u.email
            FROM documents d
            LEFT JOIN users u ON d.id_user = u.id_user
            WHERE d.id_document = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les documents par type
     */
    public function getDocumentsByType($type, $userId = null) {
        $sql = "
            SELECT 
                d.*,
                u.nom,
                u.prenom
            FROM documents d
            LEFT JOIN users u ON d.id_user = u.id_user
            WHERE d.type_document = ?
              AND d.statut = 'actif'
        ";
        
        $params = [$type];
        
        if ($userId !== null) {
            $sql .= " AND d.id_user = ?";
            $params[] = $userId;
        }
        
        $sql .= " ORDER BY d.date_upload DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Rechercher des documents
     */
    public function searchDocuments($search, $userId = null, $type = null) {
        $sql = "
            SELECT 
                d.*,
                u.nom,
                u.prenom
            FROM documents d
            LEFT JOIN users u ON d.id_user = u.id_user
            WHERE d.statut = 'actif'
              AND (
                d.titre LIKE ? 
                OR d.description LIKE ?
                OR d.nom_original LIKE ?
              )
        ";
        
        $params = ["%$search%", "%$search%", "%$search%"];
        
        if ($userId !== null) {
            $sql .= " AND d.id_user = ?";
            $params[] = $userId;
        }
        
        if ($type !== null) {
            $sql .= " AND d.type_document = ?";
            $params[] = $type;
        }
        
        $sql .= " ORDER BY d.date_upload DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =============================
       CRÉATION / MISE À JOUR
       ============================= */

    /**
     * Créer un document
     */
    public function createDocument($data) {
        $sql = "
            INSERT INTO documents (
                id_user,
                titre,
                description,
                type_document,
                nom_fichier,
                nom_original,
                taille_fichier,
                extension,
                visibilite,
                statut
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'actif')
        ";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['id_user'],
            $data['titre'],
            $data['description'] ?? null,
            $data['type_document'],
            $data['nom_fichier'],
            $data['nom_original'],
            $data['taille_fichier'],
            $data['extension'],
            $data['visibilite'] ?? 'prive'
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Mettre à jour un document (métadonnées uniquement)
     */
    public function updateDocument($id, $data) {
        $sql = "
            UPDATE documents SET
                titre = ?,
                description = ?,
                type_document = ?,
                visibilite = ?
            WHERE id_document = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['titre'],
            $data['description'] ?? null,
            $data['type_document'],
            $data['visibilite'] ?? 'prive',
            $id
        ]);
    }

    /**
     * Changer le statut d'un document
     */
    public function updateStatus($id, $statut) {
        $sql = "UPDATE documents SET statut = ? WHERE id_document = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$statut, $id]);
    }

    /**
     * Archiver un document
     */
    public function archiveDocument($id) {
        return $this->updateStatus($id, 'archive');
    }

    /* =============================
       SUPPRESSION
       ============================= */

    /**
     * Supprimer un document (soft delete)
     */
    public function deleteDocument($id) {
        return $this->updateStatus($id, 'supprime');
    }

    /**
     * Supprimer définitivement un document
     */
    public function permanentDeleteDocument($id) {
        $stmt = $this->db->prepare(
            "DELETE FROM documents WHERE id_document = ?"
        );
        return $stmt->execute([$id]);
    }

    /* =============================
       STATISTIQUES
       ============================= */

    /**
     * Compter les documents d'un utilisateur par type
     */
    public function countByType($userId) {
        $sql = "
            SELECT 
                type_document,
                COUNT(*) as count
            FROM documents
            WHERE id_user = ?
              AND statut = 'actif'
            GROUP BY type_document
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calculer l'espace total utilisé par un utilisateur
     */
    public function getTotalSize($userId) {
        $sql = "
            SELECT SUM(taille_fichier) as total_size
            FROM documents
            WHERE id_user = ?
              AND statut = 'actif'
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total_size'] ?? 0;
    }

    /**
     * Vérifier si un utilisateur peut uploader (quota)
     */
    public function canUpload($userId, $fileSize, $maxQuota = 52428800) { // 50 MB par défaut
        $currentSize = $this->getTotalSize($userId);
        return ($currentSize + $fileSize) <= $maxQuota;
    }

    /* =============================
       PERMISSIONS
       ============================= */

    /**
     * Vérifier si un utilisateur peut accéder à un document
     */
    public function canAccess($documentId, $userId) {
        $doc = $this->getDocumentById($documentId);
        
        if (!$doc) {
            return false;
        }
        
        // Le propriétaire peut toujours accéder
        if ($doc['id_user'] == $userId) {
            return true;
        }
        
        // Document public
        if ($doc['visibilite'] === 'public') {
            return true;
        }
        
        // Document d'équipe : vérifier si dans la même équipe
        if ($doc['visibilite'] === 'equipe') {
            return $this->isInSameTeam($doc['id_user'], $userId);
        }
        
        return false;
    }

    /**
     * Vérifier si deux utilisateurs sont dans la même équipe
     */
    private function isInSameTeam($userId1, $userId2) {
        $sql = "
            SELECT COUNT(*) as count
            FROM team_members tm1
            JOIN team_members tm2 ON tm1.id_team = tm2.id_team
            WHERE tm1.id_user = ?
              AND tm2.id_user = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId1, $userId2]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }

    /* =============================
       VALIDATIONS
       ============================= */

    /**
     * Valider l'extension d'un fichier
     */
    public static function isValidExtension($extension) {
        $allowedExtensions = [
            'pdf', 'doc', 'docx', 'txt', 
            'ppt', 'pptx', 'xls', 'xlsx',
            'jpg', 'jpeg', 'png', 'gif',
            'zip', 'rar', '7z'
        ];
        
        return in_array(strtolower($extension), $allowedExtensions);
    }

    /**
     * Valider la taille d'un fichier
     */
    public static function isValidSize($size, $maxSize = 10485760) { // 10 MB par défaut
        return $size <= $maxSize;
    }

    /**
     * Formater la taille pour affichage
     */
    public static function formatSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' octets';
        }
    }

    /**
     * Obtenir l'icône selon le type/extension
     */
    public static function getIcon($extension) {
        $icons = [
            'pdf' => 'fas fa-file-pdf text-danger',
            'doc' => 'fas fa-file-word text-primary',
            'docx' => 'fas fa-file-word text-primary',
            'txt' => 'fas fa-file-alt',
            'ppt' => 'fas fa-file-powerpoint text-warning',
            'pptx' => 'fas fa-file-powerpoint text-warning',
            'xls' => 'fas fa-file-excel text-success',
            'xlsx' => 'fas fa-file-excel text-success',
            'jpg' => 'fas fa-file-image text-info',
            'jpeg' => 'fas fa-file-image text-info',
            'png' => 'fas fa-file-image text-info',
            'gif' => 'fas fa-file-image text-info',
            'zip' => 'fas fa-file-archive text-secondary',
            'rar' => 'fas fa-file-archive text-secondary',
            '7z' => 'fas fa-file-archive text-secondary'
        ];
        
        return $icons[strtolower($extension)] ?? 'fas fa-file';
    }
}
?>