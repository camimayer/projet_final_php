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
$query = "SELECT annonces.*, utilisateurs.Nom, utilisateurs.Prenom 
          FROM annonces 
          JOIN utilisateurs ON annonces.NoUtilisateur = utilisateurs.NoUtilisateur 
          WHERE Etat = 1 $searchQuery 
          ORDER BY $order 
          LIMIT $start, $limit";

$result = $dbManager->getConnection()->query($query);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des annonces</title>
    <link rel="stylesheet" href="style.css">
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

        <!-- Tabla de anuncios -->
        <table>
            <thead>
                <tr>
                    <th>No séquentiel</th>
                    <th>Numéro de l'annonce</th>
                    <th>Date de parution</th>
                    <th>Auteur</th>
                    <th>Catégorie</th>
                    <th>Description abrégée</th>
                    <th>Prix</th>
                    <th>Photo</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['NoAnnonce']; ?></td>
                            <td><?php echo $row['NoAnnonce']; ?></td>
                            <td><?php echo date('Y-m-d, H:i', strtotime($row['Parution'])); ?></td>
                            <td>
                                <?php if ($_SESSION['Courriel'] === $row['Courriel']): ?>
                                    <?php echo $row['Nom'] . ' ' . $row['Prenom']; ?>
                                <?php else: ?>
                                    <a
                                        href="profil_utilisateur.php?id=<?php echo $row['NoUtilisateur']; ?>"><?php echo $row['Nom'] . ' ' . $row['Prenom']; ?></a>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $row['Categorie']; ?></td>
                            <td><a
                                    href="detail_annonce.php?id=<?php echo $row['NoAnnonce']; ?>"><?php echo htmlspecialchars($row['DescriptionAbregee']); ?></a>
                            </td>
                            <td><?php echo $row['Prix'] ? $row['Prix'] . ' $' : 'N/A'; ?></td>
                            <td><img src="photos-annonce/<?php echo $row['Photo']; ?>" width="144"></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">Aucune annonce trouvée.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

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