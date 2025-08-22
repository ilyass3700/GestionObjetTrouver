-- Base de données pour le système de gestion des objets trouvés
-- À importer dans phpMyAdmin ou via ligne de commande MySQL

CREATE DATABASE IF NOT EXISTS airport_lost_found;
USE airport_lost_found;

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('passager', 'responsable', 'directeur') NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des objets trouvés
CREATE TABLE objects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description TEXT NOT NULL,
    date_found DATE NOT NULL,
    lieu VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    photo_path VARCHAR(255),
    status ENUM('trouve', 'restitue', 'archive') DEFAULT 'trouve',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Table des objets perdus signalés par les passagers
CREATE TABLE lost_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description TEXT NOT NULL,
    date_lost DATE NOT NULL,
    lieu VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    contact_info VARCHAR(255) NOT NULL,
    passenger_id INT,
    status ENUM('signale', 'trouve', 'ferme') DEFAULT 'signale',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (passenger_id) REFERENCES users(id)
);

-- Insertion des utilisateurs par défaut
INSERT INTO users (username, password, role, email) VALUES
('responsable', 'responsable', 'responsable', 'responsable@airport.com'),
('directeur', 'directeur', 'directeur', 'directeur@airport.com');

-- Données d'exemple pour les objets trouvés
INSERT INTO objects (description, date_found, lieu, type, created_by) VALUES
('Téléphone portable Samsung Galaxy', '2024-01-15', 'Terminal 1 - Porte A12', 'Electronique', 1),
('Sac à main en cuir noir', '2024-01-16', 'Zone d\'embarquement B', 'Bagagerie', 1),
('Montre Rolex dorée', '2024-01-17', 'Contrôle de sécurité', 'Bijoux', 1),
('Ordinateur portable Dell', '2024-01-18', 'Salon VIP', 'Electronique', 1),
('Clés de voiture avec porte-clés', '2024-01-19', 'Parking P1', 'Divers', 1);