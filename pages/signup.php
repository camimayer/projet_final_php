<?php
// Inclusions des fichiers nécessaires
require_once '../config/localhost.php';
require_once '../databasemanager.php';

// Créer une instance de DatabaseManager
$dbManager = new DatabaseManager();


// Initialiser les variables d'erreurs et de succès
$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $courriel = trim($_POST['courriel']);
    $courrielConfirm = trim($_POST['courrielConfirm']);
    $password = trim($_POST['password']);
    $passwordConfirm = trim($_POST['passwordConfirm']);

    // Validation de l'adresse email
    if (!filter_var($courriel, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse de courriel est invalide.";
    }

    if ($courriel !== $courrielConfirm) {
        $errors[] = "Les adresses de courriel ne correspondent pas.";
    }

    // Validation du mot de passe
    if (strlen($password) < 5 || strlen($password) > 15) {
        $errors[] = "Le mot de passe doit contenir entre 5 et 15 caractères.";
    } elseif (preg_match('/[A-Z]/', $password)) {
        $errors[] = "Le mot de passe doit être en minuscules (pas de majuscules).";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Le mot de passe doit contenir au moins une lettre minuscule.";
    }

    if ($password !== $passwordConfirm) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    if (empty($errors)) {
        // Vérifier si l'email existe déjà
        $stmt = $dbManager->getConnection()->prepare("SELECT NoUtilisateur FROM utilisateurs WHERE Courriel = ?");
        if ($stmt) {
            $stmt->bind_param("s", $courriel);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = "L'adresse de courriel existe déjà.";
            } else {
                // Enregistrer l'utilisateur dans la base de données
                if ($dbManager->saveUser($courriel, $password)) {
                    $success = true;
                } else {
                    $errors[] = "Une erreur est survenue lors de l'inscription.";
                }
            }
            $stmt->close();
        } else {
            $errors[] = "Erreur lors de la préparation de la requête.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="../styles/style.css">
</head>

<body>
    <div class="container">
        <h1>Inscription</h1>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire d'inscription -->
        <form action="signup.php" method="post">
            <label for="courriel">Adresse de courriel:</label>
            <input type="email" id="courriel" name="courriel" required>

            <label for="courrielConfirm">Confirmer courriel:</label>
            <input type="email" id="courrielConfirm" name="courrielConfirm" required>

            <label for="password">Mot de passe:</label>
            <input type="password" id="password" name="password" required>

            <label for="passwordConfirm">Confirmer mot de passe:</label>
            <input type="password" id="passwordConfirm" name="passwordConfirm" required>

            <button type="submit">Soumettre</button>

            <?php if ($success): ?>
                <p class="success">Inscription réussie!</p>
                <!-- Redirection vers la page de connexion -->
                <a href="login.php" class="btn btn-blue">Aller à la connexion</a>
            <?php endif; ?>
        </form>
    </div>
</body>

</html>