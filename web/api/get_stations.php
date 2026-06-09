<?php
header('Content-Type: application/json');
require_once '../config/db.php';

try {
    $annee = isset($_GET['annee']) ? $_GET['annee'] : '';
    $departement = isset($_GET['departement']) ? $_GET['departement'] : '';

    // Ajout des colonnes de puissance, tarification et prises dans la sélection
    $sql = "SELECT DISTINCT s.id_station_interne, s.nom_station, s.adresse_station, s.latitude, s.longitude, 
                            a.nom_amenageur_operateur, c.nom_commune,
                            p.puissance_nominale, p.tarification, 
                            p.prise_ef, p.prise_t2, p.prise_combo_ccs, p.prise_chademo, p.prise_autre
            FROM station s
            JOIN point_de_recharge p ON s.id_station_interne = p.id_station_interne
            JOIN commune c ON s.code_insee = c.code_insee
            LEFT JOIN amenageur_operateur a ON p.id_amenageur = a.id_amenageur
            WHERE s.latitude IS NOT NULL AND s.longitude IS NOT NULL";

    $params = [];
    if (!empty($annee)) { $sql .= " AND YEAR(p.date_mise_en_service) = :annee"; $params['annee'] = (int)$annee; }
    if (!empty($departement)) { $sql .= " AND c.code_dep = :departement"; $params['departement'] = $departement; }
    $sql .= " LIMIT 1000";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll());

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>