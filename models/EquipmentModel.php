<?php
// models/EquipmentModel.php

class EquipmentModel {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnexion();
    }
    
    /* =============================
       RÉCUPÉRATION
       ============================= */

    public function getAllEquipments() {
        $sql = "
            SELECT 
                id_equipment,
                nom_equipment,
                type_equipment,
                description,
                etat,
                localisation,
                date_acquisition,
                date_creation
            FROM equipments
            ORDER BY nom_equipment
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getEquipmentById($id) {
        $sql = "
            SELECT 
                id_equipment,
                nom_equipment,
                type_equipment,
                description,
                etat,
                localisation,
                date_acquisition,
                date_creation
            FROM equipments
            WHERE id_equipment = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getAvailableEquipments() {
        $sql = "
            SELECT 
                id_equipment,
                nom_equipment,
                type_equipment,
                description,
                localisation
            FROM equipments
            WHERE etat = 'libre'
            ORDER BY nom_equipment
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getEquipmentsByType($type) {
        $sql = "
            SELECT 
                id_equipment,
                nom_equipment,
                type_equipment,
                description,
                etat,
                localisation
            FROM equipments
            WHERE type_equipment = ?
            ORDER BY nom_equipment
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEquipmentsByState($etat) {
        $sql = "
            SELECT 
                id_equipment,
                nom_equipment,
                type_equipment,
                description,
                localisation
            FROM equipments
            WHERE etat = ?
            ORDER BY nom_equipment
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$etat]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /* =============================
       CRÉATION / MISE À JOUR
       ============================= */

    public function createEquipment($data) {
        $sql = "
            INSERT INTO equipments
            (nom_equipment, type_equipment, description, etat, localisation, date_acquisition)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $data['nom_equipment'],
            $data['type_equipment'],
            $data['description'] ?? null,
            $data['etat'] ?? 'libre',
            $data['localisation'] ?? null,
            $data['date_acquisition'] ?? null
        ]);
    }
    
    public function updateEquipment($id, $data) {
        $sql = "
            UPDATE equipments SET 
                nom_equipment = ?, 
                type_equipment = ?, 
                description = ?, 
                etat = ?, 
                localisation = ?, 
                date_acquisition = ?
            WHERE id_equipment = ?
        ";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $data['nom_equipment'],
            $data['type_equipment'],
            $data['description'] ?? null,
            $data['etat'],
            $data['localisation'] ?? null,
            $data['date_acquisition'] ?? null,
            $id
        ]);
    }
    
    public function updateEquipmentState($id, $etat) {
        $sql = "UPDATE equipments SET etat = ? WHERE id_equipment = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$etat, $id]);
    }
    
    /* =============================
       SUPPRESSION
       ============================= */

    public function deleteEquipment($id) {
        $stmt = $this->db->prepare(
            "DELETE FROM equipments WHERE id_equipment = ?"
        );
        return $stmt->execute([$id]);
    }
}
?>
