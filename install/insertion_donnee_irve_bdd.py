import csv
import mysql.connector

# 1. Configuration de la connexion avec tes identifiants ISEN
db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'projet_cir2',
    'charset': 'utf8mb4'
}

def convert_to_bit(val):
    """Convertit les chaînes 'true'/'false' du CSV en entiers 1 ou 0 pour MySQL"""
    if isinstance(val, str):
        clean_val = val.strip().lower()
        if clean_val in ['true', '1', 'yes', 'oui']:
            return 1
    return 0

try:
    # Connexion à la BDD
    conn = mysql.connector.connect(**db_config)
    cursor = conn.cursor()
    print("🚀 Connexion réussie à MySQL avec les identifiants 'isen'. Début de l'importation...")
    
    # Désactivation de l'autocommit pour tout envoyer en un seul bloc (Transaction)
    conn.autocommit = False
    
    # 2. Préparation des requêtes SQL
    sql_raccordement = "INSERT IGNORE INTO raccordement (libelle_raccordement) VALUES (%s)"
    sql_implantation = "INSERT IGNORE INTO implantation_station (libelle_implantation) VALUES (%s)"
    sql_horaires     = "INSERT IGNORE INTO horaires (libelle_horaires) VALUES (%s)"
    sql_condition    = "INSERT IGNORE INTO condition_acces (libelle_condition_acces) VALUES (%s)"
    
    sql_dep          = "INSERT IGNORE INTO departement (code_dep, nom_dep) VALUES (%s, %s)"
    sql_commune      = "INSERT IGNORE INTO commune (code_insee, nom_commune, code_postal, code_dep) VALUES (%s, %s, %s, %s)"
    
    sql_enseigne     = "INSERT INTO enseigne (nom_enseigne) VALUES (%s)"
    sql_acteur       = "INSERT INTO amenageur_operateur (siren_amenageur_operateur, nom_amenageur_operateur, contact_amenageur_operateur, telephone_operateur) VALUES (%s, %s, %s, %s)"
    
    sql_station      = """
        INSERT INTO station (
            id_station_itinerance, id_station_local, nom_station, adresse_station, 
            longitude, latitude, nbre_pdc, id_amenageur, id_operateur, 
            code_insee, id_enseigne, libelle_implantation, libelle_condition_acces, libelle_horaires
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
    """
    
    sql_pdc          = """
        INSERT INTO point_de_recharge (
            id_pdc_csv, puissance_nominale, date_mise_en_service, prise_ef, prise_t2, 
            prise_combo_ccs, prise_chademo, prise_autre, cable_t2_attache, gratuit, 
            paiment_acte, paiement_cb, paiement_autre, tarification, id_station_interne, libelle_raccordement
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
    """

    # Caches en mémoire pour éviter d'insérer des doublons d'Auto-Increment
    cache_enseignes = {}
    cache_acteurs   = {}
    cache_stations  = {}

    # 3. Lecture et traitement du fichier CSV
    with open('irve_init.csv', mode='r', encoding='utf-8') as file:
        reader = csv.DictReader(file, delimiter=',')
        
        row_count = 0
        for row in reader:
            # --- ÉTAPE A : Tables Dictionnaires (VARCHAR) ---
            racc = row.get('raccordement') or 'Inconnu'
            impl = row.get('implantation_station') or 'Inconnu'
            hor  = row.get('horaires') or 'Inconnu'
            cond = row.get('condition_acces') or 'Inconnu'
            
            cursor.execute(sql_raccordement, (racc,))
            cursor.execute(sql_implantation, (impl,))
            cursor.execute(sql_horaires, (hor,))
            cursor.execute(sql_condition, (cond,))
            
            # --- ÉTAPE B : Géographie ---
            code_dep   = row.get('code_dep', '00')
            code_insee = row.get('code_insee_commune', '00000')
            
            cursor.execute(sql_dep, (code_dep, f"Département {code_dep}"))
            cursor.execute(sql_commune, (code_insee, f"Commune {code_insee}", code_insee, code_dep))
            
            # --- ÉTAPE C : Gestion de l'Enseigne (avec cache) ---
            enseigne = row.get('nom_enseigne', 'Sans enseigne').strip()
            if enseigne not in cache_enseignes:
                cursor.execute(sql_enseigne, (enseigne,))
                cache_enseignes[enseigne] = cursor.lastrowid
            id_enseigne = cache_enseignes[enseigne]
            
            # --- ÉTAPE D : Gestion du Pattern Acteur Unique (Aménageur & Opérateur) ---
            # 1. Rôle Aménageur
            amenageur = row.get('nom_amenageur', 'Inconnu').strip()
            if amenageur not in cache_acteurs:
                cursor.execute(sql_acteur, (row.get('siren_amenageur'), amenageur, row.get('contact_amenageur', 'Non renseigné'), None))
                cache_acteurs[amenageur] = cursor.lastrowid
            id_amenageur = cache_acteurs[amenageur]
            
            # 2. Rôle Opérateur
            operateur = row.get('nom_operateur', 'Inconnu').strip()
            if operateur not in cache_acteurs:
                cursor.execute(sql_acteur, (None, operateur, row.get('contact_operateur', 'Non renseigné'), row.get('telephone_operateur')))
                cache_acteurs[operateur] = cursor.lastrowid
            id_operateur = cache_acteurs[operateur]
            
            # --- ÉTAPE E : Gestion de la Station ---
            station_key = row.get('id_station_itinerance') or row.get('id_station_local') or row.get('nom_station')
            if station_key not in cache_stations:
                try:
                    lon = float(row.get('consolidated_longitude', 0))
                    lat = float(row.get('consolidated_latitude', 0))
                    nbre_pdc = int(row.get('nbre_pdc', 0))
                except ValueError:
                    lon, lat, nbre_pdc = 0.0, 0.0, 0
                    
                cursor.execute(sql_station, (
                    row.get('id_station_itinerance', ''),
                    row.get('id_station_local', ''),
                    row.get('nom_station', 'Sans nom'),
                    row.get('adresse_station', 'Sans adresse'),
                    lon, lat, nbre_pdc,
                    id_amenageur, id_operateur, code_insee, id_enseigne,
                    impl, cond, hor
                ))
                cache_stations[station_key] = cursor.lastrowid
            id_station_interne = cache_stations[station_key]
            
            # --- ÉTAPE F : Insertion du Point de Recharge (PDC) ---
            try:
                puissance = float(row.get('puissance_nominale', 0))
            except ValueError:
                puissance = 0.0
                
            date_ms = row.get('date_mise_en_service') or None
            
            cursor.execute(sql_pdc, (
                row.get('id', f"PDC-{row_count}"),
                puissance,
                date_ms,
                convert_to_bit(row.get('prise_type_ef')),
                convert_to_bit(row.get('prise_type_2')), 
                convert_to_bit(row.get('prise_type_combo_ccs')),
                convert_to_bit(row.get('prise_type_chademo')),
                convert_to_bit(row.get('prise_type_autre')),
                convert_to_bit(row.get('cable_t2_attache')),
                convert_to_bit(row.get('gratuit')),
                convert_to_bit(row.get('paiement_acte')), 
                convert_to_bit(row.get('paiement_cb')),
                convert_to_bit(row.get('paiement_autre')),
                row.get('tarification', 'Non renseignée'),
                id_station_interne,
                racc
            ))
            
            row_count += 1

    # Validation globale de la transaction
    conn.commit()
    print(f"🎉 Succès total ! {row_count} lignes insérées proprement en base de données.")

except mysql.connector.Error as err:
    print(f"❌ Erreur SQL rencontrée : {err}")
    if 'conn' in locals() and conn.is_connected():
        conn.rollback()
        print("🔄 Transaction annulée (Rollback) suite à l'erreur.")
        
finally:
    if 'cursor' in locals() and cursor:
        cursor.close()
    if 'conn' in locals() and conn.is_connected():
        conn.close()