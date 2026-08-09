<?php
// models/NewsModel.php

class NewsModel {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnexion();
    }

    /* =============================
       RÉCUPÉRATION
       ============================= */

    public function getAllNews() {
        $sql = "
            SELECT 
                n.id_news,
                n.titre,
                n.contenu,
                n.image,
                n.type_news,
                n.statut,
                n.date_publication,
                n.afficher_diaporama,
                u.nom,
                u.prenom
            FROM news n
            LEFT JOIN users u ON n.auteur = u.id_user
            ORDER BY n.date_publication DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPublishedNews() {
        $sql = "
            SELECT 
                n.id_news,
                n.titre,
                n.contenu,
                n.image,
                n.type_news,
                n.date_publication,
                n.afficher_diaporama,
                u.nom,
                u.prenom
            FROM news n
            LEFT JOIN users u ON n.auteur = u.id_user
            WHERE n.statut = 'publie'
            ORDER BY n.date_publication DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNewsById($id) {
        $sql = "
            SELECT 
                n.id_news,
                n.titre,
                n.contenu,
                n.image,
                n.type_news,
                n.statut,
                n.date_publication,
                n.afficher_diaporama,
                u.nom,
                u.prenom
            FROM news n
            LEFT JOIN users u ON n.auteur = u.id_user
            WHERE n.id_news = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getSlideshowNews() {
        $sql = "
            SELECT 
                id_news,
                titre,
                contenu,
                image,
                type_news,
                date_publication
            FROM news
            WHERE statut = 'publie'
              AND afficher_diaporama = 1
            ORDER BY date_publication DESC
            LIMIT 5
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =============================
       CRÉATION / MISE À JOUR
       ============================= */

    public function createNews($data) {
        $datePublication = null;
        if (($data['statut'] ?? 'brouillon') === 'publie') {
            $datePublication = date('Y-m-d H:i:s');
        }

        $sql = "
            INSERT INTO news
            (titre, contenu, image, type_news, auteur, statut, date_publication, afficher_diaporama)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['titre'],
            $data['contenu'],
            $data['image'] ?? null,
            $data['type_news'],
            $data['auteur'],
            $data['statut'] ?? 'brouillon',
            $datePublication,
            $data['afficher_diaporama'] ?? 0
        ]);
    }

    public function updateNews($id, $data) {
        $datePublication = $data['date_publication'] ?? null;
        if ($data['statut'] === 'publie' && empty($datePublication)) {
            $datePublication = date('Y-m-d H:i:s');
        }

        $sql = "
            UPDATE news SET
                titre = ?,
                contenu = ?,
                image = ?,
                type_news = ?,
                statut = ?,
                date_publication = ?,
                afficher_diaporama = ?
            WHERE id_news = ?
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['titre'],
            $data['contenu'],
            $data['image'] ?? null,
            $data['type_news'],
            $data['statut'],
            $datePublication,
            $data['afficher_diaporama'] ?? 0,
            $id
        ]);
    }

    /* =============================
       SUPPRESSION
       ============================= */

    public function deleteNews($id) {
        $stmt = $this->db->prepare(
            "DELETE FROM news WHERE id_news = ?"
        );
        return $stmt->execute([$id]);
    }

    /* =============================
       FILTRES & PAGINATION
       ============================= */

    public function getNewsByType($type) {
        $sql = "
            SELECT 
                id_news,
                titre,
                contenu,
                image,
                type_news,
                date_publication
            FROM news
            WHERE type_news = ?
              AND statut = 'publie'
            ORDER BY date_publication DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countPublishedNews($type = null, $search = null) {
        $sql = "SELECT COUNT(*) FROM news WHERE statut = 'publie'";
        $params = [];

        if ($type) {
            $sql .= " AND type_news = ?";
            $params[] = $type;
        }

        if ($search) {
            $sql .= " AND (titre LIKE ? OR contenu LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getPublishedNewsPaginated($limit, $offset, $type = null, $search = null) {
        $sql = "
            SELECT 
                n.id_news,
                n.titre,
                n.contenu,
                n.image,
                n.type_news,
                n.date_publication,
                u.nom,
                u.prenom
            FROM news n
            LEFT JOIN users u ON n.auteur = u.id_user
            WHERE n.statut = 'publie'
        ";
        $params = [];

        if ($type) {
            $sql .= " AND n.type_news = ?";
            $params[] = $type;
        }

        if ($search) {
            $sql .= " AND (n.titre LIKE ? OR n.contenu LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql .= " ORDER BY n.date_publication DESC LIMIT ?, ?";
        $params[] = (int) $offset;
        $params[] = (int) $limit;

        $stmt = $this->db->prepare($sql);

        foreach ($params as $i => $param) {
            $stmt->bindValue(
                $i + 1,
                $param,
                is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>