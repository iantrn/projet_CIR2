<?php
header('Content-Type: application/json');
// On remonte de deux dossiers pour aller de "back/api/" à la racine
require_once '../../config/db.php'; 

try {
    $id_station = isset($_POST['id_station']) ? $_POST['id_station'] : '';

    if (empty($id_station)) {
        throw new Exception("Identifiant de la station manquant.");
    }

    $pdo->beginTransaction();

    // 1. Supprimer les points de recharge reliés à la station
    $sqlPdc = "DELETE FROM point_de_recharge WHERE id_station_interne = :id";
    $stmtPdc = $pdo->prepare($sqlPdc);
    $stmtPdc->execute(['id' => $id_station]);

    // 2. Supprimer la station
    $sqlStation = "DELETE FROM station WHERE id_station_interne = :id";
    $stmtStation = $pdo->prepare($sqlStation);
    $stmtStation->execute(['id' => $id_station]);

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>