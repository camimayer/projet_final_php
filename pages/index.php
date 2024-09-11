<?php

    session_start();

    if (!isset($_SESSION['Courriel'])) {
        header("Location: login.php");
        exit();
    }else{
        header("Location: listeAnnonces.php");
        exit();
    }

?>