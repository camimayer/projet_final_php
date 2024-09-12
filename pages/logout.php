<?php
session_start();
require_once '../config/localhost.php';
require_once '../databasemanager.php';

// Verifique se o usuário está logado e se o 'NoUtilisateur' está na sessão
if (isset($_SESSION['NoUtilisateur'])) {
    $noUtilisateur = $_SESSION['NoUtilisateur'];

    // Instanciar a classe DatabaseManager para se conectar ao banco de dados
    $databaseManager = new DatabaseManager();

    // Atualizar a hora de logout (déconnexion) para o usuário
    $updateSuccess = $databaseManager->updateLogoutTime($noUtilisateur);

    if ($updateSuccess) {
        echo "Logout time updated successfully.";
    } else {
        echo "Error updating logout time.";
    }

    // Fechar a conexão com o banco de dados
    $databaseManager->closeConnection();
}

// Destruir a sessão
session_destroy();

// Redirecionar para a página de login
header("Location: ../pages/login.php");
exit();
?>
