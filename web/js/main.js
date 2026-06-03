// 1. Initialisation sécurisée de la carte Leaflet (uniquement si l'élément existe sur la page)
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
    const bannerImg = document.getElementById('app-banner-img');
    const adminElements = document.querySelectorAll('.admin-only');

    if (mode === 'admin') {
        if (btnAdmin) btnAdmin.classList.add('active');
        if (btnUser) btnUser.classList.remove('active');
        adminElements.forEach(el => el.classList.remove('hidden'));
        
        if (appHeader) {
            appHeader.classList.remove('user-mode');
            appHeader.classList.add('admin-mode');
        }
        if (appNavbar) {
            appNavbar.classList.remove('user-mode-nav');
            appNavbar.classList.add('admin-mode-nav');
        }
        if (bannerImg) {
            bannerImg.src = 'img/baniere_noire.png';
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
        if (bannerImg) {
            bannerImg.src = 'img/baniere_bleue.png';
        }
    }
    
    // Recalculer la taille de Leaflet si la carte existe
    if (map) {
        setTimeout(() => { map.invalidateSize(); }, 300);
    }
}

// Écouteurs de clics avec persistance via localStorage
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

// Au chargement de chaque page, restaurer automatiquement le dernier mode choisi
const savedMode = localStorage.getItem('breizhwatt-mode') || 'user';
switchMode(savedMode);


// 3. Gestion sécurisée des fenêtres Modales (si présentes)
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

const stationsList = document.getElementById('stations-list');
if (stationsList) {
    stationsList.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-detail')) {
            const modalDetail = document.getElementById('modal-detail');
            document.getElementById('detail-title').innerText = "Electric 50 Charg";
            document.getElementById('detail-body').innerHTML = `
                <p style="margin-bottom:8px;"><strong>📍 Adresse :</strong> 4 Rue de l'Énergie, Rennes</p>
                <p style="margin-bottom:8px;"><strong>🔌 Prises dispo :</strong> Type 2, Combo CCS</p>
                <p style="margin-bottom:8px;"><strong>⚡ Puissance max :</strong> 50 kW</p>
                <p style="margin-bottom:8px;"><strong>💶 Tarification :</strong> 0.45€ / kWh</p>
            `;
            if (modalDetail) modalDetail.classList.remove('hidden');
        }
        
        if (e.target.classList.contains('btn-edit')) {
            const modalEdit = document.getElementById('modal-edit');
            document.getElementById('edit-station-id').value = "123"; 
            document.getElementById('edit-nom').value = "Electric 50 Charg";
            document.getElementById('edit-tarif').value = "0.45€ / kWh";
            if (modalEdit) modalEdit.classList.remove('hidden');
        }
    });
}

// Gestion unifiée pour tous les boutons de recherche/filtrage du site
const btnSearch = document.getElementById('btn-search') || document.querySelector('.btn-rechercher');
if (btnSearch) {
    btnSearch.addEventListener('click', () => {
        const searchInput = document.getElementById('search-input');
        const inputVal = searchInput ? searchInput.value : '';
        alert("Action de recherche détectée !\n(Bientôt relié dynamiquement aux données SQL via AJAX !)");
    });
}