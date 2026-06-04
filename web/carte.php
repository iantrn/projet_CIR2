<?php 
// On inclut la connexion à la BDD pour être prêt à faire des requêtes
require_once 'config/db.php'; 

$annees = [];

try {
    // Récupération dynamique des années réelles à partir de date_mise_en_service
    $stmtAnnees = $pdo->query("SELECT DISTINCT YEAR(date_mise_en_service) as annee FROM point_de_recharge WHERE date_mise_en_service IS NOT NULL ORDER BY annee DESC");
    $annees = $stmtAnnees->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $error_msg = "Erreur BDD : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BreizhWatt</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php if (!empty($error_msg)): ?>
    <div style="background-color: #ffebee; color: #c62828; border: 2px solid #ef5350; padding: 15px; margin: 20px; border-radius: 8px; font-family: sans-serif;">
        ⚠️ <?= htmlspecialchars($error_msg) ?>
    </div>
<?php endif; ?>

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

<main class="main-container">
    <section class="sidebar">
        <div class="formulaire-carte">
          <h2>Filtrer la carte</h2>
          <div class="form-box">
            <div class="form-group">
              <label for="annee">Année d'installation</label>
              <select id="annee" name="annee">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($annees as $annee): ?>
                    <option value="<?= htmlspecialchars($annee) ?>"><?= htmlspecialchars($annee) ?></option>
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
        
            <button type="button" class="btn-rechercher">Filtrer les bornes</button>
          </div>
          
          <button id="btn-add-station" class="btn-success admin-only hidden" style="margin-top: 15px;">+ Ajouter une Borne</button>
        </div>
    </section>

    <section class="map-container">
        <div id="map"></div>
    </section>
</main>

<footer class="footer-user">
  <p>© 2026 - Espace Public CIR2 Gabriel T, Ian T</p>
</footer>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="js/main.js?v=1"></script>  </body>
</html>