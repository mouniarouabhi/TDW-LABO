<?php
// models/ReservationModel.php

class ReservationModel {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnexion();
    }

    /* =============================
       RÉCUPÉRATION UTILISATEUR
       ============================= */

    public function getUpcomingReservations($userId) {
        $sql = "
            SELECT 
                r.id_reservation,
                r.date_debut,
                r.date_fin,
                r.motif,
                r.statut,
                e.nom_equipment,
                e.type_equipment
            FROM reservations r
            INNER JOIN equipments e ON r.id_equipment = e.id_equipment
            WHERE r.id_user = :user_id
              AND r.date_debut >= NOW()
              AND r.statut IN ('en_attente', 'validee', 'en_cours')
            ORDER BY r.date_debut ASC
            LIMIT 10
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReservationsByUser($userId) {
        $sql = "
            SELECT 
                r.id_reservation,
                r.date_debut,
                r.date_fin,
                r.motif,
                r.statut,
                e.nom_equipment,
                e.type_equipment
            FROM reservations r
            INNER JOIN equipments e ON r.id_equipment = e.id_equipment
            WHERE r.id_user = :user_id
            ORDER BY r.date_debut DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =============================
       RÉCUPÉRATION ÉQUIPEMENT
       ============================= */

    public function getReservationsByEquipment($equipmentId) {
        $sql = "
            SELECT 
                r.id_reservation,
                r.date_debut,
                r.date_fin,
                r.motif,
                r.statut,
                u.nom,
                u.prenom,
                u.email
            FROM reservations r
            INNER JOIN users u ON r.id_user = u.id_user
            WHERE r.id_equipment = :equipment_id
              AND r.statut != 'annule'
            ORDER BY r.date_debut ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':equipment_id', $equipmentId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReservationById($id) {
        $sql = "
            SELECT 
                r.id_reservation,
                r.date_debut,
                r.date_fin,
                r.motif,
                r.statut,
                e.nom_equipment,
                e.type_equipment,
                u.nom,
                u.prenom,
                u.email
            FROM reservations r
            INNER JOIN equipments e ON r.id_equipment = e.id_equipment
            INNER JOIN users u ON r.id_user = u.id_user
            WHERE r.id_reservation = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* =============================
       CRÉATION
       ============================= */

    public function createReservation($data) {
        $sql = "
            INSERT INTO reservations
            (id_equipment, id_user, date_debut, date_fin, motif, statut)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['id_equipment'],
            $data['id_user'],
            $data['date_debut'],
            $data['date_fin'],
            $data['motif'] ?? null,
            $data['statut'] ?? 'en_attente'
        ]);
    }

    /* =============================
       CONFLITS
       ============================= */

    public function checkConflict($equipmentId, $dateDebut, $dateFin, $excludeId = null) {
        $sql = "
            SELECT COUNT(*) AS total
            FROM reservations
            WHERE id_equipment = :equipment_id
              AND statut IN ('en_attente', 'validee', 'en_cours')
              AND (
                (date_debut <= :date_debut AND date_fin > :date_debut)
                OR (date_debut < :date_fin AND date_fin >= :date_fin)
                OR (date_debut >= :date_debut AND date_fin <= :date_fin)
              )
        ";
        if ($excludeId) {
            $sql .= " AND id_reservation != :exclude_id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':equipment_id', $equipmentId, PDO::PARAM_INT);
        $stmt->bindParam(':date_debut', $dateDebut);
        $stmt->bindParam(':date_fin', $dateFin);

        if ($excludeId) {
            $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($res['total'] ?? 0) > 0;
    }

    public function hasActiveReservation($equipmentId) {
        $sql = "
            SELECT COUNT(*) AS total
            FROM reservations
            WHERE id_equipment = :equipment_id
              AND date_debut <= NOW()
              AND date_fin >= NOW()
              AND statut IN ('validee', 'en_cours')
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':equipment_id', $equipmentId, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($res['total'] ?? 0) > 0;
    }

    /* =============================
       STATUT
       ============================= */

    public function updateReservationStatus($id, $statut) {
        $stmt = $this->db->prepare(
            "UPDATE reservations SET statut = ? WHERE id_reservation = ?"
        );
        return $stmt->execute([$statut, $id]);
    }

    /* =============================
       STATISTIQUES
       ============================= */

    public function getTauxOccupation($equipmentId, $days = 30) {
        $sql = "
            SELECT 
                SUM(TIMESTAMPDIFF(HOUR, date_debut, date_fin)) AS total_hours
            FROM reservations
            WHERE id_equipment = ?
              AND date_debut >= DATE_SUB(NOW(), INTERVAL {$days} DAY)
              AND statut IN ('validee', 'terminee')
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$equipmentId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalHours = $res['total_hours'] ?? 0;
        $availableHours = $days * 24;

        return $availableHours > 0
            ? round(($totalHours / $availableHours) * 100, 2)
            : 0;
    }

    /* =============================
       ADMIN
       ============================= */

    public function getActiveReservations() {
        $sql = "
            SELECT 
                r.id_reservation,
                r.date_debut,
                r.date_fin,
                r.statut,
                e.nom_equipment,
                u.nom,
                u.prenom
            FROM reservations r
            INNER JOIN equipments e ON r.id_equipment = e.id_equipment
            INNER JOIN users u ON r.id_user = u.id_user
            WHERE r.statut IN ('en_attente', 'validee', 'en_cours')
              AND r.date_fin >= NOW()
            ORDER BY r.date_debut ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllReservations() {
        $sql = "
            SELECT 
                r.id_reservation,
                r.date_debut,
                r.date_fin,
                r.statut,
                e.nom_equipment,
                u.nom,
                u.prenom,
                u.email
            FROM reservations r
            INNER JOIN equipments e ON r.id_equipment = e.id_equipment
            INNER JOIN users u ON r.id_user = u.id_user
            ORDER BY r.date_creation DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUserReservations($userId) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total FROM reservations WHERE id_user = ?"
        );
        $stmt->execute([$userId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res['total'] ?? 0;
    }
}
?>
