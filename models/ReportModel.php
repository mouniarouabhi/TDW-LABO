<?php
class ReportModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnexion();
    }

    public function getAllReports() {
        $sql = "
            SELECT r.*, u.nom, u.prenom
            FROM reports r
            LEFT JOIN users u ON u.id_user = r.genere_par
            ORDER BY r.date_generation DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getReportById($id) {
        $stmt = $this->db->prepare("SELECT * FROM reports WHERE id_report=?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createReport($data) {
        $sql = "
            INSERT INTO reports(type, titre, contenu, format, fichier, genere_par)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        return $this->db->prepare($sql)->execute([
            $data['type'],
            $data['titre'],
            $data['contenu'],
            $data['format'] ?? 'PDF',
            $data['fichier'] ?? null,
            $data['genere_par']
        ]);
    }

    public function deleteReport($id) {
        return $this->db
            ->prepare("DELETE FROM reports WHERE id_report=?")
            ->execute([$id]);
    }

    public function getReportsByType($type) {
        $sql = "SELECT r.*, 
                    u.nom as generateur_nom, 
                    u.prenom as generateur_prenom
                FROM reports r
                LEFT JOIN users u ON r.genere_par = u.id_user
                WHERE r.type = ?
                ORDER BY r.date_generation DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReportsByFormat($format) {
        $sql = "SELECT r.*, 
                    u.nom as generateur_nom, 
                    u.prenom as generateur_prenom
                FROM reports r
                LEFT JOIN users u ON r.genere_par = u.id_user
                WHERE r.format = ?
                ORDER BY r.date_generation DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$format]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function countReports() {
        $sql = "SELECT COUNT(*) as total FROM reports";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }
}
