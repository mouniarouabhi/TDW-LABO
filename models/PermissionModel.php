<?php
// models/PermissionModel.php

class PermissionModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnexion();
    }

    public function getAllPermissions() {
        $stmt = $this->db->query("SELECT * FROM permissions ORDER BY code");
        return $stmt->fetchAll();
    }

    public function getPermissionById($id) {
        $stmt = $this->db->prepare("SELECT * FROM permissions WHERE id_permission = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getPermissionByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM permissions WHERE code = ?");
        $stmt->execute([$name]);
        return $stmt->fetch();
    }

    public function createPermission($code, $description) {
        $stmt = $this->db->prepare(
            "INSERT INTO permissions (code, description) VALUES (?, ?)"
        );
        if ($stmt->execute([$code, $description])) {
            return $this->db->lastInsertId();  // ✅ Return the new ID
        }
        return false;
    }

    public function updatePermission($id, $code, $description) {
        $stmt = $this->db->prepare(
            "UPDATE permissions SET code = ?, description = ? WHERE id_permission = ?"
        );
        return $stmt->execute([$code, $description, $id]);
    }

    public function deletePermission($id) {
        $stmt = $this->db->prepare(
            "DELETE FROM permissions WHERE id_permission = ?"
        );
        return $stmt->execute([$id]);
    }

    public function countRolesWithPermission($permissionId) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM role_permissions WHERE id_permission = ?"
        );
        $stmt->execute([$permissionId]);
        return $stmt->fetchColumn();
    }
}
