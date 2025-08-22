<?php
require_once 'includes/config.php';

$error = '';
$role_filter = $_GET['role'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password']; // Pas de hachage selon les spécifications
    
    // Requête préparée pour éviter l'injection SQL
    $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && $user['password'] === $password) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        
        // Redirection selon le rôle
        switch ($user['role']) {
            case 'passager':
                header('Location: passenger/dashboard.php');
                break;
            case 'responsable':
                header('Location: manager/dashboard.php');
                break;
            case 'directeur':
                header('Location: director/dashboard.php');
                break;
        }
        exit();
    } else {
        $error = 'Identifiants incorrects';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Objets Trouvés</title>
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <div class="logo">
                <h1>✈️ Connexion</h1>
                <p>Système de Gestion des Objets Trouvés</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required 
                           value="<?= $role_filter === 'staff' ? 'responsable' : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required
                           value="<?= $role_filter === 'staff' ? 'responsable' : '' ?>">
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">Se connecter</button>
                <?php if ($role_filter === 'passager' || $role_filter === ''): ?>
    <div class="forgot-password">
        <a href="forgot_password.php?role=passager">Mot de passe oublié ?</a>
    </div>
<?php endif; ?>
            </form>
            
            <div class="login-options">
                <p>Pas encore de compte passager ?</p>
                <a href="register.php" class="btn btn-outline">S'inscrire</a>
            </div>

            
            <div class="back-link">
                <a href="index.php">← Retour à l'accueil</a>
            </div>
        </div>
    </div>
</body>
</html>