<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
checkAuth('directeur');

// Récupération des statistiques avancées
$stats = getDashboardStats($pdo);

// Statistiques par mois (derniers 6 mois)
$monthly_stats = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM objects WHERE DATE_FORMAT(date_found, '%Y-%m') = ?");
    $stmt->execute([$month]);
    $monthly_stats[] = [
        'month' => date('M Y', strtotime("-$i months")),
        'count' => $stmt->fetch()['count']
    ];
}

// Activité récente
$stmt = $pdo->query("SELECT o.*, u.username FROM objects o LEFT JOIN users u ON o.created_by = u.id ORDER BY o.created_at DESC LIMIT 5");
$recent_activity = $stmt->fetchAll();

// Signalements récents
$stmt = $pdo->query("SELECT COUNT(*) as count FROM lost_reports WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$weekly_reports = $stmt->fetch()['count'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Directeur - Objets Trouvés</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/director.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <h1>👔 Espace Directeur</h1>
        </div>
        <div class="nav-menu">
            <a href="dashboard.php" class="nav-link active">Tableau de Bord</a>
            <a href="reports.php" class="nav-link">Rapports</a>
            <a href="export.php" class="nav-link">Exports</a>
            <div class="nav-user">
                <span>Bonjour, <?= $_SESSION['username'] ?></span>
                <a href="../logout.php" class="btn btn-outline btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Tableau de Bord Directeur</h2>
            <p>Vue d'ensemble des activités et statistiques du service des objets trouvés</p>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-main">
                <!-- Statistiques principales -->
                <div class="advanced-stats">
                    <h3>📊 Statistiques Générales</h3>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number"><?= $stats['total_objects'] ?></div>
                            <div class="stat-label">Total Objets</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?= $stats['pending_objects'] ?></div>
                            <div class="stat-label">En Attente</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?= $stats['returned_objects'] ?></div>
                            <div class="stat-label">Restitués</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?= $stats['weekly_objects'] ?></div>
                            <div class="stat-label">Cette Semaine</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?= $weekly_reports ?></div>
                            <div class="stat-label">Signalements/Semaine</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?= round(($stats['returned_objects'] / max($stats['total_objects'], 1)) * 100) ?>%</div>
                            <div class="stat-label">Taux Restitution</div>
                        </div>
                    </div>
                </div>

                <!-- Graphique par type d'objet -->
                <div class="chart-container">
                    <h3>📈 Répartition par Type d'Objet</h3>
                    <?php if (!empty($stats['objects_by_type'])): ?>
                        <?php 
                        $max_count = max(array_column($stats['objects_by_type'], 'count'));
                        foreach ($stats['objects_by_type'] as $type_stat): 
                            $percentage = ($type_stat['count'] / $max_count) * 100;
                        ?>
                            <div class="chart-bar">
                                <div class="chart-label"><?= $type_stat['type'] ?></div>
                                <div class="chart-progress">
                                    <div class="chart-fill" style="width: <?= $percentage ?>%"></div>
                                </div>
                                <div class="chart-value"><?= $type_stat['count'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucune donnée disponible</p>
                    <?php endif; ?>
                </div>

                <!-- Évolution mensuelle -->
                <div class="chart-container">
                    <h3>📅 Évolution Mensuelle (6 derniers mois)</h3>
                    <?php if (!empty($monthly_stats)): ?>
                        <?php 
                        $max_monthly = max(array_column($monthly_stats, 'count'));
                        foreach ($monthly_stats as $month_stat): 
                            $percentage = $max_monthly > 0 ? ($month_stat['count'] / $max_monthly) * 100 : 0;
                        ?>
                            <div class="chart-bar">
                                <div class="chart-label"><?= $month_stat['month'] ?></div>
                                <div class="chart-progress">
                                    <div class="chart-fill" style="width: <?= $percentage ?>%"></div>
                                </div>
                                <div class="chart-value"><?= $month_stat['count'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucune donnée disponible</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dashboard-sidebar">
                <!-- Actions d'export -->
                <div class="export-actions">
                    <h3>📄 Exports</h3>
                    <div class="export-buttons">
                        <a href="export.php?type=objects&format=csv" class="export-btn">
                            <div class="export-icon">📊</div>
                            <div class="export-info">
                                <div class="export-title">Export CSV Objets</div>
                                <div class="export-desc">Tous les objets trouvés</div>
                            </div>
                        </a>
                        
                        <a href="export.php?type=reports&format=csv" class="export-btn">
                            <div class="export-icon">📋</div>
                            <div class="export-info">
                                <div class="export-title">Export CSV Signalements</div>
                                <div class="export-desc">Objets perdus signalés</div>
                            </div>
                        </a>
                        
                        <a href="export.php?type=stats&format=csv" class="export-btn">
                            <div class="export-icon">📈</div>
                            <div class="export-info">
                                <div class="export-title">Export Statistiques</div>
                                <div class="export-desc">Données agrégées</div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Activité récente -->
                <div class="recent-activity">
                    <h3>🕒 Activité Récente</h3>
                    <div class="activity-list">
                        <?php if (!empty($recent_activity)): ?>
                            <?php foreach ($recent_activity as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <?php
                                        $icons = [
                                            'trouve' => '🔍',
                                            'restitue' => '✅',
                                            'archive' => '📦'
                                        ];
                                        echo $icons[$activity['status']] ?? '📝';
                                        ?>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-text">
                                            <?= htmlspecialchars(substr($activity['description'], 0, 50)) ?>...
                                        </div>
                                        <div class="activity-time">
                                            <?= formatDate($activity['created_at']) ?> par <?= $activity['username'] ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Aucune activité récente</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/common.js"></script>
    <script>
        // Animation des barres de progression
        document.addEventListener('DOMContentLoaded', function() {
            const chartFills = document.querySelectorAll('.chart-fill');
            chartFills.forEach((fill, index) => {
                setTimeout(() => {
                    const width = fill.style.width;
                    fill.style.width = '0%';
                    setTimeout(() => {
                        fill.style.width = width;
                    }, 100);
                }, index * 200);
            });
        });
    </script>
</body>
</html>