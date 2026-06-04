// =========================================================================
// 1. Initialisation, Détection du Contexte et Gestion du Mode (Header)
// =========================================================================
document.addEventListener('DOMContentLoaded', () => {
    const scriptTag = document.getElementById('main-script');

    // Détection de la position physique du fichier PHP dans l'arborescence
    // On se base sur data-location="backfolder" OU sur l'URL
    const isInsideBackFolder =
        (scriptTag && scriptTag.getAttribute('data-location') === 'backfolder') ||
        window.location.pathname.includes('/back/');

    // data-mode="admin" → on est en back-office (actions CRUD activées)
    const isBackOffice = scriptTag && scriptTag.getAttribute('data-mode') === 'admin';

    // Le chemin vers l'API dépend de l'emplacement physique du fichier PHP
    // front (web/)      → api/
    // back  (web/back/) → ../api/
    const apiPrefix = isInsideBackFolder ? '../api/' : 'api/';

    // --- GESTION DU SWITCHER DE MODE (HEADER) ---
    const btnModeUser  = document.getElementById('btn-mode-user');
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

        /**
         * Charge les marqueurs depuis l'API selon les filtres sélectionnés.
         * Les IDs des selects sont normalisés : #filter-annee et #filter-departement
         * sur TOUTES les pages carte (front et back).
         */
        function loadMarkers() {
            const annee       = document.getElementById('filter-annee')?.value       || '';
            const departement = document.getElementById('filter-departement')?.value  || '';

            fetch(`${apiPrefix}get_stations.php?annee=${encodeURIComponent(annee)}&departement=${encodeURIComponent(departement)}`)
                .then(response => response.json())
                .then(stations => {
                    markersGroup.clearLayers();

                    if (!Array.isArray(stations)) {
                        console.error("Réponse API inattendue :", stations);
                        return;
                    }

                    stations.forEach(st => {
                        if (!st) return;

                        // Les coordonnées proviennent des colonnes latitude / longitude
                        const lat = parseFloat(st.latitude);
                        const lon = parseFloat(st.longitude);

                        if (isNaN(lat) || isNaN(lon)) return;

                        // Construction de la liste des prises disponibles
                        const listePrises = [];
                        if (parseInt(st.prise_ef)        === 1) listePrises.push('EF');
                        if (parseInt(st.prise_t2)        === 1) listePrises.push('T2');
                        if (parseInt(st.prise_combo_ccs) === 1) listePrises.push('Combo CCS');
                        if (parseInt(st.prise_chademo)   === 1) listePrises.push('CHAdeMO');

                        const prisesText   = listePrises.length > 0 ? listePrises.join(', ') : 'Non spécifiées';
                        const tarifText    = st.tarification  || 'Non spécifiée';
                        const stationNom   = st.nom_station   || 'Station sans nom';
                        const stationAdresse = st.adresse_station || 'Adresse inconnue';

                        // Lien vers la page détail (chemin relatif adapté selon le contexte)
                        const detailUrl = isInsideBackFolder
                            ? `../detail.php?id=${st.id_station_interne}`
                            : `detail.php?id=${st.id_station_interne}`;

                        const popupContent = `
                            <div style="font-family: sans-serif; font-size: 13px; min-width: 200px;">
                                <h4 style="margin: 0 0 6px 0; color: #0c1c3e;">${stationNom}</h4>
                                <p style="margin: 3px 0;"><strong>📍 Adresse :</strong> ${stationAdresse}</p>
                                <p style="margin: 3px 0;"><strong>⚡ Puissance :</strong> ${st.puissance_nominale || 'N/A'} kW</p>
                                <p style="margin: 3px 0;"><strong>🔌 Prises :</strong> ${prisesText}</p>
                                <p style="margin: 3px 0;"><strong>💰 Tarif :</strong> ${tarifText}</p>
                                <p style="margin: 6px 0 0 0;">
                                    <a href="${detailUrl}" style="color: #1565c0; font-weight: bold;">→ Voir le détail</a>
                                </p>
                            </div>
                        `;

                        L.marker([lat, lon]).bindPopup(popupContent).addTo(markersGroup);
                    });
                })
                .catch(err => console.error('Erreur lors du chargement de la carte :', err));
        }

        // Rechargement automatique dès qu'un filtre change
        document.getElementById('filter-annee')?.addEventListener('change', loadMarkers);
        document.getElementById('filter-departement')?.addEventListener('change', loadMarkers);

        // Bouton "Filtrer les bornes" présent sur la page carte front
        document.getElementById('btn-filter-carte')?.addEventListener('click', loadMarkers);

        // Chargement initial au démarrage
        loadMarkers();
    }

    // =========================================================================
    // 3. GESTION DE LA RECHERCHE ET DU TABLEAU DYNAMIQUE
    // =========================================================================
    const btnSearchPage = document.getElementById('btn-search');
    if (btnSearchPage) {
        btnSearchPage.addEventListener('click', () => {
            const amenageur   = document.getElementById('amenageur')?.value    || '';
            const typePrise   = document.getElementById('type_prise')?.value   || '';
            const departement = document.getElementById('departement')?.value  || '';

            fetch(`${apiPrefix}search_stations.php?amenageur=${encodeURIComponent(amenageur)}&type_prise=${encodeURIComponent(typePrise)}&departement=${encodeURIComponent(departement)}`)
                .then(r => r.json())
                .then(data => {
                    const tbody     = document.getElementById('search-table-body');
                    const container = document.getElementById('search-results-container');
                    const countSpan = document.getElementById('results-count');

                    if (!tbody) return;
                    tbody.innerHTML = '';

                    if (!Array.isArray(data)) {
                        tbody.innerHTML = '<tr><td colspan="6" style="padding:15px; text-align:center;">Erreur de format de données.</td></tr>';
                        return;
                    }

                    if (countSpan) countSpan.textContent = data.length;

                    // En mode admin, on affiche la colonne Actions
                    const colActions = document.querySelector('.col-actions');
                    if (colActions) colActions.style.display = isBackOffice ? '' : 'none';

                    if (data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" style="padding:15px; text-align:center;">Aucun résultat trouvé.</td></tr>';
                    } else {
                        data.forEach(s => {
                            if (!s) return;

                            const listePrises = [];
                            if (parseInt(s.prise_ef)        === 1) listePrises.push('EF');
                            if (parseInt(s.prise_t2)        === 1) listePrises.push('T2');
                            if (parseInt(s.prise_combo_ccs) === 1) listePrises.push('CCS');
                            if (parseInt(s.prise_chademo)   === 1) listePrises.push('CHA');
                            if (parseInt(s.prise_autre)     === 1) listePrises.push('Autre');

                            const textPrises  = listePrises.length > 0 ? listePrises.join(', ') : 'Aucune';
                            const textTarif   = s.tarification || 'Non spécifié';
                            const sNom        = s.nom_station             || 'Sans nom';
                            const sAmenageur  = s.nom_amenageur_operateur || 'N/A';
                            const sCommune    = s.nom_commune             || '';
                            const sAdresse    = s.adresse_station         || '';

                            // Lien vers la page détail
                            const detailUrl = isInsideBackFolder
                                ? `../detail.php?id=${s.id_station_interne}`
                                : `detail.php?id=${s.id_station_interne}`;

                            let actionCell = '';
                            if (isBackOffice) {
                                actionCell = `
                                    <td style="padding:12px; display: flex; gap: 8px; align-items: center;">
                                        <button class="btn-edit-trigger" data-id="${s.id_station_interne}"
                                            style="background-color:#29b6f6; color:white; border:none; padding:6px 10px; border-radius:4px; cursor:pointer; font-weight:bold;">
                                            ✏️ Modifier
                                        </button>
                                        <button class="btn-delete-trigger" data-id="${s.id_station_interne}"
                                            style="background-color:#ef5350; color:white; border:none; padding:6px 10px; border-radius:4px; cursor:pointer; font-weight:bold;"
                                            title="Supprimer cette station">
                                            🗑️
                                        </button>
                                    </td>
                                `;
                            }

                            tbody.innerHTML += `
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding:12px; font-weight:bold; color:#333;">
                                        <a href="${detailUrl}" style="color:#1565c0; text-decoration:none;">${sNom}</a>
                                    </td>
                                    <td style="padding:12px; color:#555;">${sAmenageur}</td>
                                    <td style="padding:12px; font-size:13px;"><strong>${sCommune}</strong><br>${sAdresse}</td>
                                    <td style="padding:12px;">
                                        <span style="background:#e3f2fd; color:#0d47a1; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:bold;">
                                            ${textPrises}
                                        </span>
                                    </td>
                                    <td style="padding:12px; font-size:13px; color:#666; max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"
                                        title="${textTarif}">${textTarif}</td>
                                    ${actionCell}
                                </tr>
                            `;
                        });
                    }

                    if (container) container.style.display = 'block';
                    // Retire la classe hidden si présente (cas front)
                    if (container) container.classList.remove('hidden');
                })
                .catch(e => console.error('Erreur lors de la recherche :', e));
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

                    const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
                    const setCheck = (id, val) => { const el = document.getElementById(id); if (el) el.checked = (parseInt(val) === 1); };

                    set('edit-id-station',  station.id_station_interne);
                    set('edit-nom',         station.nom_station);
                    set('edit-adresse',     station.adresse_station);
                    set('edit-puissance',   station.puissance_nominale);
                    set('edit-tarification',station.tarification);

                    setCheck('edit-prise-ef',  station.prise_ef);
                    setCheck('edit-prise-t2',  station.prise_t2);
                    setCheck('edit-prise-ccs', station.prise_combo_ccs);
                    setCheck('edit-prise-cha', station.prise_chademo);

                    const modal = document.getElementById('modal-edit');
                    if (modal) modal.style.display = 'flex';
                })
                .catch(err => alert("Erreur d'acquisition des données : " + err.message));
        }
    });

    // Fermeture de la modale
    document.getElementById('btn-close-modal')?.addEventListener('click', () => {
        const modal = document.getElementById('modal-edit');
        if (modal) modal.style.display = 'none';
    });

    // Soumission du formulaire de modification
    document.getElementById('form-edit-station')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const form = e.target;
        const body = new URLSearchParams({
            id_station:      form.querySelector('#edit-id-station')?.value  || '',
            nom_station:     form.querySelector('#edit-nom')?.value          || '',
            adresse_station: form.querySelector('#edit-adresse')?.value      || '',
            puissance:       form.querySelector('#edit-puissance')?.value    || '',
            tarification:    form.querySelector('#edit-tarification')?.value || '',
            prise_ef:        form.querySelector('#edit-prise-ef')?.checked   ? 1 : 0,
            prise_t2:        form.querySelector('#edit-prise-t2')?.checked   ? 1 : 0,
            prise_combo_ccs: form.querySelector('#edit-prise-ccs')?.checked  ? 1 : 0,
            prise_chademo:   form.querySelector('#edit-prise-cha')?.checked  ? 1 : 0,
        });

        fetch(`${apiPrefix}update_station.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        })
        .then(r => r.json())
        .then(result => {
            if (result && result.success) {
                alert('✅ Station modifiée avec succès !');
                const modal = document.getElementById('modal-edit');
                if (modal) modal.style.display = 'none';
                document.getElementById('btn-search')?.click(); // Rafraîchit le tableau
            } else {
                alert('❌ Erreur : ' + (result?.error || 'Inconnue'));
            }
        })
        .catch(err => alert('Erreur réseau lors de la modification : ' + err.message));
    });

    // =========================================================================
    // 5. ACTION DE SUPPRESSION ADMIN
    // =========================================================================
    document.addEventListener('click', (e) => {
        if (e.target && e.target.classList.contains('btn-delete-trigger')) {
            const idStation = e.target.getAttribute('data-id');

            if (confirm('⚠️ Êtes-vous sûr de vouloir supprimer définitivement cette station et tous ses points de recharge ?')) {
                fetch(`${apiPrefix}delete_station.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id_station=${encodeURIComponent(idStation)}`
                })
                .then(r => r.json())
                .then(result => {
                    if (result && result.success) {
                        alert('✅ La station a été supprimée avec succès !');
                        document.getElementById('btn-search')?.click();
                    } else {
                        alert('❌ Erreur : ' + (result?.error || 'Inconnue'));
                    }
                })
                .catch(err => alert('Erreur réseau lors de la suppression : ' + err.message));
            }
        }
    });
});
