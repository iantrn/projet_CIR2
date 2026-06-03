// =========================================================================
// 1. Initialisation sécurisée de la carte Leaflet (uniquement si #map existe)
// =========================================================================
let map;
const mapContainer = document.getElementById('map');

if (mapContainer) {
    map = L.map('map').setView([48.202047, -2.932644], 8); 

    // Chargement des tuiles de la carte OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
}

// =========================================================================
// 2. Gestion globale et persistante de la bascule Mode Admin / Utilisateur
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
        
        // Affiche les éléments réservés à l'admin s'ils existent sur la page
        if (adminElements) {
            adminElements.forEach(el => el.classList.remove('hidden'));
        }
        
        // Bascule des classes CSS pour l'en-tête (Bannière noire en CSS) et la Navbar
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
        
        if (adminElements) {
            adminElements.forEach(el => el.classList.add('hidden'));
        }
        
        if (appHeader) {
            appHeader.classList.remove('admin-mode');
            appHeader.classList.add('user-mode');
        }
        if (appNavbar) {
            appNavbar.classList.remove('admin-mode-nav');
            appNavbar.classList.add('user-mode-nav');
        }
    }
    
    // Si la carte Leaflet est présente sur la page, on force son recalcul fluide
    if (map) {
        setTimeout(() => { map.invalidateSize(); }, 300);
    }
}

// Écouteurs d'événements pour les clics sur les boutons (sécurisés avec des conditions)
const btnUser = document.getElementById('btn-mode-user');
const btnAdmin = document.getElementById('btn-mode-admin');

if (btnUser) {
    btnUser.addEventListener('click', () => {
        localStorage.setItem('breizhwatt-mode', 'user'); // Sauvegarde le choix
        switchMode('user');
    });
}

if (btnAdmin) {
    btnAdmin.addEventListener('click', () => {
        localStorage.setItem('breizhwatt-mode', 'admin'); // Sauvegarde le choix
        switchMode('admin');
    });
}

// RESTAURATION AUTOMATIQUE : Applique le mode mémorisé dès le chargement de la page actuelle
const savedMode = localStorage.getItem('breizhwatt-mode') || 'user';
switchMode(savedMode);


// =========================================================================
// 3. Gestion sécurisée des fenêtres Modales et listes (si présentes sur la page)
// =========================================================================

// Fermer toutes les fenêtres modales au clic sur la croix "X"
document.querySelectorAll('.close-modal').forEach(cross => {
    cross.addEventListener('click', () => {
        const modalDetail = document.getElementById('modal-detail');
        const modalAdd = document.getElementById('modal-add');
        const modalEdit = document.getElementById('modal-edit');
        if (modalDetail) modalDetail.classList.add('hidden');
        if (modalAdd) modalAdd.classList.add('hidden');
        if (modalEdit) modalEdit.classList.add('hidden');
    });
});

// Ouvrir le formulaire d'ajout
const btnAddStation = document.getElementById('btn-add-station');
if (btnAddStation) {
    btnAddStation.addEventListener('click', () => {
        const modalAdd = document.getElementById('modal-add');
        if (modalAdd) modalAdd.classList.remove('hidden');
    });
}

// Écouteur sur la liste des stations
const stationsList = document.getElementById('stations-list');
if (stationsList) {
    stationsList.addEventListener('click', (e) => {
        const modalDetail = document.getElementById('modal-detail');
        const modalEdit = document.getElementById('modal-edit');
        
        if (e.target.classList.contains('btn-detail') && modalDetail) {
            document.getElementById('detail-title').innerText = "Electric 50 Charg";
            document.getElementById('detail-body').innerHTML = `
                <p style="margin-bottom:8px;"><strong>📍 Adresse :</strong> 4 Rue de l'Énergie, Rennes</p>
                <p style="margin-bottom:8px;"><strong>🔌 Prises dispo :</strong> Type 2, Combo CCS</p>
                <p style="margin-bottom:8px;"><strong>⚡ Puissance max :</strong> 50 kW</p>
                <p style="margin-bottom:8px;"><strong>💶 Tarification :</strong> 0.45€ / kWh</p>
            `;
            modalDetail.classList.remove('hidden');
        }
        
        if (e.target.classList.contains('btn-edit') && modalEdit) {
            document.getElementById('edit-station-id').value = "123"; 
            document.getElementById('edit-nom').value = "Electric 50 Charg";
            document.getElementById('edit-tarif').value = "0.45€ / kWh";
            modalEdit.classList.remove('hidden');
        }
    });
}

// Écouteur sur les boutons de recherche ou de filtrage
const btnSearch = document.getElementById('btn-search') || document.querySelector('.btn-rechercher');
if (btnSearch) {
    btnSearch.addEventListener('click', () => {
        const searchInput = document.getElementById('search-input');
        if (searchInput && searchInput.value.trim() !== "") {
            alert("Recherche de : " + searchInput.value + "\n(Bientôt relié aux données de la BDD via AJAX !)");
        } else {
            alert("Action de recherche ou filtrage déclenchée !");
        }
    });
}