<?php 
// On inclut la connexion à la BDD pour être prêt à faire des requêtes
require_once 'config/db.php'; 
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
  <img src="img/baniere_bleue.png" alt="BreizhWatt" style="width:100%; display:block;">
  <div class="mode-switcher">
    <button class="btn active">Utilisateur</button>
    <button class="btn">Admin</button>
  </div>
</header>

<nav id="app-navbar" class="app-navbar user-mode-nav">
    <a href="accueil.php" class="nav-btn">Accueil</a>
    <a href="recherche.php" class="nav-btn">Recherche</a>
    <a href="carte.php" class="nav-btn">Carte</a>
</nav>

<div class="formulaire">
  <div class="form-box">

    <div class="form-group">
      <label for="annee">Année d'installation</label>
      <select id="annee" name="annee">
        <option value="">-- Sélectionner --</option>
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

    <button type="button" class="btn-rechercher">Rechercher</button>

  </div>
</div>


<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="js/main.js"></script>
<footer>
  <p>© 2026 - CIR2 Gabriel T, Ian T</p>
</footer>
</body>
</html>