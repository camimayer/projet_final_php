<?php
    require '../librairies/PHPMailer/src/Exception.php';
    require '../librairies/PHPMailer/src/PHPMailer.php';
    require '../librairies/PHPMailer/src/SMTP.php';
    require_once '../config/localhost.php';
    
    session_start();
    require_once '../databasemanager.php'; // Charger le fichier DatabaseManager

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    $pathToCss = "../styles/style.css";
    $pagesTitle = $_SESSION['PagesTitle'];
    $dbManager = new DatabaseManager();
    $errors = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $courriel = trim($_POST['tbEmail']);

        // Vérifie si l'e-mail est enregistré dans le système ou si le champ est vide
        $stmt = $dbManager->getConnection()->prepare("SELECT Courriel, MotDePasse, Nom, Prenom FROM utilisateurs WHERE Courriel = ?");

        if ($stmt) {
            $stmt->bind_param("s", $courriel);
            $stmt->execute();
            $result = $stmt->get_result();
    
            if (!$courriel) {
                $errors[] = "Veuillez entrer une addresse email";
            } elseif ($result->num_rows <= 0) {
                $errors[] = "Cet email est lié à aucun compte !";
                $courriel = null;
            } else {
                $user = $result->fetch_assoc();
                if ($user) { 
                    $_SESSION['Courriel'] = $courriel;
                    $motPasse = $user['MotDePasse'];
                    $nom      = $user['Nom'];
                    $prenom   = $user['Prenom'];
                } else {
                    $errors[] = "Erreur lors de la récupération des informations utilisateur.";
                }
            }
    
            $stmt->close();
        } else {
            $errors[] = "Erreur lors de la préparation de la requête.";
        }

        if (empty($errors)) {
            // Créer une instance de PHPMailer
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
        
                // Configurer l'encodage
                $mail->CharSet = 'UTF-8';

                $mail->setFrom(EMAILUSER, 'Equipe CamilaFlavioSilvia');
                $mail->addAddress($courriel, $nom, $prenom);

                $mail->isHTML(true);
                $mail->Subject = 'Récupération du mot de passe';
                $mail->Body    = 
                                "Salut $prenom ! <br> 
                                  <br> Vous trouverez ci-dessous le mot de passe que vous avez demandé por le système <strong>Les petites annonces GG</strong>. 
                                  <br>Mot de passe:  <strong>$motPasse</strong>";
                $mail->AltBody = "Vous trouverez ci-dessous le mot de passe que vous avez demandé pour 
                                l'utilisateur : $courriel => Mot de passe: $motPasse";
                $mail->send();
            } catch (Exception $e) {
                $errors[] = "Erreur lors de l'envoi de l'email de vérification: {$mail->ErrorInfo}";
            }
            // Rediriger l'utilisateur vers la page EnvoieOublie.php
            header("Location: EnvoieOublie.php");
            exit();
        } 
    }

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="<?php echo $pathToCss; ?>">
    <title><?php echo $pagesTitle; ?></title>
</head>
<body>
    <div class="container">
        <h1 class="text-center" id="titreConnexion">Récupération</h1>
        <h1 class="text-center" id="titreConnexion">du mot de passe</h1>
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <br>
        <form action="forgot_password.php" method="POST">
                <label for="tbEmail">Email</label>
                <input type="email" class="form-control" id="tbEmail" name="tbEmail" placeholder="Email">
                <button type="submit" class="btn btn-primary">Envoyer le mot de passe</button>
              <br>
            <div class="links">
                <a href="login.php">Page de connexion</a>
                <a href="signup.php">Créer un compte</a>
            </div>
        </form>
    </div>
    <br>
</body>
</html>