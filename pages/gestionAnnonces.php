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

// Vérification si l'utilisateur est authentifié
if (!isset($_SESSION['NoUtilisateur'])) {
    // Redirection vers la page de connexion si l'utilisateur n'est pas authentifié
    header("Location: login.php");
    exit();
}

$noUtilisateur = $_SESSION['NoUtilisateur'];

// Insertion d'une nouvelle annonce
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $categorie = trim($_POST['categorie']);
    $descriptionAbregee = trim($_POST['descriptionAbregee']);
    $descriptionComplete = trim($_POST['descriptionComplete']);
    $prix = trim($_POST['prix']);
    $etat = isset($_POST['etat']) ? $_POST['etat'] : 1;

    // Validation des champs obligatoires
    if (empty($categorie) || empty($descriptionAbregee) || empty($descriptionComplete)) {
        $errors[] = "Tous les champs doivent être remplis.";
    }

    if (empty($prix)) {
        $prix = null;
    } else {
        $prix = floatval($prix);
    }

    // Vérification si la catégorie existe
    $categorieResult = $dbManager->getConnection()->query("SELECT NoCategorie FROM categories WHERE NoCategorie = $categorie");

    if ($categorieResult->num_rows === 0) {
        $errors[] = "La catégorie sélectionnée est invalide.";
    }

    // Si aucune erreur n'est détectée
    if (empty($errors)) {
        // Vérification de l'image téléchargée
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($_FILES['photo']['type'], $allowedTypes)) {
                $errors[] = "Le fichier doit être une image (JPEG, PNG ou GIF).";
            }

            if ($_FILES['photo']['size'] > 2000000) {
                $errors[] = "La taille du fichier ne doit pas dépasser 2MB.";
            }

            // Si aucune erreur dans l'image
            if (empty($errors)) {
                $photoName = $_FILES['photo']['name'];
                $photoPath = '../photos-annonce/' . basename($photoName);

                // Création du répertoire s'il n'existe pas
                if (!is_dir('../photos-annonce')) {
                    mkdir('../photos-annonce', 0777, true);
                }

                // Déplacement de l'image vers le répertoire spécifié
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
                    // Insertion de l'annonce dans la base de données
                    $stmt = $dbManager->getConnection()->prepare("
                        INSERT INTO annonces (NoUtilisateur, Categorie, DescriptionAbregee, DescriptionComplete, Prix, Photo, Etat, Parution) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
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

// Chargement de toutes les annonces de l'utilisateur actuel
$annonces = $dbManager->getConnection()->query("SELECT annonces.*, categories.Description as CategorieDesc FROM annonces 
JOIN categories ON annonces.Categorie = categories.NoCategorie 
WHERE NoUtilisateur = $noUtilisateur");
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des annonces</title>
    <link rel="stylesheet" href="../styles/ajouteAnnonces_style.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #ccc;
        }

        th,
        td {
            padding: 10px;
            text-align: center;
        }

        img {
            width: 144px;
            height: auto;
        }

        .btn {
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-green {
            background-color: #28a745;
            color: white;
        }

        .btn-red {
            background-color: #dc3545;
            color: white;
        }

        .btn-gray {
            background-color: #6c757d;
            color: white;
        }

        .btn:hover {
            opacity: 0.8;
        }

        /* Estilo para que el botón "Ajouter" esté alineado a la derecha */
        .btn-container {
            text-align: right;
            margin-bottom: 40px;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="divGestion">
        <!-- Botón "Ajouter" alineado a la derecha -->
        <div class="btn-container">
            <a href="AjoutAnnonce.php" class="btn btn-primary">Ajouter</a>
        </div>

        <!-- Tableau pour afficher les annonces -->
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th>No</th>
                    <th>No Annonce</th>
                    <th>Description</th>
                    <th>Catégorie</th>
                    <th>Prix</th>
                    <th>Date de parution</th>
                    <th>État</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($annonces->num_rows > 0): ?>
                    <?php $index = 1; ?>
                    <?php while ($annonce = $annonces->fetch_assoc()): ?>
                        <tr>
                            <td><img src="../photos-annonce/<?php echo $annonce['Photo']; ?>" alt="Photo annonce"></td>
                            <td><?php echo $index++; ?></td>
                            <td><?php echo $annonce['NoAnnonce']; ?></td>
                            <td><?php echo $annonce['DescriptionAbregee']; ?></td>
                            <td><?php echo $annonce['CategorieDesc']; ?></td>
                            <td><?php echo $annonce['Prix'] ? $annonce['Prix'] . ' €' : 'N/A'; ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($annonce['Parution'])); ?></td>
                            <td><?php echo $annonce['Etat'] == 1 ? 'Actif' : 'Inactif'; ?></td>
                            <td>
                                <button class="btn btn-green">Modification</button>
                            </td>
                            <td>
                                <button class="btn btn-red">Retrait</button>
                            </td>
                            <td>
                                <button class="btn btn-gray">Désactiver</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">Aucune annonce trouvée.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>