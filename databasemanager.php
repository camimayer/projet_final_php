<?php
// databasemanager.php
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

    // Exemple de méthode saveUser pour sauvegarder un utilisateur
    public function saveUser($email, $password)
    {
        $stmt = $this->connection->prepare("INSERT INTO utilisateurs (Courriel, MotDePasse) VALUES (?, ?)");
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param("ss", $email, $hashedPassword);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function closeConnection()
    {
        $this->connection->close();
    }
}
?>