<?php
// Inclusions des fichiers nécessaires
require_once '../config/localhost.php';
require_once("../models/classe-mysql-2024-08-27.php");
require_once '../databasemanager.php';

$strNomFichierCSS = "../styles/style.css";
$strTitreApplication = "";
// Créer une instance de DatabaseManager
$dbManager = new DatabaseManager();

// Initialiser les variables d'erreurs et de succès
$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $courriel1 = trim($_POST['courriel1']);
    $courriel2 = trim($_POST['courriel2']);
    $password1 = trim($_POST['password1']);
    $password2 = trim($_POST['password2']);

    // Validation de l'adresse email
    if (!filter_var($courriel1, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse de courriel 1 est invalide.";
    }

    if ($courriel1 !== $courriel2) {
        $errors[] = "Les adresses de courriel ne correspondent pas.";
    }

    // Validation du mot de passe
    if (strlen($password1) < 5 || strlen($password1) > 15) {
        $errors[] = "Le mot de passe doit contenir entre 5 et 15 caractères.";
    } elseif (preg_match('/[A-Z]/', $password1)) {
        $errors[] = "Le mot de passe doit être en minuscules (pas de majuscules).";
    } elseif (!preg_match('/[a-z]/', $password1)) {
        $errors[] = "Le mot de passe doit contenir au moins une lettre minuscule.";
    }

    if ($password1 !== $password2) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    if (empty($errors)) {
        // Vérifier si l'email existe déjà
        $stmt = $dbManager->getConnection()->prepare("SELECT NoUtilisateur FROM utilisateurs WHERE Courriel = ?");
        if ($stmt) {
            $stmt->bind_param("s", $courriel1);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = "L'adresse de courriel existe déjà.";
            } else {
                // Enregistrer l'utilisateur dans la base de données
                if ($dbManager->saveUser($courriel1, $password1)) {
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
<div class="container">
    <h1>Inscription</h1>

    <?php if (!empty($errors)): ?>
        <div class="errors">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Formulário de inscrição -->
    <form action="index.php" method="post">
        <label for="courriel1">Adresse de courriel 1:</label>
        <input type="email" id="courriel1" name="courriel1" required>

        <label for="courriel2">Adresse de courriel 2:</label>
        <input type="email" id="courriel2" name="courriel2" required>

        <label for="password1">Mot de passe 1:</label>
        <input type="password" id="password1" name="password1" required>

        <label for="password2">Mot de passe 2:</label>
        <input type="password" id="password2" name="password2" required>

        <button type="submit">Soumettre</button>

        <?php if ($success): ?>
            <p class="success">Inscription réussie!</p>
            <!-- Botão para redirecionar para a página de login -->
            <a href="login.php">
                <button type="button">Aller à la connexion</button>
            </a>
        <?php endif; ?>
    </form>
</div>