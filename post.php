<?php
include $_SERVER["DOCUMENT_ROOT"] . "/SMS/Database.php";
session_start();
if (isset($_SESSION['user_id'])) {
    $username = $_SESSION['username']; // استخراج قيمة المتغير $['username']_SESSION
 
  
} else {
    header('Location: login.php'); // إعادة توجيه المستخدم إلى صفحة تسجيل الدخول إذا لم يتم تسجيل الدخول
    exit();
}


class Post {
    private $connection;
    
    public function __construct($connection) {
        $this->connection = $connection;
    }
    
    public function addPost($title, $content, $category_name) {
        try {
            $title = $this->connection->real_escape_string($title);
            $content = $this->connection->real_escape_string($content);
            $user_id = $_SESSION['user_id']; // احصل على رقم المستخدم من الجلسة
            $date = date("Y-m-d"); // توليد التاريخ الحالي
            
            // استعلام محضر لاسترداد معرف الفئة بناءً على اسم الفئة
            $category_query = "SELECT category_id FROM categories WHERE category_name = ?";
            
            // إعداد الاستعلام المحضر
            $category_stmt = $this->connection->prepare($category_query);
            
            // ربط قيمة اسم الفئة
            $category_stmt->bind_param("s", $category_name);
            
            // تنفيذ الاستعلام المحضر
            $category_stmt->execute();
            
            // الحصول على نتيجة الاستعلام
            $category_result = $category_stmt->get_result();
            
            if ($category_result->num_rows > 0) {
                $category_row = $category_result->fetch_assoc();
                $category_id = $category_row["category_id"];
                
                $sql = "INSERT INTO posts (title, content, user_id, category_id, date) VALUES (?, ?, ?, ?, ?)";
                
                // إعداد الاستعلام المحضر
                $stmt = $this->connection->prepare($sql);
                
                // ربط البيانات
                $stmt->bind_param("ssiss", $title, $content, $user_id, $category_id, $date);
                
                if ($stmt->execute()) {
                    echo "تم إضافة البوست بنجاح!";
                } else {
                    throw new Exception("حدث خطأ أثناء إضافة البوست: " . $stmt->error);
                }
            } else {
                throw new Exception("لم يتم العثور على الفئة المحددة!");
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $content = $_POST["content"];

    $db = new Database();
    $connection = $db->establishConnection();

    // تعيين قيمة المدخلات المستخدمة لـ $category_name
    $category_name = $_POST["category_name"];

    $post = new Post($connection);
    $post->addPost($title, $content, $category_name);

    // إغلاق اتصال قاعدة البيانات
    $db->closeConnection();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Post</title>
    <link rel="stylesheet" href="css/post.css">
</head>
<body>
    <form method="POST" action="" class="form">
    <br><?php echo $username; ?></br>
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
        <label for="title" class="label">Title:</label><br>
        <input type="text" id="title" name="title" class="input"><br><br>
         
        <label for="category_name" class="label">Category Name:</label><br>
        <input type="text" id="category_name" name="category_name" class="input"><br><br>
        <label for="content" class="label">Content:</label><br>
        <textarea id="content" name="content" class="textarea"></textarea><br><br>
       
        
        <input type="submit" value="Add Post" class="button">
    </form>
</body>
</html>