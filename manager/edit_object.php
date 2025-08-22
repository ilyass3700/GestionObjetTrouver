<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
checkAuth('responsable');

$object_id = $_GET['id'] ?? 0;
$success = '';
$error = '';

// Récupération de l'objet à modifier
$stmt = $pdo->prepare("SELECT * FROM objects WHERE id = ?");
$stmt->execute([$object_id]);
$object = $stmt->fetch();

if (!$object) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = sanitize($_POST['description']);
    $date_found = $_POST['date_found'];
    $lieu = sanitize($_POST['lieu']);
    $type = sanitize($_POST['type']);
    $status = $_POST['status'];
    
    $photo_path = $object['photo_path']; // Conserver l'ancienne photo par défaut
    
    // Gestion de l'upload de nouvelle photo
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadImage($_FILES['photo']);
        if ($upload_result['success']) {
            // Supprimer l'ancienne photo si elle existe
            if ($object['photo_path'] && file_exists('../uploads/' . $object['photo_path'])) {
                unlink('../uploads/' . $object['photo_path']);
            }
            $photo_path = $upload_result['filename'];
        } else {
            $error = $upload_result['message'];
        }
    }
    
    if (!$error) {
        $stmt = $pdo->prepare("UPDATE objects SET description = ?, date_found = ?, lieu = ?, type = ?, photo_path = ?, status = ? WHERE id = ?");
        
        if ($stmt->execute([$description, $date_found, $lieu, $type, $photo_path, $status, $object_id])) {
            $success = 'Objet modifié avec succès !';
            // Recharger les données de l'objet
            $stmt = $pdo->prepare("SELECT * FROM objects WHERE id = ?");
            $stmt->execute([$object_id]);
            $object = $stmt->fetch();
        } else {
            $error = 'Erreur lors de la modification de l\'objet';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Objet - Espace Responsable</title>
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
            <a href="lost_reports.php" class="nav-link">Signalements Perdus</a>
            <div class="nav-user">
                <span>Bonjour, <?= $_SESSION['username'] ?></span>
                <a href="../logout.php" class="btn btn-outline btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Modifier l'Objet #<?= $object['id'] ?></h2>
            <div class="header-actions">
                <a href="dashboard.php" class="btn btn-outline">← Retour à la liste</a>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data" class="object-form">
                <div class="form-row full-width">
                    <div class="form-group">
                        <label for="description">Description détaillée de l'objet *</label>
                        <textarea id="description" name="description" required 
                                  placeholder="Décrivez l'objet en détail (marque, couleur, taille, état, etc.)"><?= htmlspecialchars($object['description']) ?></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="type">Type d'objet *</label>
                        <select id="type" name="type" required>
                            <option value="">Sélectionnez un type</option>
                            <option value="Electronique" <?= $object['type'] === 'Electronique' ? 'selected' : '' ?>>Électronique</option>
                            <option value="Bagagerie" <?= $object['type'] === 'Bagagerie' ? 'selected' : '' ?>>Bagagerie</option>
                            <option value="Bijoux" <?= $object['type'] === 'Bijoux' ? 'selected' : '' ?>>Bijoux</option>
                            <option value="Vetements" <?= $object['type'] === 'Vetements' ? 'selected' : '' ?>>Vêtements</option>
                            <option value="Documents" <?= $object['type'] === 'Documents' ? 'selected' : '' ?>>Documents</option>
                            <option value="Divers" <?= $object['type'] === 'Divers' ? 'selected' : '' ?>>Divers</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date_found">Date de découverte *</label>
                        <input type="date" id="date_found" name="date_found" required 
                               max="<?= date('Y-m-d') ?>" value="<?= $object['date_found'] ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="lieu">Lieu de découverte *</label>
                        <select id="lieu" name="lieu" required>
                            <option value="">Sélectionnez un lieu</option>
                            <option value="Terminal 1 - Porte A" <?= $object['lieu'] === 'Terminal 1 - Porte A' ? 'selected' : '' ?>>Terminal 1 - Porte A</option>
                            <option value="Terminal 1 - Porte B" <?= $object['lieu'] === 'Terminal 1 - Porte B' ? 'selected' : '' ?>>Terminal 1 - Porte B</option>
                            <option value="Terminal 2 - Porte C" <?= $object['lieu'] === 'Terminal 2 - Porte C' ? 'selected' : '' ?>>Terminal 2 - Porte C</option>
                            <option value="Terminal 2 - Porte D" <?= $object['lieu'] === 'Terminal 2 - Porte D' ? 'selected' : '' ?>>Terminal 2 - Porte D</option>
                            <option value="Zone d'embarquement A" <?= $object['lieu'] === 'Zone d\'embarquement A' ? 'selected' : '' ?>>Zone d'embarquement A</option>
                            <option value="Zone d'embarquement B" <?= $object['lieu'] === 'Zone d\'embarquement B' ? 'selected' : '' ?>>Zone d'embarquement B</option>
                            <option value="Contrôle de sécurité" <?= $object['lieu'] === 'Contrôle de sécurité' ? 'selected' : '' ?>>Contrôle de sécurité</option>
                            <option value="Salon VIP" <?= $object['lieu'] === 'Salon VIP' ? 'selected' : '' ?>>Salon VIP</option>
                            <option value="Parking P1" <?= $object['lieu'] === 'Parking P1' ? 'selected' : '' ?>>Parking P1</option>
                            <option value="Parking P2" <?= $object['lieu'] === 'Parking P2' ? 'selected' : '' ?>>Parking P2</option>
                            <option value="Hall d'arrivée" <?= $object['lieu'] === 'Hall d\'arrivée' ? 'selected' : '' ?>>Hall d'arrivée</option>
                            <option value="Hall de départ" <?= $object['lieu'] === 'Hall de départ' ? 'selected' : '' ?>>Hall de départ</option>
                            <option value="Autre" <?= $object['lieu'] === 'Autre' ? 'selected' : '' ?>>Autre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Statut *</label>
                        <select id="status" name="status" required>
                            <option value="trouve" <?= $object['status'] === 'trouve' ? 'selected' : '' ?>>Trouvé</option>
                            <option value="restitue" <?= $object['status'] === 'restitue' ? 'selected' : '' ?>>Restitué</option>
                            <option value="archive" <?= $object['status'] === 'archive' ? 'selected' : '' ?>>Archivé</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="photo">Photo de l'objet</label>
                        <?php if ($object['photo_path']): ?>
                            <div class="current-photo">
                                <p>Photo actuelle :</p>
                                <img src="../uploads/<?= $object['photo_path'] ?>" alt="Photo actuelle" 
                                     style="max-width: 200px; max-height: 200px; border-radius: 10px; margin-bottom: 1rem;">
                            </div>
                        <?php endif; ?>
                        
                        <div class="photo-upload" onclick="document.getElementById('photo').click()">
                            <input type="file" id="photo" name="photo" accept="image/*" style="display: none;">
                            <div class="upload-icon">📷</div>
                            <div class="upload-text">
                                <?= $object['photo_path'] ? 'Cliquez pour changer la photo' : 'Cliquez pour ajouter une photo' ?>
                            </div>
                            <div class="upload-hint">ou glissez-déposez une image ici</div>
                            <div class="upload-hint">Formats acceptés : JPG, PNG, GIF (max 5MB)</div>
                        </div>
                        <div id="photo-preview" class="photo-preview"></div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Modifier l'Objet</button>
                    <a href="dashboard.php" class="btn btn-outline">Annuler</a>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/common.js"></script>
    <script>
        // Script spécifique pour la prévisualisation d'image
        document.getElementById('photo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validation de la taille
                if (file.size > 5 * 1024 * 1024) {
                    alert('Le fichier est trop volumineux (maximum 5MB)');
                    this.value = '';
                    return;
                }
                
                // Validation du type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Format de fichier non autorisé. Utilisez JPG, PNG ou GIF.');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('photo-preview');
                    preview.innerHTML = `
                        <p style="color: #8e44ad; font-weight: 600; margin-bottom: 0.5rem;">Nouvelle photo :</p>
                        <img src="${e.target.result}" alt="Nouvelle photo" style="max-width: 200px; max-height: 200px; border-radius: 10px; margin-top: 1rem; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                        <p style="margin-top: 0.5rem; color: #27ae60; font-size: 0.9rem;">✓ Nouvelle image sélectionnée : ${file.name}</p>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>