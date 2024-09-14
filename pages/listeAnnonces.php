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
            $orderField = 'utilisateurs.Nom, utilisateurs.Prenom'; // Ordenar por autor alfabéticamente
            break;
        case 'Categorie':
            $orderField = 'categories.Description'; // Ordenar por categoría alfabéticamente
            break;
        default:
            $orderField = 'Parution'; // Valeur par défaut si TypeOrdre n'est pas défini
            break;
    }
}
$order = "$orderField $orderDirection";

// Recherche
$searchConditions = [];

// Filtrer par date
if (!empty($_GET['DateDebut']) && !empty($_GET['DateFin'])) {
    $dateDebut = $dbManager->getConnection()->real_escape_string($_GET['DateDebut']);
    $dateFin = $dbManager->getConnection()->real_escape_string($_GET['DateFin']);

    // Vérifier que les dates sont valides et en format YYYY-MM-DD
    if (preg_match("/^\d{4}-\d{2}-\d{2}$/", $dateDebut) && preg_match("/^\d{4}-\d{2}-\d{2}$/", $dateFin)) {
        // Filtrer les annonces par la plage de dates
        $searchConditions[] = "Parution >= '$dateDebut' AND Parution <= '$dateFin'";
    } else {
        // Message en cas de date invalide
        echo "Format de date invalide.";
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
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles/listeAnnonces_style.css" rel="stylesheet">
</head>

<body>
    <div id="divPanel" class="d-flex justify-content-between mx-5 mt-2">
        <form method="GET" action="listeAnnonces.php" id="frmRecherche" class="d-flex flex-column">
            <!-- Champs cachés pour conserver les valeurs pendant la pagination. -->
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
            <div id="divNbParPage" class="ml-3 text-left flex-fill">
                <div class="d-inline-flex" style="width: 100%">
                    <label for="ddlNbParPage" class="col-form-label mr-2">Éléments par page:</label>
                    <select id="ddlNbParPage" class="form-control form-control-sm" style="width: 70px;"
                        onchange="updateLimitAndSubmit()">
                        <option value="5" <?php echo $limit == 5 ? 'selected' : ''; ?>>5</option>
                        <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                        <option value="15" <?php echo $limit == 15 ? 'selected' : ''; ?>>15</option>
                        <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20</option>
                    </select>
                </div>
                <h5 class="text-secondary font-italic"><?php echo $totalAnnonces . " annonces trouvées."; ?></h5>
            </div>

            <!-- Autres filtres de recherche... -->
            <div class="d-flex flex-column align-items-end">
                <div id="divRechercheSimple" class="d-flex align-items-left">
                    <div class="form-group d-inline-flex my-0">
                        <label for="TypeOrdre" class="col-form-label mr-2  ms-2">Ordre :</label>
                        <div class="my-auto">
                            <select class="form-control form-control-sm" id="TypeOrdre" name="TypeOrdre"
                                onchange="submitForm()">
                                <option value="Date" <?php echo $currentTypeOrdre == 'Date' ? 'selected' : ''; ?>>Date
                                </option>
                                <option value="Auteur" <?php echo $currentTypeOrdre == 'Auteur' ? 'selected' : ''; ?>>
                                    Auteur
                                </option>
                                <option value="Categorie" <?php echo $currentTypeOrdre == 'Categorie' ? 'selected' : ''; ?>>
                                    Catégorie</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group d-inline-flex mx-2 me-4 my-0">
                        <div class="my-auto me-2">
                            <select name="Ordre" id="Ordre" class="form-control form-control-sm"
                                onchange="submitOrdreChange()">
                                <option value="ASC" <?php echo $orderDirection === 'ASC' ? 'selected' : ''; ?>>&#9650;
                                    Ascendant</option>
                                <option value="DESC" <?php echo $orderDirection === 'DESC' ? 'selected' : ''; ?>>&#9660;
                                    Descendant</option>
                            </select>
                        </div>
                    </div>
                    <br>
                    <br>
                    <div class="form-group d-flex flex-column my-0 ms-2">
                        <div class="my-auto">
                            <input class="form-control form-control-sm" type="text" id="Description" name="Description"
                                value="<?php echo htmlspecialchars($currentDescription); ?>">
                        </div>
                    </div>

                    <input class="btn btn-primary btn-sm mx-2" type="submit" value="Rechercher">
                    <button id="btnAfficherAvance" type="button"
                        class="btn btn-secondary btn-sm font-weight-bold">+</button>
                </div>
            </div>

            <!-- Formulaire de recherche avancée -->
            <div id="divRechercheAvancé" class="col-12 mt-2 border pt-2 pr-5" style="display: none;">
                <div class="form-group row">
                    <label for="Auteur" class="col-3">Auteur</label>
                    <input type="text" class="col form-control form-control-sm" id="Auteur" name="Auteur"
                        value="<?php echo htmlspecialchars($currentAuteur); ?>">
                </div>
                <div class="form-group row">
                    <label class="col-3">Catégorie :</label>
                    <select class="col form-control form-control-sm" id="Categorie" name="Categorie">
                        <option value="">Toutes</option>
                        <option value="1" <?php echo $currentCategorie == 1 ? 'selected' : ''; ?>>Location</option>
                        <option value="2" <?php echo $currentCategorie == 2 ? 'selected' : ''; ?>>Recherche</option>
                        <option value="3" <?php echo $currentCategorie == 3 ? 'selected' : ''; ?>>À vendre</option>
                        <option value="4" <?php echo $currentCategorie == 4 ? 'selected' : ''; ?>>À donner</option>
                        <option value="5" <?php echo $currentCategorie == 5 ? 'selected' : ''; ?>>Service offert</option>
                        <option value="6" <?php echo $currentCategorie == 6 ? 'selected' : ''; ?>>Autre</option>
                    </select>
                </div>
                <div class="form-group row">
                    <label class="col-3">Date:</label>
                    <input type="date" class="col form-control form-control-sm mx-1" id="DateDebut" name="DateDebut"
                        value="<?php echo $currentDateDebut; ?>">
                    <p class="p-0 m-auto">à</p>
                    <input type="date" class="col form-control form-control-sm mx-1" id="DateFin" name="DateFin"
                        value="<?php echo $currentDateFin; ?>">
                </div>
            </div>
        </form>
    </div>

    <hr>

    <!-- Conteneur des cartes -->
    <div class="d-flex flex-wrap justify-content-around mt-2 border-secondary">
        <?php if ($result->num_rows > 0): ?>
            <?php
            // Iniciar un contador para el número secuencial
            $sequentialNumber = ($page - 1) * $limit + 1;
            ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div id="divAnnonce-<?php echo $row['NoAnnonce']; ?>" class="col-12 col-md-6 col-lg-3 mb-3">
                    <div class="card annonce">
                        <div class="card-header d-flex justify-content-between py-1">
                            <!-- Mostrar el número de anuncio con # -->
                            <div class="text-left">
                                <strong>#<?php echo $row['NoAnnonce']; ?></strong>
                            </div>
                            <!-- Mostrar la categoría -->
                            <div class="text-right"><?php echo $row['CategorieDescription']; ?></div>
                        </div>
                        <div class="overflow-hidden text-center imageSize">
                            <img src="../photos-annonce/<?php echo $row['Photo']; ?>" alt="Photo annonce" width="300"
                                height="280">
                        </div>
                        <div class="card-body pb-1">
                            <!-- Afficher la petite description comme un texte sous l'image. -->
                            <p class="d-flex justify-content-between">
                                <a href="detailsAnnonce.php?NoAnnonce=<?php echo $row['NoAnnonce']; ?>" ;
                                    class="text-primary font-weight-bold">
                                    <?php echo $row['DescriptionAbregee']; ?>
                                </a>
                            </p>
                            <!-- Afficher le nom et le prénom de l'utilisateur sous la petite description -->
                            <p class="d-flex justify-content-between">
                                <a href="mailto:<?php echo $row['Courriel']; ?>" class="text-secondary">
                                    <?php echo $row['Courriel'] . ' ' . $row['Prenom'] . ' ' . $row['Nom']; ?>
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
                <?php $sequentialNumber++; // Incremente le numero secuenciel ?>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Aucune annonce trouvée.</p>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <div id="divPages" class="m-auto text-center">
        <!-- Botons de la première page et de la page precédente -->
        <?php
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

        // Première page
        echo '<a class="h3 p-1" href="/projet_final_php/pages/listeAnnonces.php?page=1&limit=' . $limit . '&TypeOrdre=' . $currentTypeOrdre . '&Ordre=' . $currentOrdre . '&Description=' . $currentDescription . '&Auteur=' . $currentAuteur . '&Categorie=' . $currentCategorie . '&DateDebut=' . $currentDateDebut . '&DateFin=' . $currentDateFin . '">«</a>';

        // Page précédente
        echo '<a class="h3 p-1" href="/projet_final_php/pages/listeAnnonces.php?page=' . $previousPage . '&limit=' . $limit . '&TypeOrdre=' . $currentTypeOrdre . '&Ordre=' . $currentOrdre . '&Description=' . $currentDescription . '&Auteur=' . $currentAuteur . '&Categorie=' . $currentCategorie . '&DateDebut=' . $currentDateDebut . '&DateFin=' . $currentDateFin . '"><</a>';
        ?>

        <!-- Select por choisir la page -->
        <select id="ddlPage" onchange="window.location.href=this.value">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <option
                    value="/projet_final_php/pages/listeAnnonces.php?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>&TypeOrdre=<?php echo $currentTypeOrdre; ?>&Ordre=<?php echo $currentOrdre; ?>&Description=<?php echo $currentDescription; ?>&Auteur=<?php echo $currentAuteur; ?>&Categorie=<?php echo $currentCategorie; ?>&DateDebut=<?php echo $currentDateDebut; ?>&DateFin=<?php echo $currentDateFin; ?>"
                    <?php echo $i == $currentPage ? 'selected' : ''; ?>>
                    <?php echo $i; ?>
                </option>
            <?php endfor; ?>
        </select>

        <!-- Page suivante et dernière page -->
        <?php
        // Page suivante
        echo '<a class="h3 p-1" href="/projet_final_php/pages/listeAnnonces.php?page=' . $nextPage . '&limit=' . $limit . '&TypeOrdre=' . $currentTypeOrdre . '&Ordre=' . $currentOrdre . '&Description=' . $currentDescription . '&Auteur=' . $currentAuteur . '&Categorie=' . $currentCategorie . '&DateDebut=' . $currentDateDebut . '&DateFin=' . $currentDateFin . '">></a>';

        // Dernière page
        echo '<a class="h3 p-1" href="/projet_final_php/pages/listeAnnonces.php?page=' . $totalPages . '&limit=' . $limit . '&TypeOrdre=' . $currentTypeOrdre . '&Ordre=' . $currentOrdre . '&Description=' . $currentDescription . '&Auteur=' . $currentAuteur . '&Categorie=' . $currentCategorie . '&DateDebut=' . $currentDateDebut . '&DateFin=' . $currentDateFin . '">»</a>';
        ?>
    </div>
    </div>

    <script>
        function updateLimitAndSubmit() {
            var ddlNbParPage = document.getElementById("ddlNbParPage");
            var limit = ddlNbParPage.value;
            document.getElementById("hiddenLimit").value = limit;
            document.getElementById("frmRecherche").submit();
        }

        document.getElementById('btnAfficherAvance').addEventListener('click', function () {
            var advancedSearch = document.getElementById('divRechercheAvancé');
            var btn = document.getElementById('btnAfficherAvance');

            if (advancedSearch.style.display === "none" || advancedSearch.style.display === "") {
                advancedSearch.style.display = "block";
                btn.textContent = '-';
            } else {
                advancedSearch.style.display = "none";
                btn.textContent = '+';
            }
        });

        function submitOrdreChange() {
            document.getElementById('frmRecherche').submit();
        }

        function updateOrdreIcon() {
            var ordreSelect = document.getElementById('Ordre');
            var selectedValue = ordreSelect.value;

            if (selectedValue === 'ASC') {
                ordreSelect.options[0].text = '▲ Ascendant';
                ordreSelect.options[1].text = '▼ Descendant';
            } else {
                ordreSelect.options[0].text = '▲ Ascendant';
                ordreSelect.options[1].text = '▼ Descendant';
            }
        }

        function submitForm() {
            document.getElementById("frmRecherche").submit();
        }

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