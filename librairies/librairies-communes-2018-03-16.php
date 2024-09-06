<?php
/*
|-------------------------------------------------------------------------------------|
| 01. function AAAAMMJJ()
| 02. function ajouteZeros($numValeur, $intLargeur)
| 03. function annee($strDate)
| 04. function aujourdhui($binAAAAMMJJ=true)
| 05. function bissextile($intAnnee)
| xx. function chargeFichierEnMemoire($strNomFichier, &$tContenu, &$intNbLignes, &$strContenu, &$intTaille, &$strContenuHTML)
| xx. function compteLignesFichier($strNomFichier)
| 06. function convertitSousChaineEnEntier($strChaine, $intDepart, $intLongueur)
| 07. function dateEnLitteral()
| 08. function dateValide($strDate)
| 09. function decomposeURL($strURL, &$strChemin, &$strNom, &$strSuffixe)
| xx. function detecteFinFichier($fp)
| xx. function detecteServeur(&$strMonIP, &$strIPServeur, &$strNomServeur, &$strInfosSensibles)
| 00. function dollar($dblNombre, $intNbDecimales = 2)
| 11. function dollarParentheses($dblNombre, $intNbDecimales = 2)
| 12. function droite($strChaine, $intLargeur) 
| 13. function ecrit($strChaine, $intNbBR = 0)
| xx. function ecritLigneDansFichier($fp, $strLigneCourante, $binSaut_intNbLignesSaut = false)
| 14. function er($intEntier, $binExposant=true)
| 15. function estNumerique($strValeur) 
| 16. function etatCivilValide($chrEtat, $chrSexe, &$strEtatCivil)
| 17. function extraitJJMMAAAA(&$intJour, &$intMois, &$intAnnee)
| 18. function extraitJSJJMMAAAA(&$intJourSemaine, &$intJour, &$intMois, &$intAnnee)
| 19. function extraitJSJJMMAAAAv2(&$intJourSemaine, &$intJour, &$intMois, &$intAnnee)
| xx. function fermeFichier($fp)
| xx. function fichierExiste($strNomFichier)
| xx. function genereNombre($Maximum)
| 20. function get($strNomParametre)
| 21. function hierOuDemain($binDemain, $intJour_Annee_Courant, $intMois_Courant, $intAnnee_Jour_Courant, &$intJourSemaine_HierOuDemain=0, &$intJour_HierOuDemain=0, &$intMois_HierOuDemain=0, &$intAnnee_HierOuDemain=0) 
| 22. function input($strID, $strCLASS, $strMAXLENGTH, $strVALUE, $binECHO=false)
| 23. function JJMMAAAA($intJour, $intMois, $intAnnee)
| 24. function jour($strDate)
| 25. function jourSemaineEnLitteral($intNoJour, $binMajuscule=false)
| xx. function litLigneDansFichier($fp)
| 26. function majuscules($strChaine) 
| 27. function minuscules($strChaine) 
| 28. function mois($strDate)
| 29. function moisEnLitteral($intMois, $binMajuscule=false)
| 30. function nombreJoursAnnee($intAnnee)
| 31. function nombreJoursEntreDeuxDates($strDate1, $strDate2)
| 32. function nombreJoursMois($intMois, $intAnnee)
| xx. function ouvreFichier($strNomFichier, $strMode="L")
| 33. function post($strNomParametre)
| 34. function pourcent($dblNombre, $intNbDecimales = 2)
>>>>>35. function renommeFichier($strAncienNom, $strNouveauNom, $binVerifie)
|-------------------------------------------------------------------------------------|
*/

/*
|-------------------------------------------------------------------------------------|
| AAAAMMJJ (2018-02-01)
| Scénario : AAAAMMJJ($intJour, $intMois, $intAnnee) = > "$intAnnee-$intMois-$intJour"
|            Si $intAnnee sur 2 positions 
|               Si $intAnnee <= 30 => 2000 à 2030 autrement => 1931 à 1999
|-------------------------------------------------------------------------------------|
*/
function AAAAMMJJ()
{
   if (func_num_args() == 1) {
      $strDate = func_get_arg(0);
      $intJour = intval(substr($strDate, 0, 2));
      $intMois = intval(substr($strDate, 3, 2));
      $intAnnee = intval(substr($strDate, 6, 4));
   } else {
      $intJour = intval(func_get_arg(0));
      $intMois = intval(func_get_arg(1));
      $intAnnee = intval(func_get_arg(2));
   }
   $intAnnee = $intAnnee <= 30 ? 2000 + $intAnnee :
      ($intAnnee <= 99 ? 1900 + $intAnnee : $intAnnee);
   /* La date retournée doit avoir le format 0J-0M-AAAA */
   return ajouteZeros($intAnnee, 4) . "-" .
      ajouteZeros($intMois, 2) . "-" .
      ajouteZeros($intJour, 2);
}
/*
|-------------------------------------------------------------------------------------|
| ajouteZeros (2017-02-06)
| Scénario : ajouteZeros($numValeur, $intLargeur)
|-------------------------------------------------------------------------------------|
*/
function ajouteZeros($numValeur, $intLargeur)
{
   $strFormat = "%0" . $intLargeur . "d";
   return sprintf($strFormat, $numValeur);
}
/*
|-------------------------------------------------------------------------------------|
| annee (2018-02-05)
| > annee("aaaa-mm-jj") ou annee("jj-mm-aaaa") retourne aaaa
|-------------------------------------------------------------------------------------|
*/
function annee($strDate)
{
   if (substr($strDate, 4, 1) == "-") {
      /* AAAA-MM-JJ */
      $intAnnee = intval(substr($strDate, 0, 4), 10);
   } else {
      /* JJ-MM-AAAA */
      $intAnnee = intval(substr($strDate, 6, 4), 10);
   }
   return $intAnnee;
}
/*
|-------------------------------------------------------------------------------------|
| aujourdhui (2017-01-29)
| > aujourdhui()      => aaaa-mm-jj
| > aujourdhui(true)  => aaaa-mm-jj
| > aujourdhui(false) => jj-mm-aaaa
|-------------------------------------------------------------------------------------|
*/
function aujourdhui($binAAAAMMJJ = true)
{
   $strDate = "";
   if (func_num_args() == 1) {
      $binAAAAMMJJ = func_get_arg(0);
   }
   switch ($binAAAAMMJJ) {
      case true:
         $strDate = date("Y-m-d");
         break;
      case false;
         $strDate = date("d-m-Y");
         break;
   }
   return $strDate;
}
/*
|-------------------------------------------------------------------------------------|
| bissextile (2017-01-29)
|-------------------------------------------------------------------------------------|
*/
function bissextile($intAnnee)
{
   $strDate = strtotime("01-01-$intAnnee");
   return date("L", $strDate);
}
/*
|----------------------------------------------------------------------------------|
| chargeFichierEnMemoire() (2018-03-13)
| Réf.: http://php.net/manual/fr/function.count.php
|       http://ca.php.net/manual/fr/function.file.php
|       http://php.net/manual/fr/function.file-get-contents.php
|       http://ca.php.net/manual/fr/function.str-replace.php
|       http://php.net/manual/fr/function.strlen.php
|----------------------------------------------------------------------------------|
*/
function chargeFichierEnMemoire(
   $strNomFichier,
   &$tContenu,
   &$intNbLignes,
   &$strContenu,
   &$intTaille,
   &$strContenuHTML
) {
   /* Récupère toutes les lignes et les entrepose dans un tableau
      Retrait de tous les CR et LF
      Récupère le nombre de lignes */
   $tContenu = file($strNomFichier);
   $tContenu = str_replace("\n", "", str_replace("\r", "", $tContenu));
   $intNbLignes = count($tContenu);

   /* Récupère toutes les lignes et les entrepose dans une chaîne */
   $strContenu = file_get_contents($strNomFichier);
   $intTaille = strlen($strContenu);

   /* Entrepose la chaîne résultante dans une autre après l'avoir XHTMLisé ! */
   $strContenuHTML = str_replace("\n\r", "<br />", str_replace("\r\n", "<br />", $strContenu));
}
/*
|----------------------------------------------------------------------------------|
| compteLignesFichier() (2018-03-13)
| Réf.: http://ca.php.net/manual/fr/function.count.php
|       http://ca.php.net/manual/fr/function.file.php
|----------------------------------------------------------------------------------|
*/
function compteLignesFichier($strNomFichier)
{
   $intNbLignes = -1;
   if (fichierExiste($strNomFichier)) {
      $intNbLignes = count(file($strNomFichier));
   }
   return $intNbLignes;
}
/*
|-------------------------------------------------------------------------------------|
| convertitSousChaineEnEntier (2017-02-06)
|-------------------------------------------------------------------------------------|
*/
function convertitSousChaineEnEntier($strChaine, $intDepart, $intLongueur)
{
   $intEntier = intval(substr($strChaine, $intDepart, $intLongueur));
   return $intEntier;
}
/*
|-------------------------------------------------------------------------------------|
| dateEnLitteral (2018-01-31)
| > dateEnLitteral()                    Date du jour SANS le jour de la semaine
| > dateEnLitteral("jj-mm-aaaa")        Date passée en argument SANS le jour de la semaine
| 
| > dateEnLitteral("C")                 Date du jour AVEC le jour de la semaine
| > dateEnLitteral("jj-mm-aaaa", "C")   Date passée en argument AVEC le jour de la semaine
| > dateEnLitteral("C", "jj-mm-aaaa");  Date passée en argument AVEC le jour de la semaine
|
| Notes : Le format de la date peut être "jj-mm-aaaa" ou "aaaa-mm-jj"
|         Minuscule et majuscule traitées indifféremment 
|-------------------------------------------------------------------------------------|
 */
function dateEnLitteral()
{
   $strDate = aujourdhui();
   $binJourSemaineDemande = false;
   for ($i = 0; $i < func_num_args(); $i++) {
      $strParametre = strtoupper(func_get_arg($i));
      if ($strParametre == "C") {
         $binJourSemaineDemande = true;
      }
      if (dateValide($strParametre)) {
         $strDate = $strParametre;
      }
   }
   extraitJSJJMMAAAAv2($intJourSemaine, $intJour, $intMois, $intAnnee, $strDate);
   $strDateEnLitteral = ($binJourSemaineDemande ? jourSemaineEnLitteral($intJourSemaine, true) . " " : "") . er($intJour) . " " . moisEnLitteral($intMois) . " " . $intAnnee;

   return $strDateEnLitteral;
}
// /*
// |-------------------------------------------------------------------------------------|
// | dateValide (2018-02-08)
// |-------------------------------------------------------------------------------------|
// */
// function dateValide($strDate) {
//    $intJour;
//    $intMois;
//    $intAnnee;
//    $intJourSemaine;
//    $binVerdict = false;
//    if (preg_match("/^\d{4}\-\d{2}\-\d{2}$/", $strDate) ||
//        preg_match("/^\d{2}\-\d{2}\-\d{4}$/", $strDate)) {
//       extraitJSJJMMAAAAv2($intJourSemaine, $intJour, $intMois, $intAnnee, $strDate);
//       $binVerdict = checkdate($intMois, $intJour, $intAnnee);
//    }
//    return $binVerdict;
// }
/*
|-------------------------------------------------------------------------------------|
| decomposeURL (2017-02-03)
|-------------------------------------------------------------------------------------|
*/
function decomposeURL(
   $strURL,
   &$strChemin,
   &$strNom,
   &$strSuffixe
) {
   $strChemin = pathinfo($strURL, PATHINFO_DIRNAME);
   $strNom = pathinfo($strURL, PATHINFO_FILENAME);
   $strSuffixe = pathinfo($strURL, PATHINFO_EXTENSION);
}
/*
|----------------------------------------------------------------------------------|
| detecteFinFichier() (2018-03-13)
| Réf.: http://php.net/manual/fr/function.feof.php
|----------------------------------------------------------------------------------|
*/
function detecteFinFichier($fp)
{
   $binVerdict = true;
   if ($fp) {
      $binVerdict = feof($fp);
   }
   return $binVerdict;
}
/*
|-------------------------------------------------------------------------------------|
| detecteServeur (2017-03-18)
|-------------------------------------------------------------------------------------|
*/
function detecteServeur(&$strMonIP, &$strIPServeur, &$strNomServeur, &$strInfosSensibles)
{
   $strMonIP = $_SERVER["REMOTE_ADDR"];
   $strIPServeur = $_SERVER["SERVER_ADDR"];
   $strNomServeur = $_SERVER["SERVER_NAME"];
   $strInfosSensibles = str_replace(".", "-", $strNomServeur) . ".php";
}
/*
|-------------------------------------------------------------------------------------|
| dollar (2017-03-14)
|-------------------------------------------------------------------------------------|
*/
function dollar($dblNombre, $intNbDecimales = 2)
{
   return number_format(doubleval($dblNombre), $intNbDecimales, ",", " ") . " $";
}
/*
|-------------------------------------------------------------------------------------|
| dollarParentheses (2018-01-07)
|-------------------------------------------------------------------------------------|
*/
function dollarParentheses($dblNombre, $intNbDecimales = 2)
{
   return ($dblNombre < 0 ? "(" : "") . str_replace("-", "", number_format(doubleval($dblNombre), $intNbDecimales, ",", " ")) . ($dblNombre < 0 ? ")" : "") . " $";
}
/*
|-------------------------------------------------------------------------------------|
| droite (2014-03-25)
|-------------------------------------------------------------------------------------|
*/
function droite($strChaine, $intLargeur)
{
   return substr($strChaine, -$intLargeur);
}
/*
|-------------------------------------------------------------------------------------|
| ecrit (2017-02-03)
|-------------------------------------------------------------------------------------|
 */
function ecrit($strChaine, $intNbBR = 0)
{
   echo $strChaine;
   if (func_num_args() == 2) {
      $intNbBR = intval(func_get_arg(1));
   }
   for ($i = 1; $i <= $intNbBR; $i++) {
      echo "<br />";
   }
}
/*
|----------------------------------------------------------------------------------|
| ecritLigneDansFichier() (2018-03-13)
| Réf.: http://php.net/manual/fr/function.fputs.php
|       http://php.net/manual/fr/function.gettype.php
|----------------------------------------------------------------------------------|
*/
function ecritLigneDansFichier($fp, $strLigneCourante, $binSaut_intNbLignesSaut = false)
{
   $binVerdict = fputs($fp, $strLigneCourante);
   if ($binVerdict) {
      switch (gettype($binSaut_intNbLignesSaut)) {
         case "integer":
            for ($i = 1; $i <= $binSaut_intNbLignesSaut && $binVerdict; $i++) {
               $binVerdict = fputs($fp, "\r\n");
            }
            break;
         case "boolean":
            if ($binSaut_intNbLignesSaut) {
               $binVerdict = fputs($fp, "\r\n");
            }
      }
   }
   return $binVerdict;
}
/*
|-------------------------------------------------------------------------------------|
| er (2017-02-06)
| Scénarios : er($intEntier)
|             er($intEntier, $binExposant)
|-------------------------------------------------------------------------------------|
*/
function er($intEntier, $binExposant = true)
{
   return $intEntier . ($intEntier == 1 ? ($binExposant ? "<sup>er</sup>" : "er") : "");
}
/*
|-------------------------------------------------------------------------------------|
| estNumerique (2018-03-10)
|-------------------------------------------------------------------------------------|
*/
function estNumerique($strValeur)
{
   return is_numeric(str_replace(",", ".", $strValeur));
}
/*
|-------------------------------------------------------------------------------------|
| etatCivilValide (03-fév-2017)
|-------------------------------------------------------------------------------------|
*/
function etatCivilValide($chrEtat, $chrSexe, &$strEtatCivil)
{
   $binEtatCivil = true;
   $chrSexe = strtoupper($chrSexe);
   $chrEtat = strtoupper($chrEtat);
   switch ($chrEtat) {
      case "C":
         $strEtatCivil = $chrSexe == "H" ? "Célibataire" : "Célibataire";
         break;
      case "F":
         $strEtatCivil = $chrSexe == "H" ? "Conjoint de fait" : "Conjointe de fait";
         break;
      case "M":
         $strEtatCivil = $chrSexe == "H" ? "Marié" : "Mariée";
         break;
      case "S":
         $strEtatCivil = $chrSexe == "H" ? "Séparé" : "Séparée";
         break;
      case "D":
         $strEtatCivil = $chrSexe == "H" ? "Divorcé" : "Divorcée";
         break;
      case "V":
         $strEtatCivil = $chrSexe == "H" ? "Veuf" : "Veuve";
         break;
      default:
         $strEtatCivil = "ERREUR";
         $binEtatCivil = false;
   }
   return $binEtatCivil;
}
/*
|-------------------------------------------------------------------------------------|
| extraitJJMMAAAA (2017-01-29)
| Scénarios : extraitJJMMAAAA($intJour, $intMois, $intAnnee)           <= date()
|             extraitJJMMAAAA($intJour, $intMois, $intAnnee, $strDate) <= $strDate
|-------------------------------------------------------------------------------------|
*/
function extraitJJMMAAAA(&$intJour, &$intMois, &$intAnnee)
{
   /* Par défaut, l'extraction s'effectue à partir de la date courante;
    * autrement elle s'effectue à partir du 4e argument spécifié à l'appel */
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
/*
|-------------------------------------------------------------------------------------|
| extraitJSJJMMAAAA (2017-01-29)
| Scénarios : extraitJSJJMMAAAA($intJourSemaine, $intJour, $intMois, $intAnnee)
|             extraitJSJJMMAAAA($intJourSemaine, $intJour, $intMois, $intAnnee, strDate)
|-------------------------------------------------------------------------------------|
*/
function extraitJSJJMMAAAA(&$intJourSemaine, &$intJour, &$intMois, &$intAnnee)
{
   /* Par défaut, l'extraction s'effectue à partir de la date courante;
    * autrement elle s'effectue à partir du 5e argument spécifié à l'appel */
   if (func_num_args() == 4) {
      /* Récupération de la date courante */
      $strDate = date("d-m-Y");
      /* Récupération du jour de la semaine */
      $intJourSemaine = date("N");
   } else {
      /* Récupération du 5e argument */
      $strDate = func_get_arg(4);
      /* Récupération du jour de la semaine après avoir convertit la date passée
       * sous forme d'une chaîne en timestamp Unix */
      $intJourSemaine = date("N", strtotime($strDate));
   }
   $intJour = intval(substr($strDate, 0, 2));
   $intMois = intval(substr($strDate, 3, 2));
   $intAnnee = intval(substr($strDate, 6, 4));
}
/*
|-------------------------------------------------------------------------------------|
| extraitJSJJMMAAAAv2 (2017-01-29)
| Scénarios : extraitJSJJMMAAAAv2($intJourSemaine, $intJour, $intMois, $intAnnee)
|             où date()
|             extraitJSJJMMAAAAv2($intJourSemaine, $intJour, $intMois, $intAnnee, $strDate)
|             où $strDate = "jj-mm-aaaa" ou "aaaa-mm-jj"
|-------------------------------------------------------------------------------------|
*/
function extraitJSJJMMAAAAv2(&$intJourSemaine, &$intJour, &$intMois, &$intAnnee)
{
   /* Par défaut, l'extraction s'effectue à partir de la date courante;
    * autrement elle s'effectue à partir du 5e argument spécifié à l'appel */
   if (func_num_args() == 4) {
      /* Récupération de la date courante */
      $strDate = date("d-m-Y");
      /* Récupération du jour de la semaine */
      $intJourSemaine = date("N");
   } else {
      /* Récupération du 5e argument */
      $strDate = func_get_arg(4);
      /* Récupération du jour de la semaine après avoir convertit la date passée
       * sous forme d'une chaîne en timestamp Unix */
      $intJourSemaine = date("N", strtotime($strDate));
   }

   //if (preg_match("/^\d{4}\-\d{2}\-\d{2}$/", $strDate)) {
   if (substr($strDate, 4, 1) == "-") {
      /* AAAA-MM-JJ */
      $intJour = intval(substr($strDate, 8, 2), 10);
      $intMois = intval(substr($strDate, 5, 2), 10);
      $intAnnee = intval(substr($strDate, 0, 4), 10);
   } else {
      /* JJ-MM-AAAA */
      $intJour = intval(substr($strDate, 0, 2), 10);
      $intMois = intval(substr($strDate, 3, 2), 10);
      $intAnnee = intval(substr($strDate, 6, 4), 10);
   }
}
/*
|----------------------------------------------------------------------------------|
| fermeFichier() (2018-03-13)
| Réf.: http://ca.php.net/manual/fr/function.fclose.php
|----------------------------------------------------------------------------------|
*/
function fermeFichier($fp)
{
   $binVerdict = false;
   if ($fp) {
      $binVerdict = fclose($fp);
   }
   return $binVerdict;
}
/*
|----------------------------------------------------------------------------------|
| fichierExiste() (2018-03-13)
| Réf.: http://ca.php.net/manual/fr/function.file-exists.php
|----------------------------------------------------------------------------------|
*/
function fichierExiste($strNomFichier)
{
   return file_exists($strNomFichier);
}
/*
|-------------------------------------------------------------------------------------|
| gauche (2018-03-10)
|-------------------------------------------------------------------------------------|
*/
function gauche($strChaine, $intLargeur)
{
   return substr($strChaine, 0, $intLargeur);
}
/*
|-------------------------------------------------------------------------------------|
| genereNombre (30-jun-2011) NOUVELLE FONCTION À AJOUTER DANS VOTRE LIBRAIRIE
|-------------------------------------------------------------------------------------|
*/
function genereNombre($Maximum)
{
   list($usec, $sec) = explode(' ', microtime());
   $dblGerme = (float) $sec + ((float) $usec * 100000);
   srand($dblGerme);
   return floor(rand() % $Maximum + 1);
}
/*
|-----------------------------------------------------------------------------------|
| get (2017-01-31)
| Scénario : get($strNomParametre) retourne la valeur du paramètre ou NULL
| Réf.: Préférable d'utiliser des filtres
|       http://php.net/manual/fr/function.filter-input.php
|       http://php.net/manual/fr/filter.filters.php
|-----------------------------------------------------------------------------------|
*/
function get($strNomParametre)
{
   return isset($_GET[$strNomParametre]) ? $_GET[$strNomParametre] : null;
}
/*
|-------------------------------------------------------------------------------------|
| hierOuDemain (2018-01-09)
| Entrées : $binDemain             : true=demain, false=hier
|           $intJour_Annee_Courant : jour ou année
|           $intMois_Courant       : mois
|           $intAnnee_Jour_Courant : année ou jour
| Sorties : $intJourSemaine_HierOuDemain : 1 à 7 correspondant au jour de la semaine du lendemain (facultatif)
|           $intJour_HierOuDemain        : 01 à 09, 10 à 28, 29, 30 ou 31 correspondant au jour du lendemain (facultatif)
|           $intMois_HierOuDemain        : 01 à 12 correspondant au mois du lendemain (facultatif)
|           $intAnnee_HierOuDemain       : 1900 à 2199 correspondant à l'année du lendemain (facultatif)
| Retour  : $strDateHierOuDemain         : Date correspondant à hier ou à demain selon le format "AAAA-MM-JJ"
|-------------------------------------------------------------------------------------|
*/
function hierOuDemain(
   $binDemain,
   $intJour_Annee_Courant,
   $intMois_Courant,
   $intAnnee_Jour_Courant,
   &$intJourSemaine_HierOuDemain = 0,
   &$intJour_HierOuDemain = 0,
   &$intMois_HierOuDemain = 0,
   &$intAnnee_HierOuDemain = 0
) {
   /* On extrait le jour, le mois et l'année en fonction des paramètres passés */
   if ($intJour_Annee_Courant <= 31) {
      $intJour = $intJour_Annee_Courant;
      $intAnnee = $intAnnee_Jour_Courant;
   } else {
      $intJour = $intAnnee_Jour_Courant;
      $intAnnee = $intJour_Annee_Courant;
   }
   $intMois = $intMois_Courant;
   /* On détermine la date d'hier ou de demain
    *  Réf.: https://stackoverflow.com/questions/14460518/php-get-tomorrows-date-from-date/14460546
    *        https://stackoverflow.com/questions/10040291/converting-a-unix-timestamp-to-formatted-date-string
    */
   $strHierOuDemain = $binDemain ? "+1 day" : "-1 day";
   $hierOuDemain_timestamp = strtotime($strHierOuDemain, strtotime(AAAAMMJJ($intJour, $intMois, $intAnnee)));
   $strDateHierOuDemain = gmdate("Y-m-d", $hierOuDemain_timestamp);
   /* On détermine le jour de la semaine correspondant */
   extraitJSJJMMAAAAv2($intJourSemaine_HierOuDemain, $intJour_HierOuDemain, $intMois_HierOuDemain, $intAnnee_HierOuDemain, $strDateHierOuDemain);
   /* On s'assure que le jour et le mois s'affichent sur deux positions */
   $intJour_HierOuDemain = ajouteZeros($intJour_HierOuDemain, 2);
   $intMois_HierOuDemain = ajouteZeros($intMois_HierOuDemain, 2);
   /* On retourne la date sous le format "AAAA-MM-JJ" */
   return $strDateHierOuDemain;
}
/*
|-------------------------------------------------------------------------------------|
| input (2018-02-01)
|-------------------------------------------------------------------------------------|
*/
function input($strID, $strCLASS, $strMAXLENGTH, $strVALUE, $binECHO = false)
{
   $strINPUT = "<input id=\"$strID\" name=\"$strID\" class=\"$strCLASS\" type=\"text\" maxlength=\"$strMAXLENGTH\" value=\"$strVALUE\" />";
   if ($binECHO) {
      echo $strINPUT;
   } else {
      return $strINPUT;
   }
}
/*
|-------------------------------------------------------------------------------------|
| JJMMAAAA (2017-01-31)
| Scénario : JJMMAAAA($intJour, $intMois, $intAnnee) = > "$intJour-$intMois-$intAnnee"
|            Si $intAnnee sur 2 positions 
|               Si $intAnnee <= 20 => 2000 à 2020 autrement => 1921 à 1999
|-------------------------------------------------------------------------------------|
*/
function JJMMAAAA($intJour, $intMois, $intAnnee)
{
   $intAnnee = $intAnnee <= 20 ? 2000 + $intAnnee :
      ($intAnnee <= 99 ? 1900 + $intAnnee : $intAnnee);
   /* La date retournée doit avoir le format 0J-0M-AAAA */
   return ajouteZeros($intJour, 2) . "-" .
      ajouteZeros($intMois, 2) . "-" .
      ajouteZeros($intAnnee, 4);
}
/*
|-------------------------------------------------------------------------------------|
| jour (2018-02-05)
| > jour("aaaa-mm-jj") ou jour("jj-mm-aaaa") retourne jj
|-------------------------------------------------------------------------------------|
*/
function jour($strDate)
{
   if (substr($strDate, 4, 1) == "-") {
      /* AAAA-MM-JJ */
      $intJour = intval(substr($strDate, 8, 2), 10);
   } else {
      /* JJ-MM-AAAA */
      $intJour = intval(substr($strDate, 0, 2), 10);
   }
   return $intJour;
}
/*
|-------------------------------------------------------------------------------------|
| jourSemaineEnLitteral (2017-01-31)
| Scénarios : jourSemaineEnLitteral($intNoJour)
|             jourSemaineEnLitteral($intNoJour, $binMajuscule)
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
|----------------------------------------------------------------------------------|
| litLigneDansFichier() (2018-03-13)
| Réf.: http://ca.php.net/manual/fr/function.fgets.php
|       http://ca.php.net/manual/fr/function.str-replace.php
|----------------------------------------------------------------------------------|
*/
function litLigneDansFichier($fp)
{
   return str_replace("\n", "", str_replace("\r", "", fgets($fp)));
}
/*
|-------------------------------------------------------------------------------------|
| majuscules : 30-jun-2011, 25-mar-2014
! Scénarios  :
|-------------------------------------------------------------------------------------|
*/
function majuscules($strChaine)
{
   $strChaine = str_replace(
      array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'à', 'â', 'ä', 'á', 'ã', 'å', 'î', 'ï', 'ì', 'í', 'ô', 'ö', 'ò', 'ó', 'õ', 'ø', 'ù', 'û', 'ü', 'ú', 'é', 'è', 'ê', 'ë', 'ç', 'ÿ', 'ñ', ),
      array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'À', 'Â', 'Ä', 'Á', 'Ã', 'Å', 'Î', 'Ï', 'Ì', 'Í', 'Ô', 'Ö', 'Ò', 'Ó', 'Õ', 'Ø', 'Ù', 'Û', 'Ü', 'Ú', 'É', 'È', 'Ê', 'Ë', 'Ç', 'Ÿ', 'Ñ', ),
      $strChaine
   );
   return ($strChaine);
}
/*
|-------------------------------------------------------------------------------------|
| minuscules : 30-jun-2011, 25-mar-2014
! Scénarios  :
|-------------------------------------------------------------------------------------|
*/
function minuscules($strChaine)
{
   $strChaine = str_replace(
      array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'À', 'Â', 'Ä', 'Á', 'Ã', 'Å', 'Î', 'Ï', 'Ì', 'Í', 'Ô', 'Ö', 'Ò', 'Ó', 'Õ', 'Ø', 'Ù', 'Û', 'Ü', 'Ú', 'É', 'È', 'Ê', 'Ë', 'Ç', 'Ÿ', 'Ñ', ),
      array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'à', 'â', 'ä', 'á', 'ã', 'å', 'î', 'ï', 'ì', 'í', 'ô', 'ö', 'ò', 'ó', 'õ', 'ø', 'ù', 'û', 'ü', 'ú', 'é', 'è', 'ê', 'ë', 'ç', 'ÿ', 'ñ', ),
      $strChaine
   );
   return ($strChaine);
}
/*
|-------------------------------------------------------------------------------------|
| majuscules (2018-03-10)
|-------------------------------------------------------------------------------------|
*/
function majusculesx($strChaine)
{
   $strChaine = strtoupper($strChaine);
   $strChaine = str_replace("ç", "Ç", $strChaine);
   $strChaine = str_replace("à", "À", $strChaine);
   $strChaine = str_replace("â", "Â", $strChaine);
   $strChaine = str_replace("ä", "Ä", $strChaine);
   $strChaine = str_replace("é", "É", $strChaine);
   $strChaine = str_replace("è", "È", $strChaine);
   $strChaine = str_replace("ê", "Ê", $strChaine);
   $strChaine = str_replace("ë", "Ë", $strChaine);
   $strChaine = str_replace("ì", "Ì", $strChaine);
   $strChaine = str_replace("î", "Î", $strChaine);
   $strChaine = str_replace("ï", "Ï", $strChaine);
   $strChaine = str_replace("ò", "Ò", $strChaine);
   $strChaine = str_replace("ô", "Ô", $strChaine);
   $strChaine = str_replace("ö", "Ö", $strChaine);
   $strChaine = str_replace("ù", "Ù", $strChaine);
   $strChaine = str_replace("û", "Û", $strChaine);
   $strChaine = str_replace("ü", "Ü", $strChaine);
   return $strChaine;
}
/*
|-------------------------------------------------------------------------------------|
| minuscules (2018-03-10)
|-------------------------------------------------------------------------------------|
*/
function minusculesx($strChaine)
{
   $strChaine = strtolower($strChaine);
   $strChaine = str_replace("Ç", "ç", $strChaine);
   $strChaine = str_replace("À", "à", $strChaine);
   $strChaine = str_replace("Â", "â", $strChaine);
   $strChaine = str_replace("Ä", "ä", $strChaine);
   $strChaine = str_replace("É", "é", $strChaine);
   $strChaine = str_replace("È", "è", $strChaine);
   $strChaine = str_replace("Ê", "ê", $strChaine);
   $strChaine = str_replace("Ë", "ë", $strChaine);
   $strChaine = str_replace("Ì", "ì", $strChaine);
   $strChaine = str_replace("Î", "î", $strChaine);
   $strChaine = str_replace("Ï", "ï", $strChaine);
   $strChaine = str_replace("Ò", "ò", $strChaine);
   $strChaine = str_replace("Ô", "ô", $strChaine);
   $strChaine = str_replace("Ö", "ö", $strChaine);
   $strChaine = str_replace("Ù", "ù", $strChaine);
   $strChaine = str_replace("Û", "û", $strChaine);
   $strChaine = str_replace("Ü", "ü", $strChaine);
   return $strChaine;
}
/*
|-------------------------------------------------------------------------------------|
| mois (2018-02-05)
| > mois("aaaa-mm-jj") ou mois("jj-mm-aaaa") retourne mm
|-------------------------------------------------------------------------------------|
*/
function mois($strDate)
{
   if (substr($strDate, 4, 1) == "-") {
      /* AAAA-MM-JJ */
      $intMois = intval(substr($strDate, 5, 2), 10);
   } else {
      /* JJ-MM-AAAA */
      $intMois = intval(substr($strDate, 3, 2), 10);
   }
   return $intMois;
}
/*
|-------------------------------------------------------------------------------------|
| moisEnLitteral (2017-01-31)
| Scénarios : moisEnLitteral($intMois)                => Première lettre en minuscule
|             moisEnLitteral($intMois, $binMajuscule) => En fonction de $binMajuscule
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
    *    $strMois = ucfirst($strMois);
    */
   $strMois = $binMajuscule ? ucfirst($strMois) : $strMois;

   return $strMois;
}
/*
|-------------------------------------------------------------------------------------|
| nombreJoursAnnee (2014-04-13)
|-------------------------------------------------------------------------------------|
*/
function nombreJoursAnnee($intAnnee)
{
   return (bissextile($intAnnee) ? 366 : 365);
}
/*
|-------------------------------------------------------------------------------------|
| nombreJoursEntreDeuxDates (2017-01-29)
| $strDate1 et $strDate2 peuvent être saisis selon le format JJ-MM-AAAA ou AAAA-MM-JJ
|-------------------------------------------------------------------------------------|
 */
function nombreJoursEntreDeuxDates($strDate1, $strDate2)
{
   /* La date ne doit pas être antérieure au 14 décembre 1901 */
   $intNoSerie1 = strtotime($strDate1) / 86400;
   $intNoSerie2 = strtotime($strDate2) / 86400;
   return round($intNoSerie2 - $intNoSerie1);
}
/*
|-------------------------------------------------------------------------------------|
| nombreJoursMois (2017-01-29)
|-------------------------------------------------------------------------------------|
*/
function nombreJoursMois($intMois, $intAnnee)
{
   $strDate = strtotime("01-$intMois-$intAnnee");
   return date("t", $strDate);
}
/*
|----------------------------------------------------------------------------------|
| ouvreFichier() (2018-03-13)
| Réf.: http://ca.php.net/manual/fr/function.fopen.php
|       http://ca.php.net/manual/fr/function.strtoupper.php
|----------------------------------------------------------------------------------|
*/
function ouvreFichier($strNomFichier, $strMode = "L")
{
   switch (strtoupper($strMode)) {
      case "A":
      case "A":
         $strMode = "a";
         break;
      case "E":
      case "W":
         $strMode = "w";
         break;
      case "L":
      case "R":
         $strMode = "r";
         break;
   }
   $fp = fopen($strNomFichier, $strMode);
   return $fp;
}
/*
|-----------------------------------------------------------------------------------|
| post (2017-01-31)
| Scénario : post($strNomParametre) retourne la valeur du paramètre ou NULL
|-----------------------------------------------------------------------------------|
*/
function post($strNomParametre)
{
   return isset($_POST[$strNomParametre]) ? $_POST[$strNomParametre] : null;
}
/*
|-------------------------------------------------------------------------------------|
| pourcent (2018-01-06)
|-------------------------------------------------------------------------------------|
*/
function pourcent($dblNombre, $intNbDecimales = 2)
{
   return number_format(doubleval($dblNombre), $intNbDecimales, ",", " ") . " %";
}
/*
|-------------------------------------------------------------------------------------|
| renommeFichier (2018-01-31)
|-------------------------------------------------------------------------------------|
*/
function renommeFichier($strAncienNom, $strNouveauNom, $binVerifie)
{
   if ($binVerifie) {
      if (file_exists($strAncienNom) && !file_exists($strNouveauNom)) {
         return rename($strAncienNom, $strNouveauNom);
      }
      return false;
   } else {
      if (file_exists($strNouveauNom)) {
         unlink($strNouveauNom);
      }
      return rename($strAncienNom, $strNouveauNom);
   }
}

?>