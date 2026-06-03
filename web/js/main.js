// =========================================================================
// 1. Initialisation de la carte Leaflet et du groupe de marqueurs
// =========================================================================
let map;
let markersGroup; // Contiendra tous les marqueurs pour pouvoir les effacer facilement
const mapContainer = document.getElementById('map');

if (mapContainer) {
    // Création de la carte centrée sur la Bretagne
    map = L.map('map').setView([48.202047, -2.932644], 8); 
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Initialisation du groupe de calques Leaflet lié à la carte
    markersGroup = L.layerGroup().addTo(map);

    // Chargement initial automatique de toutes les bornes par défaut au chargement de la page
    loadMapMarkers('', '');
}

// =========================================================================
// Fonction AJAX principale : va chercher les données en PHP et dessine les marqueurs
// =========================================================================
function loadMapMarkers(annee, departement) {
    // Appel asynchrone à notre API PHP avec les paramètres de filtrage
    fetch(`api/get_stations.php?annee=${annee}&departement=${departement}`)
        .then(response => {
            if (!response.ok) throw new Error("Erreur lors de la récupération des données");
            return response.json();
        })
        .then(stations => {
            // Étape essentielle : on vide les anciens marqueurs présents sur la carte
            markersGroup.clearLayers();

            // On boucle sur chaque station reçue de la BDD
            stations.forEach(station => {
                const lat = parseFloat(station.latitude);
                const lng = parseFloat(station.longitude);

                if (!isNaN(lat) && !isNaN(lng)) {
                    // Création du marqueur Leaflet
                    const marker = L.marker([lat, lng]);

                    // Ajout d'une bulle d'info (Popup) au clic sur le marqueur
                    marker.bindPopup(`
                        <div style="font-family: sans-serif;">
                            <strong style="color: #0c1c3e;">${station.nom_station}</strong><br>
                            <span style="color: #555; font-size: 12px;">📍 ${station.adresse_station}</span>
                        </div>
                    `);

                    // On ajoute le marqueur dans notre groupe global
                    markersGroup.addLayer(marker);
                }
            });
        })
        .catch(error => console.error("Erreur de chargement de la carte :", error));
}

// =========================================================================
// 2. Gestion de la bascule Mode (Utilisateur / Admin)
// =========================================================================
function switchMode(mode) {
    const btnUser = document.getElementById('btn-mode-user');
    const btnAdmin = document.getElementById('btn-mode-admin');
    const appHeader = document.getElementById('app-header');
    const appNavbar = document.getElementById('app-navbar');
    const adminElements = document.querySelectorAll('.admin-only');

    if (mode === 'admin') {
        if (btnAdmin) btnAdmin.classList.add('active');
        if (btnUser) btnUser.classList.remove('active');
        if (adminElements.length > 0) adminElements.forEach(el => el.classList.remove('hidden'));
        if (appHeader) { appHeader.classList.remove('user-mode'); appHeader.classList.add('admin-mode'); }
        if (appNavbar) { appNavbar.classList.remove('user-mode-nav'); appNavbar.classList.add('admin-mode-nav'); }
    } else {
        if (btnUser) btnUser.classList.add('active');
        if (btnAdmin) btnAdmin.classList.remove('active');
        if (adminElements.length > 0) adminElements.forEach(el => el.classList.add('hidden'));
        if (appHeader) { appHeader.classList.remove('admin-mode'); appHeader.classList.add('user-mode'); }
        if (appNavbar) { appNavbar.classList.remove('admin-mode-nav'); appNavbar.classList.add('user-mode-nav'); }
    }
}

// Écouteurs pour la bascule de mode
document.addEventListener('DOMContentLoaded', () => {
    const btnUser = document.getElementById('btn-mode-user');
    const btnAdmin = document.getElementById('btn-mode-admin');

    if (btnUser) btnUser.addEventListener('click', () => { switchMode('user'); localStorage.setItem('breizhwatt-mode', 'user'); });
    if (btnAdmin) btnAdmin.addEventListener('click', () => { switchMode('admin'); localStorage.setItem('breizhwatt-mode', 'admin'); });

    const savedMode = localStorage.getItem('breizhwatt-mode') || 'user';
    switchMode(savedMode);
});

// =========================================================================
// 3. Gestion des Modales et Actions de formulaires
// =========================================================================
document.addEventListener('DOMContentLoaded', () => {
    // Fermeture des modales
    document.querySelectorAll('.close-modal').forEach(cross => {
        cross.addEventListener('click', () => {
            const modals = ['modal-detail', 'modal-add', 'modal-edit'];
            modals.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.classList.add('hidden');
            });
        });
    });

    // Ouverture modale Ajouter une Borne
    const btnAddStation = document.getElementById('btn-add-station');
    if (btnAddStation) {
        btnAddStation.addEventListener('click', () => {
            const modalAdd = document.getElementById('modal-add');
            if (modalAdd) modalAdd.classList.remove('hidden');
        });
    }

    // INTERCEPTION DU CLIC FILTRER SUR LA CARTE
    const btnFilterMap = document.querySelector('.formulaire-carte .btn-rechercher');
    if (btnFilterMap) {
        btnFilterMap.addEventListener('click', () => {
            // On récupère les valeurs choisies par l'utilisateur
            const anneeSelectionnee = document.getElementById('annee').value;
            const departementSelectionne = document.getElementById('departement').value;
            
            // On relance le chargement des marqueurs avec les filtres requis !
            loadMapMarkers(anneeSelectionnee, departementSelectionne);
        });
    }
});