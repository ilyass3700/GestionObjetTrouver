<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
checkAuth('passager');

// Récupération des signalements du passager connecté
$stmt = $pdo->prepare("SELECT * FROM lost_reports WHERE passenger_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$reports = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Signalements - Espace Passager</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/passenger.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <h1>✈️ Espace Passager</h1>
        </div>
        <div class="nav-menu">
            <a href="dashboard.php" class="nav-link">Accueil</a>
            <a href="report_lost.php" class="nav-link">Signaler un Objet Perdu</a>
            <a href="my_reports.php" class="nav-link active">Mes Signalements</a>
            <div class="nav-user">
                <span>Bonjour, <?= $_SESSION['username'] ?></span>
                <a href="../logout.php" class="btn btn-outline btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Mes Signalements d'Objets Perdus</h2>
            <p>Suivez l'état de vos signalements d'objets perdus.</p>
        </div>

        <?php if (empty($reports)): ?>
            <div class="no-results">
                <p>Vous n'avez encore fait aucun signalement d'objet perdu.</p>
                <a href="report_lost.php" class="btn btn-primary">Signaler un objet perdu</a>
            </div>
        <?php else: ?>
            <div class="reports-list">
                <?php foreach ($reports as $report): ?>
                    <div class="report-card">
                        <div class="report-header">
                            <h3><?= htmlspecialchars($report['description']) ?></h3>
                            <span class="status-badge status-<?= $report['status'] ?>">
                                <?php
                                $status_labels = [
                                    'signale' => 'Signalé',
                                    'trouve' => 'Trouvé !',
                                    'ferme' => 'Fermé'
                                ];
                                echo $status_labels[$report['status']];
                                ?>
                            </span>
                        </div>
                        
                        <div class="report-details">
                            <div class="detail-row">
                                <span class="label">Type:</span>
                                <span class="value"><?= $report['type'] ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Lieu de perte:</span>
                                <span class="value"><?= $report['lieu'] ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Date de perte:</span>
                                <span class="value"><?= formatDate($report['date_lost']) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Contact:</span>
                                <span class="value"><?= $report['contact_info'] ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Signalé le:</span>
                                <span class="value"><?= formatDate($report['created_at']) ?></span>
                            </div>
                        </div>
                        
                        <?php if ($report['status'] === 'trouve'): ?>
                            <div class="alert alert-success">
                                🎉 Bonne nouvelle ! Votre objet a été retrouvé. Contactez le service des objets trouvés pour le récupérer.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="../assets/js/common.js"></script>
</body>
</html>