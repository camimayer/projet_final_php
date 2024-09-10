<?php
session_start();
require_once '../databasemanager.php'; // Include the database connection
require_once '../config/localhost.php';

$dbManager = new DatabaseManager();
$errors = [];
$success = false;

// Para pruebas: asignar temporalmente un valor a NoUtilisateur
if (!isset($_SESSION['NoUtilisateur'])) {
    // Establecer el ID del usuario manualmente (usar el ID de un usuario existente en la base de datos)
    $_SESSION['NoUtilisateur'] = 1;  // Cambia '1' por el ID del usuario con el que desees trabajar
}

$noUtilisateur = $_SESSION['NoUtilisateur'];

// Inserción de una nueva annonce
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $categorie = trim($_POST['categorie']);
    $descriptionAbregee = trim($_POST['descriptionAbregee']);
    $descriptionComplete = trim($_POST['descriptionComplete']);
    $prix = trim($_POST['prix']);
    $etat = isset($_POST['etat']) ? $_POST['etat'] : 1;

    if (empty($categorie) || empty($descriptionAbregee) || empty($descriptionComplete)) {
        $errors[] = "Tous les champs doivent être remplis.";
    }

    if (empty($prix)) {
        $prix = null;
    } else {
        $prix = floatval($prix);
    }

    if (empty($errors)) {
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($_FILES['photo']['type'], $allowedTypes)) {
                $errors[] = "Le fichier doit être une image (JPEG, PNG ou GIF).";
            }

            if ($_FILES['photo']['size'] > 2000000) {
                $errors[] = "La taille du fichier ne doit pas dépasser 2MB.";
            }

            if (empty($errors)) {
                $photoName = $_FILES['photo']['name'];
                $photoPath = 'photos-annonce/' . basename($photoName);

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
                    $stmt = $dbManager->getConnection()->prepare("
                        INSERT INTO annonces (NoUtilisateur, Categorie, DescriptionAbregee, DescriptionComplete, Prix, Photo, Etat, Parution, DerniereMiseAJour) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ");
                    $stmt->bind_param("isssisi", $noUtilisateur, $categorie, $descriptionAbregee, $descriptionComplete, $prix, $photoName, $etat);

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

// Cargar todas las annonces existentes
$annonces = $dbManager->getConnection()->query("SELECT * FROM annonces WHERE NoUtilisateur = $noUtilisateur");
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription d'une annonce</title>
    <link rel="stylesheet" href="../styles/style.css">
</head>

<body>
    <div class="container">
        <h1>Ajout d'une annonce</h1>
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="success">Annonce enregistrée avec succès !</p>
        <?php else: ?>
            <form action="gestionAnnonces.php" method="post" enctype="multipart/form-data">
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

                <label for="petiteDescription" aria-placeholder="Petite description">Petite description :</label>
                <input type="text" name="descriptionAbregee" id="descriptionAbregee" required>

                <label for="descriptionlongues">Description longues :</label>
                <textarea name="descriptionlongues" id="descriptionComplete" required></textarea>

                <label for="prix">Prix :</label>
                <input type="number" name="prix" id="prix">

                <label for="photo">Téléverser une photo :</label>
                <input type="file" name="photo" id="photo" required>

                <button type="submit">Soumettre</button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>