<?php
session_start();
require_once '../databasemanager.php'; // Charger le fichier DatabaseManager

$pathToCss = "../styles/style.css";
$pagesTitle = "Équipe Camila/Ricardo/Silvia";
$_SESSION['PagesTitle'] = $pagesTitle;

$dbManager = new DatabaseManager();

$errors = [];


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $courriel = trim($_POST['courriel']);
    $password = trim($_POST['password']);

    // Validation de l'adresse e-mail
    if (!filter_var($courriel, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse de courriel est invalide.";
    }

    if (empty($errors)) {
        // Appeler la fonction loginUser pour vérifier les informations d'identification
        $loginResult = $dbManager->loginUser($courriel, $password);

        if ($loginResult['success']) {
            // Si la connexion est réussie, stocker les informations dans la session
            $_SESSION['Courriel'] = $courriel;
            $_SESSION['Nom'] = $loginResult['nom'];
            $_SESSION['Prenom'] = $loginResult['prenom'];
            $_SESSION['Statut'] = $loginResult['statut']; // Stocker le statut de l'utilisateur
            $_SESSION['NoUtilisateur'] = $loginResult['noUtilisateur'];

            // Rediriger l'utilisateur vers la page de profil
            header("Location: listeAnnonces.php");
            exit();
        } else {
            // Si la connexion échoue, ajouter un message d'erreur
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
    <title><?php echo $pagesTitle; ?></title>
    <link rel="stylesheet" href="<?php echo $pathToCss; ?>">
</head>

<body>
    <div class="container">
        <!-- Titre de la page -->
        <h1>Connexion</h1>

        <!-- Affichage des erreurs si elles existent -->
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire de connexion -->
        <form action="login.php" method="post">
            <label for="courriel">Email:</label>
            <input type="email" id="courriel" name="courriel" required>

            <label for="password">Mot de passe:</label>
            <input type="password" id="password" name="password" required>

            <!-- Liens pour créer un compte et récupérer le mot de passe -->
            <div class="links">
                <a href="signup.php">Créer un compte</a>
                <a href="forgot_password.php">Mot de passe oublié</a>
            </div>

            <!-- Bouton de connexion -->
            <button type="submit" class="btn btn-blue">Connexion</button>
        </form>
    </div>
</body>

</html>