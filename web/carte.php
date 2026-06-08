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
              <label for="filter-annee">Année d'installation</label>
              <select id="filter-annee" name="filter-annee">
                <option value="">-- Toutes --</option>
                <?php foreach ($annees as $annee): ?>
                    <option value="<?= htmlspecialchars($annee) ?>"><?= htmlspecialchars($annee) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
        
            <div class="form-group">
              <label for="filter-departement">Département</label>
              <select id="filter-departement" name="filter-departement">
                <option value="">-- Tous --</option>
                <option value="22">Côtes-d'Armor (22)</option>
                <option value="29">Finistère (29)</option>
                <option value="35">Ille-et-Vilaine (35)</option>
                <option value="56">Morbihan (56)</option>
              </select>
            </div>
        
            <button type="button" class="btn-rechercher" id="btn-filter-carte">Filtrer les bornes</button>
          </div>
        </div>
    </section>

    <section class="map-container">
        <div id="map"></div>
    </section>
</main>
<div id="modal-detail" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); justify-content: center; align-items: center; z-index: 9999;">
    <div style="background: white; padding: 25px; border-radius: 12px; max-width: 500px; width: 100%; box-shadow: 0 4px 15px rgba(0,0,0,0.3); font-family: sans-serif; max-height: 90vh; overflow-y: auto;">
        <h3 style="margin-top: 0; border-bottom: 2px solid #3498db; padding-bottom: 10px; color: #0c1c3e;">👁️ Détails de la borne</h3>
        
        <div id="detail-content" style="margin-top: 15px; font-size: 14px; line-height: 1.6; color: #333;">
            <p>Chargement des informations...</p>
        </div>
        
        <button id="btn-close-detail" style="margin-top: 20px; width: 100%; padding: 10px; background: #3498db; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px;">Fermer</button>
    </div>
</div>
<footer class="footer-user">
  <p>© 2026 - Espace Public CIR2 Gabriel T, Ian T</p>
</footer>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="js/main.js?v=2" id="main-script" data-location="frontfolder"></script>
</body>
</html>
