<?php
/**
 * Fonctions utilitaires pour le système de gestion des objets trouvés
 */

/**
 * Upload d'une image avec validation
 */
function uploadImage($file) {
    global $allowed_extensions;
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Erreur lors de l\'upload'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'Fichier trop volumineux (max 5MB)'];
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_extensions)) {
        return ['success' => false, 'message' => 'Format de fichier non autorisé'];
    }
    
    $filename = uniqid() . '.' . $extension;
    $filepath = UPLOAD_DIR . $filename;
    
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Erreur lors de la sauvegarde'];
}

/**
 * Génération d'export CSV
 */
function exportToCSV($data, $filename) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    $output = fopen('php://output', 'w');
    
    // En-têtes CSV
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]));
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit();
}

/**
 * Obtenir les statistiques pour le tableau de bord
 */
function getDashboardStats($pdo) {
    $stats = [];
    
    // Total des objets trouvés
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM objects");
    $stats['total_objects'] = $stmt->fetch()['total'];
    
    // Objets restitués
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM objects WHERE status = 'restitue'");
    $stats['returned_objects'] = $stmt->fetch()['total'];
    
    // Objets en attente
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM objects WHERE status = 'trouve'");
    $stats['pending_objects'] = $stmt->fetch()['total'];
    
    // Objets par type
    $stmt = $pdo->query("SELECT type, COUNT(*) as count FROM objects GROUP BY type ORDER BY count DESC");
    $stats['objects_by_type'] = $stmt->fetchAll();
    
    // Objets trouvés cette semaine
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM objects WHERE date_found >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
    $stats['weekly_objects'] = $stmt->fetch()['total'];
    
    return $stats;
}

/**
 * Formater la date en français
 */
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

/**
 * Obtenir le badge de statut coloré
 */
function getStatusBadge($status) {
    $badges = [
        'trouve' => '<span class="badge badge-warning">Trouvé</span>',
        'restitue' => '<span class="badge badge-success">Restitué</span>',
        'archive' => '<span class="badge badge-secondary">Archivé</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge badge-light">Inconnu</span>';
}
?>