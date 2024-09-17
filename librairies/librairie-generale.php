<?php

/*
-----------------------------------------------------------------------------------
 ajouteZeros (2024-07-09)
 Scénarios : ajouteZeros($numValeur, $intLargeur)
             ajouteZeros($numValeur, $intLargeur) => Retourne $numValeur préfixé
             avec des zéros pour atteindre une largeur minimale de $intLargeur.
 --------------------------------------------------------------------------------
 */
function ajouteZeros($numValeur, $intLargeur)
{
    return sprintf("%0{$intLargeur}d", $numValeur);
}

/*
-----------------------------------------------------------------------------------
 convertitSousChaineEnEntier (2021-01-20)
 --------------------------------------------------------------------------------
 */
function convertitSousChaineEnEntier($strChaine, $intDepart, $intLongueur)
{
    $intEntier = intval(substr($strChaine, $intDepart, $intLongueur));
    return $intEntier;
}


/*
|-------------------------------------------------------------------------------------|
| er (2021-01-20)
| Scénarios : er($intEntier)
| er($intEntier, $binExposant)
|-------------------------------------------------------------------------------------|
*/
function er($intEntier, $binExposant = true)
{
    return $intEntier . ($intEntier == 1 ? ($binExposant ? "<sup>er</sup>" : "er") : "");
}
?>