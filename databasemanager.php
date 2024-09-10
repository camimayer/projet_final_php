<?php
require_once '../config/localhost.php';

class DatabaseManager
{
    private $connection;
    public $requete = "";  // Requête exécutée
    public $OK = false;    // État de l'opération

    // Connexion à la base de données
    public function __construct()
    {
        $this->connection = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);

        if ($this->connection->connect_error) {
            die("Connexion échouée: " . $this->connection->connect_error);
        } else {
            //echo "</br>Connexion réussie à la base de données MySQL.";
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }
    public function loginUser($courriel, $password)
    {
        // Préparer la requête SQL pour obtenir les données de l'utilisateur à partir de l'adresse e-mail
        $stmt = $this->connection->prepare("SELECT NoUtilisateur, Courriel, MotDePasse, Nom, Prenom FROM utilisateurs WHERE Courriel = ?");
        $stmt->bind_param("s", $courriel);
        $stmt->execute();
        $result = $stmt->get_result();

        // Vérifier si l'utilisateur a été trouvé
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Vérifier le mot de passe
            if ($password == $user['MotDePasse']) {
                // Si le mot de passe est correct, retourner les informations de l'utilisateur
                return [
                    'success' => true,
                    'nom' => $user['Nom'],
                    'prenom' => $user['Prenom']
                ];
            } else {
                // Si le mot de passe est incorrect
                return [
                    'success' => false,
                    'message' => "Mot de passe incorrect."
                ];
            }
        } else {
            // Si l'utilisateur n'est pas trouvé
            return [
                'success' => false,
                'message' => "Aucun utilisateur trouvé avec cette adresse e-mail."
            ];
        }
    }

    // Méthode pour créer une table
    public function createTable($tableName, ...$fields)
    {
        // Vérifier si la table existe déjà
        $checkQuery = "SHOW TABLES LIKE '$tableName'";
        $result = $this->connection->query($checkQuery);

        if ($result->num_rows == 0) {
            // Construire la requête SQL pour créer la table
            $this->requete = "CREATE TABLE $tableName (" . implode(", ", $fields) . ")";
            if ($this->connection->query($this->requete) === TRUE) {
                echo "Table '$tableName' créée avec succès.<br>";
                $this->OK = true;
            } else {
                echo "Erreur lors de la création de la table '$tableName': " . $this->connection->error . "<br>";
                $this->OK = false;
            }
        } else {
            //echo "La table '$tableName' existe déjà.<br>";
            $this->OK = false;
        }
    }

    // Méthode pour supprimer une table
    public function dropTable($tableName)
    {
        $this->requete = "DROP TABLE IF EXISTS $tableName";
        if ($this->connection->query($this->requete) === TRUE) {
            echo "Table '$tableName' supprimée avec succès.<br>";
            $this->OK = true;
        } else {
            echo "Erreur lors de la suppression de la table '$tableName': " . $this->connection->error . "<br>";
            $this->OK = false;
        }
    }

    // Méthode saveUser pour sauvegarder un utilisateur
    public function saveUser($email, $password)
    {
        $stmt = $this->connection->prepare("INSERT INTO utilisateurs (Courriel, MotDePasse) VALUES (?, ?)");
        // $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param("ss", $email, $password);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Méthode pour insérer un enregistrement générique
    public function insertRecord($tableName, ...$values)
    {
        // Échapper les valeurs pour éviter les injections SQL
        $escapedValues = array_map(function ($value) {
            if (is_null($value)) {
                return 'NULL';
            } else {
                return "'" . $this->connection->real_escape_string($value) . "'";
            }
        }, $values);

        $valuesStr = implode(", ", $escapedValues);
        $this->requete = "INSERT INTO $tableName VALUES ($valuesStr)";
        $this->OK = $this->connection->query($this->requete);

        if ($this->OK) {
            echo "Insertion réussie dans '$tableName'.<br>";
        } else {
            echo "Erreur lors de l'insertion: " . $this->connection->error . "<br>";
        }

        return $this->OK;
    }

    // Méthode pour afficher des informations sur la base de données
    public function displayTableInfo()
    {
        $result = $this->connection->query("SHOW TABLES");
        if ($result) {
            while ($row = $result->fetch_row()) {
                echo "Table: $row[0]<br>";
            }
        } else {
            echo "Erreur lors de la récupération des tables: " . $this->connection->error;
        }
    }

    // Fermer la connexion à la base de données
    public function closeConnection()
    {
        $this->connection->close();
        echo "Déconnexion réussie.";
    }
}
?>