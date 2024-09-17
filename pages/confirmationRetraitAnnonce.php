<?php
session_start();
require_once '../databasemanager.php';
require_once '../config/localhost.php';

$dbManager = new DatabaseManager();
$noAnnonce = isset($_GET['NoAnnonce']) ? intval($_GET['NoAnnonce']) : null;

// Vérifier si le numéro d'annonce a été passé
if (!$noAnnonce) {
    // Redirection vers la gestion des annonces si aucun numéro d'annonce n'est fourni
    header("Location: gestionAnnonces.php");
    exit();
}

// Obtenir les détails de l'annonce
$annonce = $dbManager->getConnection()->query("SELECT * FROM annonces WHERE NoAnnonce = $noAnnonce")->fetch_assoc();
if (!$annonce) {
    // Afficher un message d'erreur si l'annonce n'est pas trouvée
    echo "Erreur : L'annonce n'a pas été trouvée.";
    exit();
}

// Obtenir les informations de l'utilisateur créateur de l'annonce
$noUtilisateurAnnonce = $annonce['NoUtilisateur'];
$utilisateur = $dbManager->getConnection()->query("SELECT Nom, Prenom, NoTelCellulaire, NoTelMaison, NoTelTravail FROM utilisateurs WHERE NoUtilisateur = $noUtilisateurAnnonce")->fetch_assoc();
if (!$utilisateur) {
    // Afficher un message d'erreur si l'utilisateur n'est pas trouvé
    echo "Erreur : L'utilisateur n'a pas été trouvé.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de Retrait & Détails de l'annonce</title>
    <link rel="stylesheet" href="../styles/confirRetraitAnnonce_style.css?v=<?php echo time(); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <!-- Message de confirmation pour retirer l'annonce -->
        <div class="alert alert-warning confirmation">
            <h4 class="alert-heading">Confirmation de Retrait</h4>
            <p>Êtes-vous sûr de vouloir retirer l'annonce suivante : <?php echo $annonce['DescriptionAbregee']; ?> ?
                Cette action est irréversible.</p>
            <hr>

            <!-- Formulaire de confirmation -->
            <form action="changerEtatAnnonce.php" method="POST">
                <input type="hidden" name="NoAnnonce" value="<?php echo $noAnnonce; ?>">
                <input type="hidden" name="newEtat" value="3"> <!-- 3 pour indiquer l'état "retiré" -->
                <button type="submit" class="btn btn-danger">Confirmer le Retrait</button>
                <a href="gestionAnnonces.php" class="btn btn-secondary">Annuler</a>
            </form>
        </div>

        <!-- Détails de l'annonce -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-center"><?php echo $annonce['DescriptionAbregee']; ?></h3>

                <!-- Afficher la photo si elle est disponible -->
                <?php if (!empty($annonce['Photo'])): ?>
                    <img src="../photos-annonce/<?php echo $annonce['Photo']; ?>" alt="Photo de l'annonce"
                        class="img-fluid mb-4">
                <?php endif; ?>

                <!-- Affichage de la description complète si elle existe -->
                <?php if (isset($annonce['DescriptionComplete'])): ?>
                    <p class="mx-3"><?php echo $annonce['DescriptionComplete']; ?></p>
                <?php else: ?>
                    <p>Description non disponible</p>
                <?php endif; ?>

                <br>

                <!-- Conteneur pour la table -->
                <div class="row">
                    <table class="table table-borderless mx-4">
                        <tr>
                            <th>Numéro :</th>
                            <td><?php echo $annonce['NoAnnonce']; ?></td>
                        </tr>
                        <tr>
                            <th>Auteur :</th>
                            <td><?php echo (!empty($utilisateur['Nom']) && !empty($utilisateur['Prenom']) ? $utilisateur['Nom'] . ', ' . $utilisateur['Prenom'] : (!empty($utilisateur['Nom']) ? $utilisateur['Nom'] : $utilisateur['Prenom'])); ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Prix :</th>
                            <td><?php echo number_format($annonce['Prix'], 2, ',', ' '); ?> $</td>
                        </tr>
                        <tr>
                            <th>Date de parution :</th>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($annonce['Parution'])); ?></td>
                        </tr>
                        <tr>
                            <th>Date de modification :</th>
                            <td><?php echo (!empty($annonce['MiseAJour']) && $annonce['MiseAJour'] != "0000-00-00 00:00:00" ? date('Y-m-d H:i:s', strtotime($annonce['MiseAJour'])) : "N/A"); ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Contact</th>
                            <td></td>
                        </tr>
                        <tr>
                            <th>Téléphone cellulaire :</th>
                            <td><?php echo (!empty($utilisateur['NoTelCellulaire']) ? $utilisateur['NoTelCellulaire'] : "Non spécifié"); ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Téléphone maison :</th>
                            <td><?php echo (!empty($utilisateur['NoTelMaison']) ? $utilisateur['NoTelMaison'] : "Non spécifié"); ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Téléphone travail :</th>
                            <td><?php echo (!empty($utilisateur['NoTelTravail']) ? $utilisateur['NoTelTravail'] : "Non spécifié"); ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>