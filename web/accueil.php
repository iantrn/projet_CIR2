<?php 
// Inclusion du fichier de connexion à la base de données
require_once 'config/db.php'; 

// Initialisation des compteurs à 0
$total = $annee2020 = $annee2021 = $annee2022 = $annee2023 = $annee2024 = $annee2025 = $annee2026 = 0;
$dept29 = $dept22 = $dept56 = $dept35 = 0;
$nbAmenageurs = 0;
$nbTypesPrise = 5; 
$statsAnneeDept = []; // Tableau pour la stat croisée

try {
    // 1. Nombre total de points de recharge
    $total = $pdo->query("SELECT COUNT(*) FROM point_de_recharge")->fetchColumn();

    // 2. Compteurs par année (on extrait l'année avec YEAR)
    $annee2020 = $pdo->query("SELECT COUNT(*) FROM point_de_recharge WHERE YEAR(date_mise_en_service) = 2020")->fetchColumn();
    $annee2021 = $pdo->query("SELECT COUNT(*) FROM point_de_recharge WHERE YEAR(date_mise_en_service) = 2021")->fetchColumn();
    $annee2022 = $pdo->query("SELECT COUNT(*) FROM point_de_recharge WHERE YEAR(date_mise_en_service) = 2022")->fetchColumn();
    $annee2023 = $pdo->query("SELECT COUNT(*) FROM point_de_recharge WHERE YEAR(date_mise_en_service) = 2023")->fetchColumn();
    $annee2024 = $pdo->query("SELECT COUNT(*) FROM point_de_recharge WHERE YEAR(date_mise_en_service) = 2024")->fetchColumn();
    $annee2025 = $pdo->query("SELECT COUNT(*) FROM point_de_recharge WHERE YEAR(date_mise_en_service) = 2025")->fetchColumn();
    $annee2026 = $pdo->query("SELECT COUNT(*) FROM point_de_recharge WHERE YEAR(date_mise_en_service) = 2026")->fetchColumn();

    $totalAnnees = $annee2020 + $annee2021 + $annee2022 + $annee2023 + $annee2024 + $annee2025 + $annee2026;

    // 3. Compteurs par département (on passe par des JOIN pour atteindre le code_dep de la commune)
    $baseDeptQuery = "SELECT COUNT(*) FROM point_de_recharge p 
                      JOIN station s ON p.id_station_interne = s.id_station_interne 
                      JOIN commune c ON s.code_insee = c.code_insee 
                      WHERE c.code_dep = ";
                      
    $dept22 = $pdo->query($baseDeptQuery . "'22'")->fetchColumn();
    $dept29 = $pdo->query($baseDeptQuery . "'29'")->fetchColumn();
    $dept35 = $pdo->query($baseDeptQuery . "'35'")->fetchColumn();
    $dept56 = $pdo->query($baseDeptQuery . "'56'")->fetchColumn();

    $totalDepts = $dept22 + $dept29 + $dept35 + $dept56;

    // 4. Statistique croisée demandée : points par année ET par département
    $sqlCroise = "SELECT YEAR(p.date_mise_en_service) as annee, d.nom_dep, COUNT(p.id_pdc_interne) as total_points
                  FROM point_de_recharge p
                  JOIN station s ON p.id_station_interne = s.id_station_interne
                  JOIN commune c ON s.code_insee = c.code_insee
                  JOIN departement d ON c.code_dep = d.code_dep
                  WHERE YEAR(p.date_mise_en_service) BETWEEN 2020 AND 2026
                  GROUP BY YEAR(p.date_mise_en_service), d.nom_dep
                  ORDER BY annee ASC, d.nom_dep ASC";
    
    $statsAnneeDept = $pdo->query($sqlCroise)->fetchAll(PDO::FETCH_ASSOC);

    // 5. Nombre total d'aménageurs uniques
    $nbAmenageurs = $pdo->query("SELECT COUNT(*) FROM amenageur_operateur")->fetchColumn();

} catch (PDOException $e) {
    // Récupération du message d'erreur en cas de problème SQL
    $error_msg = "Erreur BDD : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BreizhWatt - Accueil</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php if (!empty($error_msg)): ?>
    <div style="background-color: #ffebee; color: #c62828; border: 2px solid #ef5350; padding: 15px; margin: 20px; border-radius: 8px;">
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
    <a href="accueil.php" class="nav-btn active">Accueil</a>
    <a href="recherche.php" class="nav-btn">Recherche</a>
    <a href="carte.php" class="nav-btn">Carte</a>
</nav>

<div class="content-wrapper">
    <div class="presentation">
      <h2>Présentation du projet BreizhWatt</h2>
      <div class="presentation-box">
        <p>
          Dans le cadre du déploiement croissant de la mobilité électrique, ce 
          projet vise à concevoir et développer une application web dédiée à la 
          gestion et à la visualisation des points de recharge (PDC) pour 
          véhicules électriques en région Bretagne.
        </p>
        <p style="margin-top: 10px;">
          L'application offre aux utilisateurs une interface claire et intuitive 
          pour consulter l'ensemble des points de recharge disponibles sur le 
          territoire breton, avec la possibilité de filtrer et rechercher selon 
          différents critères (localisation, type de connecteur, puissance, aménageur).
        </p>
      </div>
    </div>

    <div class="statistiques">
      <h2>Statistiques du réseau IRVE Breton</h2>

      <div class="stat-box">
        <div class="stat-row">
          <span class="stat-label">Nombre total de points de recharge en base</span>
          <span class="stat-arrow">→</span>
          <span class="stat-value" id="total"><?= htmlspecialchars($total) ?></span>
        </div>
      </div>

      <div class="stat-box">
        <div class="stat-row">
          <span class="stat-label">Évolution : Nombre de points par année de mise en service</span>
          <div class="stat-sub">
            <div class="stat-subrow"><span class="stat-dash">— 2020 →</span><span class="stat-value"><?= htmlspecialchars($annee2020) ?></span></div>
            <div class="stat-subrow"><span class="stat-dash">— 2021 →</span><span class="stat-value"><?= htmlspecialchars($annee2021) ?></span></div>
            <div class="stat-subrow"><span class="stat-dash">— 2022 →</span><span class="stat-value"><?= htmlspecialchars($annee2022) ?></span></div>
            <div class="stat-subrow"><span class="stat-dash">— 2023 →</span><span class="stat-value"><?= htmlspecialchars($annee2023) ?></span></div>
            <div class="stat-subrow"><span class="stat-dash">— 2024 →</span><span class="stat-value"><?= htmlspecialchars($annee2024) ?></span></div>
            <div class="stat-subrow"><span class="stat-dash">— 2025 →</span><span class="stat-value"><?= htmlspecialchars($annee2025) ?></span></div>
            <div class="stat-subrow"><span class="stat-dash">— 2026 →</span><span class="stat-value"><?= htmlspecialchars($annee2026) ?></span></div>
            <div class="stat-subrow" style="border-top: 1px dashed #bbb; margin-top: 5px; padding-top: 5px; font-weight: bold;">
              <span class="stat-dash" style="color: #0c1c3e;">TOTAL CONNU →</span>
              <span class="stat-value" style="color: #0c1c3e;"><?= htmlspecialchars($totalAnnees) ?></span>
            </div>
          </div>
        </div>
      </div>

      <div class="stat-box">
        <div class="stat-row">
          <span class="stat-label">Répartition : Nombre de points par département</span>
          <div class="stat-sub">
            <div class="stat-subrow"><span class="stat-dash">— Finistère (29) →</span><span class="stat-value"><?= htmlspecialchars($dept29) ?></span></div>
            <div class="stat-subrow"><span class="stat-dash">— Côtes-d'Armor (22) →</span><span class="stat-value"><?= htmlspecialchars($dept22) ?></span></div>
            <div class="stat-subrow"><span class="stat-dash">— Morbihan (56) →</span><span class="stat-value"><?= htmlspecialchars($dept56) ?></span></div>
            <div class="stat-subrow"><span class="stat-dash">— Ille-et-Vilaine (35) →</span><span class="stat-value"><?= htmlspecialchars($dept35) ?></span></div>
            <div class="stat-subrow" style="border-top: 1px dashed #bbb; margin-top: 5px; padding-top: 5px; font-weight: bold;">
              <span class="stat-dash" style="color: #0c1c3e;">TOTAL REGIONAL →</span>
              <span class="stat-value" style="color: #0c1c3e;"><?= htmlspecialchars($totalDepts) ?></span>
            </div>
          </div>
        </div>
      </div>

      <div class="stat-box">
        <div class="stat-row" style="align-items: flex-start; flex-direction: column;">
          <span class="stat-label" style="margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px; width: 100%;">Nombre de points par année ET par département</span>
          <div style="max-height: 200px; overflow-y: auto; width: 100%; font-size: 14px;">
            <table style="width: 100%; text-align: left; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f4f6f7;">
                        <th style="padding: 5px; border-bottom: 1px solid #ddd;">Année</th>
                        <th style="padding: 5px; border-bottom: 1px solid #ddd;">Département</th>
                        <th style="padding: 5px; border-bottom: 1px solid #ddd; text-align: right;">Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($statsAnneeDept)): ?>
                        <?php // Boucle PHP pour afficher les lignes de la statistique croisée ?>
                        <?php foreach ($statsAnneeDept as $row): ?>
                            <tr>
                                <td style="padding: 5px; border-bottom: 1px solid #eee;"><?= htmlspecialchars($row['annee']) ?></td>
                                <td style="padding: 5px; border-bottom: 1px solid #eee;"><?= htmlspecialchars($row['nom_dep']) ?></td>
                                <td style="padding: 5px; border-bottom: 1px solid #eee; text-align: right; font-weight: bold; color: #3498db;"><?= htmlspecialchars($row['total_points']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align:center; padding:10px;">Aucune donnée disponible.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
          </div>
        </div>
      </div>

      <div style="display: flex; gap: 20px; flex-wrap: wrap;">
          <div class="stat-box" style="flex: 1; min-width: 250px;">
            <div class="stat-row">
              <span class="stat-label">Nombre d'aménageurs</span>
              <span class="stat-arrow">→</span>
              <span class="stat-value"><?= htmlspecialchars($nbAmenageurs) ?></span>
            </div>
          </div>

          <div class="stat-box" style="flex: 1; min-width: 250px;">
            <div class="stat-row">
              <span class="stat-label">Types de prises répertoriés</span>
              <span class="stat-arrow">→</span>
              <span class="stat-value"><?= htmlspecialchars($nbTypesPrise) ?></span>
            </div>
          </div>
      </div>

    </div>
</div>

<footer class="footer-user">
  <p>© 2026 - Espace Public CIR2 Gabriel T, Ian T</p>
</footer>

<script src="js/main.js?v=3" id="main-script" data-location="frontfolder"></script>
</body>
</html>