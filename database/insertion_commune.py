import csv
import mysql.connector


db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'projet_cir2',
    'charset': 'utf8mb4'
}


def import_communes(file_path):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        print("🚀 Connexion réussie. Début de l'importation...")

        conn.autocommit = False  # Transaction manuelle

        # Requêtes SQL (optimisées)
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

        with open(file_path, mode='r', encoding='utf-8') as file:
            # IMPORTANT : Vérifie ici que ces noms correspondent EXACTEMENT à ton en-tête de CSV
            reader = csv.DictReader(file, delimiter=';')

            dep_count = 0
            commune_count = 0
            deps_visites = set()

            for row in reader:
                try:
                    # ADAPTATION : remplace les noms entre guillemets par ceux de TON CSV
                    code_dep = row.get('dep_code')
                    nom_dep = row.get('dep_nom')
                    code_insee = row.get('code_insee')
                    nom_commune = row.get('nom_standard')
                    code_postal = row.get('code_postal')

                    if not code_dep or not code_insee:
                        continue

                    # Insertion Département
                    if code_dep not in deps_visites:
                        cursor.execute(sql_dep, (code_dep, nom_dep))
                        deps_visites.add(code_dep)
                        dep_count += 1

                    # Insertion Commune
                    cursor.execute(sql_commune, (code_insee, nom_commune, code_postal, code_dep))
                    commune_count += 1

                except Exception as e:
                    print(f"⚠️ Erreur sur la ligne {row}: {e}")
                    continue

        conn.commit()
        print(f"🎉 Succès ! {dep_count} départements et {commune_count} communes injectés.")

    except mysql.connector.Error as err:
        print(f"❌ Erreur SQL : {err}")
        if 'conn' in locals() and conn.is_connected():
            conn.rollback()
    finally:
        if 'cursor' in locals(): cursor.close()
        if 'conn' in locals() and conn.is_connected(): conn.close()


# Lance l'import
import_communes('communes-france-2024-limite.csv')
