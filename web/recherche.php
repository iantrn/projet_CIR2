<?php 
// On inclut la connexion à la BDD
require_once 'config/db.php'; 

$amenageurs = [];
$prises = [];

try {
    // 1. Récupérer la liste unique des aménageurs (triée par ordre alphabétique)
    $stmtAmenageurs = $pdo->query("SELECT DISTINCT amenageur FROM bornes WHERE amenageur IS NOT NULL AND amenageur != '' ORDER BY amenageur ASC");
    $amenageurs = $stmtAmenageurs->fetchAll(PDO::FETCH_COLUMN);

    // 2. Récupérer la liste unique des types de prise (triée par ordre alphabétique)
    $stmtPrises = $pdo->query("SELECT DISTINCT type_prise FROM bornes WHERE type_prise IS NOT NULL AND type_prise != '' ORDER BY type_prise ASC");
    $prises = $stmtPrises->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
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
    <div class="formulaire">
      <h2>Recherche</h2>
      <div class="form-box">
    
        <div class="form-group">
          <label for="amenageur">Aménageur</label>
          <select id="amenageur" name="amenageur">
            <option value="">-- Sélectionner --</option>
            <?php foreach ($amenageurs as $amenageur): ?>
                <option value="<?= htmlspecialchars($amenageur) ?>"><?= htmlspecialchars($amenageur) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
    
        <div class="form-group">
          <label for="type_prise">Type de prise</label>
          <select id="type_prise" name="type_prise">
            <option value="">-- Sélectionner --</option>
            <?php foreach ($prises as $prise): ?>
                <option value="<?= htmlspecialchars($prise) ?>"><?= htmlspecialchars($prise) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
    
        <div class="form-group">
          <label for="departement">Département</label>
          <select id="departement" name="departement">
            <option value="">-- Sélectionner --</option>
            <option value="22">Côtes-d'Armor (22)</option>
            <option value="29">Finistère (29)</option>
            <option value="35">Ille-et-Vilaine (35)</option>
            <option value="56">Morbihan (56)</option>
          </select>
        </div>
    
        <button type="button" class="btn-rechercher" id="btn-search">Rechercher</button>
    
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