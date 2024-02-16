<?php

include $_SERVER["DOCUMENT_ROOT"] . "/SMS/SqlServices.php";
include $_SERVER["DOCUMENT_ROOT"] . "/SMS/Database.php";

session_start();
if (isset($_SESSION['user_id'])) {
    $username = $_SESSION['username']; // استخراج قيمة المتغير $['username']_SESSION
 
} else {
    header('Location: login.php');
    exit();
}

class CategoryAddition {
    private $category_name;
    private $connection;
    
    public function __construct($connection, $category_name) {
        $this->connection = $connection;
        $this->category_name = $category_name;
    }
    
    public function addCategory() {
        try {
            $sqlServices = new SqlServices($this->connection);
            $category_name = $this->connection->real_escape_string($this->category_name);
            $sql = "INSERT INTO categories (category_name) VALUES ('$category_name')";
            $result = $sqlServices->query($sql);
            return $result;
        } catch (Exception $e) {
            throw new Exception('Error occurred while adding the category: ' . $e->getMessage());
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = $_POST['category_name'];

    $db = new Database();
    $connection = $db->establishConnection();

    $categoryAddition = new CategoryAddition($connection, $category_name);

    // Add Category
    try {
        if ($categoryAddition->addCategory()) {
            // Category added successfully
            echo 'Category added successfully!';
        } else {
            // Error occurred while adding the category
            echo 'Error occurred while adding the category!';
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }

    // Close database connection
    $db->closeConnection();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Category</title>
    <link rel="stylesheet" href="css/addcategory.css">
</head>
<body><br>

    <form class="form" method="POST" action="">
    <?php echo $username; ?></br><br>

        <nav class="nav">
            <ul>
                <li><a href="home.php" id="li1">Home</a></li>
                <li><a href="login.php" id="li2">Login</a></li>
                <li><a href="signup.php" class="nav-link">Sign Up</a></li>
                <li><a href="addcategory.php" class="nav-link">Add Category</a></li>
                <li><a href="post.php" class="nav-link">Add Post</a></li>
                <li><a href="search.php" class="nav-link">search</a></li>
                <li><a href="logout.php" class="nav-link">Logout</a></li><br><br>
            </ul>
        </nav>
        <label class="label" for="category_name">Category Name:</label><br>
        <input class="input" type="text" id="category_name" name="category_name"><br><br>

        <input class="button" type="submit" value="Add Category">
    </form>
</body>
</html>