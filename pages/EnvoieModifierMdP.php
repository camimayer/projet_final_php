
<?php
    session_start();
    // Vérification si l'utilisateur est authentifié
    if (!isset($_SESSION['Authentifie']) || !$_SESSION['Authentifie']) {
        header("Location: login.php");
        exit();
    }
    require_once 'header.php';
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    </head>
    <body>
        <div class="container-fluid my-4">
            <div id="divMAJProfile" class="col-4 m-auto">
                <h1 class="text-center" id="titreMAJProfile">Mise à jour du mot de passe</h1>
                <br />
                <br />
                <div class="text-center">
                    <p>Votre mot de passe a été mis à jour avec succès</p>
                    <p><a href="miseAJourProfil.php">Retour à la mise à jour du profile</a></p>
                    <p><a href="ListeAnnonces.php">Retour à la liste des annonces</a></p>
                </div>
            </div>
        </div>
        <br>
    </body>
</html>

