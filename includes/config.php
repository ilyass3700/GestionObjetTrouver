<?php
/**
 * Configuration de la base de données
 * Paramètres pour connexion MySQL via XAMPP
 */

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Mot de passe vide par défaut sur XAMPP
define('DB_NAME', 'airport_lost_found');

// Configuration des sessions
session_start();

// Connexion à la base de données avec gestion d'erreurs
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Fonction pour vérifier l'authentification
function checkAuth($required_role = null) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
    
    if ($required_role && $_SESSION['user_role'] !== $required_role) {
        header('Location: unauthorized.php');
        exit();
    }
}

// Fonction pour nettoyer les données d'entrée
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Configuration des uploads
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
?>