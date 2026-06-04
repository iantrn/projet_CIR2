// =========================================================================
// 1. Initialisation, Détection du Contexte et Gestion du Mode (Header)
// =========================================================================
document.addEventListener('DOMContentLoaded', () => {
    const scriptTag = document.getElementById('main-script');
    
    // Détection robuste de la position du fichier dans l'arborescence
    const isInsideBackFolder = (scriptTag && scriptTag.getAttribute('data-location') === 'backfolder') || window.location.pathname.includes('/back/');
    const isBackOffice = scriptTag && scriptTag.getAttribute('data-mode') === 'admin';
    
    // Le chemin vers l'API dépend UNIQUEMENT de l'emplacement physique du fichier PHP
    const apiPrefix = isInsideBackFolder ? '../api/' : 'api/';

    // --- GESTION DU SWITCHER DE MODE (HEADER) ---
    const btnModeUser = document.getElementById('btn-mode-user');
    const btnModeAdmin = document.getElementById('btn-mode-admin');

    if (btnModeUser && btnModeAdmin) {
        btnModeUser.addEventListener('click', () => {
            if (isInsideBackFolder) {
                window.location.href = '../accueil.php'; 
            } else {
                btnModeUser.classList.add('active');
                btnModeAdmin.classList.remove('active');
            }
        });

        btnModeAdmin.addEventListener('click', () => {
            if (!isInsideBackFolder) {
                window.location.href = 'back/accueil.php'; 
            } else {
                btnModeAdmin.classList.add('active');
                btnModeUser.classList.remove('active');
            }
        });
    }

    // =========================================================================
    // 2. INITIALISATION DE LA CARTE LEAFLET
    // =========================================================================
    const mapElement = document.getElementById('map');
    if (mapElement) {
        const map = L.map('map').setView([48.2020, -2.9326], 8);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        let markersGroup = L.layerGroup().addTo(map);

        function loadMarkers() {
            const annee = document.getElementById('filter-annee')?.value || '';
            const departement = document.getElementById('filter-departement')?.value || '';

            fetch(`${apiPrefix}get_stations.php?annee=${annee}&departement=${departement}`)
                .then(response => response.json())
                .then(stations => {
                    markersGroup.clearLayers();

                    if (!Array.isArray(stations)) return;

                    stations.forEach(st => {
                        if (st && st.coordonnees_xy) {
                            const coordsStr = st.coordonnees_xy.replace(/[\[\]\s]/g, '');
                            const parts = coordsStr.split(',');
                            
                            if (parts.length === 2) {
                                const lat = parseFloat(parts[0]);
                                const lon = parseFloat(parts[1]);

                                if (!isNaN(lat) && !isNaN(lon)) {
                                    let listePrises = [];
                                    if (parseInt(st.prise_ef) === 1) listePrises.push("EF");
                                    if (parseInt(st.prise_t2) === 1) listePrises.push("T2");
                                    if (parseInt(st.prise_combo_ccs) === 1) listePrises.push("Combo CCS");
                                    if (parseInt(st.prise_chademo) === 1) listePrises.push("CHAdeMO");
                                    
                                    let prisesText = listePrises.length > 0 ? listePrises.join(', ') : 'Non spécifiées';
                                    let tarifText = st.tarification ? st.tarification : 'Non spécifiée';
                                    let stationNom = st.nom_station || st.nom || 'Station sans nom';
                                    let stationAdresse = st.adresse_station || st.adresse || 'Inconnue';

                                    const popupContent = `
                                        <div style="font-family: sans-serif; font-size: 13px;">
                                            <h4 style="margin: 0 0 5px 0; color: #0c1c3e;">${stationNom}</h4>
                                            <p style="margin: 3px 0;"><strong>📍 Adresse :</strong> ${stationAdresse}</p>
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

        document.getElementById('filter-annee')?.addEventListener('change', loadMarkers);
        document.getElementById('filter-departement')?.addEventListener('change', loadMarkers);
        
        loadMarkers();
    }

    // =========================================================================
    // 3. GESTION DE LA RECHERCHE ET DU TABLEAU DYNAMIQUE
    // =========================================================================
    const btnSearchPage = document.getElementById('btn-search');
    if (btnSearchPage) {
        btnSearchPage.addEventListener('click', () => {
            const amenageur = document.getElementById('amenageur')?.value || '';
            const typePrise = document.getElementById('type_prise')?.value || '';
            const departement = document.getElementById('departement')?.value || '';

            fetch(`${apiPrefix}search_stations.php?amenageur=${amenageur}&type_prise=${typePrise}&departement=${departement}`)
                .then(r => r.json())
                .then(data => {
                    const tbody = document.getElementById('search-table-body');
                    const container = document.getElementById('search-results-container');
                    const countSpan = document.getElementById('results-count');

                    if (!tbody) return;
                    tbody.innerHTML = '';
                    
                    if (!Array.isArray(data)) {
                        tbody.innerHTML = '<tr><td colspan="6" style="padding:15px; text-align:center;">Erreur de format de données.</td></tr>';
                        return;
                    }

                    if (countSpan) countSpan.textContent = data.length;
                    
                    if (data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" style="padding:15px; text-align:center;">Aucun résultat trouvé.</td></tr>';
                    } else {
                        data.forEach(s => {
                            if (!s) return;
                            
                            let listePrises = [];
                            if (parseInt(s.prise_ef) === 1) listePrises.push("EF");
                            if (parseInt(s.prise_t2) === 1) listePrises.push("T2");
                            if (parseInt(s.prise_combo_ccs) === 1) listePrises.push("CCS");
                            if (parseInt(s.prise_chademo) === 1) listePrises.push("CHA");
                            if (parseInt(s.prise_autre) === 1) listePrises.push("Autre");
                            
                            let textPrises = listePrises.length > 0 ? listePrises.join(', ') : 'Aucune';
                            let textTarif = s.tarification ? s.tarification : 'Non spécifié';
                            
                            // Sécurité contre les propriétés manquantes selon le mode d'API
                            let sNom = s.nom_station || s.nom || 'Sans nom';
                            let sAmenageur = s.nom_amenageur_operateur || s.amenageur || 'N/A';
                            let sCommune = s.nom_commune || s.commune || '';
                            let sAdresse = s.adresse_station || s.adresse || '';

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
                                    <td style="padding:12px; font-weight: bold; color: #333;">${sNom}</td>
                                    <td style="padding:12px; color: #555;">${sAmenageur}</td>
                                    <td style="padding:12px; font-size: 13px;"><strong>${sCommune}</strong><br>${sAdresse}</td>
                                    <td style="padding:12px;"><span style="background: #e3f2fd; color: #0d47a1; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">${textPrises}</span></td>
                                    <td style="padding:12px; font-size: 13px; color: #666; max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${textTarif}">${textTarif}</td>
                                    ${actionCell}
                                </tr>
                            `;
                        });
                    }
                    
                    if (container) container.style.display = "block";
                })
                .catch(e => console.error("Erreur lors de la recherche :", e));
        });
    }

    // =========================================================================
    // 4. FENÊTRE MODALE ADMIN : OUVERTURE & PRÉ-REMPLISSAGE
    // =========================================================================
    document.addEventListener('click', (e) => {
        if (e.target && e.target.classList.contains('btn-edit-trigger')) {
            const idStation = e.target.getAttribute('data-id');

            fetch(`${apiPrefix}get_station_details.php?id=${encodeURIComponent(idStation)}`)
                .then(r => r.json())
                .then(station => {
                    if (!station) return;
                    if (document.getElementById('edit-id-station')) document.getElementById('edit-id-station').value = station.id_station_interne || '';
                    if (document.getElementById('edit-nom')) document.getElementById('edit-nom').value = station.nom_station || station.nom || '';
                    if (document.getElementById('edit-adresse')) document.getElementById('edit-adresse').value = station.adresse_station || station.adresse || '';
                    if (document.getElementById('edit-puissance')) document.getElementById('edit-puissance').value = station.puissance_nominale || '';
                    if (document.getElementById('edit-tarification')) document.getElementById('edit-tarification').value = station.tarification || '';

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

    const btnCloseModal = document.getElementById('btn-close-modal');
    if (btnCloseModal) {
        btnCloseModal.addEventListener('click', () => {
            const modal = document.getElementById('modal-edit');
            if (modal) modal.style.display = "none";
        });
    }

    // =========================================================================
    // 5. ACTION DE SUPPRESSION ADMIN
    // =========================================================================
    document.addEventListener('click', (e) => {
        if (e.target && e.target.classList.contains('btn-delete-trigger')) {
            const idStation = e.target.getAttribute('data-id');
            
            if (confirm("⚠️ Êtes-vous sûr de vouloir supprimer définitivement cette station et tous ses points de recharge ?")) {
                fetch(`${apiPrefix}delete_station.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id_station=${encodeURIComponent(idStation)}`
                })
                .then(r => r.json())
                .then(result => {
                    if (result && result.success) {
                        alert("✅ La station a été supprimée avec succès !");
                        document.getElementById('btn-search')?.click();
                    } else {
                        alert("❌ Erreur : " + (result?.error || "Inconnue"));
                    }
                })
                .catch(err => alert("Erreur réseau lors de la suppression : " + err.message));
            }
        }
    });
});