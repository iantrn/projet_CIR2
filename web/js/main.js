// =========================================================================
// 1. Initialisation, Détection du Contexte et Gestion du Mode (Header)
// =========================================================================
document.addEventListener('DOMContentLoaded', () => {
    const scriptTag = document.getElementById('main-script');

    // Détection de la position physique du fichier PHP dans l'arborescence
    const isInsideBackFolder =
        (scriptTag && scriptTag.getAttribute('data-location') === 'backfolder') ||
        window.location.pathname.includes('/back/');

    // data-mode="admin" → on est en back-office (actions CRUD activées)
    const isBackOffice = scriptTag && scriptTag.getAttribute('data-mode') === 'admin';

    // Le chemin vers l'API dépend de l'emplacement physique du fichier PHP
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
    // 2. INITIALISATION DE LA CARTE LEAFLET (AVEC LIEN DÉTAILS)
    // =========================================================================
    const mapElement = document.getElementById('map');
    if (mapElement) {
        const map = L.map('map').setView([48.2020, -2.9326], 8);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        let markersGroup = L.layerGroup().addTo(map);

        function loadMarkers() {
            const annee       = document.getElementById('filter-annee')?.value       || '';
            const departement = document.getElementById('filter-departement')?.value  || '';

            fetch(`${apiPrefix}get_stations.php?annee=${encodeURIComponent(annee)}&departement=${encodeURIComponent(departement)}`)
                .then(response => response.json())
                .then(stations => {
                    markersGroup.clearLayers();

                    if (!Array.isArray(stations)) return;

                    stations.forEach(st => {
                        if (!st) return;

                        const lat = parseFloat(st.latitude);
                        const lon = parseFloat(st.longitude);

                        if (isNaN(lat) || isNaN(lon)) return;

                        const listePrises = [];
                        if (parseInt(st.prise_ef)        === 1) listePrises.push('EF');
                        if (parseInt(st.prise_t2)        === 1) listePrises.push('T2');
                        if (parseInt(st.prise_combo_ccs) === 1) listePrises.push('Combo CCS');
                        if (parseInt(st.prise_chademo)   === 1) listePrises.push('CHAdeMO');

                        const prisesText   = listePrises.length > 0 ? listePrises.join(', ') : 'Non spécifiées';
                        const tarifText    = st.tarification  || 'Non spécifiée';
                        const stationNom   = st.nom_station   || 'Station sans nom';
                        const stationAdresse = st.adresse_station || 'Adresse inconnue';

                        // MODIFIÉ : Ajout du lien hypertexte "btn-detail-trigger" dans la popup Leaflet
                        const popupContent = `
                            <div style="font-family: sans-serif; font-size: 13px; min-width: 200px;">
                                <h4 style="margin: 0 0 6px 0; color: #0c1c3e;">${stationNom}</h4>
                                <p style="margin: 3px 0;"><strong>📍 Adresse :</strong> ${stationAdresse}</p>
                                <p style="margin: 3px 0;"><strong>⚡ Puissance :</strong> ${st.puissance_nominale || 'N/A'} kW</p>
                                <p style="margin: 3px 0;"><strong>🔌 Prises :</strong> ${prisesText}</p>
                                <p style="margin: 3px 0;"><strong>💰 Tarif :</strong> ${tarifText}</p>
                                <hr style="border:0; border-top:1px solid #eee; margin:8px 0;">
                                <a href="#" class="btn-detail-trigger" data-id="${st.id_station_interne}" style="color:#3498db; font-weight:bold; text-decoration:none; display:inline-block;">👁️ Voir tous les détails</a>
                            </div>
                        `;

                        L.marker([lat, lon]).bindPopup(popupContent).addTo(markersGroup);
                    });
                })
                .catch(err => console.error('Erreur lors du chargement de la carte :', err));
        }

        document.getElementById('filter-annee')?.addEventListener('change', loadMarkers);
        document.getElementById('filter-departement')?.addEventListener('change', loadMarkers);
        document.getElementById('btn-filter-carte')?.addEventListener('click', loadMarkers);

        loadMarkers();
    }

    // =========================================================================
    // 3. GESTION DE LA RECHERCHE ET DU TABLEAU DYNAMIQUE (USER & BACKOFFICE)
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

                            // MODIFIÉ : Le bouton vert "Détail" est maintenant inclus POUR TOUT LE MONDE (User ET Admin)
                            let actionCell = `
                                <td style="padding:12px; display: flex; gap: 8px; align-items: center;">
                                    <button class="btn-detail-trigger" data-id="${s.id_station_interne}" 
                                        style="background-color: #4caf50; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-weight: bold;">
                                        👁️ Détail
                                    </button>
                            `;

                            // Ajout additionnel des boutons de modification uniquement en admin
                            if (isBackOffice) {
                                actionCell += `
                                    <button class="btn-edit-trigger" data-id="${s.id_station_interne}"
                                        style="background-color:#29b6f6; color:white; border:none; padding:6px 10px; border-radius:4px; cursor:pointer; font-weight:bold;">
                                        ✏️ Modifier
                                    </button>
                                    <button class="btn-delete-trigger" data-id="${s.id_station_interne}"
                                        style="background-color:#ef5350; color:white; border:none; padding:6px 10px; border-radius:4px; cursor:pointer; font-weight:bold;"
                                        title="Supprimer cette station">
                                        🗑️
                                    </button>
                                `;
                            }
                            actionCell += `</td>`;

                            tbody.innerHTML += `
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding:12px; font-weight:bold; color:#333;">${sNom}</td>
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
                    if (container) container.classList.remove('hidden');
                })
                .catch(e => console.error('Erreur lors de la recherche :', e));
        });
    }

    // =========================================================================
    // 4. FENÊTRE MODALE : OUVERTURE DÉTAIL GLOBAL (TABLEAU + CARTE)
    // =========================================================================
    document.addEventListener('click', (e) => {
        if (e.target && e.target.classList.contains('btn-detail-trigger')) {
            // Empêche le comportement par défaut si c'est une balise <a> sur la carte
            if(e.target.tagName === 'A') { e.preventDefault(); }
            
            const idStation = e.target.getAttribute('data-id');

            // Appel à l'API pour récupérer TOUTES les infos
            fetch(`${apiPrefix}get_station_details.php?id=${encodeURIComponent(idStation)}`)
                .then(r => r.json())
                .then(station => {
                    if (!station || station.error) {
                        alert("Erreur lors du chargement des détails : " + (station?.error || "Inconnue"));
                        return;
                    }

                    // Formatage des prises
                    const listePrises = [];
                    if (parseInt(station.prise_ef) === 1) listePrises.push('EF (Prise Domestique)');
                    if (parseInt(station.prise_t2) === 1) listePrises.push('Type 2 (Standard AC)');
                    if (parseInt(station.prise_combo_ccs) === 1) listePrises.push('Combo CCS (Recharge Rapide DC)');
                    if (parseInt(station.prise_chademo) === 1) listePrises.push('CHAdeMO (Recharge Rapide DC)');
                    if (parseInt(station.prise_autre) === 1) listePrises.push('Autre type de connecteur');
                    const prisesText = listePrises.length > 0 ? listePrises.join('<br>• ') : 'Aucune précision';

                    // Formatage des booléens (Oui/Non)
                    const isGratuit = parseInt(station.gratuit) === 1 ? '<span style="color:green; font-weight:bold;">Oui ✅</span>' : 'Non ❌';
                    const isCB = parseInt(station.paiement_cb) === 1 ? 'Oui ✅' : 'Non ❌';
                    const isActe = parseInt(station.paiment_acte) === 1 ? 'Oui ✅' : 'Non ❌';
                    const isAutrePaiement = parseInt(station.paiement_autre) === 1 ? 'Oui ✅' : 'Non ❌';
                    const hasCable = parseInt(station.cable_t2_attache) === 1 ? 'Oui (Prêt à l\'emploi) ✅' : 'Non (Apportez votre câble) ❌';

                    // Création de l'affichage HTML structuré en blocs
                    const htmlInfos = `
                        <div style="margin-bottom: 15px; background: #f8fafc; padding: 10px; border-radius: 8px; border-left: 4px solid #3498db;">
                            <h4 style="margin: 0 0 5px 0; color: #1e3a8a;">📍 Localisation & Identité</h4>
                            <p style="margin: 3px 0;"><strong>Nom :</strong> ${station.nom_station || 'Inconnu'}</p>
                            <p style="margin: 3px 0;"><strong>Enseigne :</strong> ${station.nom_enseigne || 'Non spécifiée'}</p>
                            <p style="margin: 3px 0;"><strong>Implantation :</strong> ${station.libelle_implantation || 'Non spécifiée'}</p>
                            <p style="margin: 3px 0;"><strong>Adresse :</strong> ${station.adresse_station || ''}, ${station.code_postal || ''} ${station.nom_commune || ''}</p>
                            <p style="margin: 3px 0;"><strong>Département :</strong> ${station.nom_dep || ''} (${station.code_dep || ''})</p>
                            <p style="margin: 3px 0;"><strong>GPS :</strong> <code>${station.latitude || 'N/A'}, ${station.longitude || 'N/A'}</code></p>
                        </div>

                        <div style="margin-bottom: 15px; background: #f0fdf4; padding: 10px; border-radius: 8px; border-left: 4px solid #10b981;">
                            <h4 style="margin: 0 0 5px 0; color: #065f46;">⚡ Détails Techniques</h4>
                            <p style="margin: 3px 0;"><strong>Puissance Maximale :</strong> ${station.puissance_nominale ? station.puissance_nominale + ' kW' : 'Inconnue'}</p>
                            <p style="margin: 3px 0;"><strong>Câble T2 attaché :</strong> ${hasCable}</p>
                            <p style="margin: 3px 0;"><strong>Raccordement :</strong> ${station.libelle_raccordement || 'Inconnu'}</p>
                            <p style="margin: 3px 0;"><strong>Mise en service :</strong> ${station.date_mise_en_service || 'Inconnue'}</p>
                            <p style="margin: 5px 0 2px 0;"><strong>Connecteurs disponibles :</strong><br>• ${prisesText}</p>
                        </div>

                        <div style="margin-bottom: 15px; background: #fffbeb; padding: 10px; border-radius: 8px; border-left: 4px solid #f59e0b;">
                            <h4 style="margin: 0 0 5px 0; color: #92400e;">💰 Tarification & Accès</h4>
                            <p style="margin: 3px 0;"><strong>Horaires :</strong> ${station.libelle_horaires || 'Non spécifiés'}</p>
                            <p style="margin: 3px 0;"><strong>Conditions d'accès :</strong> ${station.libelle_condition_acces || 'Non spécifiées'}</p>
                            <p style="margin: 3px 0;"><strong>Recharge Gratuite :</strong> ${isGratuit}</p>
                            <p style="margin: 3px 0;"><strong>Paiement à l'acte :</strong> ${isActe} | <strong>Carte Bancaire (CB) :</strong> ${isCB} | <strong>Autre :</strong> ${isAutrePaiement}</p>
                            <p style="margin: 3px 0;"><strong>Détails Tarifs :</strong> <span style="font-size: 13px; color: #4b5563;">${station.tarification || 'Non communiqué'}</span></p>
                        </div>

                        <div style="margin-bottom: 5px; background: #f3f4f6; padding: 10px; border-radius: 8px; border-left: 4px solid #6b7280;">
                            <h4 style="margin: 0 0 5px 0; color: #374151;">🛠️ Opérateur & Assistance</h4>
                            <p style="margin: 3px 0;"><strong>Aménageur :</strong> ${station.nom_amenageur_operateur || 'Inconnu'}</p>
                            <p style="margin: 3px 0;"><strong>Téléphone Opérateur :</strong> ${station.telephone_operateur || 'Non renseigné'}</p>
                            <p style="margin: 3px 0;"><strong>Contact Opérateur :</strong> ${station.contact_operateur || 'Non renseigné'}</p>
                        </div>
                    `;

                    // Injection dans la page et affichage
                    const conteneurDetail = document.getElementById('detail-content');
                    if (conteneurDetail) conteneurDetail.innerHTML = htmlInfos;

                    const modalDetail = document.getElementById('modal-detail');
                    if (modalDetail) modalDetail.style.display = 'flex';
                })
                .catch(err => console.error("Erreur popup détail :", err));
        }
    });

    // Fermeture de la fenêtre Détail
    document.getElementById('btn-close-detail')?.addEventListener('click', () => {
        const modalDetail = document.getElementById('modal-detail');
        if (modalDetail) modalDetail.style.display = 'none';
    });

    // =========================================================================
    // 5. FENÊTRE MODALE ADMIN : ÉDITION ET SUPPRESSION
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

    document.getElementById('btn-close-modal')?.addEventListener('click', () => {
        const modal = document.getElementById('modal-edit');
        if (modal) modal.style.display = 'none';
    });

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
                document.getElementById('btn-search')?.click();
            } else {
                alert('❌ Erreur : ' + (result?.error || 'Inconnue'));
            }
        })
        .catch(err => alert('Erreur réseau lors de la modification : ' + err.message));
    });

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
