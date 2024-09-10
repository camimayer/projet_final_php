<?php
session_start();
require_once '../databasemanager.php'; // Inclure la connexion à la base de données
require_once '../config/localhost.php';

$dbManager = new DatabaseManager();
$errors = [];
$success = false;

// Pour les tests : assigner temporairement une valeur à NoUtilisateur
if (!isset($_SESSION['NoUtilisateur'])) {
    // Définir l'ID de l'utilisateur manuellement (utiliser l'ID d'un utilisateur existant dans la base de données)
    $_SESSION['NoUtilisateur'] = 1;  // Changez '1' par l'ID de l'utilisateur que vous souhaitez utiliser
}

$noUtilisateur = $_SESSION['NoUtilisateur'];

// Insertion d'une nouvelle annonce
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $categorie = trim($_POST['categorie']);
    $descriptionAbregee = trim($_POST['descriptionAbregee']);
    $descriptionComplete = trim($_POST['descriptionComplete']);
    $prix = trim($_POST['prix']);
    $etat = isset($_POST['etat']) ? $_POST['etat'] : 1;

    // Vérification des champs obligatoires
    if (empty($categorie) || empty($descriptionAbregee) || empty($descriptionComplete)) {
        $errors[] = "Tous les champs doivent être remplis.";
    }

    // Si le prix est vide, définir à NULL
    if (empty($prix)) {
        $prix = null;
    } else {
        $prix = floatval($prix);  // Convertir en float
    }

    // Si aucune erreur, continuer avec le téléchargement de l'image
    if (empty($errors)) {
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

            // Vérification du type de fichier
            if (!in_array($_FILES['photo']['type'], $allowedTypes)) {
                $errors[] = "Le fichier doit être une image (JPEG, PNG ou GIF).";
            }

            // Vérification de la taille du fichier
            if ($_FILES['photo']['size'] > 2000000) {
                $errors[] = "La taille du fichier ne doit pas dépasser 2MB.";
            }

            // Si aucune erreur, continuer avec l'upload de l'image
            if (empty($errors)) {
                $photoName = $_FILES['photo']['name'];
                $photoPath = 'photos-annonce/' . basename($photoName);

                // Vérifier si le dossier existe, sinon le créer avec les permissions appropriées
                if (!is_dir('photos-annonce')) {
                    if (!mkdir('photos-annonce', 0777, true)) {
                        $errors[] = "Erreur lors de la création du répertoire pour les photos.";
                    }
                }

                // Déplacer l'image téléchargée vers le répertoire spécifié
                if (empty($errors) && move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
                    // Préparer la requête d'insertion
                    $stmt = $dbManager->getConnection()->prepare("
                        INSERT INTO annonces (NoUtilisateur, Categorie, DescriptionAbregee, DescriptionComplete, Prix, Photo, Etat, Parution, DerniereMiseAJour) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ");
                    $stmt->bind_param("isssisi", $noUtilisateur, $categorie, $descriptionAbregee, $descriptionComplete, $prix, $photoName, $etat);

                    // Exécuter la requête
                    if ($stmt->execute()) {
                        $success = true;
                    } else {
                        $errors[] = "Une erreur est survenue lors de l'enregistrement de l'annonce.";
                    }
                    $stmt->close();
                } else {
                    $errors[] = "Erreur lors du téléchargement de la photo.";
                }
            }
        } else {
            $errors[] = "Aucune photo téléchargée.";
        }
    }
}

// Charger toutes les annonces existantes
$annonces = $dbManager->getConnection()->query("SELECT * FROM annonces WHERE NoUtilisateur = $noUtilisateur");
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription d'une annonce</title>
    <link rel="stylesheet" href="../styles/ajouteAnnonces_style.css">
</head>

<body>
    <div class="container">
        <h1>Ajout d'une annonce</h1>
        <!-- Affichage des erreurs -->
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Affichage du message de succès -->
        <?php if ($success): ?>
            <p class="success">Annonce enregistrée avec succès !</p>
        <?php else: ?>
            <!-- Formulaire pour ajouter une annonce -->
            <form action="gestionAnnonces.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="MAX_FILE_SIZE" value="100000">
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="categorie">Catégorie :</label>
                        <select name="categorie" id="categorie" required>
                            <option value="" 0>Selectionner une catégorie</option>
                            <option value="1">Location</option>
                            <option value="2">Recherche</option>
                            <option value="3">À vendre</option>
                            <option value="4">À donner</option>
                            <option value="5">Service offert</option>
                            <option value="6">Autre</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="petiteDescription" aria-placeholder="Petite description">Petite description :</label>
                        <input type="text" name="descriptionAbregee" id="descriptionAbregee" required>
                        <p id="errPetitDescription" class="text-danger font-weight-bold"></p>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="descriptionlongues">Description longues :</label>
                        <br>
                        <textarea name="descriptionComplete" id="descriptionComplete" required></textarea>
                        <p id="errLongueDesc" class="text-danger font-weight-bold"></p>
                    </div>
                </div>
                <div>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="prix">Prix :</label>
                            <input type="number" name="prix" id="prix">
                            <p id="errFichierTransfert" class="text-danger font-weight-bold"></p>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="photo">Sélectionner l'image de l'annonce :</label>
                        <input type="file" name="photo" id="photo" required>
                        <p id="errFichierTransfert" class="text-danger font-weight-bold"></p>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="etat">Activé?</label>
                        <select name="Activation" id="etat" required>
                            <option value="1">Actif</option>
                            <option value="2">Inactif</option>
                        </select>
                        <p id="errEmailConfirm" class="text-danger font-weight-bold"></p>
                    </div>
                </div>
                <input type="submit" value="S'inscrire" class="btn btn-primary col-md-12" id="btnInscription">
                <div id="divEnvoie">
                    <a href="gestionAnnonces.php">Annuler l'ajout de l'annonce</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>