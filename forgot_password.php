<?php
require_once 'includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Vérifier que l'utilisateur existe et est passager
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND role = 'passager'");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = "Nom d'utilisateur introuvable ou non autorisé.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (empty($new_password)) {
        $error = "Le nouveau mot de passe ne peut pas être vide.";
    } else {
        // Mettre à jour le mot de passe (non haché comme dans ton code)
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$new_password, $user['id']]);
        $success = "Mot de passe changé avec succès. Vous pouvez maintenant vous connecter.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Réinitialiser le mot de passe - Objets Trouvés</title>
    <link rel="stylesheet" href="assets/css/common.css" />
    <link rel="stylesheet" href="assets/css/login.css" />
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <div class="logo">
                <h1>✈️ Réinitialiser le mot de passe</h1>
                <p>Pour les passagers uniquement</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php elseif ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <a href="login.php?role=passager" class="btn btn-primary btn-full">Retour à la connexion</a>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" />
                </div>
                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe</label>
                    <input type="password" id="new_password" name="new_password" required />
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" required />
                </div>
                <button type="submit" class="btn btn-primary btn-full">Changer le mot de passe</button>
            </form>
            <?php endif; ?>

            <div class="back-link">
                <a href="login.php?role=passager">← Retour à la connexion</a>
            </div>
        </div>
    </div>
</body>
</html>
