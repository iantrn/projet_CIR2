<?php
header('Content-Type: application/json');
require_once '../config/db.php';

try {
    $amenageur = isset($_GET['amenageur']) ? $_GET['amenageur'] : '';
    $type_prise = isset($_GET['type_prise']) ? $_GET['type_prise'] : '';
    $departement = isset($_GET['departement']) ? $_GET['departement'] : '';

    // AJOUT : Sélection de la tarification et des indicateurs de prises
    $sql = "SELECT DISTINCT s.id_station_interne, s.nom_station, s.adresse_station, 
                    a.nom_amenageur_operateur, c.nom_commune,
                    p.tarification, p.prise_ef, p.prise_t2, p.prise_combo_ccs, p.prise_chademo, p.prise_autre
            FROM point_de_recharge p
            JOIN station s ON p.id_station_interne = s.id_station_interne
            JOIN commune c ON s.code_insee = c.code_insee
            LEFT JOIN amenageur_operateur a ON p.id_amenageur = a.id_amenageur
            WHERE 1=1";

    $params = [];

    if (!empty($amenageur)) {
        $sql .= " AND p.id_amenageur = :amenageur";
        $params['amenageur'] = $amenageur;
    }

    $allowed_prises = ['prise_ef', 'prise_t2', 'prise_combo_ccs', 'prise_chademo', 'prise_autre'];
    if (!empty($type_prise) && in_array($type_prise, $allowed_prises)) {
        $sql .= " AND p.$type_prise = 1";
    }

    if (!empty($departement)) {
        $sql .= " AND c.code_dep = :departement";
        $params['departement'] = $departement;
    }

    $sql .= " LIMIT 200";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    echo json_encode($results);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>