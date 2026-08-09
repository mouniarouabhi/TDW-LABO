<?php
class UserRoleModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnexion();
    }

    /* ==============================
       RÔLES D’UN UTILISATEUR
       ============================== */

    public function getUserRoles($userId) {
        $stmt = $this->db->prepare("
            SELECT r.*
            FROM user_roles ur
            INNER JOIN roles r ON ur.id_role = r.id_role
            WHERE ur.id_user = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function userHasRole($userId, $roleName) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM user_roles ur
            INNER JOIN roles r ON ur.id_role = r.id_role
            WHERE ur.id_user = ? AND r.nom = ?
        ");
        $stmt->execute([$userId, $roleName]);
        return $stmt->fetchColumn() > 0;
    }

    /* ==============================
       GESTION
       ============================== */

    public function assignRole($userId, $roleId) {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO user_roles (id_user, id_role)
            VALUES (?, ?)
        ");
        return $stmt->execute([$userId, $roleId]);
    }

    public function removeRole($userId, $roleId) {
        $stmt = $this->db->prepare("
            DELETE FROM user_roles
            WHERE id_user = ? AND id_role = ?
        ");
        return $stmt->execute([$userId, $roleId]);
    }

    public function removeAllRoles($userId) {
        return $this->db
            ->prepare("DELETE FROM user_roles WHERE id_user = ?")
            ->execute([$userId]);
    }
}
