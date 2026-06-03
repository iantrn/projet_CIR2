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
<div class="presentation">
  <h2>Présentation</h2>
  <div class="presentation-box">
    <p>
      Dans le cadre du déploiement croissant de la mobilité électrique, ce 
      projet vise à concevoir et développer une application web dédiée à la 
      gestion et à la visualisation des points de recharge (PDC) pour 
      véhicules électriques en région Bretagne.
      L'application offrira aux utilisateurs une interface claire et intuitive 
      pour consulter l'ensemble des points de recharge disponibles sur le 
      territoire breton, avec la possibilité de filtrer et rechercher selon 
      différents critères (localisation, type de connecteur, puissance, 
      disponibilité, etc.).
    </p>
  </div>
</div>
<div class="statistiques">
  <h2>Statistiques</h2>

  <!-- Total enregistrements -->
  <div class="stat-box">
    <div class="stat-row">
      <span class="stat-label">Nombre d'enregistrements</span>
      <span class="stat-arrow">→</span>
      <span class="stat-value" id="total">...</span>
    </div>
  </div>

  <!-- Par année -->
  <div class="stat-box">
    <div class="stat-row">
      <span class="stat-label">Nombre de points</span>
      <div class="stat-sub">
        <div class="stat-subrow">
          <span class="stat-dash">— 2020 →</span>
          <span class="stat-value" id="annee-2020">...</span>
        </div>
        <div class="stat-subrow">
          <span class="stat-dash">— 2021 →</span>
          <span class="stat-value" id="annee-2021">...</span>
        </div>
        <div class="stat-subrow">
          <span class="stat-dash">— 2022 →</span>
          <span class="stat-value" id="annee-2022">...</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Par département -->
  <div class="stat-box">
    <div class="stat-row">
      <span class="stat-label">Nombre de points</span>
      <div class="stat-sub">
        <div class="stat-subrow">
          <span class="stat-dash">— Finistère →</span>
          <span class="stat-value" id="dept-29">...</span>
        </div>
        <div class="stat-subrow">
          <span class="stat-dash">— Côtes-d'Armor →</span>
          <span class="stat-value" id="dept-22">...</span>
        </div>
        <div class="stat-subrow">
          <span class="stat-dash">— Morbihan →</span>
          <span class="stat-value" id="dept-56">...</span>
        </div>
        <div class="stat-subrow">
          <span class="stat-dash">— Ille-et-Vilaine →</span>
          <span class="stat-value" id="dept-35">...</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Nombre d'aménageurs -->
  <div class="stat-box">
    <div class="stat-row">
      <span class="stat-label">Nombre d'aménageurs</span>
      <span class="stat-arrow">→</span>
      <span class="stat-value" id="nb-amenageurs">...</span>
    </div>
  </div>

  <!-- Nombre de types de prise -->
  <div class="stat-box">
    <div class="stat-row">
      <span class="stat-label">Nombre de type de prise</span>
      <span class="stat-arrow">→</span>
      <span class="stat-value" id="nb-types-prise">...</span>
    </div>
  </div>

</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="js/main.js"></script>
<footer>
  <p>© 2026 - CIR2 Gabriel T, Ian T</p>
</footer>
</body>
</html>