<?php
session_start();
require_once '../databasemanager.php';
require_once '../config/localhost.php';


$dbManager = new DatabaseManager();

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $courriel = trim($_POST['courriel']);
    $password = trim($_POST['password']);

    // Validação do email
    if (!filter_var($courriel, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse de courriel est invalide.";
    }

    if (empty($errors)) {
        // Chamar a função loginUser
        $loginResult = $dbManager->loginUser($courriel, $password);

        if ($loginResult['success']) {
            // Armazenar os dados do usuário na sessão
            $_SESSION['Courriel'] = $courriel;
            $_SESSION['Nom'] = $loginResult['nom'];
            $_SESSION['Prenom'] = $loginResult['prenom'];

            // Redirecionar para a página de perfil do usuário
            header("Location: profil_utilisateur.php");
            exit();
        } else {
            $errors[] = $loginResult['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h1>Connexion</h1>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="post">
            <label for="courriel">Adresse de courriel:</label>
            <input type="email" id="courriel" name="courriel" required>

            <label for="password">Mot de passe:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>

</html>

<?php
// Fechar conexão
$dbManager->closeConnection();
?>