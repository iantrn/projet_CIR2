<?php
header('Content-Type: application/json');
require_once '../config/db.php';

try {
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    if (empty($id)) { throw new Exception("Identifiant manquant."); }

    // Requête géante pour récupérer absolument TOUTES les informations liées à la station
    $sql = "SELECT 
                s.*, 
                p.id_pdc_csv, p.puissance_nominale, p.date_mise_en_service, 
                p.prise_ef, p.prise_t2, p.prise_combo_ccs, p.prise_chademo, p.prise_autre, 
                p.cable_t2_attache, p.gratuit, p.paiment_acte, p.paiement_cb, p.paiement_autre, 
                p.tarification, p.libelle_raccordement,
                a.nom_amenageur_operateur, a.telephone_operateur, a.contact_operateur, a.contact_amenageur,
                c.nom_commune, c.code_postal, c.code_dep,
                d.nom_dep,
                e.nom_enseigne
            FROM station s
            LEFT JOIN point_de_recharge p ON s.id_station_interne = p.id_station_interne
            LEFT JOIN amenageur_operateur a ON p.id_amenageur = a.id_amenageur
            LEFT JOIN commune c ON s.code_insee = c.code_insee
            LEFT JOIN departement d ON c.code_dep = d.code_dep
            LEFT JOIN enseigne e ON s.id_enseigne = e.id_enseigne
            WHERE s.id_station_interne = :id
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $station = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$station) { throw new Exception("Borne introuvable."); }
    echo json_encode($station);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>