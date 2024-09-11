<?php
require_once 'databasemanager.php';

$dbManager = new DatabaseManager();

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Vérifier si le token existe dans la base de données
    $stmt = $dbManager->getConnection()->prepare("SELECT NoUtilisateur FROM utilisateurs WHERE Token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Activer le compte de l'utilisateur (mettre à jour le statut)
        $stmt_update = $dbManager->getConnection()->prepare("UPDATE utilisateurs SET Statut = 1, Token = NULL WHERE Token = ?");
        $stmt_update->bind_param("s", $token);
        $stmt_update->execute();

        echo "Votre compte a été vérifié avec succès!";
    } else {
        echo "Token invalide ou expiré.";
    }

    $stmt->close();
}
?>
