<?php 
// On inclut la connexion à la BDD
require_once 'config/db.php'; 

// Initialisation des variables pour éviter les erreurs d'affichage
$total = $annee2020 = $annee2021 = $annee2022 = 0;
$dept29 = $dept22 = $dept56 = $dept35 = 0;
$nbAmenageurs = $nbTypesPrise = 0;

try {
    // 1. Total des enregistrements
    $total = $pdo->query("SELECT COUNT(*) FROM bornes")->fetchColumn();

    // 2. Statistiques par année
    $annee2020 = $pdo->query("SELECT COUNT(*) FROM bornes WHERE annee = 2020")->fetchColumn();
    $annee2021 = $pdo->query("SELECT COUNT(*) FROM bornes WHERE annee = 2021")->fetchColumn();
    $annee2022 = $pdo->query("SELECT COUNT(*) FROM bornes WHERE annee = 2022")->fetchColumn();

    // 3. Statistiques par département
    $dept22 = $pdo->query("SELECT COUNT(*) FROM bornes WHERE departement = '22'")->fetchColumn();
    $dept29 = $pdo->query("SELECT COUNT(*) FROM bornes WHERE departement = '29'")->fetchColumn();
    $dept35 = $pdo->query("SELECT COUNT(*) FROM bornes WHERE departement = '35'")->fetchColumn();
    $dept56 = $pdo->query("SELECT COUNT(*) FROM bornes WHERE departement = '56'")->fetchColumn();

    // 4. Nombre d'aménageurs uniques
    $nbAmenageurs = $pdo->query("SELECT COUNT(DISTINCT amenageur) FROM bornes")->fetchColumn();

    // 5. Nombre de types de prise uniques
    $nbTypesPrise = $pdo->query("SELECT COUNT(DISTINCT type_prise) FROM bornes")->fetchColumn();

} catch (PDOException $e) {
    // En cas d'erreur de requête, on affiche un message dans la console ou discrètement
    $error_msg = "Erreur BDD : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BreizhWatt - Bornes de Recharge IRVE</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header id="app-header" class="app-header user-mode">
  <div class="mode-switcher">
    <button id="btn-mode-user" class="btn active">Utilisateur</button>
    <button id="btn-mode-admin" class="btn">Admin</button>
  </div>
</header>

<nav id="app-navbar" class="app-navbar user-mode-nav">
    <a href="accueil.php" class="nav-btn">Accueil</a>
    <a href="recherche.php" class="nav-btn">Recherche</a>
    <a href="carte.php" class="nav-btn">Carte</a>
</nav>

<div class="content-wrapper">
    <div class="presentation">
      <h2>Présentation</h2>
      <div class="presentation-box">
        <p>
          Dans le cadre du déploiement croissant de la mobilité électrique, ce 
          projet vise à concevoir et développer une application web dédiée à la 
          gestion et à la visualisation des points de recharge (PDC) pour 
          véhicules électriques en région Bretagne.
          L'application offrira aux utilisateurs une interface claire et intuitive 
          pour consulter l'ensemble des points de recharge disponibles sur le 
          territoire breton, avec la possibilité de filtrer et rechercher selon 
          différents critères (localisation, type de connecteur, puissance, 
          disponibilité, etc.).
        </p>
      </div>
    </div>

    <div class="statistiques">
      <h2>Statistiques</h2>

      <div class="stat-box">
        <div class="stat-row">
          <span class="stat-label">Nombre d'enregistrements</span>
          <span class="stat-arrow">→</span>
          <span class="stat-value" id="total"><?= htmlspecialchars($total) ?></span>
        </div>
      </div>

      <div class="stat-box">
        <div class="stat-row">
          <span class="stat-label">Nombre de points</span>
          <div class="stat-sub">
            <div class="stat-subrow">
              <span class="stat-dash">— 2020 →</span>
              <span class="stat-value" id="annee-2020"><?= htmlspecialchars($annee2020) ?></span>
            </div>
            <div class="stat-subrow">
              <span class="stat-dash">— 2021 →</span>
              <span class="stat-value" id="annee-2021"><?= htmlspecialchars($annee2021) ?></span>
            </div>
            <div class="stat-subrow">
              <span class="stat-dash">— 2022 →</span>
              <span class="stat-value" id="annee-2022"><?= htmlspecialchars($annee2022) ?></span>
            </div>
          </div>
        </div>
      </div>

      <div class="stat-box">
        <div class="stat-row">
          <span class="stat-label">Nombre de points</span>
          <div class="stat-sub">
            <div class="stat-subrow">
              <span class="stat-dash">— Finistère →</span>
              <span class="stat-value" id="dept-29"><?= htmlspecialchars($dept29) ?></span>
            </div>
            <div class="stat-subrow">
              <span class="stat-dash">— Côtes-d'Armor →</span>
              <span class="stat-value" id="dept-22"><?= htmlspecialchars($dept22) ?></span>
            </div>
            <div class="stat-subrow">
              <span class="stat-dash">— Morbihan →</span>
              <span class="stat-value" id="dept-56"><?= htmlspecialchars($dept56) ?></span>
            </div>
            <div class="stat-subrow">
              <span class="stat-dash">— Ille-et-Vilaine →</span>
              <span class="stat-value" id="dept-35"><?= htmlspecialchars($dept35) ?></span>
            </div>
          </div>
        </div>
      </div>

      <div class="stat-box">
        <div class="stat-row">
          <span class="stat-label">Nombre d'aménageurs</span>
          <span class="stat-arrow">→</span>
          <span class="stat-value" id="nb-amenageurs"><?= htmlspecialchars($nbAmenageurs) ?></span>
        </div>
      </div>

      <div class="stat-box">
        <div class="stat-row">
          <span class="stat-label">Nombre de type de prise</span>
          <span class="stat-arrow">→</span>
          <span class="stat-value" id="nb-types-prise"><?= htmlspecialchars($nbTypesPrise) ?></span>
        </div>
      </div>
    </div>
</div>

<footer>
  <p>© 2026 - CIR2 Gabriel T, Ian T</p>
</footer>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="js/main.js"></script>
</body>
</html>