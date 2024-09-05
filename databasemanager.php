<?php
class DatabaseManager
{
    private $conn;

    public function __construct($servername, $username, $password, $dbname)
    {
        // Creer conexion
        $this->conn = new mysqli($servername, $username, $password, $dbname);

        // Verifier conexion
        if ($this->conn->connect_error) {
            die("Conexão falhou: " . $this->conn->connect_error);
        }
    }

    public function createTables()
    {
        // Creer table 'utilisateurs'
        $sql = "CREATE TABLE IF NOT EXISTS utilisateurs (
            NoUtilisateur INT(3) AUTO_INCREMENT PRIMARY KEY,
            Courriel VARCHAR(50) NOT NULL UNIQUE,
            MotDePasse VARCHAR(15) NOT NULL,
            Creation DATETIME NOT NULL,
            NbConnexions INT(4) DEFAULT 0,
            Statut INT(1) NOT NULL CHECK (Statut IN (0, 1, 2, 3, 4, 5)),
            NoEmpl INT(4),
            Nom VARCHAR(25) NOT NULL,
            Prenom VARCHAR(20) NOT NULL,
            NoTelMaison VARCHAR(15),
            NoTelTravail VARCHAR(21),
            NoTelCellulaire VARCHAR(15),
            Modification DATETIME,
            AutresInfos VARCHAR(50)
        )";

        if ($this->conn->query($sql) === TRUE) {
            echo "Tabela 'utilisateurs' criada com sucesso.<br>";
        } else {
            echo "Erro ao criar tabela 'utilisateurs': " . $this->conn->error . "<br>";
        }

        // Creer table 'connexions'
        $sql = "CREATE TABLE IF NOT EXISTS connexions (
            NoConnexion INT(4) PRIMARY KEY,
            NoUtilisateur INT(3),
            Connexion DATETIME NOT NULL,
            Deconnexion DATETIME,
            FOREIGN KEY (NoUtilisateur) REFERENCES utilisateurs(NoUtilisateur)
        )";

        if ($this->conn->query($sql) === TRUE) {
            echo "Tabela 'connexions' criada com sucesso.<br>";
        } else {
            echo "Erro ao criar tabela 'connexions': " . $this->conn->error . "<br>";
        }

        // Creer table 'categories'
        $sql = "CREATE TABLE IF NOT EXISTS categories (
            NoCategorie INT(1) PRIMARY KEY,
            Description VARCHAR(20) NOT NULL
        )";

        if ($this->conn->query($sql) === TRUE) {
            echo "Tabela 'categories' criada com sucesso.<br>";
        } else {
            echo "Erro ao criar tabela 'categories': " . $this->conn->error . "<br>";
        }

        // Creer table 'annonces'
        $sql = "CREATE TABLE IF NOT EXISTS annonces (
            NoAnnonce INT(4) PRIMARY KEY,
            NoUtilisateur INT(3),
            Parution DATETIME NOT NULL,
            Categorie INT(1),
            DescriptionAbregee VARCHAR(50),
            DescriptionComplete VARCHAR(250),
            Prix DECIMAL(10, 2) DEFAULT 0.00,
            Photo VARCHAR(50),
            MiseAJour DATETIME,
            Etat INT(1) CHECK (Etat IN (1, 2, 3)),
            FOREIGN KEY (NoUtilisateur) REFERENCES utilisateurs(NoUtilisateur),
            FOREIGN KEY (Categorie) REFERENCES categories(NoCategorie)
        )";

        if ($this->conn->query($sql) === TRUE) {
            echo "Tabela 'annonces' criada com sucesso.<br>";
        } else {
            echo "Erro ao criar tabela 'annonces': " . $this->conn->error . "<br>";
        }
    }

    // Methode pour enregistrer le user
    public function saveUser($courriel, $password)
    {
        // Hash mot de passe
        $passwordHashed = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $this->conn->prepare("INSERT INTO utilisateurs (Courriel, MotDePasse, Creation, NbConnexions, Statut) 
                                      VALUES (?, ?, NOW(), 0, 0)");
        if ($stmt) {
            $stmt->bind_param("ss", $courriel, $passwordHashed);
            if ($stmt->execute()) {
                return true; // sucess pour inscrire user
            } else {
                return false; // pas marche l'inscription
            }
            $stmt->close();
        } else {
            return false; // si le query ne marche pas
        }
    }

    public function closeConnection()
    {
        $this->conn->close();
    }

    public function getConnection()
    {
        return $this->conn;
    }
}
?>
