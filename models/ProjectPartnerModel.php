<?php
// models/ProjectPartnerModel.php

class ProjectPartnerModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnexion();
    }

    /**
     * Récupérer les partenaires d'un projet
     */
    public function getProjectPartners($projectId) {
        $sql = "SELECT pp.*, p.nom_partenaire, p.type_partenaire, p.pays, p.logo
                FROM project_partners pp
                INNER JOIN partners p ON pp.id_partner = p.id_partner
                WHERE pp.id_project = :project_id
                ORDER BY pp.date_debut DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ajouter un partenaire à un projet
     */
    public function addPartnerToProject($data) {
        try {
            $sql = "INSERT INTO project_partners 
                    (id_project, id_partner, type_collaboration, date_debut, date_fin, description)
                    VALUES (:id_project, :id_partner, :type_collaboration, :date_debut, :date_fin, :description)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_project', $data['id_project'], PDO::PARAM_INT);
            $stmt->bindParam(':id_partner', $data['id_partner'], PDO::PARAM_INT);
            $stmt->bindParam(':type_collaboration', $data['type_collaboration']);
            $stmt->bindParam(':date_debut', $data['date_debut']);
            $stmt->bindParam(':date_fin', $data['date_fin']);
            $stmt->bindParam(':description', $data['description']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error adding partner to project: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retirer un partenaire d'un projet
     */
    public function removePartnerFromProject($projectId, $partnerId) {
        $sql = "DELETE FROM project_partners 
                WHERE id_project = :project_id AND id_partner = :partner_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
        $stmt->bindParam(':partner_id', $partnerId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Récupérer un partenariat par son ID
     */
    public function getPartnershipById($id) {
        $sql = "SELECT * FROM project_partners WHERE id_project_partner = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Supprimer un partenariat par son ID
     */
    public function deletePartnership($id) {
        $sql = "DELETE FROM project_partners WHERE id_project_partner = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>