<?php
include $_SERVER["DOCUMENT_ROOT"] . "/SMS/Database.php";
include $_SERVER["DOCUMENT_ROOT"] . "/SMS/SqlServices.php";

class User {
    private $username;
    private $email;
    private $password;
    private $location;
    private $dateOfBirth;
    private $connection;
    
    public function __construct($connection, $username, $email, $password, $location, $dateOfBirth) {
        $this->connection = $connection;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->location = $location;
        $this->dateOfBirth = $dateOfBirth;
    }
    
    public function createUser() {
        $currentYear = date('Y');
        $birthYear = date('Y', strtotime($this->dateOfBirth));
        $age = $currentYear - $birthYear;
        
        if ($age < 18) {
            echo "You must be 18 years or older to create an account.";
        } else {
            $sqlServices = new SqlServices($this->connection);
            $username = $this->connection->real_escape_string($this->username);
            $email = $this->connection->real_escape_string($this->email);
            $password = $this->connection->real_escape_string($this->password);
            $location = $this->connection->real_escape_string($this->location);
            $dateOfBirth = $this->connection->real_escape_string($this->dateOfBirth);
            
            $sql = "INSERT INTO users (username, email, password, location, date_of_birth) VALUES ('$username', '$email', '$password', '$location', '$dateOfBirth')";
            $result = $sqlServices->query($sql);
            
            if ($result) {
                echo "User created successfully!";
            } else {
                echo "Failed to create user.";
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $location = $_POST['location'];
    $dateOfBirth = $_POST['date-of-birth'];
    
    $db = new Database();
    $connection = $db->establishConnection();
    
    $user = new User($connection, $username, $email, $password, $location, $dateOfBirth);
    $user->createUser();
    
    $db->closeConnection();
}
?><!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="css/signup.css">
</head>
<body>
    <header>
        <div class="logo">
            
        </div>
        <div class="nav">
            <nav>               
                <ul> 
                    <li><a id="li1" href="login.php">Login</a></li>
                  
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <form class="register-form" method="POST" action="">
            <h1>Register</h1>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>

            <label for="location">Location:</label>
            <input type="text" id="location" name="location" required><br>

            <label for="date-of-birth">Date of Birth:</label>
            <input type="date" id="date-of-birth" name="date-of-birth" required><br>

            <input type="submit" value="Register">
        </form>
    </main>
</body>
</html>