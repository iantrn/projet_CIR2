<?php 
// Connexion à la base de données (on remonte d'un dossier car on est dans /back)
require_once '../config/db.php'; 

// Initialisation des compteurs
$total = $annee2020 = $annee2021 = $annee2022 = $annee2023 = $annee2024 = $annee2025 = $annee2026 = 0;
$dept29 = $dept22 = $dept56 = $dept35 = 0;
$nbAmenageurs = 0;
$nbTypesPrise = 5; 
$statsAnneeDept = []; // Pour la stat croisée
$bornes100 = [];      // Pour le tableau des 100 items max

try {
    // 1. Nombre total de points
    $total = $pdo->query("SELECT COUNT(*) FROM point_de_recharge")->fetchColumn();

    // 2. Compteurs par année
    $annee2020 = $pdo->query("SELECT COUNT(*) FROM point_de_recharge WHERE YEAR(date_mise_en_service) = 2020")->fetchColumn();
    $annee2021 = $pdo->query("SELECT COUNT(*) FROM point_de_recharge WHERE YEAR(date_mise_en_service) = 2021")->fetchColumn();
    $annee2022 = $pdo->query("SELECT COUNT(*) FROM point_de_recharge WHERE YEAR(date_mise_en_service) = 2022")->fetchColumn();
    $annee2023 = $pdo->query("SELECT COUNT(*) FROM point_de_recharge WHERE YEAR(date_mise_en_service) = 2023")->fetchColumn();
    $annee2024 = $pdo->query("SELECT COUNT(*) FROM point_de_recharge WHERE YEAR(date_mise_en_service) = 2024")->fetchColumn();
    $annee2025 = $pdo->query("SELECT COUNT(*) FROM point_de_recharge WHERE YEAR(date_mise_en_service) = 2025")->fetchColumn();
    $annee2026 = $pdo->query("SELECT COUNT(*) FROM point_de_recharge WHERE YEAR(date_mise_en_service) = 2026")->fetchColumn();

    $totalAnnees = $annee2020 + $annee2021 + $annee2022 + $annee2023 + $annee2024 + $annee2025 + $annee2026;

    // 3. Compteurs par département avec des JOIN
    $baseDeptQuery = "SELECT COUNT(*) FROM point_de_recharge p 
                      JOIN station s ON p.id_station_interne = s.id_station_interne 
                      JOIN commune c ON s.code_insee = c.code_insee 
                      WHERE c.code_dep = ";
                      
    $dept22 = $pdo->query($baseDeptQuery . "'22'")->fetchColumn();
    $dept29 = $pdo->query($baseDeptQuery . "'29'")->fetchColumn();
    $dept35 = $pdo->query($baseDeptQuery . "'35'")->fetchColumn();
    $dept56 = $pdo->query($baseDeptQuery . "'56'")->fetchColumn();
    $totalDepts = $dept22 + $dept29 + $dept35 + $dept56;

    // 4. Statistique croisée année et département
    $sqlCroise = "SELECT YEAR(p.date_mise_en_service) as annee, d.nom_dep, COUNT(p.id_pdc_interne) as total_points
                  FROM point_de_recharge p
                  JOIN station s ON p.id_station_interne = s.id_station_interne
                  JOIN commune c ON s.code_insee = c.code_insee
                  JOIN departement d ON c.code_dep = d.code_dep
                  WHERE YEAR(p.date_mise_en_service) BETWEEN 2020 AND 2026
                  GROUP BY YEAR(p.date_mise_en_service), d.nom_dep
                  ORDER BY annee ASC, d.nom_dep ASC";
    $statsAnneeDept = $pdo->query($sqlCroise)->fetchAll(PDO::FETCH_ASSOC);

    // 5. Nombre d'aménageurs
    $nbAmenageurs = $pdo->query("SELECT COUNT(*) FROM amenageur_operateur")->fetchColumn();

    // 6. Récupération des 100 premières bornes (demande du sujet pour l'accueil admin)
    $sql100 = "SELECT s.id_station_interne, s.nom_station, c.nom_commune, c.code_dep 
               FROM station s 
               JOIN commune c ON s.code_insee = c.code_insee 
               ORDER BY s.id_station_interne DESC 
               LIMIT 100";
    $bornes100 = $pdo->query($sql100)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Récupération de l'erreur SQL
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
        <div class="stat-row" style="align-items: flex-start; flex-direction: column;">
          <span class="stat-label" style="margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px; width: 100%;">Nombre de points par année et par département</span>
          <div style="max-height: 180px; overflow-y: auto; width: 100%; font-size: 14px;">
            <table style="width: 100%; text-align: left; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f4f6f7;">
                        <th style="padding: 5px; border-bottom: 1px solid #ddd;">Année</th>
                        <th style="padding: 5px; border-bottom: 1px solid #ddd;">Département</th>
                        <th style="padding: 5px; border-bottom: 1px solid #ddd; text-align: right;">Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($statsAnneeDept as $row): ?>
                        <tr>
                            <td style="padding: 5px; border-bottom: 1px solid #eee;"><?= htmlspecialchars($row['annee']) ?></td>
                            <td style="padding: 5px; border-bottom: 1px solid #eee;"><?= htmlspecialchars($row['nom_dep']) ?></td>
                            <td style="padding: 5px; border-bottom: 1px solid #eee; text-align: right; font-weight: bold; color: #d32f2f;"><?= htmlspecialchars($row['total_points']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
          </div>
        </div>
      </div>

      <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 25px;">
          <div class="stat-box" style="flex: 1; min-width: 250px;">
            <div class="stat-row">
              <span class="stat-label">Nombre d'aménageurs</span>
              <span class="stat-arrow">→</span>
              <span class="stat-value" id="nb-amenageurs"><?= htmlspecialchars($nbAmenageurs) ?></span>
            </div>
          </div>

          <div class="stat-box" style="flex: 1; min-width: 250px;">
            <div class="stat-row">
              <span class="stat-label">Nombre de types de prise</span>
              <span class="stat-arrow">→</span>
              <span class="stat-value" id="nb-types-prise"><?= htmlspecialchars($nbTypesPrise) ?></span>
            </div>
          </div>
      </div>

      <h2>Aperçu des installations du réseau (100 max)</h2>
      <div class="stat-box" style="padding: 15px;">
          <div style="max-height: 350px; overflow-y: auto;">
              <table style="width: 100%; border-collapse: collapse; font-size: 14px; text-align: left;">
                  <thead>
                      <tr style="background-color: #1c2833; color: white;">
                          <th style="padding: 10px; border: 1px solid #ddd;">ID Interne</th>
                          <th style="padding: 10px; border: 1px solid #ddd;">Nom de la Station</th>
                          <th style="padding: 10px; border: 1px solid #ddd;">Commune</th>
                          <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">Département</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php if (!empty($bornes100)): ?>
                          <?php // Boucle pour générer les 100 lignes du tableau ?>
                          <?php foreach ($bornes100 as $borne): ?>
                              <tr style="border-bottom: 1px solid #ddd;">
                                  <td style="padding: 8px; font-weight: bold; color: #d32f2f;"><?= htmlspecialchars($borne['id_station_interne']) ?></td>
                                  <td style="padding: 8px;"><?= htmlspecialchars($borne['nom_station']) ?></td>
                                  <td style="padding: 8px;"><?= htmlspecialchars($borne['nom_commune']) ?></td>
                                  <td style="padding: 8px; text-align: center;"><?= htmlspecialchars($borne['code_dep']) ?></td>
                              </tr>
                          <?php endforeach; ?>
                      <?php else: ?>
                          <tr><td colspan="4" style="padding: 10px; text-align: center;">Aucune installation trouvée.</td></tr>
                      <?php endif; ?>
                  </tbody>
              </table>
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