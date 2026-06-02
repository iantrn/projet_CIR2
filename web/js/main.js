// 1. Initialisation de la carte Leaflet (Centrée sur la Bretagne)
const map = L.map('map').setView([48.202047, -2.932644], 8); 

// Chargement des tuiles de la carte OpenStreetMap
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// 2. Gestion de la bascule Mode Utilisateur / Mode Admin
const btnUser = document.getElementById('btn-mode-user');
const btnAdmin = document.getElementById('btn-mode-admin');
const adminElements = document.querySelectorAll('.admin-only');
const appHeader = document.getElementById('app-header');

function switchMode(mode) {
    if (mode === 'admin') {
        btnAdmin.classList.add('active');
        btnUser.classList.remove('active');
        adminElements.forEach(el => el.classList.remove('hidden'));
        
        // On bascule sur la bannière noire
        appHeader.classList.remove('user-mode');
        appHeader.classList.add('admin-mode');
    } else {
        btnUser.classList.add('active');
        btnAdmin.classList.remove('active');
        adminElements.forEach(el => el.classList.add('hidden'));
        
        // On bascule sur la bannière bleue
        appHeader.classList.remove('admin-mode');
        appHeader.classList.add('user-mode');
    }
}

btnUser.addEventListener('click', () => switchMode('user'));
btnAdmin.addEventListener('click', () => switchMode('admin'));

// 3. Gestion des fenêtres Modales (Popups)
const modalDetail = document.getElementById('modal-detail');
const modalAdd = document.getElementById('modal-add');
const modalEdit = document.getElementById('modal-edit');

// Fermer toutes les fenêtres au clic sur la croix "X"
document.querySelectorAll('.close-modal').forEach(cross => {
    cross.addEventListener('click', () => {
        modalDetail.classList.add('hidden');
        modalAdd.classList.add('hidden');
        modalEdit.classList.add('hidden');
    });
});

// Ouvrir le formulaire d'ajout (Bouton Admin)
document.getElementById('btn-add-station').addEventListener('click', () => {
    modalAdd.classList.remove('hidden');
});

// Écouteur global sur la liste pour gérer les boutons "Détail" et "Modifier"
document.getElementById('stations-list').addEventListener('click', (e) => {
    
    // Clic sur bouton "Détail"
    if (e.target.classList.contains('btn-detail')) {
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
    if (e.target.classList.contains('btn-edit')) {
        document.getElementById('edit-station-id').value = "123"; 
        document.getElementById('edit-nom').value = "Electric 50 Charg";
        document.getElementById('edit-tarif').value = "0.45€ / kWh";
        modalEdit.classList.remove('hidden');
    }
});

// Écouteur du bouton Rechercher
document.getElementById('btn-search').addEventListener('click', () => {
    const inputVal = document.getElementById('search-input').value;
    if(inputVal.trim() !== "") {
        alert("Recherche de : " + inputVal + "\n(Bientôt relié aux données de la BDD via AJAX !)");
    }
});