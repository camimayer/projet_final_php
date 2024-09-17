<?php
session_start();
require_once '../databasemanager.php';

if (!isset($_SESSION['Authentifie']) || !$_SESSION['Authentifie']) {
    header("Location: login.php");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['tbEmail'];
    $statut = $_POST['tbStatut'];
    $noEmp = $_POST['tbNoEmp'];
    $nom = $_POST['tbNom'];
    $prenom = $_POST['tbPrenom'];

    $telM = $_POST['tbTelM'];
    $telT = $_POST['tbTelT'];
    $telC = $_POST['tbTelC'];

    // Ajoute 'N' à la fin s'il est privé, sinon ajoute P
    if (isset($_POST['cbTelMP'])) {
        $telM .= 'N';
    } else {
        $telM .= 'P'; 
    }

    // Ajoute le numéro de Poste après le numéro de téléphone professionnel, s'il est renseigné
    if (!empty($_POST['tbTelTPoste'])) {
        $poste = $_POST['tbTelTPoste'];
        $telT .= " #$poste";
    }

    // Ajoute 'N' à la fin s'il est privé, sinon ajoute P
    if (isset($_POST['cbTelTP'])) {
        $telT .= 'N';
    } else {
        $telT .= 'P';
    }

    // Ajoute 'N' à la fin s'il est privé, sinon ajoute P
    if (isset($_POST['cbTelCP'])) {
        $telC .= 'N';
    } else {
        $telC .= 'P';
    }

    $databaseManager = new DatabaseManager();
    if ($databaseManager->updateUser($email, $statut, $noEmp, $nom, $prenom, $telM, $telT, $telC)) {
        $_SESSION['Statut'] = $statut; // Stocker le nouveau statut dans la variable de session
?>
<!DOCTYPE html>
<html>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
        <title>FAF - Modification du profile</title>
    </head>
    <body>
        <div class="container-fluid my-4">
            <div id="divMAJProfile" class="col-4 m-auto">
                <h1 class="text-center" id="titreMAJProfile">Mise à jour du profile</h1>
                <br />
                <br />
                <div class="text-center">
                    <p>Vos informations ont été mise à jour avec succès</p>
                    <p><a href="miseAJourProfil.php">Retour à la mise à jour de profile</a></p>
                    <p><a href="ListeAnnonces.php">Retour à la liste des annonces</a></p>
                </div>
            </div>
        </div>
        <br>
    </body>
</html>

<?php
    } else {
        echo "Erreur lors de la mise à jour!";
    }
}
?>

