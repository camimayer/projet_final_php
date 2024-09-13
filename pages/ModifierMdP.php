<?php
    session_start();
    $pagesTitle = $_SESSION['PagesTitle'];
    require_once '../databasemanager.php';
    // require_once 'header.php';
    $email = $_SESSION['Courriel'];
    $databaseManager = new DatabaseManager();
    $errors = [];
    $success = false;

    // if ($databaseManager->checkPasswordDB($email, "MotdePasse")) {}

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $courriel = trim($_POST['password']);
        $password = trim($_POST['newPassword']);
        $passwordConfirm = trim($_POST['newPasswordConfirm']);
    
        if ($courriel !== $courrielConfirm) {
            $errors[] = "Les adresses de courriel ne correspondent pas.";
        }
    
        // Validação da senha
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

    }



// Inicializar as variáveis de erro e sucesso


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
            <input type="email" id="courriel" name="Password" required>

            <label for="password">Nouveau mot de passe:</label>
            <input type="password" id="password" name="newPassword" required>

            <label for="passwordConfirm">Confirmation nouveau mot de passe:</label>
            <input type="password" id="passwordConfirm" name="newPasswordConfirm" required>

            <button type="submit">Soumettre</button>

            <?php if ($success): ?>
                <p class="success">Inscription réussie! Un e-mail de vérification a été envoyé.</p>
            <?php endif; ?>
        </form>
    </div>
</body>


<!-- <body>

<br><br>

    <div class="container-fluid my-4">
        <div id="divMAJMdP" class="col-4 m-auto">
            <h1 class="text-center" id="titreMAJMdP">Mise à jour du mot de passe</h1>
            <br/>
            <br/>
            <form id="formMAJProfile" action="EnvoieModifierMdP.php" method="POST">

                <div class="form-group row">
                    <label class="col-4 col-form-label" for="tbMdpVieux">Ancien mot de passe</label>
                    <div class="col-6">
                        <input type="password" class="form-control" id="tbMdpVieux" name="tbMdpVieux"
                               value="" required>
                    </div>
                    <p id="errMdpVieux" class="text-danger font-weight-bold">
                                            </p>
                </div>
                <div class="form-group row">
                    <label class="col-4 col-form-label" for="tbMdpNouv">Nouveau mot de passe</label>
                    <div class="col-6">
                        <input type="password" class="form-control" id="tbMdpNouv" name="tbMdpNouv"
                               value="" required>
                    </div>
                    <p id="errMdpNouv" class="text-danger font-weight-bold">
                                            </p>
                </div>
                <div class="form-group row">
                    <label class="col-4 col-form-label" for="tbMdpNouvVerif">Confirmation nouveau mot de passe</label>
                    <div class="col-6">
                        <input type="password" class="form-control" id="tbMdpNouvVerif" name="tbMdpNouvVerif"
                               value="" required>
                    </div>
                    <p id="errMdpNouvVerif" class="text-danger font-weight-bold">
                                            </p>
                </div>
                <div class="d-flex">
                    <button type="submit" class="btn btn-primary" id="btnMAJProfile">Enregistrer</button>
                </div>
            </form>
            <br/>
            <p><a href="miseAJourProfil.php">Retour à la mise à jour du profile</a></p>
        </div>
    </div>
    <br>
</body> -->
</html>