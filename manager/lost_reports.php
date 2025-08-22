<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
checkAuth('responsable');

// Récupération des signalements d'objets perdus
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$sql = "SELECT lr.*, u.username as passenger_name FROM lost_reports lr 
        LEFT JOIN users u ON lr.passenger_id = u.id WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND lr.description LIKE ?";
    $params[] = "%$search%";
}

if ($status_filter) {
    $sql .= " AND lr.status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY lr.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();

// Traitement des actions (marquer comme trouvé, fermer)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $report_id = $_POST['report_id'];
    $action = $_POST['action'];
    
    if ($action === 'mark_found') {
        $stmt = $pdo->prepare("UPDATE lost_reports SET status = 'trouve' WHERE id = ?");
        $stmt->execute([$report_id]);
        $success = 'Signalement marqué comme trouvé';
    } elseif ($action === 'close') {
        $stmt = $pdo->prepare("UPDATE lost_reports SET status = 'ferme' WHERE id = ?");
        $stmt->execute([$report_id]);
        $success = 'Signalement fermé';
    }
    
    // Recharger la page pour afficher les changements
    header('Location: lost_reports.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signalements d'Objets Perdus - Espace Responsable</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/manager.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <h1>👨‍💼 Espace Responsable</h1>
        </div>
        <div class="nav-menu">
            <a href="dashboard.php" class="nav-link">Gestion des Objets</a>
            <a href="add_object.php" class="nav-link">Ajouter un Objet</a>
            <a href="lost_reports.php" class="nav-link active">Signalements Perdus</a>
            <div class="nav-user">
                <span>Bonjour, <?= $_SESSION['username'] ?></span>
                <a href="../logout.php" class="btn btn-outline btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Signalements d'Objets Perdus</h2>
            <p>Gérez les signalements d'objets perdus effectués par les passagers</p>
        </div>

        <!-- Statistiques rapides -->
        <div class="stats-grid">
            <?php
            $total_reports = count($reports);
            $pending_reports = count(array_filter($reports, fn($r) => $r['status'] === 'signale'));
            $found_reports = count(array_filter($reports, fn($r) => $r['status'] === 'trouve'));
            $closed_reports = count(array_filter($reports, fn($r) => $r['status'] === 'ferme'));
            ?>
            <div class="stat-card">
                <div class="stat-number"><?= $total_reports ?></div>
                <div class="stat-label">Total Signalements</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $pending_reports ?></div>
                <div class="stat-label">En Attente</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $found_reports ?></div>
                <div class="stat-label">Trouvés</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $closed_reports ?></div>
                <div class="stat-label">Fermés</div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="search-filters">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="Rechercher dans les descriptions..." 
                           value="<?= htmlspecialchars($search) ?>" class="form-control">
                </div>
                
                <div class="filter-group">
                    <select name="status" class="form-control">
                        <option value="">Tous les statuts</option>
                        <option value="signale" <?= $status_filter === 'signale' ? 'selected' : '' ?>>Signalé</option>
                        <option value="trouve" <?= $status_filter === 'trouve' ? 'selected' : '' ?>>Trouvé</option>
                        <option value="ferme" <?= $status_filter === 'ferme' ? 'selected' : '' ?>>Fermé</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Filtrer</button>
                <a href="lost_reports.php" class="btn btn-outline">Réinitialiser</a>
            </form>
        </div>

        <!-- Table des signalements -->
        <div class="reports-table">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Lieu de Perte</th>
                        <th>Date de Perte</th>
                        <th>Passager</th>
                        <th>Contact</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reports)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Aucun signalement trouvé</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><?= htmlspecialchars($report['description']) ?></td>
                                <td><?= $report['type'] ?></td>
                                <td><?= $report['lieu'] ?></td>
                                <td><?= formatDate($report['date_lost']) ?></td>
                                <td><?= $report['passenger_name'] ?? 'Inconnu' ?></td>
                                <td><?= htmlspecialchars($report['contact_info']) ?></td>
                                <td>
                                    <?php
                                    $status_badges = [
                                        'signale' => '<span class="badge badge-pending">Signalé</span>',
                                        'trouve' => '<span class="badge badge-found">Trouvé</span>',
                                        'ferme' => '<span class="badge badge-closed">Fermé</span>'
                                    ];
                                    echo $status_badges[$report['status']];
                                    ?>
                                </td>
                                <td class="actions">
                                    <?php if ($report['status'] === 'signale'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                                            <input type="hidden" name="action" value="mark_found">
                                            <button type="submit" class="btn btn-sm btn-success"
                                                    onclick="return confirm('Marquer ce signalement comme trouvé ?')">
                                                Trouvé
                                            </button>
                                        </form>
                                        
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                                            <input type="hidden" name="action" value="close">
                                            <button type="submit" class="btn btn-sm btn-outline"
                                                    onclick="return confirm('Fermer ce signalement ?')">
                                                Fermer
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">Traité</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="../assets/js/common.js"></script>
</body>
</html>