<?php
    session_start();
    $courriel = $_SESSION['Courriel'];
    $pagesTitle = $_SESSION['PagesTitle'];
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
        <title><?php echo $pagesTitle; ?></title>
    </head>
    <h1 class="text-center" id="titreMAJProfile">Récupération du mot de passe</h1>
    <br />
    <br />
    <div id="divEnvoie">
        <div class="border border-primary col-6 p-4 m-auto text-center">
            Un email comprenant votre mot de passe vous a été envoyé à l'adresse : 
            <span class="font-weight-bold"> <?php echo $courriel;?> </span> <br/>
            Pensez à regarder votre dossier "Spam".
        </div>
    <div class="text-center">
    <p><a href="login.php">Retourne à la connexion</a></p>
    </div>
</html>