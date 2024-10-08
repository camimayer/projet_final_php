<?php
require '../librairies/PHPMailer/src/Exception.php';
require '../librairies/PHPMailer/src/PHPMailer.php';
require '../librairies/PHPMailer/src/SMTP.php';

// Inclure DatabaseManager
require_once '../config/localhost.php';
require_once '../databasemanager.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Créer une instance de DatabaseManager
$dbManager = new DatabaseManager();

// Initialiser les variables d'erreurs et de succès
$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $courriel = trim($_POST['courriel']);
    $courrielConfirm = trim($_POST['courrielConfirm']);
    $password = trim($_POST['password']);
    $passwordConfirm = trim($_POST['passwordConfirm']);

    // Validation du courriel
    if (!filter_var($courriel, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse de courriel est invalide.";
    }

    if ($courriel !== $courrielConfirm) {
        $errors[] = "Les adresses de courriel ne correspondent pas.";
    }

    // Validation du mot de passe
    if (strlen($password) < 5 || strlen($password) > 15) {
        $errors[] = "Le mot de passe doit contenir entre 5 et 15 caractères.";
    } elseif (preg_match('/[A-Z]/', $password)) {
        $errors[] = "Le mot de passe doit être en minuscules (pas de majuscules).";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Le mot de passe doit contenir au moins une lettre minuscule.";
    }

    if ($password !== $passwordConfirm) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    // Si aucune erreur n'est détectée
    if (empty($errors)) {
        // Vérifier si le courriel existe déjà
        $stmt = $dbManager->getConnection()->prepare("SELECT NoUtilisateur FROM utilisateurs WHERE Courriel = ?");
        if ($stmt) {
            $stmt->bind_param("s", $courriel);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = "L'adresse de courriel existe déjà.";
            } else {
                // Générer un token de vérification unique
                $token = bin2hex(random_bytes(50));

                // Sauvegarder l'utilisateur avec le token
                if ($dbManager->saveUserWithToken($courriel, $password, $token)) {
                    $success = true;

                    // Création de l'instance de PHPMailer
                    $mail = new PHPMailer(true);
                    try {
                        // Paramètres du serveur SMTP
                        $mail->isSMTP();                               
                        $mail->Host       = 'smtp.office365.com';               
                        $mail->SMTPAuth   = true;                     
                        $mail->Username   = EMAILUSER;
                        $mail->Password   = EMAILPASS;
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = 587;
                        $mail->CharSet = 'UTF-8';

                        $mail->setFrom(EMAILUSER, 'Equipe CamilaFlavioSilvia');

                        $mail->addAddress($courriel);

                        $mail->isHTML(true);
                        $mail->Subject = 'Vérifiez votre adresse e-mail';
                        $mail->Body = "Merci de vous être inscrit. Cliquez sur ce lien pour vérifier votre adresse e-mail: 
                        <a href='http://localhost/projet_final_php/config/verify.php?token=$token'>Cliquez ici pour vérifier</a>";
                        $mail->AltBody = "Merci de vous être inscrit. Copiez ce lien pour vérifier votre adresse e-mail: 
                        http://localhost/verify.php?token=$token";

                        // Envoi de l'email
                        $mail->send();
                    } catch (Exception $e) {
                        $errors[] = "Erreur lors de l'envoi de l'email de vérification: {$mail->ErrorInfo}";
                    }
                } else {
                    $errors[] = "Une erreur est survenue lors de l'inscription.";
                }
            }
            $stmt->close();
        } else {
            $errors[] = "Erreur lors de la préparation de la requête.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="../styles/style.css">
</head>

<body>
    <div class="container">
        <h1>Inscription</h1>

        <!-- Affichage des erreurs -->
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire d'inscription -->
        <form action="signup.php" method="post">
            <label for="courriel">Adresse de courriel:</label>
            <input type="email" id="courriel" name="courriel" required>

            <label for="courrielConfirm">Confirmer courriel:</label>
            <input type="email" id="courrielConfirm" name="courrielConfirm" required>

            <label for="password">Mot de passe:</label>
            <input type="password" id="password" name="password" required>

            <label for="passwordConfirm">Confirmer mot de passe:</label>
            <input type="password" id="passwordConfirm" name="passwordConfirm" required>

            <button type="submit">Soumettre</button>

            <!-- Message de succès après inscription -->
            <?php if ($success): ?>
                <p class="success">Inscription réussie! Un e-mail de vérification a été envoyé.</p>
            <?php endif; ?>
            <br>
            <p>Déjà Membre ?</p>
            <div class="links">
                <a href="login.php">Connectez vous ici</a>
                <a href="forgot_password.php">Mot de passe oublié</a>
            </div>
        </form>
    </div>
</body>

</html>