<?php
class NotificationModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnexion();
    }

    public function getUserNotifications($userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications
            WHERE id_user = ?
            ORDER BY date_creation DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function countUnread($userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM notifications
            WHERE id_user = ? AND lu = 0
        ");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function markAsRead($id) {
        return $this->db
            ->prepare("UPDATE notifications SET lu=1 WHERE id_notification=?")
            ->execute([$id]);
    }

    public function createNotification($data) {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (id_user, type, message, lien)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['id_user'],
            $data['type'],
            $data['message'],
            $data['lien'] ?? null
        ]);
    }
}
