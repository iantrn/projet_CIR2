import csv
import mysql.connector

db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'projet_cir2',
    'charset': 'utf8mb4'
}


def format_bool(val):
    return 1 if val and val.lower() == 'true' else 0


def get_or_insert(cursor, table, col_id, col_name, value):
    """Insère ou récupère l'ID d'une valeur dans une table de référence."""
    cursor.execute(f"SELECT {col_id} FROM {table} WHERE {col_name} = %s", (value,))
    res = cursor.fetchone()
    if res:
        return res[0]
    cursor.execute(f"INSERT INTO {table} ({col_name}) VALUES (%s)", (value,))
    return cursor.lastrowid


try:
    conn = mysql.connector.connect(**db_config)
    cursor = conn.cursor(buffered=True)
    print("🚀 Connexion réussie. Début de l'importation...")

    with open('irve_init.csv', 'r', encoding='utf-8') as csvfile:
        reader = csv.DictReader(csvfile)

        for row in reader:
            # 1. Gestion des tables de référence (Parentes)
            id_enseigne = get_or_insert(cursor, 'enseigne', 'id_enseigne', 'nom_enseigne', row['nom_enseigne'])
            id_raccord = get_or_insert(cursor, 'raccordement', 'libelle_raccordement', 'libelle_raccordement',
                                       row['raccordement'])
            id_impl = get_or_insert(cursor, 'implantation_station', 'libelle_implantation', 'libelle_implantation',
                                    row['implantation_station'])
            id_cond = get_or_insert(cursor, 'condition_acces', 'libelle_condition_acces', 'libelle_condition_acces',
                                    row['condition_acces'])
            id_hor = get_or_insert(cursor, 'horaires', 'libelle_horaires', 'libelle_horaires', row['horaires'])

            # Gestion Aménageur
            id_amenageur = get_or_insert(cursor, 'amenageur_operateur', 'id_amenageur', 'nom_amenageur_operateur',
                                         row['nom_amenageur'])

            # 2. Gestion Département et Commune (pour éviter erreur FK)
            cursor.execute("INSERT IGNORE INTO departement (code_dep, nom_dep) VALUES (%s, %s)",
                           (row['code_dep'], "Dept " + row['code_dep']))
            cursor.execute(
                "INSERT IGNORE INTO commune (code_insee, nom_commune, code_postal, code_dep) VALUES (%s, %s, %s, %s)",
                (row['code_insee_commune'], "Commune " + row['code_insee_commune'], "00000", row['code_dep']))

            # 3. Insertion Station
            sql_station = """INSERT IGNORE INTO station 
            (id_station_itinerance, id_station_local, nom_station, adresse_station, longitude, latitude, 
             code_insee, id_enseigne, libelle_implantation, libelle_condition_acces, libelle_horaires) 
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"""

            cursor.execute(sql_station, (
                row['id_station_itinerance'], row['id_station_local'], row['nom_station'], row['adresse_station'],
                row['consolidated_longitude'], row['consolidated_latitude'], row['code_insee_commune'],
                id_enseigne, row['implantation_station'], row['condition_acces'], row['horaires']
            ))

            # Récupération de l'ID interne de la station
            cursor.execute("SELECT id_station_interne FROM station WHERE id_station_itinerance = %s",
                           (row['id_station_itinerance'],))
            id_station = cursor.fetchone()[0]

            # 4. Insertion Point de Charge (avec le lien direct vers l'amenageur)
            sql_pdc = """INSERT INTO point_de_recharge 
            (id_pdc_csv, puissance_nominale, date_mise_en_service, prise_ef, prise_t2, prise_combo_ccs, 
             prise_chademo, prise_autre, cable_t2_attache, gratuit, paiment_acte, paiement_cb, paiement_autre, 
             tarification, id_station_interne, libelle_raccordement, id_amenageur) 
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"""

            cursor.execute(sql_pdc, (
                row['id'],
                row['puissance_nominale'], row['date_mise_en_service'],
                format_bool(row['prise_type_ef']), format_bool(row['prise_type_2']),
                format_bool(row['prise_type_combo_ccs']),
                format_bool(row['prise_type_chademo']), format_bool(row['prise_type_autre']),
                format_bool(row['cable_t2_attache']),
                format_bool(row['gratuit']), format_bool(row['paiement_acte']), format_bool(row['paiement_cb']),
                format_bool(row['paiement_autre']), row['tarification'], id_station, row['raccordement'], id_amenageur
            ))

    conn.commit()
    print("✅ Importation terminée avec succès !")

except Exception as e:
    print(f"❌ Erreur lors de l'import : {e}")
    if 'conn' in locals() and conn.is_connected():
        conn.rollback()
finally:
    if 'cursor' in locals(): cursor.close()
    if 'conn' in locals() and conn.is_connected(): conn.close()
