<?php 
require_once '../config/db.php'; 

$annees = [];
try {
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
    <title>BreizhWatt - Carte Admin</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="../css/style.css">
    <style>
        footer {
            background: #1a1a1a !important;
            background-color: #1a1a1a !important;
        }
        footer p {
            color: #ffffff !important;
            font-weight: bold !important;
        }
    </style>
</head>
<body>

<?php if (!empty($error_msg)): ?>
    <div style="background-color: #ffebee; color: #c62828; border: 2px solid #ef5350; padding: 15px; margin: 20px; border-radius: 8px; font-family: sans-serif;">
        ⚠️ <?= htmlspecialchars($error_msg) ?>
    </div>
<?php endif; ?>

<header id="app-header" class="app-header admin-mode" style="background-color: #1c2833; display: flex; justify-content: flex-end; align-items: center; padding: 10px 20px;">
  <div class="mode-switcher">
    <button id="btn-mode-user" class="btn">Quitter l'Admin</button>
    <button id="btn-mode-admin" class="btn active" style="background-color: #d32f2f; border-color: #d32f2f;">🔒 Mode Administrateur</button>
  </div>
</header>

<nav id="app-navbar" class="app-navbar admin-mode-nav">
    <a href="accueil.php" class="nav-btn">Accueil Admin</a>
    <a href="recherche.php" class="nav-btn">Gestion des Bornes</a>
    <a href="carte.php" class="nav-btn active">Carte Admin</a>
</nav>

<div class="content-wrapper">
    <div class="carte-container" style="max-width: 1000px; margin: 20px auto; padding: 15px; background: white; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <h2 style="margin-top: 0; color: #0c1c3e;">Carte de Supervision Technique</h2>
        
        <div class="filters" style="display: flex; gap: 15px; margin-bottom: 15px; font-family: sans-serif; flex-wrap: wrap;">
            <div>
                <label style="font-weight: bold; margin-right: 5px;">Année :</label>
                <select id="filter-annee" style="padding: 5px; border-radius: 4px;">
                    <option value="">Toutes</option>
                    <?php foreach ($annees as $annee): ?>
                        <option value="<?= htmlspecialchars($annee) ?>"><?= htmlspecialchars($annee) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-weight: bold; margin-right: 5px;">Département :</label>
                <select id="filter-departement" style="padding: 5px; border-radius: 4px;">
                    <option value="">Tous</option>
                    <option value="22">Côtes-d'Armor (22)</option>
                    <option value="29">Finistère (29)</option>
                    <option value="35">Ille-et-Vilaine (35)</option>
                    <option value="56">Morbihan (56)</option>
                </select>
            </div>
        </div>

        <div id="map" style="height: 550px; width: 100%; border-radius: 6px; border: 1px solid #ccc;"></div>
    </div>
</div>


<footer class="footer-admin">
  <p>© 2026 - Espace Privé CIR2 Gabriel T, Ian T</p>
</footer>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- data-location="backfolder" indique à main.js d'utiliser "../api/" comme préfixe -->
<script src="../js/main.js?v=2" id="main-script" data-location="backfolder" data-mode="admin"></script>
</body>
</html>
