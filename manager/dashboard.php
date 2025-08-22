<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
checkAuth('responsable');

// Récupération des objets avec filtres
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['type'] ?? '';

$sql = "SELECT o.*, u.username as created_by_name FROM objects o 
        LEFT JOIN users u ON o.created_by = u.id WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND o.description LIKE ?";
    $params[] = "%$search%";
}

if ($status_filter) {
    $sql .= " AND o.status = ?";
    $params[] = $status_filter;
}

if ($type_filter) {
    $sql .= " AND o.type = ?";
    $params[] = $type_filter;
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$objects = $stmt->fetchAll();

// Récupération des types pour les filtres
$types = $pdo->query("SELECT DISTINCT type FROM objects ORDER BY type")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Responsable - Gestion des Objets Trouvés</title>
     <!-- ✅ Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/manager.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <h1>👨‍💼 Espace Responsable</h1>
        </div>
        <div class="nav-menu">
            <a href="dashboard.php" class="nav-link active">Gestion des Objets</a>
            <a href="add_object.php" class="nav-link">Ajouter un Objet</a>
            <a href="lost_reports.php" class="nav-link">Signalements Perdus</a>
            <div class="nav-user">
                <span>Bonjour, <?= $_SESSION['username'] ?></span>
                <a href="../logout.php" class="btn btn-outline btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Gestion des Objets Trouvés</h2>
            <div class="header-actions">
                <a href="add_object.php" class="btn btn-primary">+ Ajouter un Objet</a>
            </div>
        </div>

        <!-- Statistiques rapides -->
        <div class="stats-grid">
            <?php
            $stats = getDashboardStats($pdo);
            ?>
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
        </div>

        <!-- Filtres -->
        <div class="search-filters">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="Rechercher..." 
                           value="<?= htmlspecialchars($search) ?>" class="form-control">
                </div>
                
                <div class="filter-group">
                    <select name="status" class="form-control">
                        <option value="">Tous les statuts</option>
                        <option value="trouve" <?= $status_filter === 'trouve' ? 'selected' : '' ?>>Trouvé</option>
                        <option value="restitue" <?= $status_filter === 'restitue' ? 'selected' : '' ?>>Restitué</option>
                        <option value="archive" <?= $status_filter === 'archive' ? 'selected' : '' ?>>Archivé</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select name="type" class="form-control">
                        <option value="">Tous les types</option>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= $type ?>" <?= $type_filter === $type ? 'selected' : '' ?>>
                                <?= $type ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Filtrer</button>
                <a href="dashboard.php" class="btn btn-outline">Réinitialiser</a>
            </form>
        </div>

        <!-- Table des objets -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Lieu</th>
                        <th>Date Trouvé</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($objects)): ?>
                        <tr>
                            <td colspan="7" class="text-center">Aucun objet trouvé</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($objects as $object): ?>
                            <tr>
                                <td>
                                    <?php if ($object['photo_path']): ?>
                                        <img src="../uploads/<?= $object['photo_path'] ?>" 
                                             alt="Photo" class="table-image">
                                    <?php else: ?>
                                        <span class="no-image">📷</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($object['description']) ?></td>
                                <td><?= $object['type'] ?></td>
                                <td><?= $object['lieu'] ?></td>
                                <td><?= formatDate($object['date_found']) ?></td>
                                <td><?= getStatusBadge($object['status']) ?></td>
                                <td class="actions">
                                <?php if ($object['status'] === 'trouve'): ?>
                                        <a href="mark_returned.php?id=<?= $object['id'] ?>" 
                                           class="btn btn-sm btn-success"
                                           onclick="return confirm('Marquer cet objet comme restitué ?')"
                                           title="Restituer"
                                           >
                                           
                                           <i class="fas fa-undo"></i> 

                                        </a>
                                    <?php endif; ?>

                                    <a href="edit_object.php?id=<?= $object['id'] ?>" 
                                            class="btn btn-sm btn-outline" title="Modifier" >
                                    <i class="fa-solid fa-pencil"></i>
                                    </a>
                                    
                                   
                                    
                                <a href="delete_object.php?id=<?= $object['id'] ?>" 
                                class="btn btn-sm btn-danger"
                                onclick="return confirm('Supprimer cet objet ?')"
                                title="Supprimer">
                                <i class="fas fa-trash-alt"></i>
                                </a>

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