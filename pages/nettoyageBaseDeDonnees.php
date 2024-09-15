<?php
session_start();
require_once '../databasemanager.php';

// Vérification si l'utilisateur est un administrateur
if (!isset($_SESSION['Courriel']) || $_SESSION['Courriel'] !== 'admin@gmail.com') {
    header("Location: login.php");
    exit();
}

$dbManager = new DatabaseManager();

// Nettoyage de la base de données
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nettoyer_utilisateurs'])) {
        // Supprimer les utilisateurs non confirmés il y a plus de trois mois
        $threeMonthsAgo = date('Y-m-d H:i:s', strtotime('-3 months'));
        $deleteUsersQuery = "DELETE FROM utilisateurs 
                             WHERE Statut = 0 AND Creation < '$threeMonthsAgo'";
        $dbManager->getConnection()->query($deleteUsersQuery);
        $message = "Utilisateurs non confirmés il y a plus de trois mois ont été supprimés.";
    }

    if (isset($_POST['nettoyer_annonces'])) {
        // Supprimer les annonces retirées logiquement
        $deleteAnnoncesQuery = "DELETE FROM annonces WHERE Etat = 3";
        $dbManager->getConnection()->query($deleteAnnoncesQuery);
        $message = "Annonces retirées logiquement ont été supprimées.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<?php    
    require_once 'header.php';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nettoyage de la base de données</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Nettoyage de la base de données</h1>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="nettoyageBaseDeDonnees.php">
            <div class="form-group">
                <button type="submit" name="nettoyer_utilisateurs" class="btn btn-danger">
                    Supprimer les utilisateurs non confirmés depuis plus de trois mois
                </button>
            </div>
            <div class="form-group">
                <button type="submit" name="nettoyer_annonces" class="btn btn-danger">
                    Supprimer les annonces retirées logiquement
                </button>
            </div>
        </form>
    </div>
</body>
</html>
