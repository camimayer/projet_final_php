<?php
session_start();
require_once '../databasemanager.php';
require_once '../config/localhost.php';

$dbManager = new DatabaseManager();

// Vérification si l'utilisateur est authentifié
if (!isset($_SESSION['Courriel'])) {
    header("Location: login.php");
    exit();
}

// Variables pour la pagination et les filtres
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10; // Nombre d'annonces par page par défaut
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page - 1) * $limit;
$orderField = 'Parution'; // Valeur par défaut pour le tri
$orderDirection = isset($_GET['Ordre']) && $_GET['Ordre'] == 'DESC' ? 'DESC' : 'ASC';

// Gestion du tri (date, auteur ou catégorie)
if (isset($_GET['TypeOrdre'])) {
    switch ($_GET['TypeOrdre']) {
        case 'Date':
            $orderField = 'Parution';
            break;
        case 'Auteur':
            $orderField = "CONCAT(utilisateurs.Nom, ' ', utilisateurs.Prenom)"; // Trier par auteur alphabétiquement
            break;
        case 'Categorie':
            $orderField = 'categories.Description'; // Trier par catégorie alphabétiquement
            break;
        default:
            $orderField = 'Parution'; // Valeur par défaut si TypeOrdre n'est pas défini
            break;
    }
}
$order = "$orderField $orderDirection";

// Recherche
$searchConditions = [];

// Initialisation des variables
$result = null;
$totalAnnonces = 0;

// Obtenir la date actuelle comme DateTime
$currentDate = new DateTime(); // Date actuelle

// Vérifier si DateDebut ou DateFin sont présents dans le formulaire
$dateDebut = isset($_GET['DateDebut']) && !empty($_GET['DateDebut']) ? DateTime::createFromFormat('Y-m-d', $_GET['DateDebut']) : null;
$dateFin = isset($_GET['DateFin']) && !empty($_GET['DateFin']) ? DateTime::createFromFormat('Y-m-d', $_GET['DateFin']) : $currentDate;

// Variable pour valider si les dates sont correctes
$dateValide = true;

// Validation des dates
if ($dateDebut && $dateFin) {
    // Vérifier que les dates ne sont pas invalides (début après fin ou dates futures)
    if ($dateDebut > $dateFin || $dateDebut > $currentDate || $dateFin > $currentDate) {
        $dateValide = false; // Dates non valides
    } else {
        $dateFin->modify('+1 day');
        // Ajouter la condition pour la requête SQL
        $searchConditions[] = "Parution >= '" . $dateDebut->format('Y-m-d') . "' AND Parution <= '" . $dateFin->format('Y-m-d') . "'";
    }
} elseif (!$dateDebut && $dateFin && $dateFin > $currentDate) {
    // Si aucune date de début et la date de fin est future
    $dateValide = false;
    echo "<p class='text-warning'>Les dates fournies ne sont pas valides.</p>";
}

// Effectuer la requête SQL seulement si les dates sont valides
if (!empty($searchConditions)) {
    // Combiner toutes les conditions de recherche
    $searchQuery = implode(' AND ', $searchConditions);
    if ($searchQuery != '') {
        $searchQuery = " AND ($searchQuery)";
    }

    // Requête pour compter le nombre total d'annonces
    $totalResult = $dbManager->getConnection()->query("
        SELECT COUNT(*) AS total
        FROM annonces
        JOIN utilisateurs ON annonces.NoUtilisateur = utilisateurs.NoUtilisateur
        JOIN categories ON annonces.Categorie = categories.NoCategorie
        WHERE Etat = 1 $searchQuery
    ");

    // Si la requête est valide, obtenir le nombre total d'annonces
    if ($totalResult) {
        $totalAnnonces = $totalResult->fetch_assoc()['total'];

        // Effectuer la requête pour obtenir les annonces si des résultats existent
        if ($totalAnnonces > 0) {
            $query = "SELECT annonces.*, utilisateurs.Nom, utilisateurs.Prenom, utilisateurs.Courriel, categories.Description AS CategorieDescription
                      FROM annonces 
                      JOIN utilisateurs ON annonces.NoUtilisateur = utilisateurs.NoUtilisateur 
                      JOIN categories ON annonces.Categorie = categories.NoCategorie
                      WHERE Etat = 1 $searchQuery 
                      ORDER BY $order 
                      LIMIT $start, $limit";

            $result = $dbManager->getConnection()->query($query);
        }
    }
}

// Afficher les annonces seulement s'il y a des résultats
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
    }
}

// Filtrer par auteur (Nom ou Prénom)
if (isset($_GET['Auteur']) && $_GET['Auteur'] != '') {
    $auteur = $dbManager->getConnection()->real_escape_string($_GET['Auteur']);
    $searchConditions[] = "(utilisateurs.Nom LIKE '%$auteur%' OR utilisateurs.Prenom LIKE '%$auteur%')";
}

// Filtrer par catégorie
if (isset($_GET['Categorie']) && $_GET['Categorie'] != '') {
    $categorie = (int) $_GET['Categorie'];
    $searchConditions[] = "annonces.Categorie = $categorie";
}

// Filtrer par description
if (isset($_GET['Description']) && $_GET['Description'] != '') {
    $description = $dbManager->getConnection()->real_escape_string($_GET['Description']);
    $searchConditions[] = "(DescriptionAbregee LIKE '%$description%' OR DescriptionComplete LIKE '%$description%')";
}

// Combiner toutes les conditions de recherche
$searchQuery = implode(' AND ', $searchConditions);
if ($searchQuery != '') {
    $searchQuery = " AND ($searchQuery)";
}

// Compter le nombre total d'annonces actives avec les filtres
$totalResult = $dbManager->getConnection()->query("
    SELECT COUNT(*) AS total
    FROM annonces
    JOIN utilisateurs ON annonces.NoUtilisateur = utilisateurs.NoUtilisateur
    JOIN categories ON annonces.Categorie = categories.NoCategorie
    WHERE Etat = 1 $searchQuery
");
$totalAnnonces = $totalResult->fetch_assoc()['total'];

// Requête SQL pour obtenir les annonces avec la description de la catégorie
$query = "SELECT annonces.*, utilisateurs.Nom, utilisateurs.Prenom, utilisateurs.Courriel, categories.Description AS CategorieDescription
          FROM annonces 
          JOIN utilisateurs ON annonces.NoUtilisateur = utilisateurs.NoUtilisateur 
          JOIN categories ON annonces.Categorie = categories.NoCategorie
          WHERE Etat = 1 $searchQuery 
          ORDER BY $order 
          LIMIT $start, $limit";

$result = $dbManager->getConnection()->query($query);
$totalPages = ceil($totalAnnonces / $limit);
$currentPage = $page;

// Initialisation des variables pour éviter les erreurs
$currentTypeOrdre = isset($_GET['TypeOrdre']) ? $_GET['TypeOrdre'] : 'Date';  // Valeur par défaut
$currentOrdre = isset($_GET['Ordre']) ? $_GET['Ordre'] : 'ASC';  // Valeur par défaut
$currentDescription = isset($_GET['Description']) ? $_GET['Description'] : '';
$currentAuteur = isset($_GET['Auteur']) ? $_GET['Auteur'] : '';
$currentCategorie = isset($_GET['Categorie']) ? $_GET['Categorie'] : '';
$currentDateDebut = isset($_GET['DateDebut']) ? $_GET['DateDebut'] : '';
$currentDateFin = isset($_GET['DateFin']) ? $_GET['DateFin'] : '';
?>

<!DOCTYPE html>
<html lang="fr">
<?php require_once 'header.php'; ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des annonces</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="../styles/listeAnnonces_style.css" rel="stylesheet">
</head>

<body>
    <div id="divPanel" class="d-flex justify-content-between mx-5 mt-2">
        <!-- Formulaire de recherche et filtres -->
        <form method="GET" action="listeAnnonces.php" id="frmRecherche" class="d-flex w-100">
            <div class="d-flex flex-wrap row w-100">
                <!-- Colonne gauche : Total d'annonces et nombre d'éléments par page -->
                <div class="col-md-6 pe-0">
                    <!-- Champs cachés pour conserver les valeurs lors de la pagination -->
                    <input type="hidden" name="page" value="<?php echo $page; ?>">
                    <input type="hidden" name="TypeOrdre" value="<?php echo $currentTypeOrdre; ?>">
                    <input type="hidden" name="Ordre" value="<?php echo $currentOrdre; ?>">
                    <input type="hidden" name="Description" value="<?php echo $currentDescription; ?>">
                    <input type="hidden" name="Auteur" value="<?php echo $currentAuteur; ?>">
                    <input type="hidden" name="Categorie" value="<?php echo $currentCategorie; ?>">
                    <input type="hidden" name="DateDebut" value="<?php echo $currentDateDebut; ?>">
                    <input type="hidden" name="DateFin" value="<?php echo $currentDateFin; ?>">
                    <input type="hidden" id="hiddenLimit" name="limit" value="<?php echo $limit; ?>">

                    <!-- Dropdown pour le nombre d'éléments par page -->
                    <div class="form-group d-inline-flex align-items-center me-2 mt-4">
                        <label for="ddlNbParPage" class="col-form-label">Éléments par page:</label>
                        <select id="ddlNbParPage" class="form-control form-control-sm w-auto mx-2 px-2"
                            onchange="updateLimitAndSubmit()">
                            <option value="5" <?php echo $limit == 5 ? 'selected' : ''; ?>>5</option>
                            <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="15" <?php echo $limit == 15 ? 'selected' : ''; ?>>15</option>
                            <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20</option>
                        </select>
                    </div>
                    <h5 class="text-secondary font-italic"><?php echo $totalAnnonces . " annonces trouvées."; ?></h5>
                </div>

                <!-- Colonne droite : Filtres de recherche -->
                <div class="col-md-6">
                    <div class="d-flex flex-row align-items-center mt-5">
                        <!-- Section d'ordre -->
                        <div class="d-flex align-items-stretch mx-4 mt-2">
                            <label for="TypeOrdre" class="col-form-label">Ordre:</label>
                            <select class="form-control form-control-sm mx-2" id="TypeOrdre" name="TypeOrdre"
                                onchange="submitForm()">
                                <option value="Date" <?php echo $currentTypeOrdre == 'Date' ? 'selected' : ''; ?>>Date
                                </option>
                                <option value="Auteur" <?php echo $currentTypeOrdre == 'Auteur' ? 'selected' : ''; ?>>
                                    Auteur</option>
                                <option value="Categorie" <?php echo $currentTypeOrdre == 'Categorie' ? 'selected' : ''; ?>>Catégorie</option>
                            </select>
                            <select name="Ordre" id="Ordre" class="form-control form-control-sm w-auto"
                                onchange="submitOrdreChange()">
                                <option value="ASC" <?php echo $orderDirection === 'ASC' ? 'selected' : ''; ?>>&#9650;
                                    Ascendant</option>
                                <option value="DESC" <?php echo $orderDirection === 'DESC' ? 'selected' : ''; ?>>
                                    &#9660;
                                    Descendant</option>
                            </select>
                        </div>

                        <!-- Section de recherche -->
                        <div class="d-flex align-items-center justify-content-end mx-2">
                            <input class="form-control form-control-sm me-0" type="text" id="Description"
                                name="Description" placeholder="Rechercher..."
                                value="<?php echo htmlspecialchars($currentDescription); ?>">

                            <input class="btn btn-primary btn-sm mx-2" type="submit" value="Rechercher">

                            <button id="btnAfficherAvance" type="button"
                                class="btn btn-secondary btn-sm font-weight-bold">+</button>
                        </div>
                    </div>

                    <!-- Formulaire de recherche avancée -->
                    <div id="divRechercheAvancé" class="mt-3 border p-3" style="display: none;">
                        <div class="form-group row">
                            <label for="Auteur" class="col-sm-3 col-form-label">Auteur</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control form-control-sm" id="Auteur" name="Auteur"
                                    value="<?php echo htmlspecialchars($currentAuteur); ?>">
                            </div>
                        </div>

                        <div class="form-group row mt-3">
                            <label for="Categorie" class="col-sm-3 col-form-label">Catégorie</label>
                            <div class="col-sm-9">
                                <select class="form-control form-control-sm" id="Categorie" name="Categorie">
                                    <option value="">Toutes</option>
                                    <option value="1" <?php echo $currentCategorie == 1 ? 'selected' : ''; ?>>Location
                                    </option>
                                    <option value="2" <?php echo $currentCategorie == 2 ? 'selected' : ''; ?>>Recherche
                                    </option>
                                    <option value="3" <?php echo $currentCategorie == 3 ? 'selected' : ''; ?>>À vendre
                                    </option>
                                    <option value="4" <?php echo $currentCategorie == 4 ? 'selected' : ''; ?>>À donner
                                    </option>
                                    <option value="5" <?php echo $currentCategorie == 5 ? 'selected' : ''; ?>>Service
                                        offert</option>
                                    <option value="6" <?php echo $currentCategorie == 6 ? 'selected' : ''; ?>>Autre
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row mt-3">
                            <label class="col-sm-3 col-form-label">Date:</label>
                            <div class="col-sm-4">
                                <input type="date" class="form-control form-control-sm" id="DateDebut" name="DateDebut"
                                    value="<?php echo $currentDateDebut; ?>">
                            </div>
                            <div class="col-sm-1 text-center">à</div>
                            <div class="col-sm-4">
                                <input type="date" class="form-control form-control-sm" id="DateFin" name="DateFin"
                                    value="<?php echo $currentDateFin; ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <hr>

    <!-- Conteneur des cartes -->
    <div class="d-flex flex-wrap justify-content-around mt-2 border-secondary">
        <?php if ($dateValide && $result && $result->num_rows > 0): ?>
            <?php
            // Initialisation d'un compteur pour le numéro séquentiel
            $sequentialNumber = ($page - 1) * $limit + 1;
            ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div id="divAnnonce-<?php echo $row['NoAnnonce']; ?>" class="col-12 col-md-6 col-lg-3 mb-3">
                    <div class="card annonce">
                        <div class="card-header d-flex justify-content-between py-1">
                            <!-- Afficher le numéro de l'annonce avec # -->
                            <div class="text-left">
                                <strong>#<?php echo $row['NoAnnonce']; ?></strong>
                            </div>
                            <!-- Afficher la catégorie -->
                            <div class="text-right"><?php echo $row['CategorieDescription']; ?></div>
                        </div>
                        <div class="overflow-hidden text-center imageSize">
                            <img src="../photos-annonce/<?php echo $row['Photo']; ?>" alt="Photo annonce" width="300"
                                height="280">
                        </div>
                        <div class="card-body pb-1">
                            <!-- Afficher la petite description comme un texte sous l'image. -->
                            <p class="d-flex justify-content-between text-decoration-none">
                                <a href="annonceDetaille.php?NoAnnonce=<?php echo $row['NoAnnonce']; ?>" ;
                                    class="text-primary font-weight-bold">
                                    <?php echo $row['DescriptionAbregee']; ?>
                                </a>
                            </p>
                            <!-- Afficher le nom et le prénom de l'utilisateur sous la petite description -->
                            <p class="d-flex justify-content-between text-decoration-none">
                                <a href="mailto:<?php echo $row['Courriel']; ?>" class="text-secondary">
                                    <?php echo (!empty($row['Nom']) && !empty($row['Prenom'])) ? $row['Nom'] . ', ' . $row['Prenom'] : (!empty($row['Nom']) ? $row['Nom'] : $row['Prenom']); ?>
                                </a>
                            </p>
                            <div class="d-flex justify-content-between">
                                <div class="text-left"></div>
                                <div class="text-right font-weight-bold">
                                    <!-- Afficher le prix  -->
                                    <span><?php echo $row['Prix'] ? number_format($row['Prix'], 2, ',', ' ') . ' $' : 'N/A'; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between py-0">
                            <!-- Afficher la date de publication -->
                            <div class="text-left"><?php echo $row['Parution']; ?></div>
                            <div class="text-right font-italic"><?php echo $sequentialNumber; ?></div>
                        </div>
                    </div>
                </div>
                <?php $sequentialNumber++; // Incrémenter le numéro séquentiel ?>
            <?php endwhile; ?>
        <?php else: ?>
            <h5>Aucune annonce trouvée.</h5>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php
    // Appeler la fonction renderPagination()
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

    <script>
        // Met à jour le nombre d'éléments par page et soumet le formulaire
        function updateLimitAndSubmit() {
            var ddlNbParPage = document.getElementById("ddlNbParPage");
            var limit = ddlNbParPage.value;
            document.getElementById("hiddenLimit").value = limit;
            document.getElementById("frmRecherche").submit();
        }

        // Affiche ou masque la recherche avancée
        document.getElementById('btnAfficherAvance').addEventListener('click', function () {
            var advancedSearch = document.getElementById('divRechercheAvancé');
            var btn = document.getElementById('btnAfficherAvance');

            if (advancedSearch.style.display === "none" || advancedSearch.style.display === "") {
                advancedSearch.style.display = "block";
                advancedSearch.classList.add('show');
                btn.textContent = '-';
            } else {
                advancedSearch.style.display = "none";
                advancedSearch.classList.remove('show');
                btn.textContent = '+';
            }
        });

        // Soumet le formulaire lors du changement de l'ordre
        function submitOrdreChange() {
            document.getElementById('frmRecherche').submit();
        }

        // Met à jour l'icône d'ordre
        function updateOrdreIcon() {
            var ordreSelect = document.getElementById('Ordre');
            var selectedValue = ordreSelect.value;

            if (selectedValue === 'ASC') {
                ordreSelect.options[0].text = '▲ Ascendante';
                ordreSelect.options[1].text = '▼ Descendante';
            } else {
                ordreSelect.options[0].text = '▲ Ascendante';
                ordreSelect.options[1].text = '▼ Descendante';
            }
        }

        // Soumet le formulaire
        function submitForm() {
            document.getElementById("frmRecherche").submit();
        }

        // Met à jour les icônes d'ordre au chargement de la page
        window.onload = function () {
            updateOrdreIcon();
        };

        document.getElementById("TypeOrdre").addEventListener("change", submitForm);
        document.getElementById("Ordre").addEventListener("change", submitForm);
    </script>
</body>

</html>

<?php
$dbManager->closeConnection();
?>