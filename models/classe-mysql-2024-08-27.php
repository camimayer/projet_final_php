<?php
/*
|----------------------------------------------------------------------------------------|
| class mysql
|----------------------------------------------------------------------------------------|
*/
class mysql
{
   /*
   |----------------------------------------------------------------------------------|
   | Attributs
   |----------------------------------------------------------------------------------|
   */
   public $cBD = null;                       /* Identifiant de connexion */
   public $listeEnregistrements = null;      /* Liste des enregistrements retournés */
   public $nomFichierInfosSensibles = "";    /* Nom du fichier 'InfosSensibles' */
   public $nomBD = "";                       /* Nom de la base de données */
   public $OK = false;                       /* Opération réussie ou non */
   public $requete = "";                     /* Requête exécutée */
   /*
   |----------------------------------------------------------------------------------|
   | __construct
   | Constructeur de la classe MySQL
   |----------------------------------------------------------------------------------|
   */
   function __construct($strNomBD, $strNomFichierInfosSensibles)
   {
      $this->nomBD = $strNomBD;
      $this->nomFichierInfosSensibles = $strNomFichierInfosSensibles;
      $this->connexion();
      $this->selectionneBD();
   }
   /*
   |----------------------------------------------------------------------------------|
   | connexion()
   | Connexion à la base de données MySQL
   |----------------------------------------------------------------------------------|
   */
   function connexion()
   {
      require($this->nomFichierInfosSensibles);
      $this->cBD = mysqli_connect("localhost", $strNomAdmin, $strMotPasseAdmin);

      if (mysqli_connect_errno()) {
         echo "</br>";
         echo "Problème de connexion... Erreur no " . mysqli_connect_errno() . " (" . mysqli_connect_error() . ")";
         die();
      } else {
         echo "</br>Connexion réussie à la base de données MySQL.";
      }

      return $this->cBD;
   }
   /*
   |----------------------------------------------------------------------------------|
   | copieEnregistrements
   | Copie les enregistrements d'une table source vers une table cible
   |----------------------------------------------------------------------------------|
   */
   function copieEnregistrements($strNomTableSource, $strListeChampsSource, $strNomTableCible, $strListeChampsCible, $strListeConditions = "")
   {
      // Si aucun champ cible n'est spécifié, on utilise les mêmes que ceux de la source
      if (empty($strListeChampsCible)) {
         $strListeChampsCible = $strListeChampsSource;
      }

      // Construire la requête SQL
      $requete = "INSERT INTO $strNomTableCible ($strListeChampsCible) SELECT $strListeChampsSource FROM $strNomTableSource";

      // Ajouter les conditions si elles sont spécifiées
      if (!empty($strListeConditions)) {
         $requete .= " WHERE $strListeConditions";
      }

      // Exécuter la requête
      $this->requete = $requete;
      $this->OK = mysqli_query($this->cBD, $this->requete);

      // Vérifier le résultat de la requête
      if (!$this->OK) {
         echo "Erreur lors de la copie: " . mysqli_error($this->cBD) . "<br>";
      } else {
         echo "Copie réussie.<br>";
      }

      return $this->OK;
   }
   /*
   |----------------------------------------------------------------------------------|
   | creeTable
   | Crée une table avec une liste de champs donnés
   |----------------------------------------------------------------------------------|
   */
   function creeTable($strNomTable, ...$champs)
   {
      // Construire la requête SQL pour créer la table
      $this->requete = "CREATE TABLE $strNomTable (";
      $this->requete .= implode(", ", $champs);
      $this->requete .= ")";

      // Exécuter la requête
      if (mysqli_query($this->cBD, $this->requete)) {
         $this->OK = true;
      } else {
         $this->OK = false;
         echo "Erreur lors de la création de la table: " . mysqli_error($this->cBD);
      }
   }
   /*
   |----------------------------------------------------------------------------------|
   | creeTableGenerique
   | Crée une table avec une structure générique
   |----------------------------------------------------------------------------------|
   */
   function creeTableGenerique($strNomTable, $strDefinitions, $strCles)
   {
      // Initialiser la requête SQL
      $this->requete = "CREATE TABLE $strNomTable (";

      // Diviser les définitions des champs en un tableau
      $champs = explode(";", $strDefinitions);

      // Parcourir les définitions et les ajouter à la requête SQL
      foreach ($champs as $champ) {
         list($type, $nomChamp) = explode(",", $champ);

         switch ($type[0]) {
            case 'N': // INT
               $this->requete .= "$nomChamp INT, ";
               break;
            case 'B': // BOOL
               $this->requete .= "$nomChamp BOOL, ";
               break;
            case 'C': // DECIMAL(x,y)
               preg_match('/C(\d+)\.(\d+)/', $type, $matches);
               $this->requete .= "$nomChamp DECIMAL($matches[1],$matches[2]), ";
               break;
            case 'D': // DATE
               $this->requete .= "$nomChamp DATE, ";
               break;
            case 'E': // INT (NbJours)
               $this->requete .= "$nomChamp INT, ";
               break;
            case 'F': // CHAR(x)
               preg_match('/F(\d+)/', $type, $matches);
               $this->requete .= "$nomChamp CHAR($matches[1]), ";
               break;
            case 'M': // DECIMAL (Prix)
               $this->requete .= "$nomChamp DECIMAL(10,2), ";  // Ajuster le type de Prix à DECIMAL
               break;
            case 'V': // VARCHAR(x)
               preg_match('/V(\d+)/', $type, $matches);
               $this->requete .= "$nomChamp VARCHAR($matches[1]), ";
               break;
         }
      }

      // Retirer la dernière virgule et ajouter la clé primaire
      $this->requete = rtrim($this->requete, ', ') . ", PRIMARY KEY($strCles))";

      // Exécuter la requête
      $this->OK = mysqli_query($this->cBD, $this->requete);

      // Vérifier si la requête a réussi
      if (!$this->OK) {
         echo "Erreur lors de la création de la table: " . mysqli_error($this->cBD);
      } else {
         echo "Table $strNomTable créée avec succès.";
      }
   }
   /*
   |----------------------------------------------------------------------------------|
   | deconnexion
   | Déconnecte la base de données
   |----------------------------------------------------------------------------------|
   */
   function deconnexion()
   {
      if ($this->cBD) {
         mysqli_close($this->cBD);
         echo "Déconnexion réussie.";
      }
   }
   /*
   |----------------------------------------------------------------------------------|
   | insereEnregistrement
   | Insère un enregistrement dans une table
   |----------------------------------------------------------------------------------|
   */
   function insereEnregistrement($strNomTable, ...$valeurs)
   {
      // Valider les dates avant d'insérer
      foreach ($valeurs as &$valeur) {
         if (is_string($valeur) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $valeur)) {
            $partes = explode('-', $valeur);
            // Vérifier que la date est valide (checkdate prend mois, jour, année)
            if (!checkdate($partes[1], $partes[2], $partes[0])) {
               echo "Erreur: la date '$valeur' n'est pas valide. Ignorer l'enregistrement.<br>";
               return false; // Ignorer l'enregistrement si la date est invalide
            }
         }
      }

      // Vérification des valeurs booléennes pour la colonne "Homme"
      if (isset($valeurs[1])) {
         $valeurs[1] = in_array($valeurs[1], ['oui', 'true', 1], true) ? 1 : (in_array($valeurs[1], ['non', 'false', 0], true) ? 0 : null);
         if (is_null($valeurs[1])) {
            echo "Erreur: Valeur non valide pour 'Homme'. Ignorer l'enregistrement.<br>";
            return false;
         }
      }

      // Vérifier la longueur du champ "NomComplet" (max 40 caractères)
      if (strlen($valeurs[8]) > 40) {
         echo "Erreur: 'NomComplet' dépasse la longueur maximale autorisée. Ignorer l'enregistrement.<br>";
         return false;
      }

      // Vérifier les doublons dans la clé primaire "NoEmploye"
      $requeteVerification = "SELECT COUNT(*) FROM $strNomTable WHERE NoEmploye = {$valeurs[0]}";
      $resultat = mysqli_query($this->cBD, $requeteVerification);
      if (mysqli_fetch_row($resultat)[0] > 0) {
         echo "Erreur: L'enregistrement avec NoEmploye '{$valeurs[0]}' existe déjà. Ignorer l'enregistrement.<br>";
         return false;
      }

      // Échapper les valeurs pour éviter les injections SQL
      $valeursEchappees = array_map(function ($valeur) {
         if (is_null($valeur)) {
            return 'NULL'; // Si la valeur est NULL, représenter explicitement dans SQL
         } elseif (is_bool($valeur)) {
            return $valeur ? 1 : 0; // Convertir les booléens en 1 ou 0
         } elseif (strtolower($valeur) === 'true') {
            return 1;
         } elseif (strtolower($valeur) === 'false') {
            return 0;
         } else {
            return is_string($valeur) ? "'" . mysqli_real_escape_string($this->cBD, $valeur) . "'" : $valeur;
         }
      }, $valeurs);

      // Convertir le tableau de valeurs en une chaîne séparée par des virgules
      $valeursStr = implode(", ", $valeursEchappees);

      // Construire la requête SQL
      $this->requete = "INSERT INTO $strNomTable VALUES ($valeursStr)";
      $this->OK = mysqli_query($this->cBD, $this->requete);

      // Vérifier le résultat de l'insertion
      if (!$this->OK) {
         echo "Erreur lors de l'insertion: " . mysqli_error($this->cBD) . "<br>";
      } else {
         echo "Insertion réussie.<br>";
      }

      // Retourner l'état OK
      return $this->OK;
   }

   /*
   |----------------------------------------------------------------------------------|
   | modifieChamp
   | Modifie un champ dans une table
   |----------------------------------------------------------------------------------|
   */
   function modifieChamp($strNomTable, $strNomChamp, $strNouvelleDefinition)
   {
      // Construire la requête SQL pour changer le nom et la définition du champ
      $this->requete = "ALTER TABLE $strNomTable CHANGE $strNomChamp $strNouvelleDefinition";

      // Exécuter la requête
      $this->OK = mysqli_query($this->cBD, $this->requete);

      // Vérifier si la requête a réussi
      if (!$this->OK) {
         echo "Erreur lors de la modification du champ '$strNomChamp': " . mysqli_error($this->cBD);
      } else {
         echo "Champ '$strNomChamp' modifié avec succès dans la table '$strNomTable'.";
      }

      return $this->OK;
   }
   /*
   |----------------------------------------------------------------------------------|
   | selectionneBD
   | Sélectionne la base de données
   |----------------------------------------------------------------------------------|
   */
   public function selectionneBD()
   {
      if (mysqli_select_db($this->cBD, $this->nomBD)) {
         $this->OK = true;
      } else {
         $this->OK = false;
         echo "Erreur lors de la sélection de la base de données: " . mysqli_error($this->cBD);
      }
   }
   /*
   |----------------------------------------------------------------------------------|
   | supprimeEnregistrements
   | Supprime des enregistrements d'une table
   |----------------------------------------------------------------------------------|
   */
   function supprimeEnregistrements($strNomTable, $strListeConditions = "")
   {
      // Construire la requête SQL
      $requete = "DELETE FROM $strNomTable";

      // Ajouter les conditions si elles sont spécifiées
      if (!empty($strListeConditions)) {
         $requete .= " WHERE $strListeConditions";
      }

      // Exécuter la requête
      $this->requete = $requete;
      $this->OK = mysqli_query($this->cBD, $this->requete);

      // Vérifier le résultat de la requête
      if (!$this->OK) {
         echo "Erreur lors de la suppression: " . mysqli_error($this->cBD) . "<br>";
      } else {
         echo "Suppression réussie.<br>";
      }

      return $this->OK;
   }
   /*
   |----------------------------------------------------------------------------------|
   | supprimeTable
   | Supprime une table
   |----------------------------------------------------------------------------------|
   */
   function supprimeTable($strNomTable)
   {
      // Construire la requête SQL pour supprimer la table
      $this->requete = "DROP TABLE IF EXISTS $strNomTable";

      // Exécuter la requête
      $this->OK = mysqli_query($this->cBD, $this->requete);

      // Vérifier si la requête a réussi
      if (!$this->OK) {
         echo "Erreur lors de la suppression de la table: " . mysqli_error($this->cBD);
      } else {
         echo "Table $strNomTable supprimée avec succès.";
      }
   }

   /*
   |----------------------------------------------------------------------------------|
   | afficheInformationsSurBD
   | Affiche la structure et le contenu de chaque table de la base de données
   |----------------------------------------------------------------------------------|
   */
   function afficheInformationsSurBD()
   {
      echo "<p>";
      echo "<span style='font-family:verdana; font-size:12pt; font-weight:bold; color:black; border:solid 1px black; padding:3px;'>Informations sur la base de données '{$this->nomBD}'</span>";

      $result = mysqli_query($this->cBD, "SHOW TABLES");

      if (!$result) {
         echo "Erreur lors de la récupération des tables: " . mysqli_error($this->cBD);
         return;
      }

      $tableCounter = 0;
      while ($row = mysqli_fetch_row($result)) {
         $tableCounter++;
         $tableName = $row[0];
         echo "<p style='font-family:verdana; font-size:10pt; font-weight:bold; color:red;'>Table no $tableCounter : $tableName</p>";

         $structureResult = mysqli_query($this->cBD, "DESCRIBE $tableName");

         if ($structureResult) {
            $fieldCount = mysqli_num_rows($structureResult);

            $contentResult = mysqli_query($this->cBD, "SELECT * FROM $tableName");
            if ($contentResult) {
               $rowCount = mysqli_num_rows($contentResult);
               echo "<p style='font-family:verdana; font-size:10pt; color:blue;'>$fieldCount champs ont été détectés dans la table.<br>$rowCount enregistrements ont été détectés dans la table.</p>";

               $fieldCounter = 0;
               echo "<p style='font-family:verdana; font-size:10pt; color:blue;'>";
               while ($structureRow = mysqli_fetch_assoc($structureResult)) {
                  $fieldCounter++;
                  $fieldName = "<span style='color:blue;'>{$structureRow['Field']}</span>";
                  $fieldType = "<span style='color:blue;'>{$structureRow['Type']}</span>";

                  $null = $structureRow['Null'] === 'NO' ? '<span style="color:magenta;">[NOT_NULL]</span>' : '';
                  $key = $structureRow['Key'] === 'PRI' ? '<span style="color:magenta;"><strong>[PRI_KEY]</strong></span>' :
                     ($structureRow['Key'] === 'UNI' ? '<span style="color:magenta;"><strong>[UNIQUE]</strong></span>' :
                        ($structureRow['Key'] === 'MUL' ? '<span style="color:magenta;"><strong>[PART_KEY]</strong></span>' : ''));

                  $num = (strpos($structureRow['Type'], 'int') !== false || strpos($structureRow['Type'], 'decimal') !== false) ? '<span style="color:magenta;">[NUM]</span>' : '';
                  $extra = $structureRow['Extra'] ? '<span style="color:magenta;">[' . strtoupper($structureRow['Extra']) . ']</span>' : '';
                  $binary = (strpos($structureRow['Type'], 'binary') !== false) ? '<span style="color:magenta;">[BINARY]</span>' : '';

                  echo "$fieldCounter. $fieldName, $fieldType $null $key $num $binary $extra<br>";
               }
               echo "</p>";

               // Afficher le contenu de la table ou un message si elle est vide
               if ($rowCount > 0) {
                  echo "<table style='border-collapse:collapse;'><tr>";
                  echo "<tbody>";
                  echo "<tr>";
                  // Afficher les en-têtes
                  $fields = mysqli_fetch_fields($contentResult);
                  foreach ($fields as $field) {
                     echo "<td style='font-family:verdana; font-size:10pt; font-weight:bold; color:red; border:solid 1px red;'>{$field->name}</td>";
                  }
                  echo "</tr>";

                  // Afficher les lignes de données
                  while ($contentRow = mysqli_fetch_assoc($contentResult)) {
                     echo "<tr>";
                     foreach ($contentRow as $value) {
                        echo "<td style='font-family:verdana; font-size:10pt; color:blue; border:solid 1px red; padding:3px;'>$value</td>";
                     }
                     echo "</tr>";
                  }
                  echo "</tbody>";
                  echo "</table>";
               } else {
                  // Afficher un message dans un tableau avec les mêmes colonnes
                  echo "<table style='border-collapse:collapse;'><tbody><tr>";
                  $fields = mysqli_fetch_fields($contentResult);

                  // Afficher les en-têtes
                  foreach ($fields as $field) {
                     echo "<td style='font-family:verdana; font-size:10pt; font-weight:bold; color:red; border:solid 1px red; padding:3px;'>{$field->name}</td>";
                  }
                  echo "</tr>";
                  echo "<tr><td style='font-family:verdana; font-size:10pt; color:blue; border:solid 1px red; padding:3px;' colspan='" . count($fields) . "'>Aucun enregistrement</td></tr>";
                  echo "</tbody></table>";
               }

               mysqli_free_result($contentResult);
            } else {
               echo "Erreur lors de la récupération du contenu de la table: " . mysqli_error($this->cBD);
            }

            mysqli_free_result($structureResult);
         } else {
            echo "Erreur lors de la récupération de la structure de la table: " . mysqli_error($this->cBD);
         }

         echo "<hr />";
      }
      echo "</p>";

      mysqli_free_result($result);
   }

   /*
   |----------------------------------------------------------------------------------|
   | formateValeurs
   | Formate les valeurs pour les requêtes SQL
   |----------------------------------------------------------------------------------|
   */
   function formateValeurs($tValeurs)
   {
      foreach ($tValeurs as &$valeur) {
         $valeur = is_numeric($valeur) ? $valeur : "'$valeur'";
      }
      return implode(",", $tValeurs);
   }

}
?>