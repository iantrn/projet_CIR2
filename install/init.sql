CREATE TABLE raccordement (
  libelle_raccordement VARCHAR(255) NOT NULL,
  CONSTRAINT raccordement_PK PRIMARY KEY (libelle_raccordement)
) ENGINE=InnoDB;

CREATE TABLE implantation_station (
  libelle_implantation VARCHAR(255) NOT NULL,
  CONSTRAINT implantation_station_PK PRIMARY KEY (libelle_implantation)
) ENGINE=InnoDB;

CREATE TABLE enseigne (
  id_enseigne INT NOT NULL AUTO_INCREMENT,
  nom_enseigne VARCHAR(255) NOT NULL,
  CONSTRAINT enseigne_PK PRIMARY KEY (id_enseigne)
) ENGINE=InnoDB;

CREATE TABLE departement (
  code_dep VARCHAR(3) NOT NULL,
  nom_dep VARCHAR(100) NOT NULL,
  CONSTRAINT departement_PK PRIMARY KEY (code_dep)
) ENGINE=InnoDB;

CREATE TABLE horaires (
  libelle_horaires VARCHAR(255) NOT NULL,
  CONSTRAINT horaires_PK PRIMARY KEY (libelle_horaires)
) ENGINE=InnoDB;

CREATE TABLE condition_acces (
  libelle_condition_acces VARCHAR(255) NOT NULL,
  CONSTRAINT condition_acces_PK PRIMARY KEY (libelle_condition_acces)
) ENGINE=InnoDB;

CREATE TABLE amenageur_operateur (
  id_amenageur INT NOT NULL AUTO_INCREMENT,
  siren_amenageur_operateur VARCHAR(9),
  nom_amenageur_operateur VARCHAR(255) NOT NULL,
  contact_amenageur VARCHAR(255) NOT NULL,
  telephone_operateur VARCHAR(255),
  contact_operateur VARCHAR(255) NOT NULL,
  CONSTRAINT amenageur_operateur_PK PRIMARY KEY (id_amenageur)
) ENGINE=InnoDB;

CREATE TABLE commune (
  code_insee VARCHAR(5) NOT NULL,
  nom_commune VARCHAR(255) NOT NULL,
  code_postal VARCHAR(5) NOT NULL,
  code_dep VARCHAR(3) NOT NULL,
  CONSTRAINT commune_PK PRIMARY KEY (code_insee),
  CONSTRAINT commune_code_dep_FK FOREIGN KEY (code_dep) REFERENCES departement (code_dep)
) ENGINE=InnoDB;


CREATE TABLE station (
  id_station_interne INT NOT NULL AUTO_INCREMENT,
  id_station_itinerance VARCHAR(50) NOT NULL,
  id_station_local VARCHAR(255) NOT NULL,
  nom_station VARCHAR(255) NOT NULL,
  adresse_station VARCHAR(255) NOT NULL,
  longitude DECIMAL(9,6) NOT NULL,
  latitude DECIMAL(9,6) NOT NULL,
  code_insee VARCHAR(5) NOT NULL,
  id_enseigne INT NOT NULL,
  libelle_implantation VARCHAR(255) NOT NULL,
  libelle_condition_acces VARCHAR(255) NOT NULL,
  libelle_horaires VARCHAR(255) NOT NULL,
  CONSTRAINT station_PK PRIMARY KEY (id_station_interne),
  CONSTRAINT station_code_insee_FK FOREIGN KEY (code_insee) REFERENCES commune (code_insee),
  CONSTRAINT station_id_enseigne_FK FOREIGN KEY (id_enseigne) REFERENCES enseigne (id_enseigne),
  CONSTRAINT station_libelle_implantation_FK FOREIGN KEY (libelle_implantation) REFERENCES implantation_station (libelle_implantation),
  CONSTRAINT station_libelle_condition_acces_FK FOREIGN KEY (libelle_condition_acces) REFERENCES condition_acces (libelle_condition_acces),
  CONSTRAINT station_libelle_horaires_FK FOREIGN KEY (libelle_horaires) REFERENCES horaires (libelle_horaires)
) ENGINE=InnoDB;

CREATE TABLE point_de_recharge (
  id_pdc_interne INT NOT NULL AUTO_INCREMENT,
  id_pdc_csv VARCHAR(255) NOT NULL,
  puissance_nominale DECIMAL(10,2) NOT NULL,
  date_mise_en_service DATE NOT NULL,
  prise_ef SMALLINT NOT NULL,
  prise_t2 SMALLINT NOT NULL,
  prise_combo_ccs SMALLINT NOT NULL,
  prise_chademo SMALLINT NOT NULL,
  prise_autre SMALLINT NOT NULL,
  cable_t2_attache SMALLINT NOT NULL,
  gratuit SMALLINT NOT NULL,
  paiment_acte SMALLINT NOT NULL,
  paiement_cb SMALLINT NOT NULL,
  paiement_autre SMALLINT NOT NULL,
  tarification TEXT NOT NULL,
  id_station_interne INT NOT NULL,
  libelle_raccordement VARCHAR(255) NOT NULL,
  id_amenageur INT NOT NULL,
  CONSTRAINT point_de_recharge_PK PRIMARY KEY (id_pdc_interne),
  CONSTRAINT point_de_recharge_id_station_interne_FK FOREIGN KEY (id_station_interne) REFERENCES station (id_station_interne),
  CONSTRAINT point_de_recharge_libelle_raccordement_FK FOREIGN KEY (libelle_raccordement) REFERENCES raccordement (libelle_raccordement),
  CONSTRAINT point_de_recharge_id_amenageur_FK FOREIGN KEY (id_amenageur) REFERENCES amenageur_operateur (id_amenageur)
) ENGINE=InnoDB;
