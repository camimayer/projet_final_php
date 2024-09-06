<?php
class fichier
{
   /*
   |----------------------------------------------------------------------------------|
   | attributs
   |----------------------------------------------------------------------------------|
   */
   private $fp = null;
   public $intNbLignes = null;
   public $intTaille = null;
   public $strLigneCourante = null;
   public $strNom = null;
   public $strContenu = null;
   public $strContenuHTML = null;
   public $tContenu = array();
   public $strMode = null;  // Ajout de l'attribut strMode
   /*
   |----------------------------------------------------------------------------------|
   | constructeur
   |----------------------------------------------------------------------------------|
   */
   function __construct($strNomFichier)
   {
      $this->strNom = $strNomFichier;
      if (func_num_args() == 2) {
         $this->ouvre(func_get_arg(1));
      }
   }
   /*
   |----------------------------------------------------------------------------------|
   | chargeEnMemoire() (2018-03-13; 2019-03-12; 2020-03-22)
   | Réf.: http://php.net/manual/fr/function.count.php
   | http://ca.php.net/manual/fr/function.file.php
   | http://php.net/manual/fr/function.file-get-contents.php
   | http://ca.php.net/manual/fr/function.str-replace.php
   | http://php.net/manual/fr/function.strlen.php
   |----------------------------------------------------------------------------------|
   */
   function chargeEnMemoire()
   {
      if ($this->existe()) {
         // Charger les lignes du fichier dans un tableau
         $this->tContenu = file($this->strNom, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

         // Remplacer les separateurs
         $this->tContenu = str_replace("\n", "", str_replace("\r", "", $this->tContenu));

         // Compter le nombre de lignes
         $this->intNbLignes = count($this->tContenu);

         // Charger le contenu complet du fichier en une seule chaîne
         $this->strContenu = file_get_contents($this->strNom);
         $this->intTaille = strlen($this->strContenu);

         // Convertir les caractères spéciaux pour affichage en HTML
         $this->strContenuHTML = str_replace(
            '\n\r',
            "<br />",
            str_replace("\r\n", "<br />", $this->strContenu)
         );

         // En cas de succès, retourner vrai
         return true;
      }

      // Si le fichier n'existe pas, retourner faux
      return false;
   }
   /*
   |----------------------------------------------------------------------------------|
   | compteLignes() (2018-03-13; 2019-03-12; 2020-03-22)
   | Réf.: http://ca.php.net/manual/fr/function.count.php
   | http://ca.php.net/manual/fr/function.file.php
   |----------------------------------------------------------------------------------|
   */
   function compteLignes()
   {
      $this->intNbLignes = -1;
      if ($this->existe()) {
         $this->intNbLignes = count(file($this->strNom));
      }
      return $this->intNbLignes;
   }
   /*
       |----------------------------------------------------------------------------------|
       | copie() (2024-08-13)
       |----------------------------------------------------------------------------------|
       */
   public function copie($nouveauNomFichier = null)
   {
      // Cas où un nouveau nom de fichier est spécifié
      if ($nouveauNomFichier !== null) {
         if (file_exists($nouveauNomFichier)) {
            // Si le fichier existe déjà, retourner false
            return false;
         } else {
            // Si le fichier n'existe pas, faire la copie et retourner true
            return copy($this->strNom, $nouveauNomFichier);
         }
      } else {
         // Cas où aucun argument n'est passé, on incrémente le nom du fichier
         $pathInfo = pathinfo($this->strNom);
         $baseName = $pathInfo['filename'];
         $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';
         $directory = isset($pathInfo['dirname']) ? $pathInfo['dirname'] . DIRECTORY_SEPARATOR : '';

         $i = 1;
         do {
            $nouveauNomFichier = sprintf("%s%s (%03d)%s", $directory, $baseName, $i++, $extension);
         } while (file_exists($nouveauNomFichier));

         // Effectuer la copie avec le nom incrémenté et retourner true
         return copy($this->strNom, $nouveauNomFichier);
      }
   }
   /*
   |----------------------------------------------------------------------------------|
   | detecteFin() (2018-03-13; 2019-03-12; 2020-03-22)
   | Réf.: http://php.net/manual/fr/function.feof.php
   |----------------------------------------------------------------------------------|
   */
   function detecteFin()
   {
      $binVerdict = true;
      if ($this->fp) {
         $binVerdict = feof($this->fp);
      }
      return $binVerdict;
   }
   /*
   |----------------------------------------------------------------------------------|
   | ecritLigne() (2018-03-13; 2019-03-12; 2020-03-22)
   | Réf.: http://php.net/manual/fr/function.fputs.php
   | http://php.net/manual/fr/function.gettype.php
   |----------------------------------------------------------------------------------|
   */
   function ecritLigne($strLigneCourante, $binSaut_intNbLignesSaut = false)
   {
      $binVerdict = fputs($this->fp, $strLigneCourante);
      if ($binVerdict) {
         switch (gettype($binSaut_intNbLignesSaut)) {
            case "integer":
               for ($i = 1; $i <= $binSaut_intNbLignesSaut && $binVerdict; $i++) {
                  $binVerdict = fputs($this->fp, "\r\n");
               }
               break;
            case "boolean":
               if ($binSaut_intNbLignesSaut) {
                  $binVerdict = fputs($this->fp, "\r\n");
               }
               break;
         }
      }
      return $binVerdict;
   }
   /*
   |----------------------------------------------------------------------------------|
   | existe() (2018-03-13; 2019-03-12; 2020-03-22)
   | Réf.: http://ca.php.net/manual/fr/function.file-exists.php
   |----------------------------------------------------------------------------------|
   */
   function existe()
   {
      return file_exists($this->strNom);
   }
   /*
   |----------------------------------------------------------------------------------|
   | ferme() (2018-03-13; 2019-03-12; 2020-03-22)
   | Réf.: http://ca.php.net/manual/fr/function.fclose.php
   |----------------------------------------------------------------------------------|
   */
   function ferme()
   {
      $binVerdict = false;
      if ($this->fp) {
         $binVerdict = fclose($this->fp);
      }
      return $binVerdict;
   }

   /*
    |----------------------------------------------------------------------------------|
    | identiqueA() (2024-08-13)
    |----------------------------------------------------------------------------------|
    */
   public function identiqueA($autreFichier)
   {
      if (!file_exists($autreFichier)) {
         // Si le fichier comparé n'existe pas, retourner false
         return false;
      }

      // Lire le contenu des deux fichiers
      $contenu1 = file_get_contents($this->strNom);
      $contenu2 = file_get_contents($autreFichier);

      // Comparer les contenus
      return $contenu1 === $contenu2;
   }
   /*
   |----------------------------------------------------------------------------------|
   | litDonneesLigne() (2018-03-13; 2019-03-12; 2020-03-22)
   | Ref. : http://php.net/manual/fr/function.array-combine.php
   | http://php.net/manual/fr/function.func-get-arg.php
   | http://php.net/manual/fr/function.func-num-args.php
   | http://stackoverflow.com/questions/6814760/php-using-explode-function-to-assign-values-to-an-associative-array
   |----------------------------------------------------------------------------------|
   */

   function litDonneesLigne(&$tValeurs, $strSeparateur)
   {
      // Parcourir les arguments supplémentaires pour définir les clés de tValeurs
      for ($i = 2; $i <= func_num_args() - 1; $i++) {
         $tValeurs[func_get_arg($i)] = func_get_arg($i);
      }

      // Lire la ligne du fichier
      $ligne = $this->litLigne();
      $valeurs = explode($strSeparateur, $ligne);

      // Vérifier si le nombre de clés et de valeurs correspond
      if (count($tValeurs) !== count($valeurs)) {
         echo "Erreur : La ligne ne contient pas le nombre correct de colonnes. " .
            "On attendait " . count($tValeurs) . " colonnes, mais " .
            count($valeurs) . " colonnes ont été trouvées.<br>";
         return false;  // Ignorer la ligne s'il y a un problème
      }

      // Combiner les clés avec les valeurs
      $tValeurs = array_combine(array_keys($tValeurs), $valeurs);
   }
   /*
|----------------------------------------------------------------------------------|
| litLigne() (2018-03-13; 2019-03-12; 2020-03-22)
| Réf.: http://ca.php.net/manual/fr/function.fgets.php
| http://ca.php.net/manual/fr/function.str-replace.php
|----------------------------------------------------------------------------------|
*/
   function litLigne()
   {
      $this->strLigneCourante = str_replace(
         "\n",
         "",
         str_replace("\r", "", fgets($this->fp))
      );
      return $this->strLigneCourante;
   }
   /*
   |----------------------------------------------------------------------------------|
   | ouvre() (2018-03-13; 2019-03-12; 2020-03-22)
   | Réf.: http://ca.php.net/manual/fr/function.fopen.php
   | http://ca.php.net/manual/fr/function.strtoupper.php
   |----------------------------------------------------------------------------------|
   */
   function ouvre($strMode = "L")
   {
      switch (strtoupper($strMode)) {
         case "A":
            $this->strMode = "A";
            $strMode = "a";
            break;
         case "E":
         case "W":
            $this->strMode = "E";
            $strMode = "w";
            break;
         case "L":
         case "R":
            $this->strMode = "L";
            $strMode = "r";
            break;
         default:
            $this->strMode = "L";
            $strMode = "r";
            break;
      }
      $this->fp = fopen($this->strNom, $strMode);
      return $this->fp;
   }

   /*
    |----------------------------------------------------------------------------------|
    | renommePour() (2024-08-13)
    |----------------------------------------------------------------------------------|
    */
   public function renommePour($nouveauNomFichier)
   {
      // Vérifier si le fichier courant existe
      if (!file_exists($this->strNom)) {
         return false;
      }

      // Renommer le fichier
      if (rename($this->strNom, $nouveauNomFichier)) {
         // Mettre à jour le nom du fichier dans l'objet
         $this->strNom = $nouveauNomFichier;
         return true;
      } else {
         return false;
      }
   }
}
?>