<?php 
require_once '../config/db.php'; 

$total = $annee2020 = $annee2021 = $annee2022 = $annee2023 = $annee2024 = $annee2025 = $annee2026 = 0;
$dept29 = $dept22 = $dept56 = $dept35 = 0;
$nbAmenageurs = 0;
$nbTypesPrise = 5; 

try {
    $total = $pdo->query("SELECT COUNT(*) FROM point_de_recharge")->fetchColumn();
    $annee2020 = $pdo->query("SELECT COUNT(*) FROM point_de_recharge WHERE YEAR(date_mise_en_service) = 2020")->fetchColumn();
    $annee2021 = $pdo->query("SELECT COUNT(*) FROM point_de_recharge WHERE YEAR(date_mise_en_service) = 2021")->fetchColumn();
    $annee2022 = $pdo->query("SELECT COUNT(*) FROM point_de_recharge WHERE YEAR(date_mise_en_service) = 2022")->fetchColumn();
    $annee2023 = $pdo->query("SELECT COUNT(*) FROM point_de_recharge WHERE YEAR(date_mise_en_service) = 2023")->fetchColumn();
    $annee2024 = $pdo->query("SELECT COUNT(*) FROM point_de_recharge WHERE YEAR(date_mise_en_service) = 2024")->fetchColumn();
    $annee2025 = $pdo->query("SELECT COUNT(*) FROM point_de_recharge WHERE YEAR(date_mise_en_service) = 2025")->fetchColumn();
    $annee2026 = $pdo->query("SELECT COUNT(*) FROM point_de_recharge WHERE YEAR(date_mise_en_service) = 2026")->fetchColumn();

    $totalAnnees = $annee2020 + $annee2021 + $annee2022 + $annee2023 + $annee2024 + $annee2025 + $annee2026;

    $baseDeptQuery = "SELECT COUNT(*) FROM point_de_recharge p 
                      JOIN station s ON p.id_station_interne = s.id_station_interne 
                      JOIN commune c ON s.code_insee = c.code_insee 
                      WHERE c.code_dep = ";
                      
    $dept22 = $pdo->query($baseDeptQuery . "'22'")->fetchColumn();
    $dept29 = $pdo->query($baseDeptQuery . "'29'")->fetchColumn();
    $dept35 = $pdo->query($baseDeptQuery . "'35'")->fetchColumn();
    $dept56 = $pdo->query($baseDeptQuery . "'56'")->fetchColumn();
    $totalDepts = $dept22 + $dept29 + $dept35 + $dept56;

    $nbAmenageurs = $pdo->query("SELECT COUNT(*) FROM amenageur_operateur")->fetchColumn();
} catch (PDOException $e) {
    $error_msg = "Erreur BDD Admin : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BreizhWatt - Admin</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Change la couleur du footer uniquement en présence du header admin */
        body:has(.admin-mode) footer {
            background-color: #1a1a1a !important;
            background: #1a1a1a !important;
        }
        body:has(.admin-mode) footer p {
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
    <a href="accueil.php" class="nav-btn active">Accueil Admin</a>
    <a href="recherche.php" class="nav-btn">Gestion des Bornes</a>
    <a href="carte.php" class="nav-btn">Carte Admin</a>
</nav>

<div class="content-wrapper">
    <div class="presentation">
      <h2>Espace Administration</h2>
      <div class="presentation-box">
        <p>
          Bienvenue sur le tableau de bord de gestion de l'infrastructure BreizhWatt. Cet espace vous permet de suivre l'état général du réseau et d'accéder aux outils d'ajout, de modification et de suppression des points de recharge de la région Bretagne.
        </p>
      </div>
    </div>

    <div class="statistiques">
      <h2>Statistiques Globales</h2>

      <div class="stat-box">
        <div class="stat-row">
          <span class="stat-label">Nombre d'enregistrements</span>
          <span class="stat-arrow">→</span>
          <span class="stat-value" id="total"><?= htmlspecialchars($total) ?></span>
        </div>
      </div>

      <div class="stat-box">
        <div class="stat-row">
          <span class="stat-label">Nombre de points par année</span>
          <div class="stat-sub">
            <div class="stat-subrow"><span class="stat-dash">— 2020 →</span><span class="stat-value"><?= htmlspecialchars($annee2020) ?></span></div>
            <div class="stat-subrow"><span class="stat-dash">— 2021 →</span><span class="stat-value"><?= htmlspecialchars($annee2021) ?></span></div>
            <div class="stat-subrow"><span class="stat-dash">— 2022 →</span><span class="stat-value"><?= htmlspecialchars($annee2022) ?></span></div>
            <div class="stat-subrow"><span class="stat-dash">— 2023 →</span><span class="stat-value"><?= htmlspecialchars($annee2023) ?></span></div>
            <div class="stat-subrow"><span class="stat-dash">— 2024 →</span><span class="stat-value"><?= htmlspecialchars($annee2024) ?></span></div>
            <div class="stat-subrow"><span class="stat-dash">— 2025 →</span><span class="stat-value"><?= htmlspecialchars($annee2025) ?></span></div>
            <div class="stat-subrow"><span class="stat-dash">— 2026 →</span><span class="stat-value"><?= htmlspecialchars($annee2026) ?></span></div>
            <div class="stat-subrow" style="border-top: 1px dashed #bbb; margin-top: 5px; padding-top: 5px; font-weight: bold; color: #d32f2f;">
              <span class="stat-dash">TOTAL →</span>
              <span class="stat-value"><?= htmlspecialchars($totalAnnees) ?></span>
            </div>
          </div>
        </div>
      </div>

      <div class="stat-box">
        <div class="stat-row">
          <span class="stat-label">Nombre de points par département</span>
          <div class="stat-sub">
            <div class="stat-subrow"><span class="stat-dash">— Finistère →</span><span class="stat-value"><?= htmlspecialchars($dept29) ?></span></div>
            <div class="stat-subrow"><span class="stat-dash">— Côtes-d'Armor →</span><span class="stat-value"><?= htmlspecialchars($dept22) ?></span></div>
            <div class="stat-subrow"><span class="stat-dash">— Morbihan →</span><span class="stat-value"><?= htmlspecialchars($dept56) ?></span></div>
            <div class="stat-subrow"><span class="stat-dash">— Ille-et-Vilaine →</span><span class="stat-value"><?= htmlspecialchars($dept35) ?></span></div>
            <div class="stat-subrow" style="border-top: 1px dashed #bbb; margin-top: 5px; padding-top: 5px; font-weight: bold; color: #d32f2f;">
              <span class="stat-dash">TOTAL RÉGIONAL →</span>
              <span class="stat-value"><?= htmlspecialchars($totalDepts) ?></span>
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
          <span class="stat-label">Nombre de types de prise</span>
          <span class="stat-arrow">→</span>
          <span class="stat-value" id="nb-types-prise"><?= htmlspecialchars($nbTypesPrise) ?></span>
        </div>
      </div>
    </div>
</div>

<footer class="footer-admin">
  <p>© 2026 - Espace Privé CIR2 Gabriel T, Ian T</p>
</footer>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../js/main.js?v=back" id="main-script" data-mode="admin"></script>
</body>
</html>