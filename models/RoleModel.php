<?php
// models/RoleModel.php

class RoleModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnexion();
    }

    public function getAllRoles() {
        $stmt = $this->db->query("SELECT * FROM roles ORDER BY nom");
        return $stmt->fetchAll();
    }

    public function getRoleById($id) {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id_role = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getRoleByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE nom = ?");
        $stmt->execute([$name]);
        return $stmt->fetch();
    }

    public function createRole($nom, $description = null) {
        $stmt = $this->db->prepare(
            "INSERT INTO roles (nom, description) VALUES (?, ?)"
        );
        return $stmt->execute([$nom, $description]);
    }

    public function updateRole($id, $nom, $description = null) {
        $stmt = $this->db->prepare(
            "UPDATE roles SET nom = ?, description = ? WHERE id_role = ?"
        );
        return $stmt->execute([$nom, $description, $id]);
    }

    public function deleteRole($id) {
        $stmt = $this->db->prepare("DELETE FROM roles WHERE id_role = ?");
        return $stmt->execute([$id]);
    }
}
