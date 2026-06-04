<?php 
// On inclut la connexion à la BDD pour être prêt à faire des requêtes
require_once 'config/db.php'; 

$amenageurs = [];

try {
    // Consigne : 20 aménageurs pris au hasard (ORDER BY RAND() LIMIT 20)
    $stmtAmenageurs = $pdo->query("SELECT id_amenageur, nom_amenageur_operateur FROM amenageur_operateur ORDER BY RAND() LIMIT 20");
    $amenageurs = $stmtAmenageurs->fetchAll();
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

<div class="content-wrapper">
    <div class="formulaire">
      <h2>Recherche</h2>
      <div class="form-box">
    
        <div class="form-group">
          <label for="amenageur">Aménageur</label>
          <select id="amenageur" name="amenageur">
            <option value="">-- Sélectionner --</option>
            <?php foreach ($amenageurs as $row): ?>
                <option value="<?= htmlspecialchars($row['id_amenageur']) ?>"><?= htmlspecialchars($row['nom_amenageur_operateur']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
    
        <div class="form-group">
          <label for="type_prise">Type de prise</label>
          <select id="type_prise" name="type_prise">
            <option value="">-- Sélectionner --</option>
            <option value="prise_ef">Prise EF (Standard Non-Générique)</option>
            <option value="prise_t2">Prise Type 2 (T2)</option>
            <option value="prise_combo_ccs">Prise Combo CCS</option>
            <option value="prise_chademo">Prise CHAdeMO</option>
            <option value="prise_autre">Autre type de prise</option>
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

<div id="search-results-container" class="search-results hidden" style="max-width: 1000px; margin: 30px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
    <h3 style="margin-bottom: 15px; color: #0c1c3e;">Résultats de la recherche (<span id="results-count">0</span>)</h3>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-family: sans-serif; text-align: left;">
            <thead>
                <tr style="background-color: #f4f6f7; border-bottom: 2px solid #ccc;">
                    <th style="padding: 12px;">Station</th>
                    <th style="padding: 12px;">Aménageur</th>
                    <th style="padding: 12px;">Commune / Adresse</th>
                    <th style="padding: 12px;">Prises</th>
                    <th style="padding: 12px;">Tarification</th>
                    <th style="padding: 12px; display:none;" class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody id="search-table-body"></tbody>
        </table>
    </div>
</div>

<footer class="footer-user">
  <p>© 2026 - Espace Public CIR2 Gabriel T, Ian T</p>
</footer>

<!-- id="main-script" sans data-mode="admin" → mode lecture seule -->
<script src="js/main.js?v=2" id="main-script" data-location="frontfolder"></script>
</body>
</html>
