<?php
// models/PartnerModel.php

class PartnerModel {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnexion();
    }

    /* =============================
       RÉCUPÉRATION DES PARTENAIRES
       ============================= */

    public function getAllPartners() {
        $sql = "
            SELECT 
                id_partner,
                nom_partenaire,
                type_partenaire,
                logo,
                site_web,
                description,
                pays,
                date_partenariat,
                date_creation
            FROM partners
            ORDER BY nom_partenaire ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPartnerById($id) {
        $sql = "
            SELECT 
                id_partner,
                nom_partenaire,
                type_partenaire,
                logo,
                site_web,
                description,
                pays,
                date_partenariat,
                date_creation
            FROM partners
            WHERE id_partner = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPartnersByType($type) {
        $sql = "
            SELECT 
                id_partner,
                nom_partenaire,
                logo,
                site_web,
                description,
                pays,
                date_partenariat
            FROM partners
            WHERE type_partenaire = ?
            ORDER BY nom_partenaire ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPartnersByCountry($pays) {
        $sql = "
            SELECT 
                id_partner,
                nom_partenaire,
                type_partenaire,
                logo,
                site_web,
                description,
                date_partenariat
            FROM partners
            WHERE pays = ?
            ORDER BY nom_partenaire ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pays]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =============================
       CRÉATION / MISE À JOUR
       ============================= */

    public function createPartner($data) {
        $sql = "
            INSERT INTO partners (
                nom_partenaire,
                type_partenaire,
                logo,
                site_web,
                description,
                pays,
                date_partenariat
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['nom_partenaire'],
            $data['type_partenaire'],
            $data['logo'] ?? null,
            $data['site_web'] ?? null,
            $data['description'] ?? null,
            $data['pays'],
            $data['date_partenariat'] ?? null
        ]);
    }

    public function updatePartner($id, $data) {
        $sql = "
            UPDATE partners SET
                nom_partenaire = ?,
                type_partenaire = ?,
                logo = ?,
                site_web = ?,
                description = ?,
                pays = ?,
                date_partenariat = ?
            WHERE id_partner = ?
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['nom_partenaire'],
            $data['type_partenaire'],
            $data['logo'] ?? null,
            $data['site_web'] ?? null,
            $data['description'] ?? null,
            $data['pays'],
            $data['date_partenariat'] ?? null,
            $id
        ]);
    }

    /* =============================
       SUPPRESSION
       ============================= */

    public function deletePartner($id) {
        $stmt = $this->db->prepare(
            "DELETE FROM partners WHERE id_partner = ?"
        );
        return $stmt->execute([$id]);
    }
}
?>
