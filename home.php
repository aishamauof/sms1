<!--صفحة المشاراكات و التعليقات-->
<?php
include $_SERVER["DOCUMENT_ROOT"] . "/SMS/Database.php";
session_start();
if (isset($_SESSION['user_id'])) {
    $username = $_SESSION['username'];
    $userId = $_SESSION['user_id'];
} else {
    header('Location: login.php');
    exit();
}

class Post {
    private $connection;
    
    public function __construct($connection) {
        $this->connection = $connection;
    }
    
    public function getPosts() {
        try {
            // استعلام لاسترداد المشاركات وتعليقاتها
            $sql = "SELECT p.post_id, p.title, p.content, u.username, c.category_name, p.date, COUNT(cmt.comment_id) AS comment_count
                    FROM posts AS p
                    INNER JOIN users AS u ON p.user_id = u.user_id
                    INNER JOIN categories AS c ON p.category_id = c.category_id
                    LEFT JOIN comments AS cmt ON p.post_id = cmt.post_id
                    GROUP BY p.post_id
                    ORDER BY p.date DESC";
            
            // تنفيذ الاستعلام
            $result = $this->connection->query($sql);
            
            if ($result->num_rows > 0) {
                return $result->fetch_all(MYSQLI_ASSOC);
            } else {
                throw new Exception("لم يتم العثور على مشاركات!");
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
    public function addComment($postId, $comment, $userId) {
        try {
            $comment = $this->connection->real_escape_string($comment);
            
            // التحقق من أن المشاركة المعنية موجودة
            $sql = "SELECT * FROM posts WHERE post_id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("i", $postId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // إضافة تعليق إلى قاعدة البيانات
                $sql = "INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)";
                $stmt = $this->connection->prepare($sql);
                $stmt->bind_param("iis", $postId, $userId, $comment);
                $stmt->execute();
                
                if ($stmt->affected_rows > 0) {
                    return true;
                } else {
                    throw new Exception("فشل في إضافة التعليق!");
                }
            } else {
                throw new Exception("المشاركة غير موجودة!");
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
public function deletePost($postId) {
        try {
            // التحقق من أن المشاركة المعنية موجودة
            $sql = "SELECT * FROM posts WHERE post_id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("i", $postId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // حذف المشاركة من قاعدة البيانات
                $sql = "DELETE FROM posts WHERE post_id = ?";
                $stmt = $this->connection->prepare($sql);
                $stmt->bind_param("i", $postId);
                $stmt->execute();
                
                if ($stmt->affected_rows > 0) {
                    // حذف التعليقات المرتبطة بالمشاركة
                    $sql = "DELETE FROM comments WHERE post_id = ?";
                    $stmt = $this->connection->prepare($sql);
                    $stmt->bind_param("i", $postId);
                    $stmt->execute();
                    
                    echo "تم حذف المشاركة !";
                } else{
                    echo "فشل في حذف المشاركة!";
                }
            } else {
                throw new Exception("المشاركة غير موجودة!");
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    public function deleteComment($commentId) {
        try {
            // التحقق من أن التعليق المعني موجود
            $sql = "SELECT * FROM comments WHERE comment_id = ?";

$stmt = $this->connection->prepare($sql);
$stmt->bind_param("i", $commentId);
            $stmt->execute();
            $result = $stmt->get_result();
    
            if ($result->num_rows > 0) {
                // حذف التعليق من قاعدة البيانات
                $sql = "DELETE FROM comments WHERE comment_id = ?";
                $stmt = $this->connection->prepare($sql);
                $stmt->bind_param("i", $commentId);
                $stmt->execute();
    
                if ($stmt->affected_rows > 0) {   

                    echo "تم حذف التعليق !";
                 
                } else {
                    throw new Exception("فشل في حذف التعليق!");
                }
            } else {
                throw new Exception("التعليق غير موجود!");
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
        
}
  

// إنشاء اتصال بقاعدة البيانات

$db = new Database();
$connection = $db->establishConnection();


// إنشاء كائن من الفئة Post
$post = new Post($connection);

// استدعاء الدالة لاسترداد المشاركات
$posts = $post->getPosts();
// التحقق من إرسال نموذج التعليق
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $postId = $_POST['post_id'];
    $comment = $_POST['comment'];
    $userId = $userId; // استخدم القيمة المخزنة في $userId
    
    // استدعاء الدالة لإضافة التعليق
    $addCommentResult = $post->addComment($postId, $comment, $userId);
    
    if ($addCommentResult) {
        // إعادة تحميل الصفحة بعد إضافة التعليق
        header('Location: home.php');
        exit();
    }
}

// التحقق من إرسال نموذج حذف المشاركة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $postId = $_POST['post_id'];

    // استدعاء الدالة لحذف المشاركة
    $deletePostResult = $post->deletePost($postId);

    
    if ($deletePostResult) {
        // إعادة تحميل الصفحة بعد حذف المشاركة
      
        header('Location: home.php');
 
        exit();
    }
}
// التحقق من إرسال نموذج حذف التعليق
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment'])) {
    $commentId = $_POST['comment_id'];

    // استدعاء الدالة لحذف التعليق
    $deleteCommentResult = $post->deleteComment($commentId);

    if ($deleteCommentResult) {
        // إعادة تحميل الصفحة بعد حذف التعليق
        header('Location: home.php');
        exit();
    }
}


?>

<!-- عرض المشاركات ونموذج التعليق -->
<!DOCTYPE html>
<html>
<head>
    <title>Home Page</title>
    <link rel="stylesheet" href="css/p.css">
</head>
<body>
    <header>
        <nav class="nav">
         
                <a href="home.php" id="li1">Home</a>
                <a href="login.php" id="li2">Login</a>
                <a href="signup.php" class="nav-link">Sign Up</a>
                <a href="addcategory.php" class="nav-link">Add Category</a>
                <a href="post.php" class="nav-link">Add Post</a>
                <a href="search.php" class="nav-link">search</a>
             
                <a href="logout.php" class="nav-link">Logout</a>
           
        </nav>
        <h1 class="logo">Social Media Website</h1>
       
    </header>
   
    
    <?php foreach ($posts as $post) { ?>
        <div class="post">
            <h2><?php echo $post['title']; ?></h2>
            <p><?php echo $post['content']; ?></p>
            <p>By: <?php echo $post['username']; ?></p>
            <p>Category: <?php echo $post['category_name']; ?></p>
            <p>Date: <?php echo $post['date']; ?></p>
            <p>Comments: <?php echo $post['comment_count']; ?></p>
            
            <!-- Add Comment Form -->
            <form method="POST" action="home.php">
                <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                <textarea name="comment" placeholder="Add a comment"></textarea>
                <button type="submit">Add Comment</button>
            </form>
            
            <form method="POST" action="home.php">
                <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                <button type="submit" name="delete">Delete Post</button>
            </form>
            
            <?php // Display comments related to the post
            $sql = "SELECT * FROM comments WHERE post_id = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $post['post_id']);
            $stmt->execute();
            $comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            foreach ($comments as $comment) { ?>
                <div class="comment">
                    <p><?php echo $comment['comment']; ?></p>
                    
                    <!-- Delete Comment Form -->
                    <form method="POST" action="home.php">
                        <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>">
                        <button type="submit" name="delete_comment">Delete Comment</button>
                    </form>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
    
    <footer>
    <div class="social-links">
    <a href="https://www.facebook.com/example" target="_blank">Facebook</a>
    <a href="https://www.twitter.com/example" target="_blank">Twitter</a>
    <a href="https://www.instagram.com/example" target="_blank">Instagram</a>
</div>
        <p>جميع الحقوق محفوظة &copy; 2024</p>
    </footer>  
</body>
</html>