<?php
// models/PublicationModel.php

class PublicationModel {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnexion();
    }

    /* =============================
       RÉCUPÉRATION
       ============================= */

    public function getAllPublications() {
        $sql = "
            SELECT 
                p.*,
                GROUP_CONCAT(
                    CONCAT(u.nom, ' ', u.prenom)
                    ORDER BY pa.ordre_auteur SEPARATOR ', '
                ) AS auteurs
            FROM publications p
            LEFT JOIN publication_authors pa ON p.id_publication = pa.id_publication
            LEFT JOIN users u ON pa.id_user = u.id_user
            GROUP BY p.id_publication
            ORDER BY p.annee_publication DESC, p.date_creation DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getValidatedPublications() {
        $sql = "
            SELECT 
                p.*,
                GROUP_CONCAT(
                    CONCAT(u.nom, ' ', u.prenom)
                    ORDER BY pa.ordre_auteur SEPARATOR ', '
                ) AS auteurs
            FROM publications p
            LEFT JOIN publication_authors pa ON p.id_publication = pa.id_publication
            LEFT JOIN users u ON pa.id_user = u.id_user
            WHERE p.statut = 'valide'
            GROUP BY p.id_publication
            ORDER BY p.annee_publication DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPublicationById($id) {
        $sql = "
            SELECT 
                p.*,
                GROUP_CONCAT(
                    CONCAT(u.nom, ' ', u.prenom)
                    ORDER BY pa.ordre_auteur SEPARATOR ', '
                ) AS auteurs
            FROM publications p
            LEFT JOIN publication_authors pa ON p.id_publication = pa.id_publication
            LEFT JOIN users u ON pa.id_user = u.id_user
            WHERE p.id_publication = ?
            GROUP BY p.id_publication
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPublicationAuthors($publicationId) {
        $sql = "SELECT u.id_user, u.nom, u.prenom, u.grade, pa.ordre_auteur
                FROM users u
                INNER JOIN publication_authors pa ON u.id_user = pa.id_user
                WHERE pa.id_publication = ?
                ORDER BY pa.ordre_auteur";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$publicationId]);
        return $stmt->fetchAll();
    }

    /* =============================
       FILTRES
       ============================= */

    public function getPublicationsByType($type) {
        $sql = "
            SELECT p.*,
                   GROUP_CONCAT(CONCAT(u.nom, ' ', u.prenom)
                   ORDER BY pa.ordre_auteur SEPARATOR ', ') AS auteurs
            FROM publications p
            LEFT JOIN publication_authors pa ON p.id_publication = pa.id_publication
            LEFT JOIN users u ON pa.id_user = u.id_user
            WHERE p.type_publication = ?
              AND p.statut = 'valide'
            GROUP BY p.id_publication
            ORDER BY p.annee_publication DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPublicationsByYear($year) {
        $sql = "
            SELECT p.*,
                   GROUP_CONCAT(CONCAT(u.nom, ' ', u.prenom)
                   ORDER BY pa.ordre_auteur SEPARATOR ', ') AS auteurs
            FROM publications p
            LEFT JOIN publication_authors pa ON p.id_publication = pa.id_publication
            LEFT JOIN users u ON pa.id_user = u.id_user
            WHERE p.annee_publication = ?
              AND p.statut = 'valide'
            GROUP BY p.id_publication
            ORDER BY p.date_creation DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPublicationsByUser($id_user) {
        $sql = "
            SELECT DISTINCT p.*,
                GROUP_CONCAT(CONCAT(u.nom, ' ', u.prenom)
                ORDER BY pa2.ordre_auteur SEPARATOR ', ') AS auteurs
            FROM publications p
            INNER JOIN publication_authors pa ON p.id_publication = pa.id_publication
            LEFT JOIN publication_authors pa2 ON p.id_publication = pa2.id_publication
            LEFT JOIN users u ON pa2.id_user = u.id_user
            WHERE pa.id_user = ?
            GROUP BY p.id_publication
            ORDER BY p.annee_publication DESC, p.date_creation DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_user]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUserPublications($userId) {
        $sql = "
            SELECT COUNT(DISTINCT p.id_publication) AS total
            FROM publications p
            INNER JOIN publication_authors pa ON p.id_publication = pa.id_publication
            WHERE pa.id_user = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return $res['total'] ?? 0;
    }

    public function isUserAuthor($publicationId, $userId) {
        $sql = "
            SELECT COUNT(*) AS count
            FROM publication_authors
            WHERE id_publication = ? AND id_user = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$publicationId, $userId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return ($res['count'] ?? 0) > 0;
    }

    /* =============================
       CRÉATION / UPDATE / DELETE
       ============================= */

    public function createPublication($data) {
        $sql = "INSERT INTO publications (
                    titre, resume, type_publication, annee_publication, 
                    doi, lien_telechargement, id_project, domaine, 
                    statut, date_soumission
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['titre'],
            $data['resume'],
            $data['type_publication'],
            $data['annee_publication'],
            $data['doi'],
            $data['lien_telechargement'],
            $data['id_project'],
            $data['domaine'],
            $data['statut'],
            $data['date_soumission']
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }

    public function updatePublication($id, $data) {
        $sql = "UPDATE publications SET 
                    titre = ?,
                    resume = ?,
                    type_publication = ?,
                    annee_publication = ?,
                    doi = ?,
                    lien_telechargement = ?,
                    id_project = ?,
                    domaine = ?,
                    statut = ?,
                    date_soumission = ?
                WHERE id_publication = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['titre'],
            $data['resume'],
            $data['type_publication'],
            $data['annee_publication'],
            $data['doi'],
            $data['lien_telechargement'],
            $data['id_project'],
            $data['domaine'],
            $data['statut'],
            $data['date_soumission'],
            $id
        ]);
    }

    public function deletePublication($id) {
        $stmt = $this->db->prepare(
            "DELETE FROM publications WHERE id_publication = ?"
        );
        return $stmt->execute([$id]);
    }

    /* =============================
       AUTEURS
       ============================= */

    public function addAuthorToPublication($publicationId, $userId, $ordre = null) {
        if ($ordre === null) {
            // Calculer l'ordre automatiquement
            $sql = "SELECT COALESCE(MAX(ordre_auteur), 0) + 1 as next_ordre 
                    FROM publication_authors 
                    WHERE id_publication = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$publicationId]);
            $ordre = $stmt->fetch()['next_ordre'];
        }
        
        $sql = "INSERT INTO publication_authors (id_publication, id_user, ordre_auteur) 
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE ordre_auteur = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$publicationId, $userId, $ordre, $ordre]);
    }

    public function removeAuthor($id_publication, $id_user) {
        $stmt = $this->db->prepare(
            "DELETE FROM publication_authors
             WHERE id_publication = ? AND id_user = ?"
        );
        return $stmt->execute([$id_publication, $id_user]);
    }

    public function removeAllAuthors($id_publication) {
        $stmt = $this->db->prepare(
            "DELETE FROM publication_authors WHERE id_publication = ?"
        );
        return $stmt->execute([$id_publication]);
    }

    public function updatePublicationStatus($id, $status) {
        $sql = "UPDATE publications SET statut = ? WHERE id_publication = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $id]);
    }

        /**
     * Récupérer les publications associées à un projet
     */
    public function getPublicationsByProject($projectId) {
        $sql = "SELECT p.*, 
                       GROUP_CONCAT(u.nom || ' ' || u.prenom SEPARATOR ', ') AS auteurs
                FROM publications p
                LEFT JOIN publication_authors pa ON p.id_publication = pa.id_publication
                LEFT JOIN users u ON pa.id_user = u.id_user
                WHERE p.id_project = :project_id
                GROUP BY p.id_publication
                ORDER BY p.annee_publication DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
