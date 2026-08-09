<?php
// models/ContactModel.php
// VERSION CORRIGÉE - Support prenom, telephone, archive

class ContactModel {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnexion();
    }

    /* =============================
       RÉCUPÉRATION DES MESSAGES
       ============================= */

    public function getAllMessages() {
        $sql = "
            SELECT 
                id_contact,
                nom,
                prenom,
                email,
                telephone,
                sujet,
                message,
                statut,
                date_envoi
            FROM contacts
            ORDER BY date_envoi DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMessageById($id) {
        $sql = "
            SELECT 
                id_contact,
                nom,
                prenom,
                email,
                telephone,
                sujet,
                message,
                statut,
                date_envoi
            FROM contacts
            WHERE id_contact = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUnreadMessages() {
        $sql = "
            SELECT 
                id_contact,
                nom,
                prenom,
                email,
                telephone,
                sujet,
                message,
                date_envoi
            FROM contacts
            WHERE statut = 'non_lu'
            ORDER BY date_envoi DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMessagesByStatus($statut) {
        $sql = "
            SELECT 
                id_contact,
                nom,
                prenom,
                email,
                telephone,
                sujet,
                message,
                statut,
                date_envoi
            FROM contacts
            WHERE statut = ?
            ORDER BY date_envoi DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$statut]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =============================
       CRÉATION / MISE À JOUR
       ============================= */

    public function createMessage($data) {
        $sql = "
            INSERT INTO contacts (nom, prenom, email, telephone, sujet, message)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['nom'],
            $data['prenom'] ?? null,
            $data['email'],
            $data['telephone'] ?? null,
            $data['sujet'],
            $data['message']
        ]);
    }

    public function updateMessageStatus($id, $statut) {
        // Valider que le statut est valide
        $validStatuts = ['non_lu', 'lu', 'traite', 'archive'];
        if (!in_array($statut, $validStatuts)) {
            return false;
        }
        
        $sql = "
            UPDATE contacts
            SET statut = ?
            WHERE id_contact = ?
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$statut, $id]);
    }

    /* =============================
       SUPPRESSION
       ============================= */

    public function deleteMessage($id) {
        $stmt = $this->db->prepare(
            "DELETE FROM contacts WHERE id_contact = ?"
        );
        return $stmt->execute([$id]);
    }

    /* =============================
       STATISTIQUES
       ============================= */

    public function countUnreadMessages() {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM contacts WHERE statut = 'non_lu'"
        );
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function countByStatus($statut) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM contacts WHERE statut = ?"
        );
        $stmt->execute([$statut]);
        return (int) $stmt->fetchColumn();
    }
}
?>