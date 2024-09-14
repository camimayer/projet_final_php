<?php
session_start();
require_once '../databasemanager.php';

// Vérification si l'utilisateur est un administrateur
if (!isset($_SESSION['Courriel']) || $_SESSION['Courriel'] !== 'admin@gmail.com') {
    header("Location: login.php");
    exit();
}

$dbManager = new DatabaseManager();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion des utilisateurs</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Menu Administrateur</h1>
        <ul class="list-group">
            <li class="list-group-item">
                <a href="listeAnnonces.php">Affichage de toutes les annonces</a>
            </li>
            <li class="list-group-item">
                <a href="listeUtilisateurs.php">Affichage de tous les utilisateurs</a>
            </li>
            <li class="list-group-item">
                <a href="nettoyageBaseDeDonnees.php">Nettoyage de la base de données</a>
            </li>
        </ul>
    </div>
</body>
</html>
