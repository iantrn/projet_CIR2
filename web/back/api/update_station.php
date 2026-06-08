<?php
// On indique au navigateur/JS qu'on va renvoyer du JSON
header('Content-Type: application/json');

// Inclusion de la connexion (on remonte d'un dossier car on est dans /api)
require_once '../config/db.php';

// Vérification de sécurité : il faut au moins l'ID de la station
if (empty($_POST['id_station'])) {
    echo json_encode(['success' => false, 'error' => 'ID de la station manquant.']);
    exit;
}

try {
    // On lance une transaction pour être sûr que les deux UPDATE fonctionnent ensemble
    $pdo->beginTransaction();

    // 1. Mise à jour des infos générales dans la table 'station'
    $sqlStation = "UPDATE station SET 
                    nom_station = :nom_station, 
                    adresse_station = :adresse_station,
                    tarification = :tarification
                   WHERE id_station_interne = :id_station";
    
    $stmt1 = $pdo->prepare($sqlStation);
    $stmt1->execute([
        ':nom_station'     => $_POST['nom_station'] ?? '',
        ':adresse_station' => $_POST['adresse_station'] ?? '',
        ':tarification'    => $_POST['tarification'] ?? '',
        ':id_station'      => $_POST['id_station']
    ]);

    // 2. Mise à jour de la puissance et des prises dans la table 'point_de_recharge'
    $sqlPdc = "UPDATE point_de_recharge SET 
                puissance_nominale = :puissance,
                prise_ef = :prise_ef,
                prise_t2 = :prise_t2,
                prise_combo_ccs = :prise_combo_ccs,
                prise_chademo = :prise_chademo
               WHERE id_station_interne = :id_station";

    $stmt2 = $pdo->prepare($sqlPdc);
    $stmt2->execute([
        ':puissance'       => !empty($_POST['puissance']) ? floatval($_POST['puissance']) : null,
        ':prise_ef'        => intval($_POST['prise_ef'] ?? 0),
        ':prise_t2'        => intval($_POST['prise_t2'] ?? 0),
        ':prise_combo_ccs' => intval($_POST['prise_combo_ccs'] ?? 0),
        ':prise_chademo'   => intval($_POST['prise_chademo'] ?? 0),
        ':id_station'      => $_POST['id_station']
    ]);

    // Si aucune erreur, on valide définitivement les changements dans la BDD
    $pdo->commit();

    // On renvoie la réponse de succès attendue par le JS
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    // Si ça plante, on annule tout pour pas foutre le bazar dans la BDD
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // On renvoie l'erreur au JS pour l'afficher dans l'alert
    echo json_encode(['success' => false, 'error' => 'Erreur SQL : ' . $e->getMessage()]);
}