<?php 
require_once '../config/db.php'; 
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
        /* Force la couleur #1a1a1a et le gras sur le footer uniquement en mode admin */
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
        
        <div class="filters" style="display: flex; gap: 15px; margin-bottom: 15px; font-family: sans-serif;">
            <div>
                <label style="font-weight: bold; margin-right: 5px;">Année :</label>
                <select id="filter-annee" style="padding: 5px; border-radius: 4px;">
                    <option value="">Toutes</option>
                    <option value="2020">2020</option>
                    <option value="2021">2021</option>
                    <option value="2022">2022</option>
                    <option value="2023">2023</option>
                    <option value="2024">2024</option>
                    <option value="2025">2025</option>
                    <option value="2026">2026</option>
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
<script src="../js/main.js?v=back" id="main-script" data-mode="admin"></script>
</body>
</html>