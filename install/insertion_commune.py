import csv
import mysql.connector

# 1. Connexion à ta base MySQL ISEN
db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'projet_cir2',
    'charset': 'utf8mb4'
}

try:
    conn = mysql.connector.connect(**db_config)
    cursor = conn.cursor()
    print("🚀 Connexion réussie. Début de l'importation des communes françaises...")

    # Désactivation de l'autocommit pour booster la vitesse (Transaction)
    conn.autocommit = False

    # 2. Requêtes SQL
    sql_dep = """
        INSERT INTO departement (code_dep, nom_dep) 
        VALUES (%s, %s)
        ON DUPLICATE KEY UPDATE nom_dep = VALUES(nom_dep)
    """

    sql_commune = """
        INSERT INTO commune (code_insee, nom_commune, code_postal, code_dep) 
        VALUES (%s, %s, %s, %s)
        ON DUPLICATE KEY UPDATE nom_commune = VALUES(nom_commune), code_postal = VALUES(code_postal)
    """

    # 3. Lecture du CSV (Séparateur ';')
    with open('communes-france-2024-limite.csv', mode='r', encoding='utf-8') as file:
        reader = csv.DictReader(file, delimiter=';')

        dep_count = 0
        commune_count = 0

        # Un petit set pour éviter de renvoyer le même département 500 fois à MySQL
        deps_visites = set()

        for row in reader:
            code_dep = row.get('dep_code')
            nom_dep = row.get('dep_nom')
            code_insee = row.get('code_insee')
            nom_commune = row.get('nom_standard')
            code_postal = row.get('code_postal')

            # Si la ligne est corrompue ou incomplète, on passe
            if not code_dep or not code_insee:
                continue

            # Étape A : Insertion du Département (si pas encore vu dans la boucle)
            if code_dep not in deps_visites:
                cursor.execute(sql_dep, (code_dep, nom_dep))
                deps_visites.add(code_dep)
                dep_count += 1

            # Étape B : Insertion de la Commune
            cursor.execute(sql_commune, (code_insee, nom_commune, code_postal, code_dep))
            commune_count += 1

    # Validation de la transaction
    conn.commit()
    print(f"🎉 Succès ! {dep_count} départements et {commune_count} communes injectés avec succès.")

except mysql.connector.Error as err:
    print(f"❌ Erreur SQL : {err}")
    if 'conn' in locals() and conn.is_connected():
        conn.rollback()
        print("🔄 Importation annulée (Rollback).")

finally:
    if 'cursor' in locals() and cursor:
        cursor.close()
    if 'conn' in locals() and conn.is_connected():
        conn.close()