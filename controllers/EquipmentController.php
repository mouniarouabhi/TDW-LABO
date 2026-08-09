<?php
// controllers/EquipmentController.php
// Point 7 du PDF: Gestion des équipements et ressources (1.5 pts)

class EquipmentController {
    private $equipmentModel;
    private $reservationModel;
    private $settingModel;
    
    public function __construct() {
        $this->equipmentModel = new EquipmentModel();
        $this->reservationModel = new ReservationModel();
        $this->settingModel = new SettingModel();
    }
    
    /**
     * Liste des équipements disponibles
     * Spécifications PDF:
     * - Équipements: salles, serveurs, PC, robots, imprimantes, capteurs, etc.
     * - État: libre, réservé, en maintenance
     * - Système de réservation en ligne (créneau d'utilisation)
     * - Historique des réservations
     * - Alertes automatiques en cas de conflits
     * - Statistiques d'utilisation des ressources
     */
    public function index() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        
        // Filtres
        $filterType = $_GET['type'] ?? null;
        $filterEtat = $_GET['etat'] ?? null;
        
        // Récupérer tous les équipements
        $equipments = $this->equipmentModel->getAllEquipments();
        
        // Appliquer les filtres
        if ($filterType) {
            $equipments = array_filter($equipments, function($e) use ($filterType) {
                return $e['type_equipment'] === $filterType;
            });
        }
        
        if ($filterEtat) {
            $equipments = array_filter($equipments, function($e) use ($filterEtat) {
                return $e['etat'] === $filterEtat;
            });
        }
        
        // Calculer l'état actuel de chaque équipement (réservé ou libre)
        foreach ($equipments as &$equipment) {
            if ($equipment['etat'] !== 'en_maintenance') {
                // Vérifier s'il y a une réservation active
                $hasActiveReservation = $this->reservationModel->hasActiveReservation($equipment['id_equipment']);
                $equipment['etat_actuel'] = $hasActiveReservation ? 'reserve' : 'libre';
            } else {
                $equipment['etat_actuel'] = 'en_maintenance';
            }
        }
        
        // Listes pour les filtres
        $allEquipments = $this->equipmentModel->getAllEquipments();
        $types = array_unique(array_filter(array_column($allEquipments, 'type_equipment')));
        sort($types);
        
        EquipmentView::renderListe([
            'settings' => $settings,
            'equipments' => $equipments,
            'types' => $types,
            'filterType' => $filterType,
            'filterEtat' => $filterEtat
        ]);
    }
    
    /**
     * Formulaire de réservation en ligne
     */
    public function reserver($id) {
        // Vérifier l'authentification
        AuthController::requireAuth();
        
        $settings = $this->settingModel->getAllSettingsAsArray();
        $equipment = $this->equipmentModel->getEquipmentById($id);
        
        if (!$equipment) {
            header('Location: index.php?url=equipments&error=not_found');
            exit;
        }
        
        // Vérifier que l'équipement est disponible
        if ($equipment['etat'] === 'en_maintenance') {
            header('Location: index.php?url=equipments&error=en_maintenance');
            exit;
        }
        
        // Récupérer les réservations existantes pour détecter conflits
        $reservations = $this->reservationModel->getReservationsByEquipment($id);
        
        EquipmentView::renderReservation([
            'settings' => $settings,
            'equipment' => $equipment,
            'reservations' => $reservations,
            'user' => AuthController::getCurrentUser(),
            'error' => $_GET['error'] ?? null
        ]);
    }
    
    /**
     * Traiter la réservation (avec alerte conflits)
     */
    public function traiterReservation() {
        AuthController::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=equipments');
            exit;
        }
        
        $user = AuthController::getCurrentUser();
        
        $idEquipment = $_POST['id_equipment'] ?? null;
        $dateDebut = $_POST['date_debut'] ?? '';
        $dateFin = $_POST['date_fin'] ?? '';
        $motif = $_POST['motif'] ?? '';
        
        // Validation
        if (empty($idEquipment) || empty($dateDebut) || empty($dateFin)) {
            header('Location: index.php?url=equipment/reserver/' . $idEquipment . '&error=empty_fields');
            exit;
        }
        
        // Vérifier que date début < date fin
        if (strtotime($dateDebut) >= strtotime($dateFin)) {
            header('Location: index.php?url=equipment/reserver/' . $idEquipment . '&error=invalid_dates');
            exit;
        }
        
        // ALERTE CONFLIT: Vérifier les conflits de réservation
        $conflict = $this->reservationModel->checkConflict($idEquipment, $dateDebut, $dateFin);
        if ($conflict) {
            header('Location: index.php?url=equipment/reserver/' . $idEquipment . '&error=conflict');
            exit;
        }
        
        // Créer la réservation
        $data = [
            'id_equipment' => $idEquipment,
            'id_user' => $user['id_user'],
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'motif' => $motif,
            'statut' => 'en_attente'
        ];
        
        if ($this->reservationModel->createReservation($data)) {
            header('Location: index.php?url=equipment/historique&success=created');
        } else {
            header('Location: index.php?url=equipment/reserver/' . $idEquipment . '&error=failed');
        }
        exit;
    }
    
    /**
     * Historique des réservations
     */
    public function historique() {
        AuthController::requireAuth();
        
        $settings = $this->settingModel->getAllSettingsAsArray();
        $user = AuthController::getCurrentUser();
        
        // Toutes les réservations de l'utilisateur
        $reservations = $this->reservationModel->getReservationsByUser($user['id_user']);
        
        EquipmentView::renderHistorique([
            'settings' => $settings,
            'reservations' => $reservations,
            'user' => $user,
            'success' => $_GET['success'] ?? null
        ]);
    }
    
    /**
     * Statistiques d'utilisation (optionnel mais mentionné dans PDF)
     */
    public function statistiques() {
        $settings = $this->settingModel->getAllSettingsAsArray();
        
        // Statistiques générales
        $stats = [
            'total_equipments' => count($this->equipmentModel->getAllEquipments()),
            'equipments_libres' => count($this->equipmentModel->getEquipmentsByState('libre')),
            'equipments_reserves' => count($this->equipmentModel->getEquipmentsByState('reserve')),
            'equipments_maintenance' => count($this->equipmentModel->getEquipmentsByState('en_maintenance')),
            'total_reservations' => count($this->reservationModel->getAllReservations()),
            'reservations_active' => count($this->reservationModel->getActiveReservations())
        ];
        
        // Taux d'occupation par équipement
        $equipments = $this->equipmentModel->getAllEquipments();
        foreach ($equipments as &$eq) {
            $eq['taux_occupation'] = $this->reservationModel->getTauxOccupation($eq['id_equipment']);
        }
        
        EquipmentView::renderStatistiques([
            'settings' => $settings,
            'stats' => $stats,
            'equipments' => $equipments
        ]);
    }
    
    /**
     * Annuler une réservation
     */
    public function annuler($id) {
        AuthController::requireAuth();
        
        $user = AuthController::getCurrentUser();
        $reservation = $this->reservationModel->getReservationById($id);
        
        // Vérifier que c'est bien la réservation de l'utilisateur
        if (!$reservation || $reservation['id_user'] != $user['id_user']) {
            header('Location: index.php?url=equipment/historique&error=unauthorized');
            exit;
        }
        
        // Annuler la réservation
        if ($this->reservationModel->updateReservationStatus($id, 'annulee')) {
            header('Location: index.php?url=equipment/historique&success=cancelled');
        } else {
            header('Location: index.php?url=equipment/historique&error=failed');
        }
        exit;
    }
}
?>