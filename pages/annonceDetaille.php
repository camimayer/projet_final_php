<?php
session_start();
require_once '../databasemanager.php';
require_once '../config/localhost.php';
require_once '../librairies/librairies-communes-2018-03-16.php';

$dbManager = new DatabaseManager();

?>

<!DOCTYPE html>
<html lang="fr">

<?php
require_once 'header.php';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'annonce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles/annonceDetaille.css?v=<?php echo time(); ?>">
</head>

<body>
    <div class="container mt-5 fade-in">
        <?php
        $noAnnonce = isset($_GET['NoAnnonce']) ? intval($_GET['NoAnnonce']) : null;

        // Vérification si le numéro de l'annonce est défini
        if ($noAnnonce) {
            // Requête pour obtenir les données de l'annonce, y compris le NoUtilisateur du créateur
            $annonce = $dbManager->getConnection()->query("SELECT * FROM annonces WHERE NoAnnonce = $noAnnonce")->fetch_assoc();

            if ($annonce) {
                // Obtention du NoUtilisateur du créateur de l'annonce
                $noUtilisateurAnnonce = $annonce['NoUtilisateur'];

                // Requête pour obtenir les données de l'utilisateur créateur de l'annonce
                $utilisateur = $dbManager->getConnection()->query("SELECT Nom, Prenom, NoTelCellulaire, NoTelMaison, NoTelTravail FROM utilisateurs WHERE NoUtilisateur = $noUtilisateurAnnonce")->fetch_assoc();

                echo "<div class='card shadow-sm'>";
                echo "<div class='card-body'>";
                // Titre avec le numéro de l'annonce
                echo "<h3 class='card-title text-center mb-4'>" . $annonce['DescriptionAbregee'] . "</h3>";

                // Affichage de la photo en haut
                if (!empty($annonce['Photo'])) {
                    echo "<div class='text-center mb-4'>";
                    echo "<img src='../photos-annonce/" . $annonce['Photo'] . "' alt='Photo de l'annonce' class='img-fluid'>";
                    echo "</div>";
                }

                // Verifica si la clave 'DescriptionComplete' o similar está disponible
                if (isset($annonce['DescriptionComplete'])) {
                    echo "<p class='mx-3'>" . $annonce['DescriptionComplete'] . "</p>";  // Usa el nombre correcto de la columna
                } else {
                    echo "<p>Description non disponible</p>";  // Mensaje si la descripción no existe
                }
                echo "<br>";
                // Conteneur pour la table sin bordes
                echo "<div class='row'>";
                echo "<table class='table table-borderless mx-4'>";
                echo "<tr><th>Numéro :</th><td>" . $annonce['NoAnnonce'] . "</td></tr>";
                echo "<tr><th>Auteur :</th><td>" . (!empty($utilisateur['Nom']) && !empty($utilisateur['Prenom']) ? $utilisateur['Nom'] . ', ' . $utilisateur['Prenom'] : (!empty($utilisateur['Nom']) ? $utilisateur['Nom'] : $utilisateur['Prenom'])) . "</td></tr>";
                echo "<tr><th>Prix :</th><td>" . number_format($annonce['Prix'], 2, ',', ' ') . " $</td></tr>";
                echo "<tr><th>Date de parution :</th><td>" . date('Y-m-d H:i:s', strtotime($annonce['Parution'])) . "</td></tr>";
                echo "<tr><th>Date de modification :</th><td>" . (!empty($annonce['MiseAJour']) && $annonce['MiseAJour'] != "0000-00-00 00:00:00" ? date('Y-m-d H:i:s', strtotime($annonce['MiseAJour'])) : "N/A") . "</td></tr>";
                echo "<tr><th>Contact</th><td></td></tr>";
                // Affichage des numéros de téléphone
                echo "<tr><th>Téléphone cellulaire :</th><td>" . (!empty($utilisateur['NoTelCellulaire']) ? $utilisateur['NoTelCellulaire'] : "Non spécifié") . "</td></tr>";
                echo "<tr><th>Téléphone maison :</th><td>" . (!empty($utilisateur['NoTelMaison']) ? $utilisateur['NoTelMaison'] : "Non spécifié") . "</td></tr>";
                echo "<tr><th>Téléphone travail :</th><td>" . (!empty($utilisateur['NoTelTravail']) ? $utilisateur['NoTelTravail'] : "Non spécifié") . "</td></tr>";

                echo "</table>";
                echo "</div>";
                echo "</div></div>";
            } else {
                // Message si l'annonce est introuvable ou l'utilisateur n'est pas autorisé à la voir
                echo "<div class='alert alert-danger'>Annonce introuvable ou vous n'êtes pas autorisé à la voir.</div>";
            }
        } else {
            // Message si le numéro d'annonce est manquant
            echo "<div class='alert alert-warning'>Informations manquantes pour afficher l'annonce.</div>";
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>