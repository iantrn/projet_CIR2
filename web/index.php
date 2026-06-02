<?php 
require_once 'config/db.php'; 
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

    <header class="app-header">
        <h1>BreizhWatt</h1>
        <div class="mode-switcher">
            <button id="btn-mode-user" class="btn active">Mode Utilisateur</button>
            <button id="btn-mode-admin" class="btn">Mode Admin</button>
        </div>
    </header>

    <main class="main-container">
        
        <section class="sidebar">
            <div class="search-box">
                <input type="text" id="search-input" placeholder="Rechercher une ville, une station...">
                <button id="btn-search" class="btn-primary">Rechercher</button>
            </div>

            <div id="admin-actions" class="admin-only hidden">
                <button id="btn-add-station" class="btn-success">+ Ajouter une station</button>
            </div>

            <div class="list-container" id="stations-list">
                <h3>Stations à proximité</h3>
                <div class="scrollable-list">
                    <div class="station-card">
                        <h4>Electric 50 Charg</h4>
                        <p>4 Rue de l'Énergie, Rennes</p>
                        <div class="card-buttons">
                            <button class="btn-small btn-detail">Détail</button>
                            <button class="btn-small btn-edit admin-only hidden">Modifier</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="map-container">
            <div id="map"></div>
        </section>

    </main>

    <div id="modal-detail" class="modal hidden">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2 id="detail-title">Détails de la Station</h2>
            <div id="detail-body"></div>
        </div>
    </div>

    <div id="modal-add" class="modal hidden">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Ajouter une nouvelle borne</h2>
            <form id="form-add-station" action="admin/ajouter.php" method="POST">
                <label>Nom de la station :</label>
                <input type="text" name="nom_station" required>
                <label>Adresse :</label>
                <input type="text" name="adresse_station" required>
                <label>Puissance (kW) :</label>
                <input type="number" name="puissance" required>
                <button type="submit" class="btn-success" style="margin-top: 15px;">Enregistrer en BDD</button>
            </form>
        </div>
    </div>

    <div id="modal-edit" class="modal hidden">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Modifier la borne</h2>
            <form id="form-edit-station" action="admin/modifier.php" method="POST">
                <input type="hidden" name="id_station" id="edit-station-id">
                <label>Nom de la station :</label>
                <input type="text" name="nom_station" id="edit-nom" required>
                <label>Tarification :</label>
                <input type="text" name="tarification" id="edit-tarif">
                <button type="submit" class="btn-primary" style="margin-top: 15px; width: 100%;">Mettre à jour</button>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="js/main.js"></script>
</body>
</html>