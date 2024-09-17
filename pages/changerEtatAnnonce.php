<?php
session_start();
require_once '../databasemanager.php';
require_once '../config/localhost.php';

$dbManager = new DatabaseManager();
$errors = [];

// Vérification si l'utilisateur est authentifié
if (!isset($_SESSION['NoUtilisateur'])) {
    // Redirection vers la page de connexion si l'utilisateur n'est pas authentifié
    header("Location: login.php");
    exit();
}

// Vérification si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['NoAnnonce']) && isset($_POST['newEtat'])) {
    $noAnnonce = $_POST['NoAnnonce'];
    $newEtat = $_POST['newEtat'];

    // Préparer la requête pour mettre à jour l'état de l'annonce
    $stmt = $dbManager->getConnection()->prepare("UPDATE annonces SET Etat = ? WHERE NoAnnonce = ?");
    $stmt->bind_param("ii", $newEtat, $noAnnonce);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Gestion des messages de succès
            if ($newEtat == 3) {
                $_SESSION['success_message'] = "L'annonce a été retirée avec succès.";
                header("Location: retraitAnnonce.php?NoAnnonce=$noAnnonce");
                exit();
            } else if ($newEtat == 2) {
                $_SESSION['success_message'] = "L'annonce a été désactivée.";
            } else if ($newEtat == 1) {
                $_SESSION['success_message'] = "L'annonce a été activée.";
            }
            // Redirection après succès
            header("Location: gestionAnnonces.php");
            exit();
        } else {
            // Si aucune ligne n'a été affectée par la requête
            $errors[] = "Aucune annonce n'a été mise à jour. Vérifiez si l'annonce existe.";
        }
    } else {
        // En cas d'erreur dans la requête SQL
        $errors[] = "Erreur dans la requête: " . $stmt->error;
    }

    $stmt->close();
}

// Filtrage des annonces actives et inactives (états 1 et 2)
$annonces = $dbManager->getConnection()->query("SELECT * FROM annonces WHERE Etat IN (1, 2)");

// Afficher les erreurs en cas de problème
if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "<div class='alert alert-danger'>$error</div>";
    }
}
?>