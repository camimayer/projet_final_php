<?php
session_start();
require_once '../databasemanager.php';
require_once '../config/localhost.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$dbManager = new DatabaseManager();
$errors = [];
$success = false;

// Verificar si el usuario está autenticado
if (!isset($_SESSION['NoUtilisateur'])) {
    // Redirigir al usuario a la página de inicio de sesión si no está autenticado
    header("Location: login.php");
    exit();
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