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

// Fonction AJAX principale pour la carte : va chercher les données en PHP et dessine les marqueurs
function loadMapMarkers(annee, departement) {
    fetch(`api/get_stations.php?annee=${annee}&departement=${departement}`)
        .then(response => response.json())
        .then(stations => {
            markersGroup.clearLayers();

            stations.forEach(station => {
                const lat = parseFloat(station.latitude);
                const lng = parseFloat(station.longitude);

                if (!isNaN(lat) && !isNaN(lng)) {
                    const marker = L.marker([lat, lng]);

                    // Ici on enrichit la popup avec plus de détails
                    marker.bindPopup(`
                        <div style="font-family: sans-serif; min-width: 200px;">
                            <strong style="color: #0c1c3e; font-size: 14px;">${station.nom_station}</strong><br>
                            <hr style="margin: 5px 0;">
                            <p style="margin: 2px 0; font-size: 12px;">
                                📍 <b>Adresse :</b> ${station.adresse_station}<br>
                                ⚡ <b>Aménageur :</b> ${station.nom_amenageur_operateur || 'Non spécifié'}<br>
                                🔌 <b>Localisation :</b> ${station.nom_commune || 'Bretagne'}
                            </p>
                        </div>
                    `);

                    markersGroup.addLayer(marker);
                }
            });
        })
        .catch(error => console.error("Erreur :", error));
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

// Écouteurs pour la bascule de mode au chargement du DOM
document.addEventListener('DOMContentLoaded', () => {
    const btnUser = document.getElementById('btn-mode-user');
    const btnAdmin = document.getElementById('btn-mode-admin');

    if (btnUser) btnUser.addEventListener('click', () => { switchMode('user'); localStorage.setItem('breizhwatt-mode', 'user'); });
    if (btnAdmin) btnAdmin.addEventListener('click', () => { switchMode('admin'); localStorage.setItem('breizhwatt-mode', 'admin'); });

    const savedMode = localStorage.getItem('breizhwatt-mode') || 'user';
    switchMode(savedMode);
});

// =========================================================================
// 3. Gestion des Modales et du filtrage de la carte
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

    // Interception du clic Filtrer sur l'onglet carte.php
    const btnFilterMap = document.querySelector('.formulaire-carte .btn-rechercher');
    if (btnFilterMap) {
        btnFilterMap.addEventListener('click', () => {
            const anneeSelectionnee = document.getElementById('annee').value;
            const departementSelectionne = document.getElementById('departement').value;
            loadMapMarkers(anneeSelectionnee, departementSelectionne);
        });
    }
});

// =========================================================================
// 4. Gestion de la Recherche Dynamique avec Tableau (Page recherche.php)
// =========================================================================
document.addEventListener('DOMContentLoaded', () => {
    const btnSearchPage = document.getElementById('btn-search');
    
    if (btnSearchPage) {
        btnSearchPage.addEventListener('click', () => {
            // Récupération des valeurs sélectionnées dans les listes déroulantes
            const amenageur = document.getElementById('amenageur').value;
            const typePrise = document.getElementById('type_prise').value;
            const departement = document.getElementById('departement').value;

            // Appel AJAX à notre API de recherche sécurisée
            fetch(`api/search_stations.php?amenageur=${amenageur}&type_prise=${typePrise}&departement=${departement}`)
                .then(response => {
                    if (!response.ok) throw new Error("Erreur serveur lors de la recherche");
                    return response.json();
                })
                .then(data => {
                    const container = document.getElementById('search-results-container');
                    const tbody = document.getElementById('search-table-body');
                    const countSpan = document.getElementById('results-count');

                    if (!container || !tbody) return;

                    // On vide le tableau précédent
                    tbody.innerHTML = '';
                    
                    if (countSpan) {
                        countSpan.textContent = data.length;
                    }

                    if (data.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="4" style="padding: 15px; text-align: center; color: #777;">Aucune borne ne correspond à ces critères.</td></tr>`;
                    } else {
                        // On boucle sur les lignes trouvées pour construire dynamiquement le tableau
                        data.forEach(station => {
                            const tr = document.createElement('tr');
                            tr.style.borderBottom = "1px solid #eee";
                            
                            // Détection dynamique du mode actuel (admin ou user) pour afficher l'Action
                            const currentMode = localStorage.getItem('breizhwatt-mode') || 'user';
                            const adminClass = (currentMode === 'admin') ? '' : 'hidden';

                            tr.innerHTML = `
                                <td style="padding: 12px; font-weight: bold; color: #333;">${station.nom_station || 'Sans nom'}</td>
                                <td style="padding: 12px; color: #555;">${station.nom_amenageur_operateur || 'Inconnu'}</td>
                                <td style="padding: 12px; font-size: 13px; color: #666;"><strong>${station.nom_commune || ''}</strong><br>${station.adresse_station || ''}</td>
                                <td style="padding: 12px;" class="admin-only ${adminClass}">
                                    <button class="btn-edit-station-trigger" data-id="${station.id_station_interne}" style="background-color: #29b6f6; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">✏️ Modifier</button>
                                </td>
                            `;
                            tbody.appendChild(tr);
                        });
                    }

                    // Affichage explicite du container de résultats (retrait de la classe et force display)
                    container.classList.remove('hidden');
                    container.style.display = "block";
                })
                .catch(error => {
                    alert("Erreur lors de la recherche : " + error.message);
                    console.error(error);
                });
        });
    }
});