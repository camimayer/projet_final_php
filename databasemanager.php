<?php
require_once '../config/localhost.php';

class DatabaseManager
{
    private $connection;

    // Connexion à la base de données
    public function __construct()
    {
        $this->connection = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);

        if ($this->connection->connect_error) {
            die("Connexion échouée: " . $this->connection->connect_error);
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }

    // Méthode pour créer toutes les tables nécessaires
    public function createTables()
    {
        // Création de la table 'utilisateurs'
        $sql = "CREATE TABLE IF NOT EXISTS utilisateurs (
            NoUtilisateur INT(3) AUTO_INCREMENT PRIMARY KEY,
            Courriel VARCHAR(50) NOT NULL UNIQUE,
            MotDePasse VARCHAR(15) NOT NULL,
            Creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            NbConnexions INT(4) DEFAULT 0,
            Statut INT(1) NOT NULL CHECK (Statut IN (0, 1, 2, 3, 4, 5)),
            NoEmpl INT(4),
            Nom VARCHAR(25) NOT NULL,
            Prenom VARCHAR(20) NOT NULL,
            NoTelMaison VARCHAR(15),
            NoTelTravail VARCHAR(21),
            NoTelCellulaire VARCHAR(15),
            Modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            AutresInfos VARCHAR(50)
        )";

        if ($this->connection->query($sql) === TRUE) {
            echo "Table 'utilisateurs' créée avec succès.<br>";
        } else {
            echo "Erreur lors de la création de la table 'utilisateurs': " . $this->connection->error . "<br>";
        }

        // Création de la table 'connexions'
        $sql = "CREATE TABLE IF NOT EXISTS connexions (
            NoConnexion INT(4) AUTO_INCREMENT PRIMARY KEY,
            NoUtilisateur INT(3),
            Connexion DATETIME NOT NULL,
            Deconnexion DATETIME,
            FOREIGN KEY (NoUtilisateur) REFERENCES utilisateurs(NoUtilisateur)
        )";

        if ($this->connection->query($sql) === TRUE) {
            echo "Table 'connexions' créée avec succès.<br>";
        } else {
            echo "Erreur lors de la création de la table 'connexions': " . $this->connection->error . "<br>";
        }

        // Création de la table 'categories'
        $sql = "CREATE TABLE IF NOT EXISTS categories (
                NoCategorie INT(1) AUTO_INCREMENT PRIMARY KEY,
                Description VARCHAR(20) NOT NULL
            )";

        if ($this->connection->query($sql) === TRUE) {
            echo "Table 'categories' créée avec succès.<br>";
            $this->insertDefaultCategories(); // Inserta las categorías predeterminadas
        } else {
            echo "Erreur lors de la création de la table 'categories': " . $this->connection->error . "<br>";
        }

        // Création de la table 'annonces'
        $sql = "CREATE TABLE IF NOT EXISTS annonces (
            NoAnnonce INT(4) AUTO_INCREMENT PRIMARY KEY,
            NoUtilisateur INT(3),
            Parution DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            Categorie INT(1),
            DescriptionAbregee VARCHAR(50),
            DescriptionComplete VARCHAR(250),
            Prix DECIMAL(10, 2) DEFAULT 0.00,
            Photo VARCHAR(50),
            MiseAJour DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            Etat INT(1) CHECK (Etat IN (1, 2, 3)),
            FOREIGN KEY (NoUtilisateur) REFERENCES utilisateurs(NoUtilisateur),
            FOREIGN KEY (Categorie) REFERENCES categories(NoCategorie)
        )";

        if ($this->connection->query($sql) === TRUE) {
            echo "Table 'annonces' créée avec succès.<br>";
        } else {
            echo "Erreur lors de la création de la table 'annonces': " . $this->connection->error . "<br>";
        }
    }

    // Méthode pour enregistrer un utilisateur
    public function saveUser($courriel, $password)
    {
        $stmt = $this->connection->prepare("INSERT INTO utilisateurs (Courriel, MotDePasse, Creation, NbConnexions, Statut) 
                                            VALUES (?, ?, NOW(), 0, 0)");
        if ($stmt) {
            $stmt->bind_param("ss", $courriel, $password);
            if ($stmt->execute()) {
                return true; // Inscription réussie
            } else {
                return false; // Échec de l'inscription
            }
            $stmt->close();
        } else {
            return false; // Échec de la préparation de la requête
        }
    }

    // Méthode pour insérer un enregistrement générique
    public function insertRecord($tableName, ...$values)
    {
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
    public function insertDefaultCategories()
    {
        $result = $this->connection->query("SELECT COUNT(*) as count FROM categories");
        $row = $result->fetch_assoc();

        if ($row['count'] == 0) {
            $sql = "INSERT INTO categories (NoCategorie, Description) VALUES
            (1, 'Location'),
            (2, 'Recherche'),
            (3, 'À vendre'),
            (4, 'À donner'),
            (5, 'Service offert'),
            (6, 'Autre')";

            if ($this->connection->query($sql) === TRUE) {
                echo "Catégories prédéfinies insérées avec succès.<br>";
            } else {
                echo "Erreur lors de l'insertion des catégories: " . $this->connection->error . "<br>";
            }
        } else {
            echo "Les catégories existent déjà.<br>";
        }
    }

    public function loginUser($courriel, $password)
    {
        // Préparer la requête pour vérifier les identifiants
        $stmt = $this->connection->prepare("SELECT MotDePasse, Nom, Prenom FROM utilisateurs WHERE Courriel = ?");
        $stmt->bind_param("s", $courriel);
        $stmt->execute();
        $result = $stmt->get_result();

        // Vérifier si l'utilisateur a été trouvé
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Vérifier si le mot de passe correspond
            if ($password == $user['MotDePasse']) {
                return [
                    'success' => true,
                    'nom' => $user['Nom'],
                    'prenom' => $user['Prenom']
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Mot de passe incorrect."
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => "Aucun utilisateur trouvé avec cette adresse e-mail."
            ];
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