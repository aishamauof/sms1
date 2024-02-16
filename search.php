<?php
include $_SERVER["DOCUMENT_ROOT"] . "/SMS/Database.php";
session_start();
if (isset($_SESSION['user_id'])) {
    $username = $_SESSION['username'];
} else {
    header('Location: login.php');
    exit();
}

class Post {
    private $connection;
    
    public function __construct($connection) {
        $this->connection = $connection;
    }
    
    public function getPosts($searchType, $searchTerm) {
        try {
            $searchTerm = $this->connection->real_escape_string($searchTerm);
            
            // تجهيز الاستعلام الأساسي لاسترداد المشاركات المناسبة
            $sql = "SELECT p.title, p.content, u.username, c.category_name, p.date, COUNT(cmt.comment_id) AS comment
                    FROM posts AS p
                    INNER JOIN users AS u ON p.user_id = u.user_id
                    INNER JOIN categories AS c ON p.category_id = c.category_id
                    LEFT JOIN comments AS cmt ON p.post_id = cmt.post_id
                    WHERE ";
            
            switch ($searchType) {
                case 'date':
                    $sql .= "p.date = ?";
                    break;
                case 'username':
                    $sql .= "u.username = ?";
                    break;
                case 'category':
                    $sql .= "c.category_name = ?";
                    break;
                case 'title':
                    $sql .= "p.title = ?";
                    break;
                default:
                    throw new Exception("نوع البحث غير صالح!");
            }
            
            $sql .= " GROUP BY p.post_id";
            
            // إعداد الاستعلام المحضر
            $stmt = $this->connection->prepare($sql);
            
            // ربط قيمة المعايير
            $stmt->bind_param("s", $searchTerm);
            
            // تنفيذ الاستعلام المحضر
            $stmt->execute();
            
            // الحصول على نتيجة الاستعلام
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_all(MYSQLI_ASSOC);
            } else {
                throw new Exception("لم يتم العثور على مشاركات مطابقة!");
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // استلام المعايير المدخلة من النموذج
    $searchType = isset($_POST['searchtype']) ? $_POST['searchtype'] : '';
    $searchTerm = isset($_POST['searchterm']) ? $_POST['searchterm'] : '';


} else {
   // إعداد القيم الافتراضية للمتغيرات إذا لم يتم إرسال النموذج
    $searchType = '';
    $searchTerm = '';
}
$db = new Database();
$connection = $db->establishConnection();

$post = new Post($connection);
$posts = $post->getPosts($searchType, $searchTerm);

// عرض المشاركات في الجدول
if (!empty($posts)) {
    echo "<table>";
    echo "<tr><th>User Name</th><th>Category</th><th>Date</th><th>Title</th><th>Comments</th></tr>";

    foreach ($posts as $post) {
        echo "<tr>";
        echo "<td>" . $post['username'] . "</td>";
        echo "<td>" . $post['category_name'] . "</td>";
        echo "<td>" . $post['date'] . "</td>";
        echo "<td>" . $post['title'] . "</td>";
        echo "<td>" . $post['comment'] . "</td>";
        echo "</tr>";
    }

    echo "</table>";
} 
// إغلاق اتصال قاعدة البيانات
$db->closeConnection();
?>
<!DOCTYPE html>
<html>
<head>
    <title>search</title>
    <link rel="stylesheet" href="css/search.css">
</head>
<body>
<nav class="nav">
    <ul>
        <li><a href="home.php" id="li1">Home</a></li>
        <li><a href="login.php" id="li2">Login</a></li>
        <li><a href="signup.php" class="nav-link">Sign Up</a></li>
        <li><a href="addcategory.php" class="nav-link">Add Category</a></li>
        <li><a href="post.php" class="nav-link">Add Post</a></li>
        <li><a href="logout.php" class="nav-link">Logout</a></li>
    </ul>
</nav>
<form method="POST" action="" class="form">
    <div class="label"><?php echo $username; ?></div>
    <div>
        <select name="searchtype" class="input">
            <option value="date">date</option>
            <option value="username">username </option>
            <option value="category">category</option>
            <option value="title">title</option>
        </select>
    </div>
    <div>
        <input type="text" name="searchterm" placeholder="مصطلح البحث" class="input">
    </div>
    <div>
        <input type="submit" value="search" class="button">
    </div>
</form>
 
</body>
</html>