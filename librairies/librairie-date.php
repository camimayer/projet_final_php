<?php
require_once "librairie-generale.php";

/*
-----------------------------------------------------------------------------------
 JJMMAAAA (2024-07-09)
 Scénarios : JJMMAAAA($intJour, $intMois, $intAnnee)
             JJMMAAAA($intJour, $intMois, $intAnnee) => Retourne la date formatée jj-mm-aaaa à partir de trois entiers (jour, mois, année).
 --------------------------------------------------------------------------------
 */

function JJMMAAAA($intJour, $intMois, $intAnnee)
{
    // Vérification et ajustement de l'année si elle est saisie en 2 chiffres
    if ($intAnnee >= 0 && $intAnnee <= 22) {
        $intAnnee += 2000;
    } elseif ($intAnnee >= 23 && $intAnnee <= 99) {
        $intAnnee += 1900;
    }

    // Formatage de la date
    return sprintf("%02d-%02d-%04d", $intJour, $intMois, $intAnnee);
}


/*
-----------------------------------------------------------------------------------
 extraitJJMMAAAA (2021-01-20)
 Scénarios : extraitJJMMAAAA($intJour, $intMois, $intAnnee)                 <=date()
             extraitJJMMAAAA($intJour, $intMois, $intAnnee, $strDate)       <=$strDate
 --------------------------------------------------------------------------------
 */
function extraitJJMMAAAA(&$intJour, &$intMois, &$intAnnee)
{
    /*Par défaut, l'extraction s'effectue à partir de la date courante;
    autrement elle s'effectue à partir du 4e argument spécifié à l'appel*/
    if (func_num_args() == 3) {
        /* Récupération de la date courante */
        $strDate = date("d-m-Y");

    } else {
        /* Récupération du 4e argument */
        $strDate = func_get_arg(3);
    }
    $intJour = intval(substr($strDate, 0, 2));
    $intMois = intval(substr($strDate, 3, 2));
    $intAnnee = intval(substr($strDate, 6, 4));
}

/** Exercice 3*/
function extraitJSJJMMAAAA(&$intJourSemaine, &$intJour, &$intMois, &$intAnnee, $strDate = null)
{
    /* Par défaut, l'extraction s'effectue à partir de la date courante;
    autrement elle s'effectue à partir du 5e argument spécifié à l'appel */
    if ($strDate === null) {
        /* Récupération de la date courante */
        $strDate = date("d-m-Y");
    }

    $intJourSemaine = date('N', strtotime($strDate)); // Jour de la semaine
    $intJour = intval(substr($strDate, 0, 2));
    $intMois = intval(substr($strDate, 3, 2));
    $intAnnee = intval(substr($strDate, 6, 4));
}



/*
|-------------------------------------------------------------------------------------|
| jourSemaineEnLitteral (2020-01-24)
| Scénarios : jourSemaineEnLitteral($intNoJour)
| jourSemaineEnLitteral($intNoJour, $binMajuscule)
|-------------------------------------------------------------------------------------|
*/
function jourSemaineEnLitteral($intNoJour, $binMajuscule = false)
{
    /* Par défaut, la première lettre du jour de la semaine s'affiche en minuscule
     * ($binMajuscule=false). Si un deuxième argument est saisi, il déterminera si
     * la première lettre doit s'afficher en majuscule ou non */
    $strJourSemaine = "N/A";
    switch ($intNoJour) {
        case 1:
            $strJourSemaine = "lundi";
            break;
        case 2:
            $strJourSemaine = "mardi";
            break;
        case 3:
            $strJourSemaine = "mercredi";
            break;
        case 4:
            $strJourSemaine = "jeudi";
            break;
        case 5:
            $strJourSemaine = "vendredi";
            break;
        case 6:
            $strJourSemaine = "samedi";
            break;
        case 7:
            $strJourSemaine = "dimanche";
            break;
    }
    $strJourSemaine = $binMajuscule ? ucfirst($strJourSemaine) : $strJourSemaine;
    return $strJourSemaine;
}


/*
|-------------------------------------------------------------------------------------|
| moisEnLitteral (2021-01-20)
| Scénarios : moisEnLitteral($intMois) => Première lettre en minuscule
| moisEnLitteral($intMois, $binMajuscule) => En fonction de $binMajuscule
|-------------------------------------------------------------------------------------|
*/
function moisEnLitteral($intMois, $binMajuscule = false)
{
    /* Par défaut, la première lettre du mois s'affiche en minuscule ($binMajuscule=false)
     * Si un deuxième argument est saisi, il déterminera si la première lettre doit
     * s'afficher en majuscule ou non */
    $strMois = "N/A";
    switch ($intMois) {
        case 1:
            $strMois = "janvier";
            break;
        case 2:
            $strMois = "f&eacute;vrier";
            break;
        case 3:
            $strMois = "mars";
            break;
        case 4:
            $strMois = "avril";
            break;
        case 5:
            $strMois = "mai";
            break;
        case 6:
            $strMois = "juin";
            break;
        case 7:
            $strMois = "juillet";
            break;
        case 8:
            $strMois = "ao&ucirc;t";
            break;
        case 9:
            $strMois = "septembre";
            break;
        case 10:
            $strMois = "octobre";
            break;
        case 11:
            $strMois = "novembre";
            break;
        case 12:
            $strMois = "d&eacute;cembre";
            break;
    }
    /*
     * if ($binMajuscule)
     * $strMois = ucfirst($strMois);
     */
    $strMois = $binMajuscule ? ucfirst($strMois) : $strMois;
    return $strMois;
}

/*
|-------------------------------------------------------------------------------------|
| premierSamedi (2021-01-20)
| Scénarios : premierSamedi($mois, $annee) => Retourne la date du premier samedi
| de chaque mois de l'année spécifiée au format 'Y-m-d'.
|-------------------------------------------------------------------------------------|
*/
// Fonction pour calculer le premier samedi de chaque mois de l'année spécifiée
function premierSamedi($mois, $annee)
{
    $date = new DateTime("$annee-$mois-01");
    $jourSemaine = $date->format('N'); // Obtenir le jour de la semaine du premier jour du mois
    $joursAajouter = 6 - $jourSemaine; // 6 - $jourSemaine pour atteindre le samedi + 6 jours
    if ($joursAajouter < 0) {
        $joursAajouter += 7;
    }
    $date->add(new DateInterval("P{$joursAajouter}D")); // Ajouter les jours nécessaires pour atteindre le samedi
    return $date->format('Y-m-d');
}

/*
|-------------------------------------------------------------------------------------|
| afficherDateComplete (2021-01-20)
| Scénarios : afficherDateComplete($numero, $date) => Retourne la date formatée en français
| Exemple : "samedi 6 janvier 2024"
|-------------------------------------------------------------------------------------|
*/
// Función para mostrar la fecha completa en el formato especificado
function afficherDateComplete($numero, $date)
{
    $jour = date('j', strtotime($date)); // Obtener el día del mes
    $mois = moisEnLitteral(date('n', strtotime($date)), true); // Obtener el mes en letras
    $annee = date('Y', strtotime($date)); // Obtener el año
    return "Réunion no " . $numero . " : <strong>" . er($jour) . " " . $mois . " " . $annee . "</strong> .</br>";
}

?>