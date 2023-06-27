<?php

class Database {
    private $conn;

    public function __construct($host, $username, $password, $dbname) {
        try {
            $this->conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            throw new Exception("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    public function closeConnection() {
        $this->conn = null;
    }

    public function getConnection() {
        return $this->conn;
    }
}

class User {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getProfile($login) {
        try {
            $conn = $this->db->getConnection();

            $stmt = $conn->prepare("SELECT * FROM user  WHERE login = :login");
            $stmt->bindParam(':login', $login);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            throw new Exception("Erreur lors de la récupération du profil de l'utilisateur : " . $e->getMessage());
        }
    }

    public function updateProfile($login, $password) {
        try {
            $conn = $this->db->getConnection();

            $stmt = $conn->prepare("UPDATE user SET password = :password WHERE login = :login");
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':login', $login);
            $stmt->execute();

            return true;
        } catch(PDOException $e) {
            throw new Exception("Erreur lors de la mise à jour du profil de l'utilisateur : " . $e->getMessage());
        }
    }
}

// Utilisation des classes

try {
    $host = "localhost";
    $username = "root";
    $password = "Dyane198124//";
    $dbname = "livreor";

    $db = new Database($host, $username, $password, $dbname);
    $user = new User($db);

    session_start();

    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['id']) || $_SESSION['id'] !== true) {
        // Rediriger vers la page de connexion
        header("Location: connexion.php");
        exit;
    }

    $login = $_SESSION['login'];

    // Si le formulaire est soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $login = $_SESSION['login'];
        $password = $_POST['password'];

        // Mettre à jour les informations de l'utilisateur dans la base de données
        $user->updateProfile($login, $password);

        // Mettre à jour les informations de l'utilisateur dans la session
        $_SESSION['password'] = $password;
        

        echo "Informations mises à jour avec succès";
    }

    $db->closeConnection();
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="profil.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gruppo&display=swap" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h1>Profil</h1>
        <form method="POST" action="profil.php">
            <div class='champs'>
                <label for="login">Login:</label><br>
                <input type="text" id="login" name="login"><br>
                <label for="password">Mot de passe:</label><br>
                <input type="text" id="password" name="password"><br>
            </div>  
            <div class='button'>
                <input type="submit" value="Mettre à jour">
            </div>    
        </form>
    </div>
</body>
</html>
