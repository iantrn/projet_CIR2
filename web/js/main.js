// =========================================================================
// 1. Initialisation de la carte Leaflet (uniquement si la carte est sur la page)
// =========================================================================
let map;
const mapContainer = document.getElementById('map');

if (mapContainer) {
    map = L.map('map').setView([48.202047, -2.932644], 8); 
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
}

// =========================================================================
// 2. Gestion de la bascule Mode
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
        
        if (adminElements.length > 0) {
             adminElements.forEach(el => el.classList.remove('hidden'));
        }
        
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
        
        if (adminElements.length > 0) {
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
    
    if (map) {
        setTimeout(() => { map.invalidateSize(); }, 300);
    }
}

// Attacher les écouteurs d'événements au chargement du DOM
document.addEventListener('DOMContentLoaded', () => {
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

    // Appliquer le dernier mode enregistré
    const savedMode = localStorage.getItem('breizhwatt-mode') || 'user';
    switchMode(savedMode);
});

// =========================================================================
// 3. Gestion sécurisée des Modales et autres actions
// =========================================================================
document.addEventListener('DOMContentLoaded', () => {
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
            alert("Action de recherche ou filtrage déclenchée !");
        });
    }
});