<?php
// models/TeamModel.php

class TeamModel {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnexion();
    }
    
    /* =============================
       RÉCUPÉRATION DES ÉQUIPES
       ============================= */

    public function getAllTeams() {
        $sql = "
            SELECT 
                t.*, 
                u.nom AS chef_nom, 
                u.prenom AS chef_prenom
            FROM teams t
            LEFT JOIN users u ON t.id_chef = u.id_user
            ORDER BY t.nom_equipe ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTeamById($id) {
        $sql = "
            SELECT 
                t.*, 
                u.nom AS chef_nom, 
                u.prenom AS chef_prenom, 
                u.email AS chef_email
            FROM teams t
            LEFT JOIN users u ON t.id_chef = u.id_user
            WHERE t.id_team = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /* =============================
       MEMBRES D'ÉQUIPE
       ============================= */

    public function getTeamMembers($id_team) {
        $sql = "
            SELECT 
                u.id_user,
                u.nom,
                u.prenom,
                u.email,
                u.grade,
                u.photo,
                tm.date_adhesion,
                GROUP_CONCAT(r.nom SEPARATOR ', ') AS roles
            FROM team_members tm
            INNER JOIN users u ON tm.id_user = u.id_user
            LEFT JOIN user_roles ur ON ur.id_user = u.id_user
            LEFT JOIN roles r ON r.id_role = ur.id_role
            WHERE tm.id_team = ?
            GROUP BY u.id_user
            ORDER BY u.nom, u.prenom
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_team]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =============================
       CRÉATION / MISE À JOUR
       ============================= */

    public function createTeam($data) {
        $sql = "
            INSERT INTO teams 
            (nom_equipe, description, thematique, id_chef)
            VALUES (?, ?, ?, ?)
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $data['nom_equipe'],
            $data['description'] ?? null,
            $data['thematique'] ?? null,
            $data['id_chef'] ?? null
        ]);
    }

    public function updateTeam($id, $data) {
        $sql = "
            UPDATE teams SET 
                nom_equipe = ?, 
                description = ?, 
                thematique = ?, 
                id_chef = ?
            WHERE id_team = ?
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $data['nom_equipe'],
            $data['description'] ?? null,
            $data['thematique'] ?? null,
            $data['id_chef'] ?? null,
            $id
        ]);
    }

    /* =============================
       SUPPRESSION
       ============================= */

    public function deleteTeam($id) {
        $stmt = $this->db->prepare("DELETE FROM teams WHERE id_team = ?");
        return $stmt->execute([$id]);
    }

    /* =============================
       GESTION DES MEMBRES
       ============================= */

    public function addMemberToTeam($id_team, $id_user) {
        $sql = "
            INSERT INTO team_members (id_team, id_user)
            VALUES (?, ?)
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id_team, $id_user]);
    }

    public function removeMemberFromTeam($id_team, $id_user) {
        $sql = "
            DELETE FROM team_members 
            WHERE id_team = ? AND id_user = ?
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id_team, $id_user]);
    }

    /* =============================
       FILTRES / STATISTIQUES
       ============================= */

    public function getTeamsByThematic($thematique) {
        $sql = "
            SELECT 
                t.*, 
                u.nom AS chef_nom, 
                u.prenom AS chef_prenom
            FROM teams t
            LEFT JOIN users u ON t.id_chef = u.id_user
            WHERE t.thematique = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$thematique]);
        return $stmt->fetchAll();
    }

    public function countTeamMembers($id_team) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total FROM team_members WHERE id_team = ?"
        );
        $stmt->execute([$id_team]);
        $res = $stmt->fetch();

        return $res['total'] ?? 0;
    }

/**
 * Récupérer les équipes d'un utilisateur
 */
public function getUserTeams($userId) {
    $sql = "
        SELECT 
            t.*,
            tm.date_adhesion
        FROM teams t
        INNER JOIN team_members tm ON t.id_team = tm.id_team
        WHERE tm.id_user = ?
        ORDER BY t.nom_equipe
    ";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}
?>
