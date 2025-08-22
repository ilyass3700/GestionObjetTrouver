<?php
require_once '../includes/config.php';
checkAuth('responsable');

$object_id = $_GET['id'] ?? 0;

if ($object_id) {
    // Récupérer les informations de l'objet pour supprimer la photo
    $stmt = $pdo->prepare("SELECT photo_path FROM objects WHERE id = ?");
    $stmt->execute([$object_id]);
    $object = $stmt->fetch();
    
    if ($object) {
        // Supprimer l'objet de la base de données
        $stmt = $pdo->prepare("DELETE FROM objects WHERE id = ?");
        if ($stmt->execute([$object_id])) {
            // Supprimer la photo si elle existe
            if ($object['photo_path'] && file_exists('../uploads/' . $object['photo_path'])) {
                unlink('../uploads/' . $object['photo_path']);
            }
            header('Location: dashboard.php?success=Objet supprimé avec succès');
        } else {
            header('Location: dashboard.php?error=Erreur lors de la suppression');
        }
    } else {
        header('Location: dashboard.php?error=Objet non trouvé');
    }
} else {
    header('Location: dashboard.php?error=ID objet manquant');
}
exit();
?>