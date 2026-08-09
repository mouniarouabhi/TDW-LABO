<?php
// models/RolePermissionModel.php

class RolePermissionModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnexion();
    }

    public function getPermissionsByRole($roleId) {
        $sql = "
            SELECT p.*
            FROM role_permissions rp
            JOIN permissions p ON rp.id_permission = p.id_permission
            WHERE rp.id_role = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleId]);
        return $stmt->fetchAll();
    }

    public function assignPermissionToRole($roleId, $permissionId) {
        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO role_permissions (id_role, id_permission)
             VALUES (?, ?)"
        );
        return $stmt->execute([$roleId, $permissionId]);
    }

    public function removePermissionFromRole($roleId, $permissionId) {
        $stmt = $this->db->prepare(
            "DELETE FROM role_permissions 
             WHERE id_role = ? AND id_permission = ?"
        );
        return $stmt->execute([$roleId, $permissionId]);
    }

    public function removeAllPermissionsFromRole($roleId) {
        $stmt = $this->db->prepare(
            "DELETE FROM role_permissions WHERE id_role = ?"
        );
        return $stmt->execute([$roleId]);
    }

    public function countRolesWithPermission($permissionId) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(DISTINCT id_role) FROM role_permissions WHERE id_permission = ?"
        );
        $stmt->execute([$permissionId]);
        return $stmt->fetchColumn();
    }
}
