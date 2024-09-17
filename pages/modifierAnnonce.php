<?php
session_start();
require_once '../databasemanager.php'; // Inclure la connexion à la base de données
require_once '../config/localhost.php';

$dbManager = new DatabaseManager();
$errors = [];
$success = false;

// Vérifier si l'utilisateur est authentifié
if (!isset($_SESSION['NoUtilisateur'])) {
    header("Location: login.php");
    exit();
}

$noUtilisateur = $_SESSION['NoUtilisateur'];

// Récupérer l'annonce à modifier
if (isset($_GET['NoAnnonce'])) {
    $noAnnonce = $_GET['NoAnnonce'];
    $annonce = $dbManager->getConnection()->query("SELECT * FROM annonces WHERE NoAnnonce = $noAnnonce AND NoUtilisateur = $noUtilisateur")->fetch_assoc();

    if (!$annonce) {
        $errors[] = "Annonce introuvable ou non autorisée.";
    }
} else {
    $errors[] = "Aucune annonce sélectionnée.";
}

// Traitement de la mise à jour de l'annonce
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $categorie = trim($_POST['categorie']);
    $descriptionAbregee = trim($_POST['descriptionAbregee']);
    $descriptionComplete = trim($_POST['descriptionComplete']);
    $prix = trim($_POST['prix']);
    $etat = isset($_POST['etat']) ? $_POST['etat'] : 1;
    if (!in_array($etat, [1, 2, 3])) {
        $errors[] = "Le champ 'Etat' doit être 1, 2 ou 3.";
    }

    // Si aucune erreur, continuer avec la mise à jour de l'annonce
    if (empty($errors)) {
        // Mise à jour des informations de l'annonce dans la base de données
        $stmt = $dbManager->getConnection()->prepare("
        UPDATE annonces 
        SET Categorie = ?, DescriptionAbregee = ?, DescriptionComplete = ?, Prix = ?, Etat = ?
        WHERE NoAnnonce = ? AND NoUtilisateur = ?
    ");
        $stmt->bind_param("sssiiii", $categorie, $descriptionAbregee, $descriptionComplete, $prix, $etat, $noAnnonce, $noUtilisateur);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "Une erreur est survenue lors de la mise à jour de l'annonce.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modification d'une annonce</title>
    <link rel="stylesheet" href="../styles/modifierAnnonce_style.css">
    <style>
        /* Estilos para el toast */
        .toast {
            visibility: hidden;
            min-width: 250px;
            margin-left: -125px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 5px;
            padding: 16px;
            position: fixed;
            z-index: 1;
            left: 50%;
            bottom: 30px;
            font-size: 17px;
        }

        .toast.show {
            visibility: visible;
            -webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
            animation: fadein 0.5s, fadeout 0.5s 2.5s;
        }

        @-webkit-keyframes fadein {
            from {
                bottom: 0;
                opacity: 0;
            }

            to {
                bottom: 30px;
                opacity: 1;
            }
        }

        @keyframes fadein {
            from {
                bottom: 0;
                opacity: 0;
            }

            to {
                bottom: 30px;
                opacity: 1;
            }
        }

        @-webkit-keyframes fadeout {
            from {
                bottom: 30px;
                opacity: 1;
            }

            to {
                bottom: 0;
                opacity: 0;
            }
        }

        @keyframes fadeout {
            from {
                bottom: 30px;
                opacity: 1;
            }

            to {
                bottom: 0;
                opacity: 0;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Modification de l'annonce</h1>

        <!-- Affichage des erreurs -->
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Affichage du message de succès et redirection -->
        <?php if ($success): ?>
            <div id="toast" class="toast">Annonce mise à jour avec succès !</div>
            <script>
                function montrerToast() {
                    var toast = document.getElementById("toast");
                    toast.className = "toast show";
                    setTimeout(function () {
                        toast.className = toast.className.replace("show", "");
                        window.location.href = 'gestionAnnonces.php';
                    }, 3000);
                }

                // Mostrar el toast
                montrerToast();
            </script>
        <?php else: ?>
            <!-- Formulaire de modification -->
            <form action="modifierAnnonce.php?NoAnnonce=<?php echo $noAnnonce; ?>" method="post"
                enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="categorie">Catégorie :</label>
                        <select name="categorie" id="categorie" required>
                            <option value="" 0>Selectionner une catégorie</option>
                            <option value="1" <?php echo $annonce['Categorie'] == 1 ? 'selected' : ''; ?>>Location</option>
                            <option value="2" <?php echo $annonce['Categorie'] == 2 ? 'selected' : ''; ?>>Recherche</option>
                            <option value="3" <?php echo $annonce['Categorie'] == 3 ? 'selected' : ''; ?>>À vendre</option>
                            <option value="4" <?php echo $annonce['Categorie'] == 4 ? 'selected' : ''; ?>>À donner</option>
                            <option value="5" <?php echo $annonce['Categorie'] == 5 ? 'selected' : ''; ?>>Service offert
                            </option>
                            <option value="6" <?php echo $annonce['Categorie'] == 6 ? 'selected' : ''; ?>>Autre</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="descriptionAbregee">Petite description :</label>
                        <input type="text" name="descriptionAbregee" id="descriptionAbregee"
                            value="<?php echo $annonce['DescriptionAbregee']; ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="descriptionComplete">Description complète :</label>
                        <textarea name="descriptionComplete" id="descriptionComplete"
                            required><?php echo $annonce['DescriptionComplete']; ?></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="prix">Prix :</label>
                        <input type="number" name="prix" id="prix" value="<?php echo $annonce['Prix']; ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="etat">État :</label>
                        <select name="etat" id="etat" required>
                            <option value="1" <?php echo $annonce['Etat'] == 1 ? 'selected' : ''; ?>>Actif</option>
                            <option value="2" <?php echo $annonce['Etat'] == 2 ? 'selected' : ''; ?>>Inactif</option>
                        </select>
                    </div>
                </div>

                <input type="submit" value="Mettre à jour" class="btn btn-primary col-md-12">
                <div id="divEnvoie">
                    <a href="gestionAnnonces.php">Annuler la modification</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>