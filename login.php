<?php
include $_SERVER["DOCUMENT_ROOT"] . "/SMS/Database.php";
include $_SERVER["DOCUMENT_ROOT"] . "/SMS/SqlServices.php";

class UserLogin {
    private $email;
    private $password;
    private $connection;
    
    public function __construct($connection, $email, $password) {
        $this->connection = $connection;
        $this->email = $email;
        $this->password = $password;
    }
    
    public function validate() {
        $sqlServices = new SqlServices($this->connection);
        $email = $this->connection->real_escape_string($this->email);
        $password = $this->connection->real_escape_string($this->password);
        $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
        
        try {
            $result = $sqlServices->query($sql);
            return $result->num_rows > 0;
        } catch (Exception $e) {
            throw new Exception("Error executing SQL query: " . $e->getMessage());
        }
    }
    
    public function getUserIDByEmail() {
        $sqlServices = new SqlServices($this->connection);
        $email = $this->connection->real_escape_string($this->email);
        $password = $this->connection->real_escape_string($this->password);
        
        $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
        
        try {
            $result = $sqlServices->query($sql);
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row['user_id'];
            } else {
                return null;
            }
        } catch (Exception $e) {
            throw new Exception("Error executing SQL query: " . $e->getMessage());
        }
    }
}

session_start();
function getUsernameFromDatabase($user_id) {
    $db = new Database();
    $connection = $db->establishConnection();
    $sqlServices = new SqlServices($connection);
    
    $user_id = $connection->real_escape_string($user_id);
    $sql = "SELECT username FROM users WHERE user_id = '$user_id'";
    
    try {
        $result = $sqlServices->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $username = $row['username'];
            $db->closeConnection();
            return $username;
        } else {
            $db->closeConnection();
            return null;
        }
    } catch (Exception $e) {
        throw new Exception("Error executing SQL query: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $db = new Database();
    $connection = $db->establishConnection();

    $userLogin = new UserLogin($connection, $email, $password);

    try {
        // التحقق من صحة بيانات الدخول
        if ($userLogin->validate()) {
            // تم تسجيل الدخول بنجاح
            $user_id = $userLogin->getUserIDByEmail();
            if ($user_id) {
                $_SESSION['user_id'] = $user_id;
                
                // قم بجلب اسم المستخدم من قاعدة البيانات باستخدام المعرف المخزن في $_SESSION['user_id']
                $username = getUsernameFromDatabase($user_id); // استبدل هذا بالكود الخاص بجلب اسم المستخدم
                
                $_SESSION['username'] = $username; // تخزين اسم المستخدم في الجلسة
                header('Location: home.php'); // توجيه المستخدم إلى الصفحة الرئيسية بعد تسجيل الدخول بنجاح
                exit();
            } else {
                throw new Exception('حدث خطأ أثناء جلب معرف المستخدم!');
            }
        } else {
            // بيانات الدخول غير صحيحة
            throw new Exception('بيانات الدخول غير صحيحة!');
        }
    } catch (Exception $e) {
        echo("An error occurred: " . $e->getMessage());
    }

    // إغلاق اتصال قاعدة البيانات
    $db->closeConnection();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <h1>Login</h1>
    <?php
    if (isset($_SESSION['user_id'])) {
        echo '<a href="home.php">Home</a>'; // رابط للصفحة الرئيسية بعد تسجيل الدخول
    }
    ?>
    <br>
    
    <form method="POST" action="login.php">
        <fieldset>
            <legend>Login Form</legend>
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" required><br><br>
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required><br><br>
            <input type="submit" value="Login">
            <small>Not having Acount yet? <a href="signup.php"><b>register</b></a></small><br>
        </fieldset>
    </form>
</body>
</html>