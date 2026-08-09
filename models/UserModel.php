<?php
// models/UserModel.php

class UserModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnexion();
    }

    /* =============================
       RÉCUPÉRATION UTILISATEURS
       ============================= */

    public function getAllUsers() {
        $sql = "SELECT * FROM users ORDER BY nom, prenom";
        return $this->db->query($sql)->fetchAll();
    }

    public function getUserById(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id_user = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getUserByLogin(string $login): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->execute([$login]);
        return $stmt->fetch() ?: null;
    }

    public function getUserByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    /* =============================
       CRÉATION UTILISATEUR
       ============================= */

    public function createUser(array $data): bool {
        $sql = "INSERT INTO users 
                (login, password, nom, prenom, email, grade, poste, domaine_recherche, photo)
                VALUES (?,?,?,?,?,?,?,?,?)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $data['login'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['nom'],
            $data['prenom'],
            $data['email'],
            $data['grade'] ?? null,
            $data['poste'] ?? null,
            $data['domaine_recherche'] ?? null,
            $data['photo'] ?? 'default.jpg'
        ]);
    }

    /* =============================
       MISE À JOUR UTILISATEUR
       ============================= */

    public function updateUser(int $id, array $data): bool {

        if (!empty($data['password'])) {
            $this->updatePassword($id, $data['password']);
        }

        $sql = "UPDATE users SET
                nom = ?,
                prenom = ?,
                email = ?,
                grade = ?,
                poste = ?,
                domaine_recherche = ?,
                biographie = ?,
                photo = ?,
                statut = ?
                WHERE id_user = ?";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $data['nom'],
            $data['prenom'],
            $data['email'],
            $data['grade'] ?? null,
            $data['poste'] ?? null,
            $data['domaine_recherche'] ?? null,
            $data['biographie'] ?? null,
            $data['photo'] ?? 'default.jpg',
            $data['statut'] ?? 'actif',
            $id
        ]);
    }

    /* =============================
       SUPPRESSION
       ============================= */

    public function deleteUser(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id_user = ?");
        return $stmt->execute([$id]);
    }

    /* =============================
       MOT DE PASSE
       ============================= */

    public function updatePassword(int $id, string $password): bool {
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id_user = ?");
        return $stmt->execute([
            password_hash($password, PASSWORD_DEFAULT),
            $id
        ]);
    }

    /* =============================
       ROLES (RBAC)
       ============================= */

    public function getUserRoles(int $idUser): array {
        $sql = "
            SELECT r.*
            FROM roles r
            JOIN user_roles ur ON ur.id_role = r.id_role
            WHERE ur.id_user = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idUser]);
        return $stmt->fetchAll();
    }

    public function getUsersByRole(string $roleName): array {
        $sql = "
            SELECT u.*
            FROM users u
            JOIN user_roles ur ON ur.id_user = u.id_user
            JOIN roles r ON r.id_role = ur.id_role
            WHERE r.nom = ?
            ORDER BY u.nom, u.prenom
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleName]);
        return $stmt->fetchAll();
    }

    public function countUsersByRole(string $roleName): int {
        $sql = "
            SELECT COUNT(*) AS total
            FROM user_roles ur
            JOIN roles r ON r.id_role = ur.id_role
            WHERE r.nom = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleName]);
        return (int)$stmt->fetch()['total'];
    }

    /* =============================
       RECHERCHE
       ============================= */

    public function searchUsers(string $keyword): array {
        $keyword = "%$keyword%";
        $sql = "
            SELECT *
            FROM users
            WHERE nom LIKE ?
               OR prenom LIKE ?
               OR email LIKE ?
               OR domaine_recherche LIKE ?
            ORDER BY nom, prenom
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$keyword, $keyword, $keyword, $keyword]);
        return $stmt->fetchAll();
    }

    /* =============================
       ORGANIGRAMME
       ============================= */

    public function getUsersWithPoste(): array {
        $sql = "
            SELECT *
            FROM users
            WHERE poste IS NOT NULL
              AND poste <> ''
              AND poste <> 'Administrateur'
            ORDER BY poste, nom, prenom
        ";
        return $this->db->query($sql)->fetchAll();
    }
}
?>
