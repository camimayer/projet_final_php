<?php
    $pathToCss = "../styles/header.css";
    $pagesTitle = $_SESSION['PagesTitle'];
?>

<head>
    <title><?php echo $pagesTitle; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<header>
    <nav class="bg-dark navbar navbar-expand-md row">
        <div class="container">
            <div id="menu" class="collapse navbar-collapse justify-content-center">
                <div class="navbar-nav">
                    <a href="listeAnnonces.php" class="nav-item nav-link text-light">Annonces</a>
<?php 
                    if ($_SESSION['Statut'] == 1): // Profil Admin 
?>
                        <a href="ListeUtilisateur.php" class="nav-item nav-link text-light">Liste des utilisateurs</a>
                        <a href="NettoyageBD.php" class="nav-item nav-link text-light">Nettoyage de la base de données</a>
<?php 
                    else : // Autres profilss 
?>
                        <a href="gestionAnnonces.php" class="nav-item nav-link text-light">Gestion de vos annonces</a>
                        <a href="miseAJourProfil.php" class="nav-item nav-link text-light">Modification du profil</a>
<?php 
                    endif; 
?>
                    <a href="logout.php" class="nav-item nav-link text-light">Déconnexion</a>
                    <span class="text-light text-center align-middle m-auto">(<?php echo $_SESSION['Courriel']; ?>)</span>
                </div>
            </div>
        </div>
    </nav>
</header>
