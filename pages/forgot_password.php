<?php
    session_start();
    require_once '../databasemanager.php'; // Charger le fichier DatabaseManager

    $pagesTitle = $_SESSION['PagesTitle'];
    $dbManager = new DatabaseManager();
    $errors = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $courriel = trim($_POST['tbEmail']);

        // Vérifie si l'e-mail est enregistré dans le système ou si le champ est vide
        $stmt = $dbManager->getConnection()->prepare("SELECT Courriel, MotDePasse FROM utilisateurs WHERE Courriel = ?");
        if ($stmt) {
            $stmt->bind_param("s", $courriel);
            $stmt->execute();
            $stmt->store_result();
            if (!$courriel) {
                $errors[] = "Veuillez entrer une addresse email";
            } elseif ($stmt->num_rows <= 0) {
                $errors[] = "Cet email est lié à aucun compte !";
            } 
            $_SESSION['Courriel'] = $courriel;
            $stmt->close();
        } else {
            $errors[] = "Erreur lors de la préparation de la requête.";
        }

        if (empty($errors)) {
            // Chama a função de envio de senha esquecida para enviar a senha cadastrada para o e-mail do usuário

            // Rediriger l'utilisateur vers la page EnvoieOublie.php
            header("Location: EnvoieOublie.php");
            exit();
        } 
    }

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    <title><?php echo $pagesTitle; ?></title>
</head>
<body>
    <div class="container-fluid my-4">
        <div id="divOublie" class="col-4 m-auto">
            <h1 class="text-center" id="titreConnexion">Récupération du mot de passe</h1>
            <br>
            <form id="formOublie" action="forgot_password.php" method="POST">
                <div class="form-group">
                    <label for="tbEmail">Email</label>
                    <input type="email" class="form-control" id="tbEmail" name="tbEmail" placeholder="Email">
                    <p id="errEmail" class="text-danger font-weight-bold">
                        <?php foreach ($errors as $error): ?>
                            <p id="errEmail" class="text-danger font-weight-bold"><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </p>
                </div>
                <div class="d-flex">
                    <button type="submit" class="btn btn-primary" id="btnConnecter">Envoyer le mot de passe</button>
                </div>
                <br>
                <a href="login.php">Retourne à la connexion</a>
            </form>
        </div>
    </div>
    <br>
</body>
</html>