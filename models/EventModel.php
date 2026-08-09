<?php
// models/EventModel.php

class EventModel {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnexion();
    }

    /* =============================
       RÉCUPÉRATION
       ============================= */

    public function getAllEvents() {
        $sql = "
            SELECT 
                e.id_event,
                e.titre,
                e.description,
                e.type_event,
                e.date_debut,
                e.date_fin,
                e.lieu,
                e.statut,
                e.lien_inscription,
                e.date_creation,
                u.nom AS org_nom,
                u.prenom AS org_prenom
            FROM events e
            LEFT JOIN users u ON e.organisateur = u.id_user
            ORDER BY e.date_debut DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventById($id) {
        $sql = "
            SELECT 
                e.id_event,
                e.titre,
                e.description,
                e.type_event,
                e.date_debut,
                e.date_fin,
                e.lieu,
                e.statut,
                e.lien_inscription,
                e.date_creation,
                u.nom AS org_nom,
                u.prenom AS org_prenom,
                u.email AS org_email
            FROM events e
            LEFT JOIN users u ON e.organisateur = u.id_user
            WHERE e.id_event = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUpcomingEvents() {
        $sql = "
            SELECT 
                e.id_event,
                e.titre,
                e.type_event,
                e.date_debut,
                e.lieu,
                u.nom AS org_nom,
                u.prenom AS org_prenom
            FROM events e
            LEFT JOIN users u ON e.organisateur = u.id_user
            WHERE e.statut = 'a_venir'
              AND e.date_debut > NOW()
            ORDER BY e.date_debut ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUpcomingEventsPaginated($limit, $offset) {
        $sql = "
            SELECT 
                e.id_event,
                e.titre,
                e.type_event,
                e.date_debut,
                e.lieu,
                u.nom AS org_nom,
                u.prenom AS org_prenom
            FROM events e
            LEFT JOIN users u ON e.organisateur = u.id_user
            WHERE e.statut = 'a_venir'
              AND e.date_debut > NOW()
            ORDER BY e.date_debut ASC
            LIMIT ? OFFSET ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUpcomingEvents() {
        $sql = "
            SELECT COUNT(*) AS total
            FROM events
            WHERE statut = 'a_venir'
              AND date_debut > NOW()
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($res['total'] ?? 0);
    }

    public function getEventsByType($type) {
        $sql = "
            SELECT 
                e.id_event,
                e.titre,
                e.type_event,
                e.date_debut,
                e.lieu,
                e.statut,
                u.nom AS org_nom,
                u.prenom AS org_prenom
            FROM events e
            LEFT JOIN users u ON e.organisateur = u.id_user
            WHERE e.type_event = ?
            ORDER BY e.date_debut DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =============================
       CRÉATION / UPDATE
       ============================= */

    public function createEvent($data) {
        $sql = "
            INSERT INTO events
            (titre, description, type_event, date_debut, date_fin, lieu, organisateur, statut, lien_inscription)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['titre'],
            $data['description'] ?? null,
            $data['type_event'],
            $data['date_debut'],
            $data['date_fin'] ?? null,
            $data['lieu'] ?? null,
            $data['organisateur'] ?? null,
            $data['statut'] ?? 'a_venir',
            $data['lien_inscription'] ?? null
        ]);
    }

    public function updateEvent($id, $data) {
        $sql = "
            UPDATE events SET
                titre = ?,
                description = ?,
                type_event = ?,
                date_debut = ?,
                date_fin = ?,
                lieu = ?,
                organisateur = ?,
                statut = ?,
                lien_inscription = ?
            WHERE id_event = ?
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['titre'],
            $data['description'] ?? null,
            $data['type_event'],
            $data['date_debut'],
            $data['date_fin'] ?? null,
            $data['lieu'] ?? null,
            $data['organisateur'] ?? null,
            $data['statut'],
            $data['lien_inscription'] ?? null,
            $id
        ]);
    }

    /* =============================
       SUPPRESSION
       ============================= */

    public function deleteEvent($id) {
        $stmt = $this->db->prepare(
            "DELETE FROM events WHERE id_event = ?"
        );
        return $stmt->execute([$id]);
    }

    /* =============================
       INSCRIPTIONS
       ============================= */

    public function getEventRegistrations($id_event) {
        $sql = "
            SELECT 
                u.id_user,
                u.nom,
                u.prenom,
                u.email,
                er.date_inscription,
                er.statut AS registration_status
            FROM event_registrations er
            JOIN users u ON er.id_user = u.id_user
            WHERE er.id_event = ?
            ORDER BY er.date_inscription
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_event]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registerUserToEvent($id_event, $id_user) {
        $stmt = $this->db->prepare(
            "INSERT INTO event_registrations (id_event, id_user) VALUES (?, ?)"
        );
        return $stmt->execute([$id_event, $id_user]);
    }

    public function unregisterUserFromEvent($id_event, $id_user) {
        $stmt = $this->db->prepare(
            "DELETE FROM event_registrations WHERE id_event = ? AND id_user = ?"
        );
        return $stmt->execute([$id_event, $id_user]);
    }

    public function isUserRegistered($id_event, $id_user) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total
             FROM event_registrations
             WHERE id_event = ? AND id_user = ? AND statut != 'annule'"
        );
        $stmt->execute([$id_event, $id_user]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($res['total'] ?? 0) > 0;
    }
}
?>
