<?php
require_once 'databasemanager.php';

$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "PFJ_CAMILAFLAVIOSILVIA";

$dbManager = new DatabaseManager($servername, $username, $password, $dbname);
$dbManager->createTables();

$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $courriel1 = trim($_POST['courriel1']);
    $courriel2 = trim($_POST['courriel2']);
    $password1 = trim($_POST['password1']);
    $password2 = trim($_POST['password2']);

    // Valider email
    if (!filter_var($courriel1, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse de courriel 1 est invalide.";
    }

    if ($courriel1 !== $courriel2) {
        $errors[] = "Les adresses de courriel ne correspondent pas.";
    }

    // Valider mot de passe
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
        // Verifier si le email existe
        $stmt = $dbManager->getConnection()->prepare("SELECT NoUtilisateur FROM utilisateurs WHERE Courriel = ?");
        if ($stmt) {
            $stmt->bind_param("s", $courriel1);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = "L'adresse de courriel existe déjà.";
            } else {
                // methode pour appeller le user
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
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'signup.php'; ?>
</body>
</html>

<?php
// Fermer conexion
$dbManager->closeConnection();
?>
