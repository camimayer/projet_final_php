<?php

    session_start();

    if (!isset($_SESSION['Authentifie']) || !$_SESSION['Authentifie'])  {
        session_destroy();
        header("Location: login.php");
        exit();
    }else{
        header("Location: listeAnnonces.php");
        exit();
    }

?>