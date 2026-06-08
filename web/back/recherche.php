<?php 
require_once '../config/db.php'; 

$amenageurs = [];
try {
    $stmtAmenageurs = $pdo->query("SELECT id_amenageur, nom_amenageur_operateur FROM amenageur_operateur ORDER BY nom_amenageur_operateur ASC");
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
    <title>BreizhWatt - Gestion Bornes</title>
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
    <a href="recherche.php" class="nav-btn active">Gestion des Bornes</a>
    <a href="carte.php" class="nav-btn">Carte Admin</a>
</nav>

<div class="content-wrapper">
    <div class="formulaire">
      <h2>Recherche & Gestion des Stations</h2>
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
    
        <div style="display: flex; flex-direction: column; gap: 10px; justify-content: center; align-items: center; width: 100%; max-width: 280px; margin-left: 15px; align-self: flex-end;">
            <button type="button" class="btn-rechercher" id="btn-search" style="width: 100%; margin: 0; padding: 10px; box-sizing: border-box; height: 40px;">
            Rechercher
            </button>
            <button type="button" id="btn-open-add-modal" style="width: 100%; background-color: #2ecc71; color: white; border: none; padding: 10px; border-radius: 4px; cursor: pointer; font-weight: bold; box-sizing: border-box; font-family: sans-serif; font-size: 14px; height: 40px; display: flex; align-items: center; justify-content: center;">
            ➕ Ajouter une station
            </button>
        </div>
    
      </div>
    </div>

    <div id="search-results-container" style="max-width: 1050px; margin: 30px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: none;">
        <h3 style="margin-bottom: 15px; color: #0c1c3e;">Résultats trouvés (<span id="results-count">0</span>)</h3>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-family: sans-serif; text-align: left;">
                <thead>
                    <tr style="background-color: #f4f6f7; border-bottom: 2px solid #ccc;">
                        <th style="padding: 12px;">Station</th>
                        <th style="padding: 12px;">Aménageur</th>
                        <th style="padding: 12px;">Commune / Adresse</th>
                        <th style="padding: 12px;">Prises</th>
                        <th style="padding: 12px;">Tarification</th>
                        <th style="padding: 12px;">Actions</th> 
                    </tr>
                </thead>
                <tbody id="search-table-body"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="modal-edit" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; font-family: sans-serif;">
    <div style="background: white; padding: 25px; border-radius: 8px; width: 100%; max-width: 500px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto;">
        <h3 style="margin-top: 0; color: #0c1c3e; border-bottom: 2px solid #d32f2f; padding-bottom: 8px;">✏️ Modifier la Station</h3>
        
        <form id="form-edit-station">
            <input type="hidden" id="edit-id-station" name="id_station">

            <div style="margin-bottom: 12px;">
                <label style="display:block; font-weight:bold; margin-bottom:4px;">Nom de la Station</label>
                <input type="text" id="edit-nom" name="nom_station" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 12px;">
                <label style="display:block; font-weight:bold; margin-bottom:4px;">Adresse Complète</label>
                <input type="text" id="edit-adresse" name="adresse_station" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 12px;">
                <label style="display:block; font-weight:bold; margin-bottom:4px;">Puissance (kW)</label>
                <input type="number" id="edit-puissance" name="puissance_nominale" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 12px;">
                <label style="display:block; font-weight:bold; margin-bottom:4px;">Régime de tarification</label>
                <textarea id="edit-tarification" name="tarification" rows="2" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px; box-sizing: border-box; font-family: sans-serif;"></textarea>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display:block; font-weight:bold; margin-bottom:6px;">Connecteurs disponibles</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <label><input type="checkbox" id="edit-prise-ef" name="prise_ef"> Prise EF</label>
                    <label><input type="checkbox" id="edit-prise-t2" name="prise_t2"> Prise T2</label>
                    <label><input type="checkbox" id="edit-prise-ccs" name="prise_combo_ccs"> Combo CCS</label>
                    <label><input type="checkbox" id="edit-prise-cha" name="prise_chademo"> CHAdeMO</label>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" id="btn-close-modal" style="background:#888; color:white; border:none; padding:8px 15px; border-radius:4px; cursor:pointer;">Annuler</button>
                <button type="submit" style="background:#d32f2f; color:white; border:none; padding:8px 15px; border-radius:4px; cursor:pointer; font-weight:bold;">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
<div id="modal-detail" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); justify-content: center; align-items: center; z-index: 9999;">
    <div style="background: white; padding: 25px; border-radius: 12px; max-width: 500px; width: 100%; box-shadow: 0 4px 15px rgba(0,0,0,0.3); font-family: sans-serif; max-height: 90vh; overflow-y: auto;">
        <h3 style="margin-top: 0; border-bottom: 2px solid #3498db; padding-bottom: 10px; color: #0c1c3e;">👁️ Détails de la borne</h3>
        
        <div id="detail-content" style="margin-top: 15px; font-size: 14px; line-height: 1.6; color: #333;">
            <p>Chargement des informations...</p>
        </div>
        
        <button id="btn-close-detail" style="margin-top: 20px; width: 100%; padding: 10px; background: #3498db; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px;">Fermer</button>
    </div>
</div>
<div id="modal-add" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); justify-content: center; align-items: center; z-index: 9999;">
    <div style="background: white; padding: 25px; border-radius: 12px; max-width: 500px; width: 100%; box-shadow: 0 4px 15px rgba(0,0,0,0.3); font-family: sans-serif; max-height: 90vh; overflow-y: auto;">
        <h3 style="margin-top: 0; border-bottom: 2px solid #2ecc71; padding-bottom: 10px; color: #0c1c3e;">➕ Ajouter une nouvelle borne</h3>
        
        <form id="form-add-station" style="margin-top: 15px;">
            <div style="margin-bottom: 12px;">
                <label style="display:block; font-weight:bold; margin-bottom:4px;">Nom de la station :</label>
                <input type="text" name="nom_station" required style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px;">
            </div>
            
            <div style="margin-bottom: 12px;">
                <label style="display:block; font-weight:bold; margin-bottom:4px;">Adresse complète :</label>
                <input type="text" name="adresse_station" required style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px;">
            </div>
            
            <div style="display: flex; gap: 10px; margin-bottom: 12px;">
                <div style="flex: 1;">
                    <label style="display:block; font-weight:bold; margin-bottom:4px;">Latitude :</label>
                    <input type="number" step="any" name="latitude" placeholder="ex: 47.84" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px;">
                </div>
                <div style="flex: 1;">
                    <label style="display:block; font-weight:bold; margin-bottom:4px;">Longitude :</label>
                    <input type="number" step="any" name="longitude" placeholder="ex: -3.88" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px;">
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-bottom: 12px;">
                <div style="flex: 1;">
                    <label style="display:block; font-weight:bold; margin-bottom:4px;">Puissance (kW) :</label>
                    <input type="number" step="0.01" name="puissance" value="22.00" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px;">
                </div>
            </div>

            <div style="margin-bottom: 12px;">
                <label style="display:block; font-weight:bold; margin-bottom:4px;">Tarification / Prix :</label>
                <input type="text" name="tarification" placeholder="ex: 0.40 kw/h" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px;">
            </div>

            <div style="margin-bottom: 18px; background: #f7fafc; padding: 10px; border-radius: 6px; border: 1px solid #edf2f7;">
                <label style="display:block; font-weight:bold; margin-bottom:6px;">Prises disponibles :</label>
                <label style="display:block; margin-bottom:4px;"><input type="checkbox" name="prise_ef" value="1"> Prise Domestique (EF)</label>
                <label style="display:block; margin-bottom:4px;"><input type="checkbox" name="prise_t2" value="1" checked> Type 2 (Standard AC)</label>
                <label style="display:block; margin-bottom:4px;"><input type="checkbox" name="prise_combo_ccs" value="1"> Combo CCS (Rapide DC)</label>
                <label style="display:block;"><input type="checkbox" name="prise_chademo" value="1"> CHAdeMO</label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" id="btn-close-add" style="background:#e2e8f0; border:none; padding:8px 15px; border-radius:4px; cursor:pointer;">Annuler</button>
                <button type="submit" style="background:#2ecc71; color:white; border:none; padding:8px 15px; border-radius:4px; cursor:pointer; font-weight:bold;">Ajouter</button>
            </div>
        </form>
    </div>
</div>
<footer class="footer-admin">
  <p>© 2026 - Espace Privé CIR2 Gabriel T, Ian T</p>
</footer>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../js/main.js?v=2" id="main-script" data-location="backfolder" data-mode="admin"></script>
</body>
</html>