<?php
// models/SettingModel.php

class SettingModel {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnexion();
    }

    /* =============================
       GET SETTINGS
       ============================= */

    /**
     * Get all settings as associative array
     * @return array ['cle' => 'valeur', ...]
     */
    public function getAllSettingsAsArray() {
        $sql = "SELECT cle, valeur FROM site_settings";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['cle']] = $row['valeur'];
        }
        
        return $result;
    }

    /**
     * Get setting value by key
     * @param string $cle
     * @return string|null
     */
    public function getSettingValue($cle) {
        $sql = "SELECT valeur FROM site_settings WHERE cle = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cle]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['valeur'] : null;
    }

    /* =============================
       UPDATE/CREATE SETTINGS
       ============================= */

    /**
     * Update or create setting (UPSERT)
     * @param string $cle
     * @param string $valeur
     * @return bool
     */
    public function updateSetting($cle, $valeur) {
        try {
            // Try INSERT ... ON DUPLICATE KEY UPDATE
            $sql = "
                INSERT INTO site_settings (cle, valeur) 
                VALUES (:cle, :valeur)
                ON DUPLICATE KEY UPDATE 
                    valeur = :valeur2,
                    date_modification = CURRENT_TIMESTAMP
            ";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':cle' => $cle,
                ':valeur' => $valeur,
                ':valeur2' => $valeur
            ]);
            
        } catch (PDOException $e) {
            // Fallback: check if exists, then UPDATE or INSERT
            if ($this->settingExists($cle)) {
                $sql = "UPDATE site_settings SET valeur = ?, date_modification = CURRENT_TIMESTAMP WHERE cle = ?";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([$valeur, $cle]);
            } else {
                return $this->createSetting($cle, $valeur);
            }
        }
    }

    /**
     * Create new setting
     * @param string $cle
     * @param string $valeur
     * @param string|null $description
     * @return bool
     */
    public function createSetting($cle, $valeur, $description = null) {
        $sql = "INSERT INTO site_settings (cle, valeur, description) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$cle, $valeur, $description]);
    }

    /**
     * Check if setting exists
     * @param string $cle
     * @return bool
     */
    public function settingExists($cle) {
        $sql = "SELECT COUNT(*) FROM site_settings WHERE cle = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cle]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /* =============================
       DELETE SETTINGS
       ============================= */

    /**
     * Delete setting by key
     * @param string $cle
     * @return bool
     */
    public function deleteSetting($cle) {
        $sql = "DELETE FROM site_settings WHERE cle = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$cle]);
    }
}
?>