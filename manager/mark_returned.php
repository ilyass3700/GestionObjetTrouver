<?php
require_once '../includes/config.php';
checkAuth('responsable');

$object_id = $_GET['id'] ?? 0;

if ($object_id) {
    $stmt = $pdo->prepare("UPDATE objects SET status = 'restitue' WHERE id = ?");
    if ($stmt->execute([$object_id])) {
        header('Location: dashboard.php?success=Objet marqué comme restitué');
    } else {
        header('Location: dashboard.php?error=Erreur lors de la mise à jour');
    }
} else {
    header('Location: dashboard.php?error=Objet non trouvé');
}
exit();
?>