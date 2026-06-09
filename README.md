# BreizhWatt

Application web de consultation et de gestion des bornes de recharge pour véhicules électriques en Bretagne. Projet réalisé dans le cadre du cours de bases de données et développement web en CIR2 à l'ISEN Ouest.

---

## Fonctionnalités

**Espace utilisateur**

- Page d'accueil avec statistiques globales : nombre de points de recharge par année et par département breton (22, 29, 35, 56), ainsi qu'un tableau croisé années/départements.
- Carte interactive (Leaflet.js) affichant les bornes avec filtres par année d'installation et par département. Un clic sur un marqueur ouvre une fenêtre modale avec les détails complets de la borne.
- Page de recherche avancée : filtrage par aménageur, type de prise (EF, T2, Combo CCS, CHAdeMO, autre) et département.

**Espace administrateur** (accessible via le bouton "Admin" dans le header)

- Tableau de bord avec liste des stations, ajout, modification et suppression de bornes.
- Les opérations CRUD passent par des API PHP dédiées qui retournent du JSON.

---

## Stack technique

- PHP 8+ avec PDO (connexion MariaDB)
- MariaDB (base de données relationnelle)
- HTML/CSS/JavaScript vanilla
- Leaflet.js 1.9.4 (carte interactive, chargé via CDN)
- Apache (serveur web sur la VM)

---

## Prérequis

- Une VM avec Apache, PHP 8+ et MariaDB installés (stack LAMP)
- Python 3 avec le module `mysql-connector-python` pour les scripts d'import
- Les fichiers CSV de données fournis dans le dossier `database/`

---

## Installation

### 1. Cloner le dépôt

```bash
git clone <url-du-repo>
cd projet_CIR2
```

### 2. Déployer les fichiers web

Copier le contenu du dossier `web/` dans le répertoire servi par Apache, par exemple `/var/www/html/` :

```bash
sudo cp -r web/ /var/www/html/breizhwatt
```

### 3. Créer la base de données

Se connecter à MariaDB et créer la base :

```sql
CREATE DATABASE projet_cir2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE projet_cir2;
SOURCE /chemin/vers/database/init.sql;
```

### 4. Configurer la connexion

Ouvrir `web/config/db.php` et renseigner les paramètres de connexion selon votre environnement :

```php
$host = 'localhost';
$db   = 'projet_cir2';
$user = 'root';
$pass = '';
```

### 5. Importer les données

Depuis le dossier `database/`, lancer les scripts Python dans cet ordre. L'import des communes doit être fait en premier pour respecter les contraintes de clé étrangère.

```bash
cd database/

# Import des communes et départements
python3 insertion_commune.py

# Import des bornes IRVE
python3 insertion_donnee_irve_bdd.py
```

Les deux scripts lisent les fichiers CSV présents dans le même dossier (`communes-france-2024-limite.csv` et `irve_init.csv`).

---

## Structure du projet

```
projet_CIR2/
├── database/
│   ├── init.sql                          # Schéma de la base (CREATE TABLE)
│   ├── insertion_commune.py              # Import des communes et départements
│   ├── insertion_donnee_irve_bdd.py      # Import des bornes IRVE
│   ├── communes-france-2024-limite.csv   # Données communes (source externe)
│   └── irve_init.csv                     # Données bornes IRVE (source externe)
└── web/
    ├── accueil.php                       # Page d'accueil avec statistiques
    ├── carte.php                         # Carte interactive Leaflet
    ├── recherche.php                     # Recherche avancée
    ├── config/
    │   └── db.php                        # Connexion PDO à la base
    ├── api/
    │   ├── get_stations.php              # API : liste des bornes (avec filtres)
    │   ├── get_station_details.php       # API : détail d'une borne par ID
    │   └── search_stations.php           # API : recherche par critères
    ├── back/
    │   ├── accueil.php                   # Dashboard administrateur
    │   ├── carte.php                     # Carte côté admin
    │   ├── recherche.php                 # Recherche côté admin
    │   └── api/
    │       ├── add_station.php           # API : ajout d'une borne
    │       ├── update_station.php        # API : modification d'une borne
    │       └── delete_station.php        # API : suppression d'une borne
    ├── css/
    │   └── style.css
    ├── js/
    │   └── main.js
    └── img/
```

---

## Auteurs

Gabriel T., Ian T. — CIR2, ISEN Ouest, 2026
