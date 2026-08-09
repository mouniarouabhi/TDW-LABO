<?php
// models/OpportunityModel.php
// VERSION CORRIGÉE - Support emploi, postdoc

class OpportunityModel {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnexion();
    }

    /* =============================
       RÉCUPÉRATION
       ============================= */

    public function getAllOpportunities() {
        $sql = "
            SELECT 
                o.id_opportunity,
                o.titre,
                o.description,
                o.type_opportunity,
                o.domaine,
                o.date_limite,
                o.contact_email,
                o.statut,
                o.date_creation,
                o.date_debut,
                u.nom AS resp_nom,
                u.prenom AS resp_prenom
            FROM opportunities o
            LEFT JOIN users u ON o.id_responsable = u.id_user
            ORDER BY o.date_creation DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOpenOpportunities() {
        $sql = "
            SELECT 
                o.id_opportunity,
                o.titre,
                o.description,
                o.type_opportunity,
                o.domaine,
                o.date_limite,
                o.date_debut,
                o.contact_email,
                u.nom AS resp_nom,
                u.prenom AS resp_prenom
            FROM opportunities o
            LEFT JOIN users u ON o.id_responsable = u.id_user
            WHERE o.statut = 'ouverte'
            ORDER BY o.date_limite ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOpportunityById($id) {
        $sql = "
            SELECT 
                o.id_opportunity,
                o.titre,
                o.description,
                o.type_opportunity,
                o.domaine,
                o.date_limite,
                o.contact_email,
                o.statut,
                o.date_creation,
                o.date_debut,
                o.id_responsable,
                u.nom AS resp_nom,
                u.prenom AS resp_prenom,
                u.email AS resp_email
            FROM opportunities o
            LEFT JOIN users u ON o.id_responsable = u.id_user
            WHERE o.id_opportunity = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getOpportunitiesByType($type) {
        $sql = "
            SELECT 
                o.id_opportunity,
                o.titre,
                o.description,
                o.domaine,
                o.date_limite,
                u.nom AS resp_nom,
                u.prenom AS resp_prenom
            FROM opportunities o
            LEFT JOIN users u ON o.id_responsable = u.id_user
            WHERE o.type_opportunity = ?
              AND o.statut = 'ouverte'
            ORDER BY o.date_limite ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOpportunitiesByDomain($domaine) {
        $sql = "
            SELECT 
                o.id_opportunity,
                o.titre,
                o.description,
                o.type_opportunity,
                o.date_limite,
                u.nom AS resp_nom,
                u.prenom AS resp_prenom
            FROM opportunities o
            LEFT JOIN users u ON o.id_responsable = u.id_user
            WHERE o.domaine = ?
              AND o.statut = 'ouverte'
            ORDER BY o.date_limite ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$domaine]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =============================
       CRÉATION / MISE À JOUR
       ============================= */

    public function createOpportunity($data) {
        // Valider le type
        $validTypes = ['stage', 'these', 'bourse', 'emploi', 'postdoc', 'collaboration', 'autre'];
        if (!in_array($data['type_opportunity'], $validTypes)) {
            return false;
        }
        
        $sql = "
            INSERT INTO opportunities
            (titre, description, type_opportunity, domaine, date_limite, contact_email, id_responsable, statut, date_debut)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['titre'],
            $data['description'],
            $data['type_opportunity'],
            $data['domaine'] ?? null,
            $data['date_limite'] ?? null,
            $data['date_debut'] ?? null,
            $data['contact_email'],
            $data['id_responsable'] ?? null,
            $data['statut'] ?? 'ouverte'
        ]);
    }

    public function updateOpportunity($id, $data) {
        // Valider le type
        $validTypes = ['stage', 'these', 'bourse', 'emploi', 'postdoc', 'collaboration', 'autre'];
        if (!in_array($data['type_opportunity'], $validTypes)) {
            return false;
        }
        
        $sql = "
            UPDATE opportunities SET
                titre = ?,
                description = ?,
                type_opportunity = ?,
                domaine = ?,
                date_limite = ?,
                contact_email = ?,
                id_responsable = ?,
                statut = ?,
                date_debut = ?
            WHERE id_opportunity = ?
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['titre'],
            $data['description'],
            $data['type_opportunity'],
            $data['domaine'] ?? null,
            $data['date_limite'] ?? null,
            $data['contact_email'],
            $data['id_responsable'] ?? null,
            $data['statut'],
            $data['date_debut'] ?? null,
            $id
        ]);
    }

    /* =============================
       SUPPRESSION
       ============================= */

    public function deleteOpportunity($id) {
        $stmt = $this->db->prepare(
            "DELETE FROM opportunities WHERE id_opportunity = ?"
        );
        return $stmt->execute([$id]);
    }

    /* =============================
       STATISTIQUES
       ============================= */

    public function countByType($type) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM opportunities WHERE type_opportunity = ? AND statut = 'ouverte'"
        );
        $stmt->execute([$type]);
        return (int) $stmt->fetchColumn();
    }

    public function countByStatus($statut) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM opportunities WHERE statut = ?"
        );
        $stmt->execute([$statut]);
        return (int) $stmt->fetchColumn();
    }
}
?>