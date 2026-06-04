<?php
header('Content-Type: application/json');
require_once '../config/db.php';

try {
    $amenageur = isset($_GET['amenageur']) ? $_GET['amenageur'] : '';
    $type_prise = isset($_GET['type_prise']) ? $_GET['type_prise'] : '';
    $departement = isset($_GET['departement']) ? $_GET['departement'] : '';

    // CORRECTION REQUÊTE : On groupe par station et on utilise MAX() pour consolider les données techniques
    $sql = "SELECT s.id_station_interne, 
                   s.nom_station, 
                   s.adresse_station, 
                   a.nom_amenageur_operateur, 
                   c.nom_commune,
                   MAX(p.tarification) AS tarification,
                   MAX(p.prise_ef) AS prise_ef,
                   MAX(p.prise_t2) AS prise_t2,
                   MAX(p.prise_combo_ccs) AS prise_combo_ccs,
                   MAX(p.prise_chademo) AS prise_chademo,
                   MAX(p.prise_autre) AS prise_autre
            FROM station s
            JOIN point_de_recharge p ON s.id_station_interne = p.id_station_interne
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

    // On ajoute le GROUP BY indispensable pour fusionner les points de recharge d'une même station
    $sql .= " GROUP BY s.id_station_interne, s.nom_station, s.adresse_station, a.nom_amenageur_operateur, c.nom_commune";
    
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