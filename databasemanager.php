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
            Statut INT(1) NOT NULL CHECK (Statut IN (0, 1, 2, 3, 4, 5, 9)),
            NoEmpl INT(4),
            Nom VARCHAR(25) NOT NULL,
            Prenom VARCHAR(20) NOT NULL,
            NoTelMaison VARCHAR(15),
            NoTelTravail VARCHAR(21),
            NoTelCellulaire VARCHAR(15),
            Modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            AutresInfos VARCHAR(50),
            Token VARCHAR(255)
        )";

        if ($this->connection->query($sql) === TRUE) {
            echo "<script>console.log('Table `utilisateurs` créée avec succès.');</script>";
            // Vérifie si l'utilisateur admin est déjà enregistré
            $result = $this->connection->query("SELECT COUNT(*) as count FROM utilisateurs WHERE Courriel = 'admin@gmail.com'");
            $row = $result->fetch_assoc();
            if ($row['count'] == 0) {
                // Enregistrer l'utilisateur administrateur avec un token
                $token = bin2hex(random_bytes(50));
                if ($this->saveUserWithToken("admin@gmail.com", "Secret123", $token)) {
                    echo "<script>console.log('Utilisateur Admin créé avec succès.');</script>";
                    $this->connection->query("UPDATE utilisateurs SET Statut = 1 WHERE Courriel = 'admin@gmail.com'");
                }
            }
        } else {
            echo "<script>console.log('Erreur lors de la création de la table `utilisateurs`: " . $this->connection->error . "');</script>";
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
            echo "<script>console.log('Table `connexions` créée avec succès.');</script>";
        } else {
            echo "<script>console.log('Erreur lors de la création de la table `connexions`: " . $this->connection->error . "');</script>";
        }

        // Création de la table 'categories'
        $sql = "CREATE TABLE IF NOT EXISTS categories (
                NoCategorie INT(1) AUTO_INCREMENT PRIMARY KEY,
                Description VARCHAR(20) NOT NULL
            )";

        if ($this->connection->query($sql) === TRUE) {
            echo "<script>console.log('Table `categories` créée avec succès.');</script>";
            $this->insertDefaultCategories(); // Insertion des catégories prédéfinies
        } else {
            echo "<script>console.log('Erreur lors de la création de la table `categories`: " . $this->connection->error . "');</script>";
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
            echo "<script>console.log('Table `annonces` créée avec succès.');</script>";
        } else {
            echo "<script>console.log('Erreur lors de la création de la table `annonces`: " . $this->connection->error . "');</script>";
        }
    }
    // Méthode pour enregistrer un utilisateur
    public function saveUserWithToken($courriel, $password, $token)
    {
        // Preparar a instrução SQL
        $stmt = $this->connection->prepare("INSERT INTO utilisateurs (Courriel, MotDePasse, Creation, NbConnexions, Statut, Token)
                                            VALUES (?, ?, NOW(), 0, 0, ?)");

        if ($stmt === false) {
            echo "Erreur lors de la préparation de la requête: " . $this->connection->error;
            return false;
        }

        // Vincular os parâmetros
        $stmt->bind_param("sss", $courriel, $password, $token);

        // Executar a consulta
        if ($stmt->execute()) {
            return true; // Sucesso na inscrição
        } else {
            echo "Erreur lors de l'inscription: " . $stmt->error;
            return false; // Falha na inscrição
        }

        $stmt->close();
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
            echo "<script>'Les catégories existent déjà.<br>'</script>";
        }
    }

    public function loginUser($courriel, $password)
    {
        // Préparer la requête pour vérifier les identifiants
        $stmt = $this->connection->prepare("SELECT NoUtilisateur, MotDePasse, Nom, Prenom, Statut, NbConnexions FROM utilisateurs WHERE Courriel = ?");
        $stmt->bind_param("s", $courriel);
        $stmt->execute();
        $result = $stmt->get_result();

        // Vérifier si l'utilisateur a été trouvé
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // // Vérifier si le compte a été activé (Statut != 0)
            // if ($user['Statut'] == 0) {
            //     return [
            //         'success' => false,
            //         'message' => "Votre compte n'a pas encore été vérifié. Veuillez vérifier votre e-mail pour activer votre compte."
            //     ];
            // }

            // Vérifier si le mot de passe correspond
            if ($password == $user['MotDePasse']) {
                // Vérifier si l'utilisateur a une connexion active (Deconnexion est NULL)
                $stmt = $this->connection->prepare("SELECT NoConnexion FROM connexions WHERE NoUtilisateur = ? AND Deconnexion IS NULL ORDER BY NoConnexion DESC LIMIT 1");
                $stmt->bind_param("i", $user['NoUtilisateur']);
                $stmt->execute();
                $activeConnectionResult = $stmt->get_result();

                if ($activeConnectionResult->num_rows > 0) {
                    // Si une connexion active existe, mettre à jour l'heure de déconnexion
                    $activeConnection = $activeConnectionResult->fetch_assoc();
                    $stmt = $this->connection->prepare("UPDATE connexions SET Deconnexion = NOW() WHERE NoConnexion = ?");
                    $stmt->bind_param("i", $activeConnection['NoConnexion']);
                    $stmt->execute();
                }

                // Incrémenter le nombre de connexions
                $nbConnexions = $user['NbConnexions'] + 1;
                $stmt = $this->connection->prepare("UPDATE utilisateurs SET NbConnexions = ? WHERE NoUtilisateur = ?");
                $stmt->bind_param("ii", $nbConnexions, $user['NoUtilisateur']);
                $stmt->execute();

                // Enregistrer la nouvelle connexion dans la table connexions
                $stmt = $this->connection->prepare("INSERT INTO connexions (NoUtilisateur, Connexion) VALUES (?, NOW())");
                $stmt->bind_param("i", $user['NoUtilisateur']);
                $stmt->execute();

                return [
                    'success' => true,
                    'nom' => $user['Nom'],
                    'prenom' => $user['Prenom'],
                    'statut' => $user['Statut'], // Récupérer le statut de l'utilisateur
                    'nbConnexions' => $nbConnexions,
                    'noUtilisateur' => $user['NoUtilisateur']
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



    public function updateLogoutTime($noUtilisateur)
    {
        // Préparer la requête pour mettre à jour l'heure de déconnexion seulement si elle est NULL
        $stmt = $this->connection->prepare("UPDATE connexions
                                        SET Deconnexion = NOW()
                                        WHERE NoUtilisateur = ?
                                        AND Deconnexion IS NULL
                                        ORDER BY NoConnexion DESC
                                        LIMIT 1");
        $stmt->bind_param("i", $noUtilisateur);

        // Exécuter la requête
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                return true; // Mise à jour réussie
            } else {
                echo "Aucune mise à jour n'a été effectuée, peut-être que l'utilisateur est déjà déconnecté.";
                return false; // Aucun enregistrement à mettre à jour
            }
        } else {
            echo "Erreur lors de la mise à jour de la déconnexion: " . $stmt->error;
            return false; // Échec de la mise à jour
        }

        $stmt->close();
    }

    // Fonction pour récupérer les données utilisateur par e-mail
    public function getUserData($email)
    {
        $query = "SELECT * FROM utilisateurs WHERE Courriel = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Fonction pour mettre à jour le profil utilisateur
    public function updateUser($email, $statut, $noEmp, $nom, $prenom, $telM, $telT, $telC)
    {
        $dateModification = date('Y-m-d H:i:s');
        $query = "UPDATE utilisateurs SET Statut = ?, NoEmpl = ?, Nom = ?, Prenom = ?, NoTelMaison = ?, NoTelTravail = ?, NoTelCellulaire = ?, Modification = ? WHERE Courriel = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("iisssssss", $statut, $noEmp, $nom, $prenom, $telM, $telT, $telC, $dateModification, $email);
        return $stmt->execute();
    }

    public function checkPasswordDB($courriel, $password)
    {
        // Préparer la requête pour vérifier les identifiants
        $stmt = $this->connection->prepare("SELECT MotDePasse FROM utilisateurs WHERE Courriel = ?");
        $stmt->bind_param("s", $courriel);
        $stmt->execute();
        $result = $stmt->get_result();

        // Vérifier si l'utilisateur a été trouvé
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Vérifier si le mot de passe correspond
            if ($password == $user['MotDePasse']) {
                return true;
            }else {
                return false;
            }
        }
        return false;
    }

    public function updatePassword($email, $password)
    {
        $dateModification = date('Y-m-d H:i:s');
        $query = "UPDATE utilisateurs SET MotDePasse = ?, Modification = ? WHERE Courriel = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("sss", $password, $dateModification, $email);
        return $stmt->execute();
    }

    // Fermer la connexion à la base de données
    public function closeConnection()
    {
        $this->connection->close();
    }

}
?>