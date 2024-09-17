<?php
    session_start();
    $pagesTitle = $_SESSION['PagesTitle'];
    require_once '../databasemanager.php';

    // Inicializar as variáveis de erro e sucesso
    $email = $_SESSION['Courriel'];
    $databaseManager = new DatabaseManager();
    $errors = [];
    $success = false;
    // Vérification si l'utilisateur est authentifié
    if (!isset($_SESSION['Authentifie']) || !$_SESSION['Authentifie']) {
        // Redirection vers la page de connexion si l'utilisateur n'est pas authentifié
        header("Location: login.php");
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $password = trim($_POST['Password']);
        $newPassword = trim($_POST['newPassword']);
        $newPasswordConfirm = trim($_POST['newPasswordConfirm']);
       
        // Validação da senha
        if (strlen($newPassword) < 5 || strlen($newPassword) > 15) {
            $errors[] = "Le mot de passe doit contenir entre 5 et 15 caractères.";
        } elseif (preg_match('/[A-Z]/', $newPassword)) {
            $errors[] = "Le mot de passe doit être en minuscules (pas de majuscules).";
        } elseif (!preg_match('/[a-z]/', $newPassword)) {
            $errors[] = "Le mot de passe doit contenir au moins une lettre minuscule.";
        }
    
        if ($newPassword !== $newPasswordConfirm) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }

        if (!$databaseManager->checkPasswordDB($email, $password)) {
            $errors[] = "Mot de passe invalid.";
        }

        if (empty($errors)) {
            $databaseManager->updatePassword($email, $newPassword);
            header("Location: EnvoieModifierMdP.php");
            exit();
        }

    }

?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous"> -->
    <link rel="stylesheet" href="../styles/style.css">
</head>

<body>
    <div class="container">
        <h1>Mise à jour du mot de passe</h1>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire d'inscription -->
        <form action="ModifierMdP.php" method="post">
            <label for="courriel">Ancien mot de passe:</label>
            <input type="password" id="courriel" name="Password" required>

            <label for="password">Nouveau mot de passe:</label>
            <input type="password" id="password" name="newPassword" required>

            <label for="passwordConfirm">Confirmation nouveau mot de passe:</label>
            <input type="password" id="passwordConfirm" name="newPasswordConfirm" required>

            <button type="submit">Soumettre</button>

            <?php if ($success): ?>
                <p class="success">Mot de passe modifié avec succès.</p>
            <?php endif; ?>
        </form>
        <br/>
        <p><a href="miseAJourProfil.php">Retour à la mise à jour du profile</a></p>
    </div>
</body>

</html>