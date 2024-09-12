<?php
session_start();
require_once '../databasemanager.php';
require_once '../config/localhost.php';

$dbManager = new DatabaseManager();

// Verifica si el usuario está autenticado
if (!isset($_SESSION['Courriel'])) {
    header("Location: login.php");
    exit();
}

// Variables para paginación y filtros
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10; // Número de anuncios por página
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page - 1) * $limit;
$order = isset($_GET['order']) ? $_GET['order'] : 'Parution DESC'; // Orden predeterminado

// Búsqueda
$searchQuery = "";
if (isset($_GET['search'])) {
    $search = $dbManager->getConnection()->real_escape_string($_GET['search']);
    $searchQuery = " AND (DescriptionAbregee LIKE '%$search%' OR DescriptionComplete LIKE '%$search%' OR Categorie LIKE '%$search%')";
}

// Contar anuncios totales
$totalResult = $dbManager->getConnection()->query("SELECT COUNT(*) AS total FROM annonces WHERE Etat = 1 $searchQuery");
$totalAnnonces = $totalResult->fetch_assoc()['total'];

// Consulta para obtener las últimas 10 anuncios
$query = "SELECT annonces.*, utilisateurs.Nom, utilisateurs.Prenom, utilisateurs.Courriel 
          FROM annonces 
          JOIN utilisateurs ON annonces.NoUtilisateur = utilisateurs.NoUtilisateur 
          WHERE Etat = 1 $searchQuery 
          ORDER BY $order 
          LIMIT $start, $limit";

$result = $dbManager->getConnection()->query($query);

?>

<!DOCTYPE html>
<html lang="fr">

<?php    
    require_once 'header.php';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Estilos para el contenedor principal */
        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-between;
        }

        /* Estilos para las cartas */
        .card {
            border: 1px solid #ccc;
            padding: 20px;
            flex: 1 1 calc(20% - 20px);
            /* 5 cartas por fila, con márgenes */
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .card img {
            width: 144px;
            height: auto;
            margin-bottom: 15px;
        }

        .pagination {
            text-align: center;
            margin: 20px 0;
        }

        /* Estilos adicionales para asegurar que se vean bien en dispositivos móviles */
        @media (max-width: 1200px) {
            .card {
                flex: 1 1 calc(33.33% - 20px);
                /* 3 cartas por fila en pantallas medianas */
            }
        }

        @media (max-width: 768px) {
            .card {
                flex: 1 1 calc(50% - 20px);
                /* 2 cartas por fila en pantallas pequeñas */
            }
        }

        @media (max-width: 576px) {
            .card {
                flex: 1 1 100%;
                /* 1 carta por fila en pantallas muy pequeñas */
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Liste des annonces</h1>
        <p>Total d'annonces disponibles: <?php echo $totalAnnonces; ?></p>

        <!-- Formulario de búsqueda -->
        <form method="GET" action="listeannonces.php">
            <input type="text" name="search" placeholder="Rechercher..."
                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit">Rechercher</button>
        </form>

        <!-- Contenedor de las cartas -->
        <div class="card-container">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="card">
                        <div>
                            <img src="../photos-annonce/<?php echo $row['Photo']; ?>" alt="Photo annonce">
                        </div>
                        <div>
                            <p><strong>No séquentiel:</strong> <?php echo $row['NoAnnonce']; ?></p>
                            <p><strong>Numéro de l'annonce:</strong> <?php echo $row['NoAnnonce']; ?></p>
                            <p><strong>Date de parution:</strong> <?php echo date('Y-m-d H:i', strtotime($row['Parution'])); ?>
                            </p>
                            <p><strong>Auteur:</strong>
                                <?php if (isset($row['Courriel']) && $_SESSION['Courriel'] === $row['Courriel']): ?>
                                    <?php echo $row['Nom'] . ' ' . $row['Prenom']; ?>
                                <?php else: ?>
                                    <a
                                        href="profil_utilisateur.php?id=<?php echo $row['NoUtilisateur']; ?>"><?php echo $row['Nom'] . ' ' . $row['Prenom']; ?></a>
                                <?php endif; ?>
                            </p>
                            <p><strong>Catégorie:</strong> <?php echo $row['Categorie']; ?></p>
                            <p><strong>Description:</strong>
                                <a
                                    href="detail_annonce.php?id=<?php echo $row['NoAnnonce']; ?>"><?php echo htmlspecialchars($row['DescriptionAbregee']); ?></a>
                            </p>
                            <p><strong>Prix:</strong> <?php echo $row['Prix'] ? $row['Prix'] . ' $' : 'N/A'; ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Aucune annonce trouvée.</p>
            <?php endif; ?>
        </div>

        <!-- Paginación -->
        <div class="pagination">
            <?php
            $totalPages = ceil($totalAnnonces / $limit);
            for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    </div>
</body>

</html>

<?php
// Cerrar la conexión
$dbManager->closeConnection();
?>