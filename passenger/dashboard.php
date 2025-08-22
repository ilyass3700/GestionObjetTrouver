<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
checkAuth('passager');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Passager</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/passenger.css">
    <link rel="stylesheet" href="../assets/css/form-modern.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        .page-header {
            margin-top: 40px;
            text-align: center;
        }

        .page-header h2 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .page-header p {
            font-size: 16px;
            color: #666;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 30px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <h1>✈️ Espace Passager</h1>
        </div>
        <div class="nav-menu">
            <a href="dashboard.php" class="nav-link active">Accueil</a>
            <a href="report_lost.php" class="nav-link">Signaler un Objet Perdu</a>
            <a href="my_reports.php" class="nav-link">Mes Signalements</a>
            <div class="nav-user">
                <span>Bonjour, <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="../logout.php" class="btn btn-outline btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Bienvenue dans votre espace passager</h2>
            <p>Ici, vous pouvez signaler un objet perdu et consulter vos signalements précédents.</p>
        </div>
    </div>

    <script src="../assets/js/common.js"></script>
</body>
</html>
