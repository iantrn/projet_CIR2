<?php
// On indique au navigateur que ce script renvoie du JSON et non du HTML
header('Content-Type: application/json');

// Connexion à la base de données (on remonte d'un dossier pour atteindre config)
require_once '../config/db.php';

try {
    // Récupération sécurisée des filtres depuis l'URL (GET)
    $annee = isset($_GET['annee']) ? $_GET['annee'] : '';
    $departement = isset($_GET['departement']) ? $_GET['departement'] : '';

    // Requête de base pour récupérer l'emplacement des stations uniques
    $sql = "SELECT DISTINCT s.id_station_interne, s.nom_station, s.adresse_station, s.latitude, s.longitude 
            FROM station s
            JOIN point_de_recharge p ON s.id_station_interne = p.id_station_interne
            JOIN commune c ON s.code_insee = c.code_insee
            WHERE s.latitude IS NOT NULL AND s.longitude IS NOT NULL";

    $params = [];

    // Si une année est sélectionnée, on l'ajoute à la requête
    if (!empty($annee)) {
        $sql .= " AND YEAR(p.date_mise_en_service) = :annee";
        $params['annee'] = (int)$annee;
    }

    // Si un département est sélectionné, on l'ajoute à la requête
    if (!empty($departement)) {
        $sql .= " AND c.code_dep = :departement";
        $params['departement'] = $departement;
    }

    // Sécurité performance : la base IRVE nationale peut contenir des dizaines de milliers de lignes.
    // Pour éviter de faire ramer le navigateur, on limite à 1000 bornes max s'il n'y a pas de filtres précis.
    $sql .= " LIMIT 1000";

    // Préparation et exécution de la requête
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $stations = $stmt->fetchAll();

    // On envoie le résultat final converti proprement en JSON
    echo json_encode($stations);

} catch (Exception $e) {
    // En cas de bug, on renvoie le message d'erreur au format JSON
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>