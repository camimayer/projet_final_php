<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annonce retirée</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @keyframes fadeInDown {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            font-size: 3rem;
            color: #28a745;
            text-align: center;
            margin-top: 50px;
            animation: fadeInDown 1s ease-out;
        }

        p {
            font-size: 1.2rem;
            text-align: center;
            margin-top: 20px;
            color: #555;
        }

        .links {
            margin-top: 30px;
            text-align: center;
        }

        .links a {
            display: block;
            font-size: 1.1rem;
            color: #007bff;
            text-decoration: none;
            margin: 10px 0;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .container {
            margin-top: 100px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Annonce retirée</h1>
        <p>Votre annonce a été retirée avec succès.</p>

        <div class="links">
            <a href="gestionAnnonces.php">Retour à la gestion des annonces</a>
            <a href="listeAnnonces.php">Retour à la liste des annonces</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>