<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
checkAuth('directeur');

// Récupération des données pour les rapports
$date_start = $_GET['date_start'] ?? date('Y-m-01'); // Premier jour du mois
$date_end = $_GET['date_end'] ?? date('Y-m-d'); // Aujourd'hui

// Statistiques pour la période sélectionnée
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM objects WHERE date_found BETWEEN ? AND ?");
$stmt->execute([$date_start, $date_end]);
$period_objects = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM objects WHERE date_found BETWEEN ? AND ? AND status = 'restitue'");
$stmt->execute([$date_start, $date_end]);
$period_returned = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM lost_reports WHERE created_at BETWEEN ? AND ?");
$stmt->execute([$date_start, $date_end]);
$period_reports = $stmt->fetch()['total'];

// Répartition par lieu pour la période
$stmt = $pdo->prepare("SELECT lieu, COUNT(*) as count FROM objects WHERE date_found BETWEEN ? AND ? GROUP BY lieu ORDER BY count DESC");
$stmt->execute([$date_start, $date_end]);
$location_stats = $stmt->fetchAll();

// Répartition par type pour la période
$stmt = $pdo->prepare("SELECT type, COUNT(*) as count FROM objects WHERE date_found BETWEEN ? AND ? GROUP BY type ORDER BY count DESC");
$stmt->execute([$date_start, $date_end]);
$type_stats = $stmt->fetchAll();

// Évolution quotidienne pour la période
$stmt = $pdo->prepare("SELECT DATE(date_found) as date, COUNT(*) as count FROM objects WHERE date_found BETWEEN ? AND ? GROUP BY DATE(date_found) ORDER BY date");
$stmt->execute([$date_start, $date_end]);
$daily_stats = $stmt->fetchAll();

// Performance du personnel
$stmt = $pdo->prepare("SELECT u.username, COUNT(o.id) as objects_added FROM users u LEFT JOIN objects o ON u.id = o.created_by AND o.date_found BETWEEN ? AND ? WHERE u.role IN ('responsable', 'directeur') GROUP BY u.id ORDER BY objects_added DESC");
$stmt->execute([$date_start, $date_end]);
$staff_performance = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapports - Espace Directeur</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/director.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <h1>👔 Espace Directeur</h1>
        </div>
        <div class="nav-menu">
            <a href="dashboard.php" class="nav-link">Tableau de Bord</a>
            <a href="reports.php" class="nav-link active">Rapports</a>
            <a href="export.php" class="nav-link">Exports</a>
            
            <div class="nav-user">
                <span>Bonjour, <?= $_SESSION['username'] ?></span>
                <a href="../logout.php" class="btn btn-outline btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Rapports d'Activité</h2>
            <p>Analyse détaillée des performances et statistiques du service</p>
        </div>

        <!-- Filtres de période -->
        <div class="advanced-filters">
            <h3>📅 Période d'Analyse</h3>
            <form method="GET" class="filter-form">
                <div class="date-range">
                    <div class="form-group">
                        <label for="date_start">Date de début</label>
                        <input type="date" id="date_start" name="date_start" value="<?= $date_start ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="date_end">Date de fin</label>
                        <input type="date" id="date_end" name="date_end" value="<?= $date_end ?>" class="form-control" max="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Générer le Rapport</button>
                <a href="reports.php" class="btn btn-outline">Réinitialiser</a>
            </form>
        </div>

        <!-- Résumé de la période -->
        <div class="advanced-stats">
            <h3>📊 Résumé de la Période (<?= formatDate($date_start) ?> - <?= formatDate($date_end) ?>)</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= $period_objects ?></div>
                    <div class="stat-label">Objets Trouvés</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $period_returned ?></div>
                    <div class="stat-label">Objets Restitués</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $period_reports ?></div>
                    <div class="stat-label">Signalements Reçus</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $period_objects > 0 ? round(($period_returned / $period_objects) * 100) : 0 ?>%</div>
                    <div class="stat-label">Taux de Restitution</div>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-main">
                <!-- Répartition par lieu -->
                <div class="chart-container">
                    <h3>📍 Répartition par Lieu de Découverte</h3>
                    <?php if (!empty($location_stats)): ?>
                        <?php 
                        $max_location = max(array_column($location_stats, 'count'));
                        foreach ($location_stats as $location): 
                            $percentage = ($location['count'] / $max_location) * 100;
                        ?>
                            <div class="chart-bar">
                                <div class="chart-label"><?= htmlspecialchars($location['lieu']) ?></div>
                                <div class="chart-progress">
                                    <div class="chart-fill" style="width: <?= $percentage ?>%"></div>
                                </div>
                                <div class="chart-value"><?= $location['count'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucune donnée pour cette période</p>
                    <?php endif; ?>
                </div>

                <!-- Répartition par type -->
                <div class="chart-container">
                    <h3>🏷️ Répartition par Type d'Objet</h3>
                    <?php if (!empty($type_stats)): ?>
                        <?php 
                        $max_type = max(array_column($type_stats, 'count'));
                        foreach ($type_stats as $type): 
                            $percentage = ($type['count'] / $max_type) * 100;
                        ?>
                            <div class="chart-bar">
                                <div class="chart-label"><?= $type['type'] ?></div>
                                <div class="chart-progress">
                                    <div class="chart-fill" style="width: <?= $percentage ?>%"></div>
                                </div>
                                <div class="chart-value"><?= $type['count'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucune donnée pour cette période</p>
                    <?php endif; ?>
                </div>

                <!-- Évolution quotidienne -->
                <?php if (!empty($daily_stats)): ?>
                <div class="chart-container">
                    <h3>📈 Évolution Quotidienne</h3>
                    <?php 
                    $max_daily = max(array_column($daily_stats, 'count'));
                    foreach ($daily_stats as $day): 
                        $percentage = ($day['count'] / $max_daily) * 100;
                    ?>
                        <div class="chart-bar">
                            <div class="chart-label"><?= formatDate($day['date']) ?></div>
                            <div class="chart-progress">
                                <div class="chart-fill" style="width: <?= $percentage ?>%"></div>
                            </div>
                            <div class="chart-value"><?= $day['count'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="dashboard-sidebar">
                <!-- Performance du personnel -->
                <div class="chart-container">
                    <h3>👥 Performance du Personnel</h3>
                    <?php if (!empty($staff_performance)): ?>
                        <?php 
                        $max_staff = max(array_column($staff_performance, 'objects_added'));
                        foreach ($staff_performance as $staff): 
                            $percentage = $max_staff > 0 ? ($staff['objects_added'] / $max_staff) * 100 : 0;
                        ?>
                            <div class="chart-bar">
                                <div class="chart-label"><?= $staff['username'] ?></div>
                                <div class="chart-progress">
                                    <div class="chart-fill" style="width: <?= $percentage ?>%"></div>
                                </div>
                                <div class="chart-value"><?= $staff['objects_added'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucune donnée pour cette période</p>
                    <?php endif; ?>
                </div>

                <!-- Actions rapides -->
                <div class="export-actions">
                    <h3>🔧 Actions Rapides</h3>
                    <div class="export-buttons">
                        <a href="export.php?type=objects&format=csv&date_start=<?= $date_start ?>&date_end=<?= $date_end ?>" class="export-btn">
                            <div class="export-icon">📊</div>
                            <div class="export-info">
                                <div class="export-title">Export Période</div>
                                <div class="export-desc">Objets de la période</div>
                            </div>
                        </a>
                        
                        <a href="dashboard.php" class="export-btn">
                            <div class="export-icon">📋</div>
                            <div class="export-info">
                                <div class="export-title">Tableau de Bord</div>
                                <div class="export-desc">Vue d'ensemble</div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Recommandations -->
                <div class="chart-container">
                    <h3>💡 Recommandations</h3>
                    <div class="recommendations">
                        <?php
                        $recommendations = [];
                        
                        if ($period_objects > 0 && ($period_returned / $period_objects) < 0.5) {
                            $recommendations[] = "Le taux de restitution est faible. Considérez améliorer la communication avec les passagers.";
                        }
                        
                        if (!empty($location_stats) && $location_stats[0]['count'] > ($period_objects * 0.4)) {
                            $recommendations[] = "Le lieu '{$location_stats[0]['lieu']}' concentre beaucoup d'objets perdus. Renforcez la surveillance.";
                        }
                        
                        if ($period_reports > $period_objects * 2) {
                            $recommendations[] = "Beaucoup de signalements par rapport aux objets trouvés. Améliorez la recherche proactive.";
                        }
                        
                        if (empty($recommendations)) {
                            $recommendations[] = "Les performances sont satisfaisantes. Continuez le bon travail !";
                        }
                        ?>
                        
                        <?php foreach ($recommendations as $index => $recommendation): ?>
                            <div class="recommendation-item">
                                <div class="recommendation-icon">💡</div>
                                <div class="recommendation-text"><?= $recommendation ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/common.js"></script>
</body>
</html>

<style>
.recommendations {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.recommendation-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 3px solid #c0392b;
}

.recommendation-icon {
    font-size: 1.2rem;
    margin-top: 0.1rem;
}

.recommendation-text {
    color: #555;
    font-size: 0.9rem;
    line-height: 1.4;
}
</style>