<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
checkAuth('directeur');

$type = $_GET['type'] ?? '';
$format = $_GET['format'] ?? 'csv';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $type && $format === 'csv') {
    switch ($type) {
        case 'objects':
            $stmt = $pdo->query("SELECT o.id, o.description, o.type, o.lieu, o.date_found, o.status, o.created_at, u.username as created_by 
                                FROM objects o LEFT JOIN users u ON o.created_by = u.id ORDER BY o.created_at DESC");
            $data = $stmt->fetchAll();
            
            // Formatage des données pour l'export
            $export_data = [];
            foreach ($data as $row) {
                $export_data[] = [
                    'ID' => $row['id'],
                    'Description' => $row['description'],
                    'Type' => $row['type'],
                    'Lieu' => $row['lieu'],
                    'Date Trouvé' => $row['date_found'],
                    'Statut' => $row['status'],
                    'Créé le' => $row['created_at'],
                    'Créé par' => $row['created_by']
                ];
            }
            
            exportToCSV($export_data, 'objets_trouves_' . date('Y-m-d') . '.csv');
            break;
            
        case 'reports':
            $stmt = $pdo->query("SELECT lr.id, lr.description, lr.type, lr.lieu, lr.date_lost, lr.contact_info, lr.status, lr.created_at, u.username as passenger 
                                FROM lost_reports lr LEFT JOIN users u ON lr.passenger_id = u.id ORDER BY lr.created_at DESC");
            $data = $stmt->fetchAll();
            
            $export_data = [];
            foreach ($data as $row) {
                $export_data[] = [
                    'ID' => $row['id'],
                    'Description' => $row['description'],
                    'Type' => $row['type'],
                    'Lieu Perte' => $row['lieu'],
                    'Date Perte' => $row['date_lost'],
                    'Contact' => $row['contact_info'],
                    'Statut' => $row['status'],
                    'Signalé le' => $row['created_at'],
                    'Passager' => $row['passenger']
                ];
            }
            
            exportToCSV($export_data, 'signalements_perdus_' . date('Y-m-d') . '.csv');
            break;
            
        case 'stats':
            $stats = getDashboardStats($pdo);
            
            $export_data = [
                ['Statistique', 'Valeur'],
                ['Total Objets', $stats['total_objects']],
                ['Objets en Attente', $stats['pending_objects']],
                ['Objets Restitués', $stats['returned_objects']],
                ['Objets Cette Semaine', $stats['weekly_objects']],
                ['Taux de Restitution (%)', round(($stats['returned_objects'] / max($stats['total_objects'], 1)) * 100, 2)]
            ];
            
            // Ajouter les statistiques par type
            foreach ($stats['objects_by_type'] as $type_stat) {
                $export_data[] = ['Objets ' . $type_stat['type'], $type_stat['count']];
            }
            
            exportToCSV($export_data, 'statistiques_' . date('Y-m-d') . '.csv');
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exports - Espace Directeur</title>
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
            <a href="reports.php" class="nav-link">Rapports</a>
            <a href="export.php" class="nav-link active">Exports</a>
            
            <div class="nav-user">
                <span>Bonjour, <?= $_SESSION['username'] ?></span>
                <a href="../logout.php" class="btn btn-outline btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Exports de Données</h2>
            <p>Exportez les données du système au format CSV pour analyse externe</p>
        </div>

        <div class="export-grid">
            <div class="export-section">
                <h3>📊 Objets Trouvés</h3>
                <p>Exportez la liste complète des objets trouvés avec toutes leurs informations.</p>
                <div class="export-options">
                    <a href="export.php?type=objects&format=csv" class="btn btn-primary">
                        📄 Télécharger CSV
                    </a>
                </div>
                <div class="export-info">
                    <small>Inclut : ID, description, type, lieu, date, statut, créateur</small>
                </div>
            </div>

            <div class="export-section">
                <h3>📋 Signalements d'Objets Perdus</h3>
                <p>Exportez tous les signalements d'objets perdus effectués par les passagers.</p>
                <div class="export-options">
                    <a href="export.php?type=reports&format=csv" class="btn btn-primary">
                        📄 Télécharger CSV
                    </a>
                </div>
                <div class="export-info">
                    <small>Inclut : ID, description, type, lieu, date perte, contact, statut</small>
                </div>
            </div>

            <div class="export-section">
                <h3>📈 Statistiques Générales</h3>
                <p>Exportez un résumé des statistiques principales du système.</p>
                <div class="export-options">
                    <a href="export.php?type=stats&format=csv" class="btn btn-primary">
                        📄 Télécharger CSV
                    </a>
                </div>
                <div class="export-info">
                    <small>Inclut : totaux, répartitions par type, taux de restitution</small>
                </div>
            </div>
        </div>

        <div class="export-filters">
            <h3>🔍 Exports Personnalisés</h3>
            <p>Fonctionnalité à venir : exports avec filtres par date, type, statut, etc.</p>
            
            <form class="filter-form" style="opacity: 0.6; pointer-events: none;">
                <div class="form-row">
                    <div class="form-group">
                        <label>Date de début</label>
                        <input type="date" class="form-control" disabled>
                    </div>
                    <div class="form-group">
                        <label>Date de fin</label>
                        <input type="date" class="form-control" disabled>
                    </div>
                    <div class="form-group">
                        <label>Type d'objet</label>
                        <select class="form-control" disabled>
                            <option>Tous les types</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Statut</label>
                        <select class="form-control" disabled>
                            <option>Tous les statuts</option>
                        </select>
                    </div>
                </div>
                <button type="button" class="btn btn-outline" disabled>Exporter avec Filtres</button>
            </form>
        </div>

        <div class="export-help">
            <h3>ℹ️ Aide sur les Exports</h3>
            <div class="help-grid">
                <div class="help-item">
                    <h4>Format CSV</h4>
                    <p>Les fichiers sont exportés au format CSV (valeurs séparées par des virgules) compatible avec Excel, Google Sheets et autres tableurs.</p>
                </div>
                <div class="help-item">
                    <h4>Encodage</h4>
                    <p>Les fichiers utilisent l'encodage UTF-8 pour supporter tous les caractères spéciaux et accents.</p>
                </div>
                <div class="help-item">
                    <h4>Fréquence</h4>
                    <p>Il est recommandé d'effectuer des exports réguliers pour la sauvegarde et l'analyse des données.</p>
                </div>
                <div class="help-item">
                    <h4>Confidentialité</h4>
                    <p>Les exports contiennent des données sensibles. Assurez-vous de les stocker de manière sécurisée.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/common.js"></script>
</body>
</html>

<style>
.export-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.export-section {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    border-left: 4px solid #c0392b;
}

.export-section h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.export-section p {
    color: #7f8c8d;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.export-options {
    margin-bottom: 1rem;
}

.export-info {
    color: #95a5a6;
    font-style: italic;
}

.export-filters {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.export-filters h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.export-help {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.export-help h3 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
}

.help-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.help-item {
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 10px;
    border-left: 3px solid #c0392b;
}

.help-item h4 {
    color: #c0392b;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

.help-item p {
    color: #555;
    font-size: 0.9rem;
    line-height: 1.5;
    margin: 0;
}
</style>