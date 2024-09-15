<?php
session_start();
require_once '../databasemanager.php';

// Vérification si l'utilisateur est un administrateur
if (!isset($_SESSION['Courriel']) || $_SESSION['Courriel'] !== 'admin@gmail.com') {
    header("Location: login.php");
    exit();
}

$dbManager = new DatabaseManager();

// Obtenez la liste de tous les utilisateurs
$query = "SELECT NoUtilisateur, Courriel, Nom, Prenom, NoTelMaison, NoTelTravail, NoTelCellulaire, Creation, Statut, NoEmpl 
          FROM utilisateurs 
          ORDER BY Nom, Prenom ASC";
$result = $dbManager->getConnection()->query($query);
?>

<!DOCTYPE html>
<html lang="fr">
<?php    
    require_once 'header.php';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des utilisateurs</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Liste de tous les utilisateurs</h1>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Courriel</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Téléphone Maison</th>
                    <th>Téléphone Travail</th>
                    <th>Téléphone Cellulaire</th>
                    <th>Création</th>
                    <th>Statut</th>
                    <th>Dernières Connexions</th>
                    <th>Annonces Actives</th>
                    <th>Annonces Inactives</th>
                    <th>Annonces Retirées</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    // Récupérer les cinq dernières connexions/déconnexions
                    $userId = $row['NoUtilisateur'];
                    $queryConnexions = "SELECT Connexion, Deconnexion FROM connexions 
                                        WHERE NoUtilisateur = $userId 
                                        ORDER BY Connexion DESC 
                                        LIMIT 5";
                    $resultConnexions = $dbManager->getConnection()->query($queryConnexions);

                    // Récupérer le nombre d'annonces
                    $queryAnnonces = "SELECT 
                                      (SELECT COUNT(*) FROM annonces WHERE NoUtilisateur = $userId AND Etat = 1) AS Actives,
                                      (SELECT COUNT(*) FROM annonces WHERE NoUtilisateur = $userId AND Etat = 2) AS Inactives,
                                      (SELECT COUNT(*) FROM annonces WHERE NoUtilisateur = $userId AND Etat = 3) AS Retirees";
                    $resultAnnonces = $dbManager->getConnection()->query($queryAnnonces);
                    $annoncesCount = $resultAnnonces->fetch_assoc();
                    ?>
                    <tr>
                        <td><?php echo $row['NoUtilisateur']; ?></td>
                        <td><?php echo $row['Courriel']; ?></td>
                        <td><?php echo $row['Nom']; ?></td>
                        <td><?php echo $row['Prenom']; ?></td>
                        <td><?php echo $row['NoTelMaison']; ?></td>
                        <td><?php echo $row['NoTelTravail']; ?></td>
                        <td><?php echo $row['NoTelCellulaire']; ?></td>
                        <td><?php echo $row['Creation']; ?></td>
                        <td><?php echo $row['Statut']; ?></td>
                        <td>
                            <?php while ($connexions = $resultConnexions->fetch_assoc()): ?>
                                <p>Connexion: <?php echo $connexions['Connexion']; ?></p>
                                <p>Déconnexion: <?php echo $connexions['Deconnexion']; ?></p>
                                <hr>
                            <?php endwhile; ?>
                        </td>
                        <td><?php echo $annoncesCount['Actives']; ?></td>
                        <td><?php echo $annoncesCount['Inactives']; ?></td>
                        <td><?php echo $annoncesCount['Retirees']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
