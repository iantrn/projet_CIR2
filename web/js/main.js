// =========================================================================
// 0. DEBUGGING INITIAL
// =========================================================================
console.log("main.js a été chargé avec succès.");

// =========================================================================
// 1. Initialisation de Leaflet (uniquement si la carte est sur la page)
// =========================================================================
let map;
const mapContainer = document.getElementById('map');

if (mapContainer) {
    console.log("Conteneur '#map' trouvé. Initialisation de Leaflet.");
    map = L.map('map').setView([48.202047, -2.932644], 8); 
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
} else {
    console.log("Conteneur '#map' NON trouvé sur cette page (Normal si accueil ou recherche).");
}

// =========================================================================
// 2. Gestion de la bascule Mode
// =========================================================================
function switchMode(mode) {
    console.log("Exécution de switchMode avec le mode : " + mode);

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
            console.log("Classe admin-mode ajoutée au header.");
        } else {
             console.error("ERREUR : Élément '#app-header' introuvable !");
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
            console.log("Classe user-mode ajoutée au header.");
        } else {
             console.error("ERREUR : Élément '#app-header' introuvable !");
        }

        if (appNavbar) {
            appNavbar.classList.remove('admin-mode-nav');
            appNavbar.classList.add('user-mode-nav');
        }
    }
    
    // Forcer le redimensionnement si Leaflet est actif
    if (map) {
        setTimeout(() => { map.invalidateSize(); }, 300);
    }
}

// Attacher les écouteurs d'événements SÉCURISÉS au chargement du DOM
document.addEventListener('DOMContentLoaded', () => {
    console.log("Le DOM est complètement chargé. Attachement des écouteurs.");

    const btnUser = document.getElementById('btn-mode-user');
    const btnAdmin = document.getElementById('btn-mode-admin');

    if (btnUser) {
        console.log("Bouton '#btn-mode-user' trouvé. Écouteur attaché.");
        btnUser.addEventListener('click', () => {
            console.log("Clic sur Utilisateur détecté.");
            localStorage.setItem('breizhwatt-mode', 'user');
            switchMode('user');
        });
    } else {
        console.error("ERREUR : Bouton '#btn-mode-user' introuvable dans le DOM !");
    }

    if (btnAdmin) {
        console.log("Bouton '#btn-mode-admin' trouvé. Écouteur attaché.");
        btnAdmin.addEventListener('click', () => {
            console.log("Clic sur Admin détecté.");
            localStorage.setItem('breizhwatt-mode', 'admin');
            switchMode('admin');
        });
    } else {
         console.error("ERREUR : Bouton '#btn-mode-admin' introuvable dans le DOM !");
    }

    // Appliquer le dernier mode enregistré
    const savedMode = localStorage.getItem('breizhwatt-mode') || 'user';
    console.log("Mode restauré depuis localStorage : " + savedMode);
    switchMode(savedMode);
});

// =========================================================================
// 3. Gestion sécurisée des Modales et autres (simplifiée pour le débuggage)
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
            console.log("Clic sur bouton de recherche/filtrage.");
            alert("Action de recherche ou filtrage déclenchée !");
        });
    }
});