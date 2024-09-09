<?php
session_start();

// Verificar se o usuário está autenticado
if (!isset($_SESSION['Courriel'])) {
    // Redirecionar para a página de login se o usuário não estiver autenticado
    header("Location: ../pages/login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de l'utilisateur</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div>
        <h1>Profil de l'utilisateur</h1>
        <p><strong>Adresse de courriel:</strong> <?php echo htmlspecialchars($_SESSION['Courriel']); ?></p>

        <a href="logout.php"><button>Se déconnecter</button></a>
    </div>
</body>

</html>