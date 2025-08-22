<?php
require_once 'includes/config.php';

// Redirection selon le rôle de l'utilisateur connecté
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['user_role']) {
        case 'passager':
            header('Location: passenger/dashboard.php');
            break;
        case 'responsable':
            header('Location: manager/dashboard.php');
            break;
        case 'directeur':
            header('Location: director/dashboard.php');
            break;
        default:
            header('Location: login.php');
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système de Gestion des Objets Trouvés - Aéroport</title>
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <style>
        .header-logos {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 60px;
            background-color: #f5f5f5;
        }

        .header-logos img {
            height: 60px;
            max-width: 150px;
            object-fit: contain;
        }

        footer {
            background-color: #f0f0f0;
            text-align: center;
            padding: 15px;
            margin-top: 40px;
            font-size: 14px;
            color: #555;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <!-- Zone des logos -->
    <div class="header-logos">
        <img src="assets/images/emsi.png" alt="Logo EMSI" class="logo-left">
        <img src="assets/images/onda.png" alt="Logo ONDA" class="logo-right">
    </div>

    <div class="container">
        <div class="welcome-section">
            <div class="logo">
                <h1>Aéroport MOHAMMED V de Casablanca</h1>
                <h2>Système de Gestion des Objets Trouvés</h2>
            </div>
            
            <div class="info-cards">
                <div class="info-card">
                    <h3>🧳 Passagers</h3>
                    <p>Signalez un objet perdu ou consultez les objets trouvés</p>
                    <a href="login.php?role=passager" class="btn btn-primary">Accès Passagers</a>
                </div>
                
                <div class="info-card">
                    <h3>👨‍💼 Personnel</h3>
                    <p>Gestion complète des objets trouvés et perdus</p>
                    <a href="login.php?role=staff" class="btn btn-secondary">Accès Personnel</a>
                </div>
            </div>
            
            <div class="quick-stats">
                <?php
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM objects WHERE status = 'trouve'");
                    $pending = $stmt->fetch()['total'];
                    
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM objects WHERE status = 'restitue'");
                    $returned = $stmt->fetch()['total'];
                    
                    echo "<p><strong>$pending</strong> objets en attente de restitution</p>";
                    echo "<p><strong>$returned</strong> objets restitués avec succès</p>";
                } catch (Exception $e) {
                    echo "<p>Statistiques non disponibles</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Pied de page -->
    <footer>
        Travail de : Benzarrouk Ilyas & Laaroussi Anas<br>
        Encadré par : Mr Tarik Hadri
    </footer>
</body>
</html>
