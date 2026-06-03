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
// 2. Gestion de la bascule Mode Utilisateur / Mode Admin
// =========================================================================
const btnUser = document.getElementById('btn-mode-user');
const btnAdmin = document.getElementById('btn-mode-admin');
const adminElements = document.querySelectorAll('.admin-only');
const appHeader = document.getElementById('app-header');
const appNavbar = document.getElementById('app-navbar');

function switchMode(mode) {
    if (mode === 'admin') {
        if (btnAdmin) btnAdmin.classList.add('active');
        if (btnUser) btnUser.classList.remove('active');
        adminElements.forEach(el => el.classList.remove('hidden'));
        
        // Changement vers la bannière et la navbar admin (noires)
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
        
        // Changement vers la bannière et la navbar utilisateur (bleues)
        if (appHeader) {
            appHeader.classList.remove('admin-mode');
            appHeader.classList.add('user-mode');
        }
        if (appNavbar) {
            appNavbar.classList.remove('admin-mode-nav');
            appNavbar.classList.add('user-mode-nav');
        }
    }
    
    // Forcer Leaflet à recalculer sa taille si la carte est présente
    if (map) {
        setTimeout(() => { map.invalidateSize(); }, 300);
    }
}

// Ajout sécurisé des écouteurs sur les boutons de mode
if (btnUser) {
    btnUser.addEventListener('click', () => switchMode('user'));
}
if (btnAdmin) {
    btnAdmin.addEventListener('click', () => switchMode('admin'));
}

// =========================================================================
// 3. Gestion des fenêtres Modales (Popups) et Actions Spécifiques
// =========================================================================
const modalDetail = document.getElementById('modal-detail');
const modalAdd = document.getElementById('modal-add');
const modalEdit = document.getElementById('modal-edit');

// Fermer toutes les fenêtres au clic sur la croix "X"
document.querySelectorAll('.close-modal').forEach(cross => {
    cross.addEventListener('click', () => {
        if (modalDetail) modalDetail.classList.add('hidden');
        if (modalAdd) modalAdd.classList.add('hidden');
        if (modalEdit) modalEdit.classList.add('hidden');
    });
});

// Ouvrir le formulaire d'ajout (Bouton Admin)
const btnAddStation = document.getElementById('btn-add-station');
if (btnAddStation) {
    btnAddStation.addEventListener('click', () => {
        if (modalAdd) modalAdd.classList.remove('hidden');
    });
}

// Écouteur global sur la liste (si présente)
const stationsList = document.getElementById('stations-list');
if (stationsList) {
    stationsList.addEventListener('click', (e) => {
        // Clic sur bouton "Détail"
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
        
        // Clic sur bouton "Modifier" (Visible uniquement en Admin)
        if (e.target.classList.contains('btn-edit') && modalEdit) {
            document.getElementById('edit-station-id').value = "123"; 
            document.getElementById('edit-nom').value = "Electric 50 Charg";
            document.getElementById('edit-tarif').value = "0.45€ / kWh";
            modalEdit.classList.remove('hidden');
        }
    });
}

// Écouteur du bouton Rechercher (S'adapte à #btn-search ou .btn-rechercher)
const btnSearch = document.getElementById('btn-search') || document.querySelector('.btn-rechercher');
if (btnSearch) {
    btnSearch.addEventListener('click', () => {
        const inputSearch = document.getElementById('search-input');
        const inputVal = inputSearch ? inputSearch.value : "";
        
        if (inputVal.trim() !== "") {
            alert("Recherche de : " + inputVal + "\n(Bientôt relié aux données de la BDD via AJAX !)");
        } else {
            alert("Filtrage ou recherche déclenchée !");
        }
    });
}