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
    header("Location: login.php");
    exit();
}

// Variables pour la pagination et les filtres
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10; // Annonces par page par défaut
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page - 1) * $limit;
$orderField = 'Parution'; // Tri par défaut
$orderDirection = isset($_GET['Ordre']) && $_GET['Ordre'] == 'DESC' ? 'DESC' : 'ASC';

$noUtilisateur = $_SESSION['NoUtilisateur'];

// Obtenir le nombre total d'annonces pour l'utilisateur
$result = $dbManager->getConnection()->query("
    SELECT COUNT(*) AS total
    FROM annonces
    WHERE NoUtilisateur = $noUtilisateur 
    AND Etat IN (1, 2)
");
$totalAnnonces = $result->fetch_assoc()['total'];

// Calculer le nombre total de pages
if ($limit > 0) {
    $totalPages = ceil($totalAnnonces / $limit);
} else {
    $totalPages = 1;
}

// Insertion d'une nouvelle annonce
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $categorie = trim($_POST['categorie']);
    $descriptionAbregee = trim($_POST['descriptionAbregee']);
    $descriptionComplete = trim($_POST['descriptionComplete']);
    $prix = trim($_POST['prix']);
    $etat = isset($_POST['etat']) ? $_POST['etat'] : 1;

    if (empty($errors)) {
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
            // Types de fichiers autorisés
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($_FILES['photo']['type'], $allowedTypes)) {
                $errors[] = "Le fichier doit être une image (JPEG, PNG ou GIF).";
            }
            if ($_FILES['photo']['size'] > 2000000) {
                $errors[] = "La taille du fichier ne doit pas dépasser 2MB.";
            }
            if (empty($errors)) {
                $photoName = $_FILES['photo']['name'];
                $photoPath = '../photos-annonce/' . basename($photoName);
                if (!is_dir('../photos-annonce')) {
                    mkdir('../photos-annonce', 0777, true);
                }
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
                    // Insertion dans la base de données
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

// Chargement des annonces pour l'utilisateur
$annonces = $dbManager->getConnection()->query("
    SELECT annonces.*, categories.Description as CategorieDesc 
    FROM annonces 
    JOIN categories ON annonces.Categorie = categories.NoCategorie 
    WHERE NoUtilisateur = $noUtilisateur 
    AND Etat IN (1, 2)
");
?>

<!DOCTYPE html>
<html lang="fr">

<?php require_once 'header.php'; ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Annonces</title>
    <link rel="stylesheet" href="../styles/gestionAnnonces_style.css?v=<?php echo time(); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <!-- Message de succès -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div id="success-message" class="alert alert-success">
            <?php echo $_SESSION['success_message']; ?>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <!-- Script pour cacher le message de succès après 3 secondes -->
    <script>
        setTimeout(function () {
            var successMessage = document.getElementById('success-message');
            if (successMessage) {
                successMessage.style.display = 'none';
            }
        }, 3000); 
    </script>

    <!-- Affichage des erreurs -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Gestion des annonces -->
    <div class="divGestion m-5">
        <div class="btn-container">
            <a href="AjoutAnnonce.php" class="btn btn-primary">Ajouter</a>
        </div>

        <table class="table table-hover border-0">
            <thead>
                <tr>
                    <th scope="col"></th>
                    <th scope="col" class="px-2">No</th>
                    <th scope="col">No Annonce</th>
                    <th scope="col">Description</th>
                    <th scope="col">Catégorie</th>
                    <th scope="col">Prix</th>
                    <th scope="col">Date de parution</th>
                    <th scope="col">État</th>
                    <th scope="col"></th>
                    <th scope="col"></th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody class="table-group-divider">
                <?php if ($annonces->num_rows > 0): ?>
                    <?php $index = 1; ?>
                    <?php while ($annonce = $annonces->fetch_assoc()): ?>
                        <tr class="border-bottom-only">
                            <td><img src="../photos-annonce/<?php echo $annonce['Photo']; ?>" alt="Photo annonce" width="50"
                                    height="50"></td>
                            <td><?php echo $index++; ?></td>
                            <td><?php echo $annonce['NoAnnonce']; ?></td>
                            <td>
                                <a href="annonceDetaille.php?NoAnnonce=<?php echo $annonce['NoAnnonce']; ?>"
                                    class="text-primary font-weight-normal">
                                    <?php echo $annonce['DescriptionAbregee']; ?>
                                </a>
                            </td>
                            <td><?php echo $annonce['CategorieDesc']; ?></td>
                            <td><?php echo $annonce['Prix'] ? $annonce['Prix'] . ' $' : 'N/A'; ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($annonce['Parution'])); ?></td>
                            <td><?php echo $annonce['Etat'] == 1 ? 'Actif' : 'Inactif'; ?></td>
                            <td><a href="modifierAnnonce.php?NoAnnonce=<?php echo $annonce['NoAnnonce']; ?>"
                                    class="btn btn-success btn-sm">Modification</a></td>
                            <td><a href="confirmationRetraitAnnonce.php?NoAnnonce=<?php echo $annonce['NoAnnonce']; ?>"
                                    class="btn btn-danger btn-sm">Retrait</a></td>
                            <td>
                                <form action="changerEtatAnnonce.php" method="POST">
                                    <input type="hidden" name="NoAnnonce" value="<?php echo $annonce['NoAnnonce']; ?>">
                                    <?php if ($annonce['Etat'] == 1): ?>
                                        <button type="submit" name="newEtat" value="2"
                                            class="btn btn-secondary btn-sm">Désactiver</button>
                                    <?php elseif ($annonce['Etat'] == 2): ?>
                                        <button type="submit" name="newEtat" value="1"
                                            class="btn btn-primary btn-sm">Activer</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" class="text-center">Aucune annonce trouvée.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php
    require_once '../librairies/librairies-communes-2018-03-16.php';
    $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $previousPage = $currentPage > 1 ? $currentPage - 1 : 1;
    $nextPage = $currentPage < $totalPages ? $currentPage + 1 : $totalPages;

    $currentSearch = isset($_GET['search']) ? $_GET['search'] : '';
    $currentTypeOrdre = isset($_GET['TypeOrdre']) ? $_GET['TypeOrdre'] : 'Date';
    $currentOrdre = isset($_GET['Ordre']) ? $_GET['Ordre'] : 'ASC';
    $currentDescription = isset($_GET['Description']) ? $_GET['Description'] : '';
    $currentAuteur = isset($_GET['Auteur']) ? $_GET['Auteur'] : '';
    $currentCategorie = isset($_GET['Categorie']) ? $_GET['Categorie'] : '';
    $currentDateDebut = isset($_GET['DateDebut']) ? $_GET['DateDebut'] : '';
    $currentDateFin = isset($_GET['DateFin']) ? $_GET['DateFin'] : '';
    renderPagination($currentPage, $totalPages, $limit, $currentTypeOrdre, $currentOrdre, $currentDescription, $currentAuteur, $currentCategorie, $currentDateDebut, $currentDateFin);
    ?>
</body>

</html>