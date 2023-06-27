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

    public function authenticate($login, $password) {
        try {
            $conn = $this->db->getConnection();

            // Vérifier si l'utilisateur existe dans la base de données
            $stmt = $conn->prepare("SELECT * FROM user WHERE login = :login");
            $stmt->bindParam(':login', $login);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $hashed_password = $row['password'];

                // Vérifier le mot de passe
                if (password_verify($password, $hashed_password)) {
                    // Authentification réussie, créer une session pour l'utilisateur
                    session_start();
                    $_SESSION['id'] = true;
                    $_SESSION['login'] = $row['login'];
        

                    // Rediriger vers la page de profil ou la page d'admin en fonction du login
                    if ($_SESSION['login'] === 'admin') {
                        header("Location: admin.php");
                    } else {
                        header("Location: profil.php");
                    }
                    exit;
                } else {
                    echo "Mot de passe incorrect";
                }
            } else {
                echo "Login incorrect";
            }
        } catch(PDOException $e) {
            echo "Erreur: " . $e->getMessage();
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $login = $_POST['login'];
        $password = $_POST['password'];

        $user->authenticate($login, $password);
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
    <link rel="stylesheet" href="connexion.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gruppo&display=swap" rel="stylesheet">
</head>

<body>
    <div class="formulaire">
        <h1>Connexion</h1>
        <?php if (!empty($errors)) : ?>
            <div class="errors">
                <?php foreach ($errors as $error) : ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="connexion.php">
            <div class='connexion'>       
                <div class='champs'>
                    <label for="login">Login:</label><br>
                    <input type="text" id="login" name="login"><br>
                    <label for="password">Mot de passe:</label><br>
                    <input type="password" id="password" name="password">
                </div>
                <div class='submit'>    
                    <input type="submit" value="Se connecter" class="button"><br>
                </div> 
                <div class='button'>       
                <a href="index.php" class="button">Retour à l'accueil</a>
                </div>
            </div>        
        </form>
            
    </div>
</body>
</html>