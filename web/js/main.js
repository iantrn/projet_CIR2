// =========================================================================
// 1. Initialisation, Détection du Contexte et Gestion du Mode (Header)
// =========================================================================
document.addEventListener('DOMContentLoaded', () => {
    // Détection automatique du mode (Public ou Admin) via la balise script
    const scriptTag = document.getElementById('main-script');
    const isBackOffice = scriptTag && scriptTag.getAttribute('data-mode') === 'admin';
    
    // Définition du préfixe des chemins d'API selon le dossier où l'on se trouve
    const apiPrefix = isBackOffice ? '../api/' : 'api/';

    // --- GESTION DU SWITCHER DE MODE (HEADER) ---
    const btnModeUser = document.getElementById('btn-mode-user');
    const btnModeAdmin = document.getElementById('btn-mode-admin');

    if (btnModeUser && btnModeAdmin) {
        // Au clic sur le bouton Utilisateur
        btnModeUser.addEventListener('click', () => {
            if (isBackOffice) {
                window.location.href = '../accueil.php'; // On quitte le sous-dossier back
            } else {
                btnModeUser.classList.add('active');
                btnModeAdmin.classList.remove('active');
            }
        });

        // Au clic sur le bouton Admin
        btnModeAdmin.addEventListener('click', () => {
            if (!isBackOffice) {
                window.location.href = 'back/accueil.php'; // On entre dans le dossier back
            } else {
                btnModeAdmin.classList.add('active');
                btnModeUser.classList.remove('active');
            }
        });
    }

    // --- INITIALISATION DE LA CARTE LEAFLET ---
    const mapElement = document.getElementById('map');
    if (mapElement) {
        // Centre la carte sur la Bretagne
        const map = L.map('map').setView([48.2020, -2.9326], 8);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        let markersGroup = L.layerGroup().addTo(map);

        // Fonction pour charger et filtrer les marqueurs
        function loadMarkers() {
            const annee = document.getElementById('filter-annee')?.value || '';
            const departement = document.getElementById('filter-departement')?.value || '';

            fetch(`${apiPrefix}get_stations.php?annee=${annee}&departement=${departement}`)
                .then(response => response.json())
                .then(stations => {
                    markersGroup.clearLayers();

                    stations.forEach(st => {
                        if (st.coordonnees_xy) {
                            // Nettoyage de la chaîne de coordonnées [lat, lon]
                            const coordsStr = st.coordonnees_xy.replace(/[\[\]\s]/g, '');
                            const parts = coordsStr.split(',');
                            
                            if (parts.length === 2) {
                                const lat = parseFloat(parts[0]);
                                const lon = parseFloat(parts[1]);

                                if (!isNaN(lat) && !isNaN(lon)) {
                                    // Préparation du texte des prises pour la bulle d'info
                                    let listePrises = [];
                                    if (parseInt(st.prise_ef) === 1) listePrises.push("EF");
                                    if (parseInt(st.prise_t2) === 1) listePrises.push("T2");
                                    if (parseInt(st.prise_combo_ccs) === 1) listePrises.push("Combo CCS");
                                    if (parseInt(st.prise_chademo) === 1) listePrises.push("CHAdeMO");
                                    
                                    let prisesText = listePrises.length > 0 ? listePrises.join(', ') : 'Non spécifiées';
                                    let tarifText = st.tarification ? st.tarification : 'Non spécifiée';

                                    const popupContent = `
                                        <div style="font-family: sans-serif; font-size: 13px;">
                                            <h4 style="margin: 0 0 5px 0; color: #0c1c3e;">${st.nom_station || 'Station sans nom'}</h4>
                                            <p style="margin: 3px 0;"><strong>📍 Adresse :</strong> ${st.adresse_station || 'Inconnue'}</p>
                                            <p style="margin: 3px 0;"><strong>⚡ Puissance :</strong> ${st.puissance_nominale || '⚡'} kW</p>
                                            <p style="margin: 3px 0;"><strong>🔌 Prises :</strong> ${prisesText}</p>
                                            <p style="margin: 3px 0;"><strong>💰 Tarif :</strong> ${tarifText}</p>
                                        </div>
                                    `;
                                    L.marker([lat, lon]).bindPopup(popupContent).addTo(markersGroup);
                                }
                            }
                        }
                    });
                })
                .catch(err => console.error("Erreur lors du chargement de la carte :", err));
        }

        // Rechargement dynamique lors d'un changement de filtre sur la carte
        document.getElementById('filter-annee')?.addEventListener('change', loadMarkers);
        document.getElementById('filter-departement')?.addEventListener('change', loadMarkers);
        
        loadMarkers();
    }

    // =========================================================================
    // 2. Gestion de la Recherche et du Tableau Dynamique
    // =========================================================================
    const btnSearchPage = document.getElementById('btn-search');
    if (btnSearchPage) {
        btnSearchPage.addEventListener('click', () => {
            const amenageur = document.getElementById('amenageur').value;
            const typePrise = document.getElementById('type_prise').value;
            const departement = document.getElementById('departement').value;

            fetch(`${apiPrefix}search_stations.php?amenageur=${amenageur}&type_prise=${typePrise}&departement=${departement}`)
                .then(r => r.json())
                .then(data => {
                    const tbody = document.getElementById('search-table-body');
                    const container = document.getElementById('search-results-container');
                    const countSpan = document.getElementById('results-count');

                    if (tbody) {
                        tbody.innerHTML = '';
                        if (countSpan) countSpan.textContent = data.length;
                        
                        if (data.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="6" style="padding:15px; text-align:center;">Aucun résultat trouvé.</td></tr>';
                        } else {
                            data.forEach(s => {
                                let listePrises = [];
                                if (parseInt(s.prise_ef) === 1) listePrises.push("EF");
                                if (parseInt(s.prise_t2) === 1) listePrises.push("T2");
                                if (parseInt(s.prise_combo_ccs) === 1) listePrises.push("CCS");
                                if (parseInt(s.prise_chademo) === 1) listePrises.push("CHA");
                                if (parseInt(s.prise_autre) === 1) listePrises.push("Autre");
                                
                                let textPrises = listePrises.length > 0 ? listePrises.join(', ') : 'Aucune';
                                let textTarif = s.tarification ? s.tarification : 'Non spécifié';

                                // Génération conditionnelle de la cellule d'action (Modifier & Supprimer)
                                let actionCell = '';
                                if (isBackOffice) {
                                    actionCell = `
                                        <td style="padding:12px; display: flex; gap: 8px; align-items: center;">
                                            <button class="btn-edit-trigger" data-id="${s.id_station_interne}" style="background-color: #29b6f6; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-weight: bold;">✏️ Modifier</button>
                                            <button class="btn-delete-trigger" data-id="${s.id_station_interne}" style="background-color: #ef5350; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 14px;" title="Supprimer cette station">🗑️</button>
                                        </td>
                                    `;
                                } else {
                                    actionCell = `<td style="padding:12px; color: #aaa; font-style: italic;">Lecture seule</td>`;
                                }

                                tbody.innerHTML += `
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td style="padding:12px; font-weight: bold; color: #333;">${s.nom_station || 'Sans nom'}</td>
                                        <td style="padding:12px; color: #555;">${s.nom_amenageur_operateur || 'N/A'}</td>
                                        <td style="padding:12px; font-size: 13px;"><strong>${s.nom_commune || ''}</strong><br>${s.adresse_station || ''}</td>
                                        <td style="padding:12px;"><span style="background: #e3f2fd; color: #0d47a1; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">${textPrises}</span></td>
                                        <td style="padding:12px; font-size: 13px; color: #666; max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${textTarif}">${textTarif}</td>
                                        ${actionCell}
                                    </tr>
                                `;
                            });
                        }
                        if (container) {
                            container.style.display = "block";
                        }
                    }
                })
                .catch(e => console.error("Erreur lors de la recherche :", e));
        });
    }

    // =========================================================================
    // 3. Fenêtre Modale Admin : Ouverture & Pré-remplissage
    // =========================================================================
    document.addEventListener('click', (e) => {
        if (e.target && e.target.classList.contains('btn-edit-trigger')) {
            const idStation = e.target.getAttribute('data-id');

            // Appel de l'API de détails (placée dans le dossier api/ à la racine)
            fetch(`${apiPrefix}get_station_details.php?id=${encodeURIComponent(idStation)}`)
                .then(r => r.json())
                .then(station => {
                    if (document.getElementById('edit-id-station')) document.getElementById('edit-id-station').value = station.id_station_interne;
                    if (document.getElementById('edit-nom')) document.getElementById('edit-nom').value = station.nom_station || '';
                    if (document.getElementById('edit-adresse')) document.getElementById('edit-adresse').value = station.adresse_station || '';
                    if (document.getElementById('edit-puissance')) document.getElementById('edit-puissance').value = station.puissance_nominale || '';
                    if (document.getElementById('edit-tarification')) document.getElementById('edit-tarification').value = station.tarification || '';

                    // Cases à cocher
                    if (document.getElementById('edit-prise-ef')) document.getElementById('edit-prise-ef').checked = (parseInt(station.prise_ef) === 1);
                    if (document.getElementById('edit-prise-t2')) document.getElementById('edit-prise-t2').checked = (parseInt(station.prise_t2) === 1);
                    if (document.getElementById('edit-prise-ccs')) document.getElementById('edit-prise-ccs').checked = (parseInt(station.prise_combo_ccs) === 1);
                    if (document.getElementById('edit-prise-cha')) document.getElementById('edit-prise-cha').checked = (parseInt(station.prise_chademo) === 1);

                    const modal = document.getElementById('modal-edit');
                    if (modal) modal.style.display = "flex";
                })
                .catch(err => alert("Erreur d'acquisition des données : " + err.message));
        }
    });

    // Fermeture de la modale d'édition
    const btnCloseModal = document.getElementById('btn-close-modal');
    if (btnCloseModal) {
        btnCloseModal.addEventListener('click', () => {
            const modal = document.getElementById('modal-edit');
            if (modal) modal.style.display = "none";
        });
    }

    // =========================================================================
    // 4. Action de Suppression Admin
    // =========================================================================
    document.addEventListener('click', (e) => {
        if (e.target && e.target.classList.contains('btn-delete-trigger')) {
            const idStation = e.target.getAttribute('data-id');
            
            // Message de confirmation de sécurité
            if (confirm("⚠️ Êtes-vous sûr de vouloir supprimer définitivement cette station et tous ses points de recharge ?")) {
                
                // Envoi de la requête à l'API de suppression (située dans le dossier api/ racine)
                fetch(`${apiPrefix}delete_station.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id_station=${encodeURIComponent(idStation)}`
                })
                .then(r => r.json())
                .then(result => {
                    if (result.success) {
                        alert("✅ La station a été supprimée avec succès !");
                        // Simulation d'un clic de recherche pour rafraîchir le tableau dynamiquement
                        document.getElementById('btn-search').click();
                    } else {
                        alert("❌ Erreur : " + result.error);
                    }
                })
                .catch(err => alert("Erreur réseau lors de la suppression : " + err.message));
            }
        }
    });
});