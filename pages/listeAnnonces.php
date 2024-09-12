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
            $orderField = 'utilisateurs.Nom, utilisateurs.Prenom';
            break;
        case 'Categorie':
            $orderField = 'categories.Description';
            break;
    }
}
$order = "$orderField $orderDirection";

// Recherche
$searchConditions = [];

if (!empty($_GET['DateDebut']) && !empty($_GET['DateFin'])) {
    $dateDebut = $dbManager->getConnection()->real_escape_string($_GET['DateDebut']);
    $dateFin = $dbManager->getConnection()->real_escape_string($_GET['DateFin']);
    $searchConditions[] = "Parution BETWEEN '$dateDebut' AND '$dateFin'";
}

if (isset($_GET['Auteur']) && $_GET['Auteur'] != '') {
    $auteur = $dbManager->getConnection()->real_escape_string($_GET['Auteur']);
    $searchConditions[] = "(utilisateurs.Nom LIKE '%$auteur%' OR utilisateurs.Prenom LIKE '%$auteur%')";
}

if (isset($_GET['Categorie']) && $_GET['Categorie'] != '') {
    $categorie = (int) $_GET['Categorie'];
    $searchConditions[] = "annonces.Categorie = $categorie";
}

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
$totalResult = $dbManager->getConnection()->query("SELECT COUNT(*) AS total FROM annonces WHERE Etat = 1 $searchQuery");
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
?>

<!DOCTYPE html>
<html lang="fr">

<?php
require_once 'header.php';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des annonces</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="listeAnnonces_style.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h1>Liste des annonces</h1>
        <div id="divPanel" class="d-flex">
            <div id="divNbParPage" class="ml-3 text-left flex-fill">
                <div class="d-inline-flex" style="width: 100%">
                    <label for="" class="col-form-label">Éléments par page : </label>
                    <select id="ddlNbParPage" class="form-control form-control-sm my-auto mx-2 p-2" name="limit"
                        onchange="this.form.submit()">
                        <option value="5" <?php echo $limit == 5 ? 'selected' : ''; ?>>5</option>
                        <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                        <option value="15" <?php echo $limit == 15 ? 'selected' : ''; ?>>15</option>
                        <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20</option>
                    </select>
                </div>
                <h5 class="text-secondary font-italic mt-2">Total d'annonces: <?php echo $totalAnnonces; ?></h5>
            </div>
            <div id="divRecherche" class="flex-fill">
                <div class="text-left float-right" style="width:50%">
                    <!-- Formulaire de recherche -->
                    <form method="GET" action="listeAnnonces.php" id="frmRecherche" class="d-flex flex-column">
                        <input type="hidden" name="limit" value="<?php echo $limit; ?>">
                        <input id="NbParPage" type="hidden" name="search" placeholder="Rechercher..."
                            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <input id="Page" name="Page" type="hidden">
                        <div id="divRechercheSimple">
                            <div class="form-group d-inline-flex my-0">
                                <label for="" class="col-form-label">Ordre :</label>
                                <div class="my-auto mx-1">
                                    <select class="form-control form-control-sm" id="TypeOrdre" name="TypeOrdre">
                                        <option value="Date">Date</option>
                                        <option value="Auteur">Auteur</option>
                                        <option value="Categorie">Catégorie</option>
                                    </select>
                                </div>
                                <div class="m-auto">
                                    <select name="Ordre" id="Ordre" class="form-control form-control-sm"
                                        onchange="submitOrdreChange()">
                                        <option value="ASC" <?php echo $orderDirection === 'ASC' ? 'selected' : ''; ?>>
                                            &#9650; Ascendant</option>
                                        <option value="DESC" <?php echo $orderDirection === 'DESC' ? 'selected' : ''; ?>>
                                            &#9660; Descendant</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group d-inline-flex mx-2 my-0">
                                <div class="m-auto mx-1">
                                    <input class="form-control form-control-sm" type="text" value id="Description"
                                        name="Description">
                                </div>
                            </div>
                            <input class="btn btn-primary form-control-sm m-auto" type="submit" value="Rechercher">
                            <button id="btnAfficherAvance" type="button"
                                class="btn btn-secondary font-weight-bold form-control-sm">+</button>
                        </div>

                        <div id="divRechercheAvancé" class="col-12 mt-2 border pt-2 pr-5" style="display: none;">
                            <div class="form-group row">
                                <label for="" class="col-3">Auteur</label>
                                <input type="text" class="col form-control form-control-sm" id="Auteur" name="Auteur">
                            </div>
                            <div class="form-group row">
                                <label class="col-3">Catégorie :</label>
                                <select class="col form-control form-control-sm" id="Categorie" name="Categorie">
                                    <option value="">Toutes</option>
                                    <option value="1">Location</option>
                                    <option value="2">Recherche</option>
                                    <option value="3">À vendre</option>
                                    <option value="4">À donner</option>
                                    <option value="5">Service offert</option>
                                    <option value="6">Autre</option>
                                </select>
                            </div>
                            <div class="form-group row">
                                <label class="col-3"> Date:</label>
                                <input type="date" class="col form-control form-control-sm mx-1" id="DateDebut"
                                    name="DateDebut">
                                <p class="p-0 m-auto">à</p>
                                <input type="date" class="col form-control form-control-sm mx-1" id="DateFin"
                                    name="DateFin">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Conteneur des cartes -->
        <div class="card-container d-flex flex-wrap justify-content-around mt-2 border-secondary">
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
                            <div class="overflow-hidden text-right imageSize">
                                <img src="../photos-annonce/<?php echo $row['Photo']; ?>" alt="Photo annonce" width="300"
                                    class="m-auto">
                            </div>
                            <div class="card-body pb-1">
                                <!-- Mostrar la petite description como un texto debajo de la imagen -->
                                <p class="card-text text-center">
                                    <a href="detailsAnnonce.php?NoAnnonce=<?php echo $row['NoAnnonce']; ?>"
                                        class="text-primary font-weight-bold">
                                        <?php echo $row['DescriptionAbregee']; ?>
                                    </a>
                                </p>
                                <div class="d-flex justify-content-between">
                                    <div class="text-left">
                                        <!-- Mostrar el nombre y apellido del usuario -->
                                        <a href="mailto:<?php echo $row['Courriel']; ?>">
                                            <?php echo $row['Nom'] . ' ' . $row['Prenom']; ?>
                                        </a>
                                    </div>
                                    <div class="text-right font-weight-bold">
                                        <!-- Mostrar el precio -->
                                        <span><?php echo $row['Prix'] ? number_format($row['Prix'], 2, ',', ' ') . ' $' : 'N/A'; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer d-flex justify-content-between py-0">
                                <!-- Mostrar la fecha de publicación -->
                                <div class="text-left"><?php echo $row['Parution']; ?></div>
                            </div>
                        </div>
                    </div>
                    <?php $sequentialNumber++; // Incrementar el número secuencial ?>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Aucune annonce trouvée.</p>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <div id="divPages" class="m-auto text-center">
            <!-- Botones de primera página y página anterior -->
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

            // Primera página
            echo '<a class="h3 p-1" href="/projet_final_php/pages/listeAnnonces.php?page=1&limit=' . $limit . '&TypeOrdre=' . $currentTypeOrdre . '&Ordre=' . $currentOrdre . '&Description=' . $currentDescription . '&Auteur=' . $currentAuteur . '&Categorie=' . $currentCategorie . '&DateDebut=' . $currentDateDebut . '&DateFin=' . $currentDateFin . '"><<</a>';

            // Página anterior
            echo '<a class="h3 p-1" href="/projet_final_php/pages/listeAnnonces.php?page=' . $previousPage . '&limit=' . $limit . '&TypeOrdre=' . $currentTypeOrdre . '&Ordre=' . $currentOrdre . '&Description=' . $currentDescription . '&Auteur=' . $currentAuteur . '&Categorie=' . $currentCategorie . '&DateDebut=' . $currentDateDebut . '&DateFin=' . $currentDateFin . '"><</a>';
            ?>

            <!-- Select para elegir la página -->
            <select id="ddlPage" onchange="window.location.href=this.value">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <option
                        value="/projet_final_php/pages/listeAnnonces.php?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>&TypeOrdre=<?php echo $currentTypeOrdre; ?>&Ordre=<?php echo $currentOrdre; ?>&Description=<?php echo $currentDescription; ?>&Auteur=<?php echo $currentAuteur; ?>&Categorie=<?php echo $currentCategorie; ?>&DateDebut=<?php echo $currentDateDebut; ?>&DateFin=<?php echo $currentDateFin; ?>"
                        <?php echo $i == $currentPage ? 'selected' : ''; ?>>
                        <?php echo $i; ?>
                    </option>
                <?php endfor; ?>
            </select>

            <!-- Página siguiente y última página -->
            <?php
            // Página siguiente
            echo '<a class="h3 p-1" href="/projet_final_php/pages/listeAnnonces.php?page=' . $nextPage . '&limit=' . $limit . '&TypeOrdre=' . $currentTypeOrdre . '&Ordre=' . $currentOrdre . '&Description=' . $currentDescription . '&Auteur=' . $currentAuteur . '&Categorie=' . $currentCategorie . '&DateDebut=' . $currentDateDebut . '&DateFin=' . $currentDateFin . '">></a>';

            // Última página
            echo '<a class="h3 p-1" href="/projet_final_php/pages/listeAnnonces.php?page=' . $totalPages . '&limit=' . $limit . '&TypeOrdre=' . $currentTypeOrdre . '&Ordre=' . $currentOrdre . '&Description=' . $currentDescription . '&Auteur=' . $currentAuteur . '&Categorie=' . $currentCategorie . '&DateDebut=' . $currentDateDebut . '&DateFin=' . $currentDateFin . '">>></a>';
            ?>
        </div>

    </div>

    <script>
        // Gestionar la visibilidad du dropdown de recherche avancée
        document.getElementById('btnAfficherAvance').addEventListener('click', function () {
            var advancedSearch = document.getElementById('divRechercheAvancé');
            var btn = document.getElementById('btnAfficherAvance');

            // Alternar entre afficher/cacher la recherche avancée
            if (advancedSearch.style.display === "none" || advancedSearch.style.display === "") {
                advancedSearch.style.display = "block";  // Afficher la recherche avancée
                btn.textContent = '-';  // Changer le texte du bouton à '-'
            } else {
                advancedSearch.style.display = "none";  // Cacher la recherche avancée
                btn.textContent = '+';  // Changer le texte du bouton à '+'
            }
        });
        function submitOrdreChange() {
            // Envoyer le formulaire lorsque l'utilisateur change la valeur
            document.getElementById('frmRecherche').submit();
        }

        // Initialiser les icônes en fonction de la valeur sélectionnée, sans envoyer le formulaire
        function updateOrdreIcon() {
            var ordreSelect = document.getElementById('Ordre');
            var selectedValue = ordreSelect.value;

            // Actualiser l'affichage de l'icône sans envoyer le formulaire
            if (selectedValue === 'ASC') {
                ordreSelect.options[0].text = '▲ Ascendant';
                ordreSelect.options[1].text = '▼ Descendant';
            } else {
                ordreSelect.options[0].text = '▲ Ascendant';
                ordreSelect.options[1].text = '▼ Descendant';
            }
        }

        // Initialiser les icônes lorsque la page se charge
        window.onload = function () {
            updateOrdreIcon();
        };
    </script>
</body>

</html>
<?php
// Fermer la connexion à la base de données
$dbManager->closeConnection();
?>