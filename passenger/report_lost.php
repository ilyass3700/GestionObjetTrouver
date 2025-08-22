<?php
require_once '../includes/config.php';
checkAuth('passager');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = sanitize($_POST['description']);
    $date_lost = $_POST['date_lost'];
    $lieu = sanitize($_POST['lieu']);
    $type = sanitize($_POST['type']);
    $contact_info = sanitize($_POST['contact_info']);
    
    $stmt = $pdo->prepare("INSERT INTO lost_reports (description, date_lost, lieu, type, contact_info, passenger_id) VALUES (?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$description, $date_lost, $lieu, $type, $contact_info, $_SESSION['user_id']])) {
        $success = 'Votre signalement a été enregistré avec succès. Nous vous contacterons si nous trouvons votre objet.';
    } else {
        $error = 'Erreur lors de l\'enregistrement du signalement';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signaler un Objet Perdu - Espace Passager</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/passenger.css">
    <link rel="stylesheet" href="../assets/css/form-modern.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <h1>✈️ Espace Passager</h1>
        </div>
        <div class="nav-menu">
            <a href="dashboard.php" class="nav-link"> Accueil</a>
            <a href="report_lost.php" class="nav-link active">Signaler un Objet Perdu</a>
            <a href="my_reports.php" class="nav-link">Mes Signalements</a>
            <div class="nav-user">
    <span>Bonjour, <?= $_SESSION['username'] ?></span>
    <a href="../logout.php" class="btn btn-outline btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Signaler un Objet Perdu</h2>
            <p>Décrivez l'objet que vous avez perdu. Nous vous contacterons si nous le retrouvons.</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" class="report-form">
                <div class="form-row">
                    <div class="form-group" style="flex: 1 1 100%;">
                        <label for="description">Description détaillée de l'objet *</label>
                        <textarea id="description" name="description" required 
                                  placeholder="Décrivez votre objet en détail (marque, couleur, taille, caractéristiques particulières...)"></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="type">Type d'objet *</label>
                        <select id="type" name="type" required>
                            <option value="">Sélectionnez un type</option>
                            <option value="Electronique">Électronique</option>
                            <option value="Bagagerie">Bagagerie</option>
                            <option value="Bijoux">Bijoux</option>
                            <option value="Vetements">Vêtements</option>
                            <option value="Documents">Documents</option>
                            <option value="Divers">Divers</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date_lost">Date de perte *</label>
                        <input type="date" id="date_lost" name="date_lost" required max="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="lieu">Lieu probable de perte *</label>
                        <select id="lieu" name="lieu" required>
                            <option value="">Sélectionnez un lieu</option>
                            <option value="Terminal 1">Terminal 1</option>
                            <option value="Terminal 2">Terminal 2</option>
                            <option value="Zone d'embarquement A">Zone d'embarquement A</option>
                            <option value="Zone d'embarquement B">Zone d'embarquement B</option>
                            <option value="Contrôle de sécurité">Contrôle de sécurité</option>
                            <option value="Salon VIP">Salon VIP</option>
                            <option value="Parking">Parking</option>
                            <option value="Autre">Autre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="contact_info">Informations de contact *</label>
                        <input type="text" id="contact_info" name="contact_info" required 
                               placeholder="Téléphone ou email pour vous contacter">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Envoyer le signalement</button>
                    <a href="dashboard.php" class="btn btn-outline">Annuler</a>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/common.js"></script>
</body>
</html>
