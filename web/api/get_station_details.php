<?php
// Dans api/get_station_details.php (à la racine)
header('Content-Type: application/json');
require_once '../config/db.php'; // Un seul ../ car on est dans api/

try {
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    if (empty($id)) { throw new Exception("Identifiant manquant."); }

    $sql = "SELECT s.*, p.puissance_nominale, p.tarification, 
                   p.prise_ef, p.prise_t2, p.prise_combo_ccs, p.prise_chademo, p.prise_autre
            FROM station s
            JOIN point_de_recharge p ON s.id_station_interne = p.id_station_interne
            WHERE s.id_station_interne = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $station = $stmt->fetch();

    if (!$station) { throw new Exception("Borne introuvable."); }
    echo json_encode($station);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>