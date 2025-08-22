<?php
require_once 'includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $email = sanitize($_POST['email']);
    
    // Vérification que l'utilisateur n'existe pas déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if ($stmt->fetch()) {
        $error = 'Ce nom d\'utilisateur ou email existe déjà';
    } else {
        // Insertion du nouvel utilisateur (rôle passager par défaut)
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'passager')");
        
        if ($stmt->execute([$username, $password, $email])) {
            $success = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
        } else {
            $error = 'Erreur lors de la création du compte';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Objets Trouvés</title>
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <div class="logo">
                <h1>✈️ Inscription Passager</h1>
                <p>Créez votre compte pour signaler des objets perdus</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">S'inscrire</button>
            </form>
            
            <div class="login-options">
                <p>Déjà un compte ?</p>
                <a href="login.php" class="btn btn-outline">Se connecter</a>
            </div>
            
            <div class="back-link">
                <a href="index.php">← Retour à l'accueil</a>
            </div>
        </div>
    </div>
</body>
</html>