<?php
// Fichier : api/add_station.php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    // 1. On démarre une transaction pour s'assurer que si une des deux insertions échoue, rien ne s'enregistre
    $pdo->beginTransaction();

    // 2. Récupération des données du formulaire (avec valeurs par défaut sécurisées si le champ est vide)
    $nom_station     = $_POST['nom_station'] ?? 'Nouvelle Station';
    $adresse_station = $_POST['adresse_station'] ?? 'Adresse non spécifiée';
    $latitude        = isset($_POST['latitude']) && $_POST['latitude'] !== '' ? (float)$_POST['latitude'] : 48.2020;
    $longitude       = isset($_POST['longitude']) && $_POST['longitude'] !== '' ? (float)$_POST['longitude'] : -2.9326;
    
    $puissance       = isset($_POST['puissance']) && $_POST['puissance'] !== '' ? (float)$_POST['puissance'] : 22.00;
    $tarification    = $_POST['tarification'] ?? 'Non spécifié';
    
    // Les cases à cocher renvoient "on", "1" ou true si cochées. On sécurise en 0 ou 1.
    $prise_ef  = !empty($_POST['prise_ef']) ? 1 : 0;
    $prise_t2  = !empty($_POST['prise_t2']) ? 1 : 0;
    $prise_ccs = !empty($_POST['prise_ccs']) || !empty($_POST['prise_combo_ccs']) ? 1 : 0;
    $prise_cha = !empty($_POST['prise_cha']) || !empty($_POST['prise_chademo']) ? 1 : 0;
    
    // Génération d'identifiants uniques obligatoires
    $id_itinerance = uniqid('FR*BW*');
    $id_local      = uniqid('LOC*');
    $id_pdc        = uniqid('PDC*');
    $date_jour     = date('Y-m-d');

    // 3. Gestion intelligente des Clés Étrangères (Foreign Keys)
    // Fonction utilitaire pour récupérer une valeur valide dans la base si on ne l'a pas en POST
    function getDefaultFK($pdo, $table, $column) {
        $stmt = $pdo->query("SELECT $column FROM $table LIMIT 1");
        return $stmt->fetchColumn();
    }

    $code_insee = !empty($_POST['code_insee']) ? $_POST['code_insee'] : getDefaultFK($pdo, 'commune', 'code_insee');
    $id_enseigne = !empty($_POST['id_enseigne']) ? $_POST['id_enseigne'] : getDefaultFK($pdo, 'enseigne', 'id_enseigne');
    $libelle_implantation = !empty($_POST['libelle_implantation']) ? $_POST['libelle_implantation'] : getDefaultFK($pdo, 'implantation_station', 'libelle_implantation');
    $libelle_condition_acces = !empty($_POST['libelle_condition_acces']) ? $_POST['libelle_condition_acces'] : getDefaultFK($pdo, 'condition_acces', 'libelle_condition_acces');
    $libelle_horaires = !empty($_POST['libelle_horaires']) ? $_POST['libelle_horaires'] : getDefaultFK($pdo, 'horaires', 'libelle_horaires');
    
    $id_amenageur = !empty($_POST['id_amenageur']) ? $_POST['id_amenageur'] : getDefaultFK($pdo, 'amenageur_operateur', 'id_amenageur');
    $libelle_raccordement = !empty($_POST['libelle_raccordement']) ? $_POST['libelle_raccordement'] : getDefaultFK($pdo, 'raccordement', 'libelle_raccordement');

    // 4. Insertion dans la table STATION (Parent)
    $sqlStation = "INSERT INTO station 
        (id_station_itinerance, id_station_local, nom_station, adresse_station, longitude, latitude, code_insee, id_enseigne, libelle_implantation, libelle_condition_acces, libelle_horaires) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
    $stmtStation = $pdo->prepare($sqlStation);
    $stmtStation->execute([
        $id_itinerance, $id_local, $nom_station, $adresse_station, $longitude, $latitude, 
        $code_insee, $id_enseigne, $libelle_implantation, $libelle_condition_acces, $libelle_horaires
    ]);
    
    // On récupère l'ID généré automatiquement pour la station
    $id_station_interne = $pdo->lastInsertId();

    // 5. Insertion dans la table POINT_DE_RECHARGE (Enfant)
    $sqlPdc = "INSERT INTO point_de_recharge 
        (id_pdc_csv, puissance_nominale, date_mise_en_service, prise_ef, prise_t2, prise_combo_ccs, prise_chademo, prise_autre, cable_t2_attache, gratuit, paiment_acte, paiement_cb, paiement_autre, tarification, id_station_interne, libelle_raccordement, id_amenageur) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
    $stmtPdc = $pdo->prepare($sqlPdc);
    $stmtPdc->execute([
        $id_pdc, $puissance, $date_jour, 
        $prise_ef, $prise_t2, $prise_ccs, $prise_cha, 0, // Prise autre à 0
        0, 0, 0, 0, 0, // Câble et moyens de paiement mis à 0 (Non) par défaut pour simplifier
        $tarification, $id_station_interne, $libelle_raccordement, $id_amenageur
    ]);

    // 6. Si on arrive ici sans erreur, on valide définitivement l'insertion dans la base
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'La station a été ajoutée avec succès.']);

} catch (PDOException $e) {
    // S'il y a la moindre erreur, on annule tout (Rollback) pour ne pas créer de "demi-données"
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Erreur SQL lors de l\'ajout : ' . $e->getMessage()]);
}
?>