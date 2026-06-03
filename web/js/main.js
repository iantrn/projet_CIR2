// 1. Initialisation sécurisée de la carte Leaflet (uniquement si l'élément existe sur la page actuelle)
let map;
const mapContainer = document.getElementById('map');

if (mapContainer) {
    map = L.map('map').setView([48.202047, -2.932644], 8); 

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
}

// 2. Gestion globale et persistante de la bascule Mode Utilisateur / Mode Admin
function switchMode(mode) {
    const btnUser = document.getElementById('btn-mode-user');
    const btnAdmin = document.getElementById('btn-mode-admin');
    const appHeader = document.getElementById('app-header');
    const appNavbar = document.getElementById('app-navbar');
    const adminElements = document.querySelectorAll('.admin-only');

    if (mode === 'admin') {
        if (btnAdmin) btnAdmin.classList.add('active');
        if (btnUser) btnUser.classList.remove('active');
        adminElements.forEach(el => el.classList.remove('hidden'));
        
        // Le changement de classe ici va automatiquement déclencher le changement d'image en CSS
        if (appHeader) {
            appHeader.classList.remove('user-mode');
            appHeader.classList.add('admin-mode');
        }
        if (appNavbar) {
            appNavbar.classList.remove('user-mode-nav');
            appNavbar.classList.add('admin-mode-nav');
        }
    } else {
        if (btnUser) btnUser.classList.add('active');
        if (btnAdmin) btnAdmin.classList.remove('active');
        adminElements.forEach(el => el.classList.add('hidden'));
        
        if (appHeader) {
            appHeader.classList.remove('admin-mode');
            appHeader.classList.add('user-mode');
        }
        if (appNavbar) {
            appNavbar.classList.remove('admin-mode-nav');
            appNavbar.classList.add('user-mode-nav');
        }
    }
    
    if (map) {
        setTimeout(() => { map.invalidateSize(); }, 300);
    }
}

// Écouteurs d'événements sécurisés pour le clic sur les boutons de mode
const btnUser = document.getElementById('btn-mode-user');
const btnAdmin = document.getElementById('btn-mode-admin');

if (btnUser) {
    btnUser.addEventListener('click', () => {
        localStorage.setItem('breizhwatt-mode', 'user');
        switchMode('user');
    });
}

if (btnAdmin) {
    btnAdmin.addEventListener('click', () => {
        localStorage.setItem('breizhwatt-mode', 'admin');
        switchMode('admin');
    });
}

// RESTAURATION AUTOMATIQUE : Vérifie et applique le dernier mode enregistré au chargement de chaque page
const savedMode = localStorage.getItem('breizhwatt-mode') || 'user';
switchMode(savedMode);


// 3. Gestion sécurisée des fenêtres Modales / Boutons d'ajout (Si présents sur la page)
const btnAddStation = document.getElementById('btn-add-station');
if (btnAddStation) {
    btnAddStation.addEventListener('click', () => {
        const modalAdd = document.getElementById('modal-add');
        if (modalAdd) modalAdd.classList.remove('hidden');
    });
}

const btnSearch = document.getElementById('btn-search') || document.querySelector('.btn-rechercher');
if (btnSearch) {
    btnSearch.addEventListener('click', () => {
        alert("Action de filtrage déclenchée !\n(La liaison SQL via requêtes PHP/AJAX arrive à l'étape suivante !)");
    });
}