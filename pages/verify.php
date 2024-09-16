<?php
    session_start();
    // Página a ser executa após o usuário clicar sobre o link de verificação do e-mail recebido após signup.
    // Se Status diferente de 0 (Zero), un message s’affiche pour lui indiquer qu’il a déjà confirmé son inscription)

    // Se o link foi alterado, un message s’affiche pour lui indiquer que le lien n’est plus valide

    // Modifica o status do usuário para confirmado, ou seja, status = 9
    // Direcionar para a página de login
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    </head>
    <body>
        <div class="container-fluid my-4">
            <div id="divMAJProfile" class="col-4 m-auto">
                <h1 class="text-center" id="titreMAJProfile">Vérification de l'adresse e-mail</h1>
                <br />
                <br />
                <div class="text-center">
                    <p>Page en construction</p>
                    <p><a href="login.php">Aller à la page de connexion</a></p>

                </div>
            </div>
        </div>
        <br>
    </body>
</html>