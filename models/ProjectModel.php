<?php
// models/ProjectModel.php

class ProjectModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnexion();
    }

    /* =============================
       RÉCUPÉRATION PROJETS
       ============================= */

    public function getAllProjects() {
        $sql = "SELECT p.*, 
                       resp.nom AS resp_nom, 
                       resp.prenom AS resp_prenom,
                       enc.nom AS enc_nom,
                       enc.prenom AS enc_prenom
                FROM projects p
                LEFT JOIN users resp ON p.id_responsable = resp.id_user
                LEFT JOIN users enc ON p.id_encadrant = enc.id_user
                ORDER BY p.date_creation DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getProjectById($id) {
        $sql = "SELECT p.*, 
                       resp.nom AS resp_nom, 
                       resp.prenom AS resp_prenom, 
                       resp.email AS resp_email,
                       enc.nom AS enc_nom,
                       enc.prenom AS enc_prenom,
                       enc.email AS enc_email
                FROM projects p
                LEFT JOIN users resp ON p.id_responsable = resp.id_user
                LEFT JOIN users enc ON p.id_encadrant = enc.id_user
                WHERE p.id_project = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /* =============================
       FILTRES
       ============================= */

    public function getProjectsByThematic($thematique) {
        $sql = "SELECT p.*, 
                       resp.nom AS resp_nom, 
                       resp.prenom AS resp_prenom,
                       enc.nom AS enc_nom,
                       enc.prenom AS enc_prenom
                FROM projects p
                LEFT JOIN users resp ON p.id_responsable = resp.id_user
                LEFT JOIN users enc ON p.id_encadrant = enc.id_user
                WHERE p.thematique = ?
                ORDER BY p.date_creation DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$thematique]);
        return $stmt->fetchAll();
    }

    public function getProjectsByStatus($statut) {
        $sql = "SELECT p.*, 
                       resp.nom AS resp_nom, 
                       resp.prenom AS resp_prenom,
                       enc.nom AS enc_nom,
                       enc.prenom AS enc_prenom
                FROM projects p
                LEFT JOIN users resp ON p.id_responsable = resp.id_user
                LEFT JOIN users enc ON p.id_encadrant = enc.id_user
                WHERE p.statut = ?
                ORDER BY p.date_creation DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$statut]);
        return $stmt->fetchAll();
    }

    public function getLatestProjects($limit = 6) {
        $limit = (int)$limit;

        $sql = "SELECT p.*, 
                       resp.nom AS resp_nom, 
                       resp.prenom AS resp_prenom,
                       enc.nom AS enc_nom,
                       enc.prenom AS enc_prenom
                FROM projects p
                LEFT JOIN users resp ON p.id_responsable = resp.id_user
                LEFT JOIN users enc ON p.id_encadrant = enc.id_user
                ORDER BY p.date_creation DESC
                LIMIT $limit";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Projets d'un utilisateur (responsable, encadrant ou membre)
     */
    public function getProjectsByUser($userId) {
        $sql = "SELECT DISTINCT p.*, 
                       resp.nom AS resp_nom, 
                       resp.prenom AS resp_prenom, 
                       resp.email AS resp_email,
                       enc.nom AS enc_nom,
                       enc.prenom AS enc_prenom,
                       enc.email AS enc_email
                FROM projects p
                LEFT JOIN users resp ON p.id_responsable = resp.id_user
                LEFT JOIN users enc ON p.id_encadrant = enc.id_user
                LEFT JOIN project_members pm ON p.id_project = pm.id_project
                WHERE p.id_responsable = :user_id 
                   OR p.id_encadrant = :user_id
                   OR pm.id_user = :user_id
                ORDER BY p.date_creation DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Membres d'un projet (avec rôles système corrects)
     */
    public function getProjectMembers($projectId) {
        $sql = "
            SELECT 
                u.id_user,
                u.nom,
                u.prenom,
                u.email,
                u.grade,
                u.photo,
                pm.role_projet,
                GROUP_CONCAT(r.nom SEPARATOR ', ') AS roles
            FROM project_members pm
            INNER JOIN users u ON u.id_user = pm.id_user
            LEFT JOIN user_roles ur ON ur.id_user = u.id_user
            LEFT JOIN roles r ON r.id_role = ur.id_role
            WHERE pm.id_project = :project_id
            GROUP BY u.id_user
            ORDER BY u.nom, u.prenom
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compter les projets d'un utilisateur
     */
    public function countUserProjects($userId) {
        $sql = "SELECT COUNT(DISTINCT p.id_project) AS total
                FROM projects p
                LEFT JOIN project_members pm ON p.id_project = pm.id_project
                WHERE p.id_responsable = :user_id 
                   OR p.id_encadrant = :user_id
                   OR pm.id_user = :user_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['total'] ?? 0;
    }

    /**
     * Vérifier si un utilisateur appartient à un projet
     */
    public function isUserInProject($projectId, $userId) {
        $sql = "SELECT COUNT(*) AS count
                FROM projects p
                LEFT JOIN project_members pm ON p.id_project = pm.id_project
                WHERE p.id_project = :project_id
                  AND (p.id_responsable = :user_id 
                       OR p.id_encadrant = :user_id 
                       OR pm.id_user = :user_id)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['count'] ?? 0) > 0;
    }

    /* =============================
       CRÉATION / UPDATE
       ============================= */

    public function createProject($data) {
        $sql = "INSERT INTO projects
                (titre, description, thematique, type_financement, statut, id_responsable, id_encadrant, date_debut, date_fin, budget)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $data['titre'],
            $data['description'],
            $data['thematique'],
            $data['type_financement'],
            $data['statut'] ?? 'en_cours',
            $data['id_responsable'] ?? null,
            $data['id_encadrant'] ?? null,
            $data['date_debut'] ?? null,
            $data['date_fin'] ?? null,
            $data['budget'] ?? null
        ]);
    }

    public function updateProject($id, $data) {
        $sql = "UPDATE projects SET
                    titre = ?,
                    description = ?,
                    thematique = ?,
                    type_financement = ?,
                    statut = ?,
                    id_responsable = ?,
                    id_encadrant = ?,
                    date_debut = ?,
                    date_fin = ?,
                    budget = ?
                WHERE id_project = ?";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $data['titre'],
            $data['description'],
            $data['thematique'],
            $data['type_financement'],
            $data['statut'],
            $data['id_responsable'] ?? null,
            $data['id_encadrant'] ?? null,
            $data['date_debut'] ?? null,
            $data['date_fin'] ?? null,
            $data['budget'] ?? null,
            $id
        ]);
    }

    /* =============================
       SUPPRESSION
       ============================= */

    public function deleteProject($id) {
        $stmt = $this->db->prepare("DELETE FROM projects WHERE id_project = ?");
        return $stmt->execute([$id]);
    }

    /* =============================
       STATISTIQUES
       ============================= */

    /**
     * Compter tous les projets
     */
    public function countAllProjects() {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM projects");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Compter par statut
     */
    public function countProjectsByStatus($statut) {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM projects WHERE statut = ?");
        $stmt->execute([$statut]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Statistiques complètes
     */
    public function getProjectStats() {
        return [
            'total' => $this->countAllProjects(),
            'en_cours' => $this->countProjectsByStatus('en_cours'),
            'termine' => $this->countProjectsByStatus('termine'),
            'soumis' => $this->countProjectsByStatus('soumis')
        ];
    }
    /**
     * Ajouter un membre à un projet
     * @param int $projectId ID du projet
     * @param int $userId ID de l'utilisateur
     * @param string $roleProjet Rôle dans le projet (ex: "Chercheur", "Doctorant", "Membre")
     * @return bool
     */
    public function addMemberToProject($projectId, $userId, $role) {
        // Vérifier UNIQUEMENT pour ce projet
        $check = $this->db->prepare(
            "SELECT 1 FROM project_members 
            WHERE id_project = ? AND id_user = ?"
        );
        $check->execute([$projectId, $userId]);

        if ($check->fetch()) {
            return false; // déjà membre de CE projet
        }

        $insert = $this->db->prepare(
            "INSERT INTO project_members (id_project, id_user, role_projet)
            VALUES (?, ?, ?)"
        );

        return $insert->execute([$projectId, $userId, $role]);
    }

    /**
     * Retirer un membre d'un projet
     * @param int $projectId ID du projet
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function removeMemberFromProject($projectId, $userId) {
        $sql = "DELETE FROM project_members 
                WHERE id_project = :project_id AND id_user = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Modifier le rôle d'un membre dans un projet
     * @param int $projectId ID du projet
     * @param int $userId ID de l'utilisateur
     * @param string $roleProjet Nouveau rôle
     * @return bool
     */
    public function updateMemberRole($projectId, $userId, $roleProjet) {
        $sql = "UPDATE project_members 
                SET role_projet = :role_projet
                WHERE id_project = :project_id AND id_user = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':role_projet', $roleProjet, PDO::PARAM_STR);
        $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Vérifier si un utilisateur est membre d'un projet
     * @param int $projectId ID du projet
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function isMemberOfProject($projectId, $userId) {
        $sql = "SELECT COUNT(*) as count 
                FROM project_members 
                WHERE id_project = :project_id AND id_user = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Compter les membres d'un projet
     * @param int $projectId ID du projet
     * @return int
     */
    public function countProjectMembers($projectId) {
        $sql = "SELECT COUNT(*) as count FROM project_members WHERE id_project = :project_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    /**
     * Récupérer les projets d'un utilisateur
     */
    public function getUserProjects($userId) {
        $sql = "
            SELECT DISTINCT
                p.id_project,
                p.titre,
                p.description,
                p.thematique,
                p.type_financement,
                p.statut,
                p.id_responsable,
                p.id_encadrant,
                p.date_debut,
                p.date_fin,
                p.budget,
                p.date_creation,
                pm.role_projet,
                CASE 
                    WHEN p.id_responsable = ? THEN 'Responsable'
                    WHEN p.id_encadrant = ? THEN 'Encadrant'
                    ELSE pm.role_projet
                END as role_projet
            FROM projects p
            LEFT JOIN project_members pm ON p.id_project = pm.id_project AND pm.id_user = ?
            WHERE p.id_responsable = ? 
            OR p.id_encadrant = ?
            OR pm.id_user = ?
            ORDER BY p.date_debut DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>