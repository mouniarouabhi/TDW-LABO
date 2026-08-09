<?php
// config/Database.php

class DatabaseConf {
private $host = "127.0.0.1";
private $dbname = "TDW";
private $username = "root";
private $password = "";
private $connexion;

public function __construct() {
try {
$dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
$this->connexion = new PDO($dsn, $this->username, $this->password);
$this->connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$this->connexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
die("Erreur de connexion à la base de données : " . $e->getMessage());
}
}

public function getConnexion() {
return $this->connexion;
}

// Installation complète : création de la base + tables + données
public static function install() {
$dsn = "mysql:host=127.0.0.1;charset=utf8mb4";
try {
$c = new PDO($dsn, "admin", "admin");
$c->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $ex) {
die("Erreur de connexion : " . $ex->getMessage());
}

// Création de la base
$c->exec("DROP DATABASE IF EXISTS TDW");
$c->exec("CREATE DATABASE IF NOT EXISTS TDW CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$c->exec("USE TDW");

/******************************
 *         TABLES
 ******************************/
// USERS
$c->exec("
CREATE TABLE IF NOT EXISTS users (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    login VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    grade VARCHAR(100),
    domaine_recherche TEXT,
    photo VARCHAR(255) DEFAULT 'default.png',
    statut ENUM('actif','suspendu','inactif') NOT NULL DEFAULT 'actif',
    biographie TEXT,
    poste VARCHAR(100),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
");

// TEAMS
$c->exec("
CREATE TABLE IF NOT EXISTS teams (
    id_team INT PRIMARY KEY AUTO_INCREMENT,
    nom_equipe VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    thematique VARCHAR(100),
    id_chef INT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_chef) REFERENCES users(id_user) ON DELETE SET NULL
) ENGINE=InnoDB
");

// TEAM MEMBERS
$c->exec("
CREATE TABLE IF NOT EXISTS team_members (
    id_team_member INT PRIMARY KEY AUTO_INCREMENT,
    id_team INT NOT NULL,
    id_user INT NOT NULL,
    date_adhesion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_team) REFERENCES teams(id_team) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    UNIQUE KEY unique_team_user (id_team, id_user)
) ENGINE=InnoDB
");

// PROJECTS
$c->exec("
CREATE TABLE IF NOT EXISTS projects (
    id_project INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    thematique VARCHAR(100),
    type_financement VARCHAR(100),
    statut ENUM('en_cours','termine','soumis') NOT NULL DEFAULT 'en_cours',
    id_responsable INT NULL,
    id_encadrant INT NULL,
    date_debut DATE,
    date_fin DATE,
    budget DECIMAL(10,2),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_responsable) REFERENCES users(id_user) ON DELETE SET NULL,
    FOREIGN KEY (id_encadrant) REFERENCES users(id_user) ON DELETE SET NULL
) ENGINE=InnoDB
");

// PROJECT MEMBERS
$c->exec("
CREATE TABLE IF NOT EXISTS project_members (
id_project_member INT PRIMARY KEY AUTO_INCREMENT,
id_project INT NOT NULL,
id_user INT NOT NULL,
role_projet VARCHAR(100),
date_adhesion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (id_project) REFERENCES projects(id_project) ON DELETE CASCADE,
FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
UNIQUE KEY unique_project_user (id_project, id_user)
) ENGINE=InnoDB
");

// PUBLICATIONS
$c->exec("
CREATE TABLE IF NOT EXISTS publications (
    id_publication INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(255) NOT NULL,
    resume TEXT,
    type_publication ENUM('article','rapport','these','communication','poster','autre') NOT NULL,
    annee_publication YEAR,
    doi VARCHAR(100),
    lien_telechargement VARCHAR(255),
    id_project INT NULL,
    statut ENUM('en_attente','valide','rejete') NOT NULL DEFAULT 'en_attente',
    date_soumission DATE,
    domaine VARCHAR(100),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_project) REFERENCES projects(id_project) ON DELETE SET NULL
) ENGINE=InnoDB
");

// PUBLICATION AUTHORS
$c->exec("
CREATE TABLE IF NOT EXISTS publication_authors (
    id_pub_author INT PRIMARY KEY AUTO_INCREMENT,
    id_publication INT NOT NULL,
    id_user INT NOT NULL,
    ordre_auteur INT NOT NULL,
    FOREIGN KEY (id_publication) REFERENCES publications(id_publication) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    UNIQUE KEY unique_pub_user (id_publication, id_user)
) ENGINE=InnoDB
");

// EQUIPMENTS
$c->exec("
CREATE TABLE IF NOT EXISTS equipments (
    id_equipment INT PRIMARY KEY AUTO_INCREMENT,
    nom_equipment VARCHAR(100) NOT NULL,
    type_equipment ENUM('salle','serveur','pc','robot','imprimante','capteur','autre') NOT NULL,
    description TEXT,
    etat ENUM('libre','reserve','en_maintenance','hors_service') NOT NULL DEFAULT 'libre',
    localisation VARCHAR(100),
    date_acquisition DATE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
");

// RESERVATIONS
$c->exec("
CREATE TABLE IF NOT EXISTS reservations (
    id_reservation INT PRIMARY KEY AUTO_INCREMENT,
    id_equipment INT NOT NULL,
    id_user INT NOT NULL,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    motif TEXT,
    statut ENUM('en_attente','confirmee','annulee','terminee') NOT NULL DEFAULT 'en_attente',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_equipment) REFERENCES equipments(id_equipment) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB
");

// EVENTS
$c->exec("
CREATE TABLE IF NOT EXISTS events (
    id_event INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    type_event ENUM('conference','atelier','seminaire','soutenance','autre') NOT NULL,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME,
    lieu VARCHAR(200),
    organisateur INT NULL,
    statut ENUM('a_venir','en_cours','termine','annule') NOT NULL DEFAULT 'a_venir',
    lien_inscription VARCHAR(255),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organisateur) REFERENCES users(id_user) ON DELETE SET NULL
) ENGINE=InnoDB
");

// EVENT REGISTRATIONS
$c->exec("
CREATE TABLE IF NOT EXISTS event_registrations (
    id_registration INT PRIMARY KEY AUTO_INCREMENT,
    id_event INT NOT NULL,
    id_user INT NOT NULL,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('inscrit','annule','present') NOT NULL DEFAULT 'inscrit',
    FOREIGN KEY (id_event) REFERENCES events(id_event) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    UNIQUE KEY unique_event_user (id_event, id_user)
) ENGINE=InnoDB
");

// NEWS
$c->exec("
CREATE TABLE IF NOT EXISTS news (
    id_news INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(200) NOT NULL,
    contenu TEXT NOT NULL,
    image VARCHAR(255),
    type_news ENUM('projet','publication','evenement','collaboration','autre') NOT NULL,
    auteur INT NULL,
    statut ENUM('brouillon','publie','archive') NOT NULL DEFAULT 'brouillon',
    date_publication DATETIME,
    afficher_diaporama BOOLEAN DEFAULT FALSE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (auteur) REFERENCES users(id_user) ON DELETE SET NULL
) ENGINE=InnoDB
");

// PARTNERS
$c->exec("
CREATE TABLE IF NOT EXISTS partners (
    id_partner INT PRIMARY KEY AUTO_INCREMENT,
    nom_partenaire VARCHAR(150) NOT NULL,
    type_partenaire ENUM('universite','entreprise','organisme','laboratoire','institution','autre') NOT NULL,
    logo VARCHAR(255),
    site_web VARCHAR(255),
    description TEXT,
    pays VARCHAR(100),
    date_partenariat DATE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
");

// OPPORTUNITIES
$c->exec("
CREATE TABLE IF NOT EXISTS opportunities (
    id_opportunity INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    type_opportunity ENUM('stage','these','bourse','emploi','postdoc','collaboration','autre') NOT NULL,
    domaine VARCHAR(100),
    date_limite DATE,
    contact_email VARCHAR(100),
    id_responsable INT NULL,
    statut ENUM('ouverte','fermee','pourvue') NOT NULL DEFAULT 'ouverte',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_debut DATE NULL,
    FOREIGN KEY (id_responsable) REFERENCES users(id_user) ON DELETE SET NULL
) ENGINE=InnoDB
");

// CONTACTS
$c->exec("
CREATE TABLE IF NOT EXISTS contacts (
    id_contact INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NULL,
    telephone VARCHAR(20) NULL,
    email VARCHAR(100) NOT NULL,
    sujet VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    statut ENUM('non_lu','lu','traite','archive') NOT NULL DEFAULT 'non_lu',
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
");

// SITE SETTINGS
$c->exec("
CREATE TABLE IF NOT EXISTS site_settings (
    id_setting INT PRIMARY KEY AUTO_INCREMENT,
    cle VARCHAR(100) NOT NULL UNIQUE,
    valeur TEXT,
    description VARCHAR(255),
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
");

// ROLES
$c->exec("
CREATE TABLE IF NOT EXISTS roles (
    id_role INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255)
) ENGINE=InnoDB
");

// USER ROLES (multi-rôles)
$c->exec("
CREATE TABLE IF NOT EXISTS user_roles (
    id_user INT NOT NULL,
    id_role INT NOT NULL,
    PRIMARY KEY (id_user, id_role),
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_role) REFERENCES roles(id_role) ON DELETE CASCADE
) ENGINE=InnoDB
");

// PERMISSIONS
$c->exec("
CREATE TABLE IF NOT EXISTS permissions (
    id_permission INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255)
) ENGINE=InnoDB
");

// ROLE PERMISSIONS
$c->exec("
CREATE TABLE IF NOT EXISTS role_permissions (
    id_role INT NOT NULL,
    id_permission INT NOT NULL,
    PRIMARY KEY (id_role, id_permission),
    FOREIGN KEY (id_role) REFERENCES roles(id_role) ON DELETE CASCADE,
    FOREIGN KEY (id_permission) REFERENCES permissions(id_permission) ON DELETE CASCADE
) ENGINE=InnoDB
");

// REPORTS (rapports administratifs)
$c->exec("
CREATE TABLE IF NOT EXISTS reports (
    id_report INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('PROJETS', 'BIBLIOGRAPHIQUE', 'EQUIPEMENTS', 'SAUVEGARDE', 'UTILISATEURS') NOT NULL,
    titre VARCHAR(255) NOT NULL,
    contenu TEXT COMMENT 'JSON des paramètres du rapport',
    format ENUM('PDF', 'SQL', 'EXCEL') NOT NULL,
    fichier VARCHAR(255) COMMENT 'Nom du fichier généré',
    genere_par INT COMMENT 'ID de l\'utilisateur qui a généré',
    date_generation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Index pour performance
    INDEX idx_type (type),
    INDEX idx_format (format),
    INDEX idx_date (date_generation),
    
    -- Clé étrangère
    FOREIGN KEY (genere_par) REFERENCES users(id_user) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Historique des rapports générés';
");

// NOTIFICATIONS
$c->exec("
CREATE TABLE IF NOT EXISTS notifications (
    id_notification INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT NOT NULL,
    type VARCHAR(50),
    message VARCHAR(255) NOT NULL,
    lien VARCHAR(255),
    lu BOOLEAN DEFAULT FALSE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB
");

// PROJECT PARTNERS

$c->exec("CREATE TABLE project_partners (
id_project_partner INT PRIMARY KEY AUTO_INCREMENT,
id_project INT NOT NULL,
id_partner INT NOT NULL,
type_collaboration VARCHAR(100),
date_debut DATE,
date_fin DATE,
description TEXT,
date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (id_project) REFERENCES projects(id_project) ON DELETE CASCADE,
FOREIGN KEY (id_partner) REFERENCES partners(id_partner) ON DELETE CASCADE,
UNIQUE KEY unique_project_partner (id_project, id_partner)
) ENGINE=InnoDB");

// DOCUMENTS
$c->exec("
CREATE TABLE IF NOT EXISTS documents (
    id_document INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT NOT NULL,
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    type_document ENUM(
        'cv',
        'diplome',
        'certificat',
        'rapport',
        'article',
        'presentation',
        'these',
        'memoire',
        'attestation',
        'autre'
    ) NOT NULL,
    nom_fichier VARCHAR(255) NOT NULL COMMENT 'Nom physique du fichier',
    nom_original VARCHAR(255) NOT NULL COMMENT 'Nom original du fichier uploadé',
    taille_fichier INT NOT NULL COMMENT 'Taille en octets',
    extension VARCHAR(10) NOT NULL,
    visibilite ENUM('prive','public','equipe') NOT NULL DEFAULT 'prive',
    statut ENUM('actif','archive','supprime') NOT NULL DEFAULT 'actif',
    date_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user (id_user),
    INDEX idx_type (type_document),
    INDEX idx_visibilite (visibilite),
    INDEX idx_statut (statut),
    INDEX idx_date (date_upload),
    
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Documents personnels des utilisateurs'
");

// INSERT TEST DATA
self::insertTestData($c);

return true;
}

private static function insertTestData($c) {

/* ================= USERS ================= */

// admin
$adminPass = password_hash("admin", PASSWORD_DEFAULT);
$c->prepare("
INSERT INTO users(login,password,nom,prenom,email,poste,photo)
VALUES ('admin', ?, 'Admin','System','admin@labo.dz','Administrateur','admin.png')
")->execute([$adminPass]);

$adminId = $c->lastInsertId();

// user / user
$userPass = password_hash("user", PASSWORD_DEFAULT);
$c->prepare("
INSERT INTO users(login,password,nom,prenom,email,grade,photo)
VALUES ('user', ?, 'User','Test','user@labo.dz','Professeur','user.png')
")->execute([$userPass]);

$userId = $c->lastInsertId();

// autres utilisateurs
$users = [
["resp1","pass","Ali","Benaissa","ali@labo.dz","Responsable IA","man1.jpg"],
["resp2","pass","Sara","Dahmani","sara@labo.dz","Responsable Sécurité","woman1.jpg"],
["cher1","pass","Kamel","Meziane","kamel@labo.dz","Chercheur","man2.jpg"],
["doc1","pass","Leila","Boudiaf","leila@labo.dz","Doctorante","woman2.jpg"],
["etu1","pass","Yacine","Karim","yacine@labo.dz","Master","man3.jpg"]
];

$stmt = $c->prepare("
INSERT INTO users(login,password,nom,prenom,email,poste,photo)
VALUES (?,?,?,?,?,?,?)
");

$userIds = [$adminId, $userId];

foreach ($users as $u) {
$stmt->execute([
    $u[0],
    password_hash($u[1], PASSWORD_DEFAULT),
    $u[2],
    $u[3],
    $u[4],
    $u[5],
    $u[6]
]);
$userIds[] = $c->lastInsertId();
}

/* ================= TEAMS ================= */
$teams = [
["Intelligence Artificielle","Equipe IA","IA",2],
["Sécurité Informatique","Equipe Sécurité","Sécurité",3],
["Cloud Computing","Equipe Cloud","Cloud",2]
];

$stmt = $c->prepare("
INSERT INTO teams(nom_equipe,description,thematique,id_chef)
VALUES (?,?,?,?)
");
foreach($teams as $t) $stmt->execute($t);

/* ================= TEAM MEMBERS ================= */
$c->exec("
INSERT INTO team_members(id_team,id_user) VALUES
(1,2),(1,4),
(2,3),(2,5),
(3,2),(3,6)
");

/* ================= PROJECTS ================= */
$projects = [
// [titre, description, thematique, financement, statut, responsable, encadrant, date_debut]
["Deep Learning","Analyse prédictive","IA","DGRSDT","en_cours",2,4,"2024-01-10"],
["Sécurité IoT","Sécurisation IoT","Sécurité","International","en_cours",3,5,"2024-02-15"],
["Cloud Hybride","Cloud hybride","Cloud","Université","soumis",2,4,"2024-03-20"]
];

$stmt = $c->prepare("
INSERT INTO projects(titre,description,thematique,type_financement,statut,id_responsable,id_encadrant,date_debut)
VALUES (?,?,?,?,?,?,?,?)
");
foreach($projects as $p) $stmt->execute($p);

/* ================= PROJECT MEMBERS ================= */
$c->exec("
INSERT INTO project_members(id_project,id_user,role_projet) VALUES
(1,2,'Responsable'),(1,4,'Chercheur'),
(2,3,'Responsable'),(2,5,'Doctorant'),(2,2,'Chercheur'),
(3,2,'Responsable'),(3,6,'Etudiant')
");

/* ================= PUBLICATIONS ================= */
$publications = [
["DL Article - User","Résumé DL","article",2024,1],
["DL Article - Sara","Résumé DL","article",2024,1],
["IoT Article - Ali","Résumé IoT","article",2024,2],
["IoT Article - Kamel","Résumé IoT","article",2024,2],
["Cloud Article - User","Résumé Cloud","article",2024,3],
["Cloud Article - Leila","Résumé Cloud","article",2024,3]
];

$stmt = $c->prepare("
INSERT INTO publications(titre,resume,type_publication,annee_publication,id_project,statut)
VALUES (?,?,?,?,?, 'valide')
");
foreach($publications as $p) $stmt->execute($p);

/* ================= PUBLICATION AUTHORS ================= */
$c->exec("
INSERT INTO publication_authors(id_publication,id_user,ordre_auteur) VALUES
(1,2,1),(2,4,1),
(3,3,1),(4,5,1),
(5,2,1),(6,6,1)
");

/* ================= EQUIPMENTS ================= */
$c->exec("
INSERT INTO equipments(nom_equipment,type_equipment,description,etat,localisation)
VALUES
('Salle TP','salle','Salle TP','libre','Bat A'),
('Serveur GPU','serveur','Serveur IA','libre','Salle serveur'),
('PC Recherche','pc','PC puissant','libre','Labo IA')
");

/* ================= RESERVATIONS ================= */
$c->exec("
INSERT INTO reservations(id_equipment,id_user,date_debut,date_fin,motif,statut)
VALUES
(1,2,'2025-01-10 09:00','2025-01-10 12:00','Cours','confirmee'),
(2,4,'2025-01-12 10:00','2025-01-12 18:00','Calcul IA','confirmee')
");

/* ================= EVENTS ================= */
$c->exec("
INSERT INTO events(titre,description,type_event,date_debut,lieu,organisateur,statut)
VALUES
('Séminaire IA','Séminaire annuel','seminaire','2026-02-01 09:00','Amphi',2,'a_venir'),
('Atelier Sécurité','Atelier pratique','atelier','2026-02-10 14:00','Salle TP',3,'a_venir')
");

/* ================= EVENT REGISTRATIONS ================= */
$c->exec("
INSERT INTO event_registrations(id_event,id_user,statut)
VALUES
(1,4,'inscrit'),(1,5,'inscrit'),
(2,6,'inscrit')
");

/* ================= OPPORTUNITIES ================= */
$c->exec("
INSERT INTO opportunities(titre,description,type_opportunity,domaine,date_limite,contact_email,id_responsable)
VALUES
('Stage IA','Stage IA','stage','IA','2025-06-01','ali@labo.dz',2),
('Thèse Sécurité','Thèse cybersécurité','these','Sécurité','2025-07-01','sara@labo.dz',3)
");

/* ================= CONTACTS ================= */
$c->exec("
INSERT INTO contacts(nom,email,sujet,message,statut)
VALUES
('Client','client@mail.com','Info','Demande info','non_lu'),
('Entreprise','hr@corp.com','Partenariat','Collaboration','lu')
");
/* ================= NEWS ================= */
$news = [
["Nouveau projet IA", "Lancement d'un projet Deep Learning", "img1.jpg", "projet", 1, true],
["Publication majeure", "Article dans IEEE Transactions", "img2.png", "publication", 1, true],
["Nouveau partenariat", "Collaboration avec une entreprise tech", "img3.jpg", "collaboration", 1, true]
];
$stmt = $c->prepare("INSERT INTO news(titre, contenu, image, type_news, auteur, statut, date_publication, afficher_diaporama) VALUES (?,?,?,?,?, 'publie', NOW(), ?)");
foreach($news as $n) $stmt->execute($n);

/* ================= PARTNERS ================= */
$partners = [
["MIT CSAIL", "universite", "https://csail.mit.edu", "mit.png", "Laboratoire MIT", "USA"],
["Google Research", "entreprise", "https://research.google", "google.jpg", "Google Research", "USA"],
["INRIA", "organisme", "https://inria.fr", "inria.png", "INRIA France", "France"]
];
$stmt = $c->prepare("INSERT INTO partners(nom_partenaire,type_partenaire, site_web, logo, description,pays) VALUES (?,?,?,?,?,?)");
foreach($partners as $p) $stmt->execute($p);

/* ================= SITE SETTINGS ================= */
$settings = [
["nom_laboratoire", "ESI LAB", "Nom du labo"],
["logo_laboratoire", "logo_lab.png", "Logo du labo dans assets/images"],
["logo_universite", "logo_univ.png", "Logo université"],
["description_laboratoire", "Laboratoire spécialisé en IA, Sécurité, Cloud et Systèmes distribués", "Texte de présentation"],
["image_presentation", "banner.png", "Visuel de la bannière"],
["email_contact", "contact@labo.dz", "Email de contact"],
["telephone", "+213 00 00 00 00", "Téléphone"],
["adresse_postale", "Campus X, Ville", "Adresse postale"],
["site_universite", "https://www.esi.dz", "Site de l'université"],
["reseaux_facebook", "https://facebook.com/tonlabo", "Lien Facebook"],
["reseaux_linkedin", "https://linkedin.com/company/tonlabo", "Lien LinkedIn"],
["reseaux_twitter", "https://x.com/tonlabo", "Lien Twitter"],
["reseaux_youtube", "https://youtube.com/@tonlabo", "Lien YouTube"],
["theme_couleur_primaire", "#1e4fb3", "Couleur principale"],
["theme_couleur_secondaire", "#f0b429", "Couleur secondaire"],
["theme_couleur_nav", "#0f2d62", "Couleur barre de navigation"],
["theme_couleur_footer", "#0f1e35", "Couleur du footer"],
["theme_couleur_texte", "#1f2d3d", "Couleur texte"],
["theme_couleur_texte_muted", "#5c6b80", "Texte atténué"],
["theme_couleur_fond", "#f7f9fc", "Fond général"],
["theme_couleur_carte", "#ffffff", "Fond des cartes"],
["theme_couleur_bordure", "#e5e9f2", "Bordures"]
];
$stmt = $c->prepare("INSERT INTO site_settings(cle, valeur, description) VALUES (?,?,?)");
foreach($settings as $s) $stmt->execute($s);

/* ================= ROLES ================= */
$c->exec("
INSERT INTO roles (nom, description) VALUES
('admin','Administrateur système - Gestion complète du laboratoire'),
('enseignant','Enseignant-chercheur - Peut gérer projets et publications'),
('chercheur','Chercheur permanent - Participe aux projets et publications'),
('doctorant','Doctorant - Travaux de recherche et publications'),
('etudiant','Étudiant Master/Licence - Accès limité aux ressources'),
('invite','Invité externe - Consultation uniquement')
");

/* ================= USER ROLES ================= */

$c->exec("INSERT INTO user_roles VALUES ($adminId, 1)");
$c->exec("INSERT INTO user_roles VALUES ($userId, 2)");
$c->exec("
INSERT INTO user_roles VALUES
({$userIds[2]},2),
({$userIds[2]},3)
");
$c->exec("INSERT INTO user_roles VALUES ({$userIds[3]},2)");
$c->exec("INSERT INTO user_roles VALUES ({$userIds[4]},3)");
$c->exec("
INSERT INTO user_roles VALUES
({$userIds[5]},4),
({$userIds[5]},3)
");
$c->exec("INSERT INTO user_roles VALUES ({$userIds[6]},5)");

/* ================= PERMISSIONS ================= */
$c->exec("
INSERT INTO permissions (code, description) VALUES
('view_users','Consulter les utilisateurs'),
('create_user','Créer un utilisateur'),
('edit_user','Modifier un utilisateur'),
('delete_user','Supprimer un utilisateur'),
('suspend_user','Suspendre un utilisateur'),
('manage_roles','Gérer les rôles et permissions'),
('view_teams','Consulter les équipes'),
('create_team','Créer une équipe'),
('edit_team','Modifier une équipe'),
('delete_team','Supprimer une équipe'),
('manage_team_members','Gérer les membres d équipe'),
('view_projects','Consulter les projets'),
('create_project','Créer un projet'),
('edit_project','Modifier un projet'),
('delete_project','Supprimer/clôturer un projet'),
('manage_project_members','Associer membres et partenaires'),
('generate_project_reports','Générer rapports PDF projets'),
('view_equipment','Consulter les équipements'),
('create_equipment','Ajouter un équipement'),
('edit_equipment','Modifier un équipement'),
('delete_equipment','Supprimer un équipement'),
('reserve_equipment','Réserver un équipement'),
('manage_reservations','Gérer les réservations'),
('generate_equipment_reports','Générer rapports utilisation'),
('view_publications','Consulter les publications'),
('submit_publication','Soumettre une publication'),
('edit_publication','Modifier une publication'),
('validate_publication','Valider une publication'),
('delete_publication','Supprimer une publication'),
('generate_biblio_reports','Générer rapports bibliographiques'),
('view_events','Consulter les événements'),
('create_event','Créer un événement'),
('edit_event','Modifier un événement'),
('delete_event','Supprimer un événement'),
('manage_registrations','Gérer les inscriptions'),
('edit_settings','Modifier paramètres site'),
('backup_database','Sauvegarde/restauration BDD'),
('view_reports','Consulter tous les rapports'),
('manage_partners','Gérer les partenaires'),
('manage_opportunities','Gérer offres et opportunités')
");

/* ================= ROLE PERMISSIONS ================= */
$c->exec("
INSERT INTO role_permissions (id_role, id_permission) VALUES
(1,1),(1,2),(1,3),(1,4),(1,5),(1,6),
(1,7),(1,8),(1,9),(1,10),(1,11),
(1,12),(1,13),(1,14),(1,15),(1,16),(1,17),
(1,18),(1,19),(1,20),(1,21),(1,22),(1,23),
(1,24),(1,25),(1,26),(1,27),(1,28),(1,29),
(1,30),(1,31),(1,32),(1,33),(1,34),
(1,35),(1,36),(1,37),(1,38),(1,39),(1,40),
(2,1),(2,7),(2,12),(2,13),(2,14),(2,16),(2,24),(2,25),(2,26),(2,22),(2,30),(2,32),
(3,7),(3,12),(3,24),(3,25),(3,26),(3,18),(3,22),(3,30),
(4,12),(4,24),(4,25),(4,26),(4,18),(4,22),(4,30),
(5,12),(5,18),(5,22),(5,30),
(6,12),(6,24),(6,30)
");

/* ================= NOTIFICATIONS ================= */
$c->exec("
INSERT INTO notifications (id_user, type, message, lien) VALUES
(2, 'publication', 'Votre publication a été validée', 'index.php?url=publications'),
(4, 'reservation', 'Votre réservation a été confirmée', 'index.php?url=reservations'),
(6, 'event', 'Nouvel événement à venir', 'index.php?url=events')
");

/* ================= PROJECT PARTNERS ================= */
$c->exec("
INSERT INTO project_partners (id_project, id_partner, type_collaboration, description) VALUES
(1, 1, 'Académique', 'Collaboration recherche avec MIT CSAIL'),
(1, 2, 'Financement', 'Support financier Google Research'),
(2, 3, 'Académique', 'Partenariat INRIA cybersécurité');
");
}
}
?>
