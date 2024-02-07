<?php
// 데이터베이스 연결 설정
$db = new PDO('mysql:host=localhost;dbname=board', 'root', '');

// 액션 파라미터로 어떤 작업을 할지 결정. 기본값은 'list'
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// POST 요청 처리: 생성, 업데이트
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['create'])) {
        // 게시글 생성 로직
        $title = $_POST['title'];
        $content = $_POST['content'];
        $author = $_POST['author'];
        $stmt = $db->prepare("INSERT INTO posts (title, content, author) VALUES (?, ?, ?)");
        $stmt->execute(array($title, $content, $author));
        header("Location: noticeboard.php"); // 생성 후 목록 페이지로 리다이렉트
        exit();
    } elseif (isset($_POST['update'])) {
        // 게시글 업데이트 로직
        $title = $_POST['title'];
        $content = $_POST['content'];
        $author = $_POST['author'];
        $stmt = $db->prepare("UPDATE posts SET title = ?, content = ?, author = ? WHERE id = ?");
        $stmt->execute(array($title, $content, $author, $id));
        header("Location: noticeboard.php"); // 업데이트 후 목록 페이지로 리다이렉트
        exit();
    }
}

// 게시글 삭제 로직
if ($action == 'delete' && $id) {
    $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute(array($id));
    header("Location: noticeboard.php"); // 삭제 후 목록 페이지로 리다이렉트
    exit();
}

// 게시글 목록 로딩
$stmt = $db->query("SELECT id, title, author, created_at FROM posts ORDER BY created_at DESC");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if ($action == 'create'): ?>
<!-- 게시글 생성 폼 -->
<form method="post">
    Title: <input type="text" name="title"><br>
    Content:<br>
    <textarea name="content"></textarea><br>
    Author: <input type="text" name="author"><br>
    <input type="submit" name="create" value="Create Post">
</form>
<?php elseif ($action == 'edit' && $id): ?>
<?php
    // 게시글 수정 폼을 위한 데이터 로딩
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute(array($id));
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$post) {
        echo "Post not found."; // 게시글이 존재하지 않을 경우 메시지 출력
        exit; // 스크립트 종료
    }
?>
<!-- 게시글 수정 폼 -->
<form method="post">
    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
    Title: <input type="text" name="title" value="<?php echo htmlspecialchars($post['title'], ENT_QUOTES); ?>"><br>
    Content:<br>
    <textarea name="content"><?php echo htmlspecialchars($post['content'], ENT_QUOTES); ?></textarea><br>
    Author: <input type="text" name="author" value="<?php echo htmlspecialchars($post['author'], ENT_QUOTES); ?>"><br>
    <input type="submit" name="update" value="Update Post">
</form>
<?php endif; ?>

<?php if ($action == 'list'): ?>
<!-- 게시글 목록 -->
<h2>게시글 목록</h2>
<a href="?action=create">Create New Post</a>
<?php foreach ($posts as $post): ?>
    <div>
        <h3><a href="?action=view&id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title'], ENT_QUOTES); ?></a></h3>
        <p>Author: <?php echo htmlspecialchars($post['author'], ENT_QUOTES); ?></p>
        <a href="?action=edit&id=<?php echo $post['id']; ?>">Edit</a> |
        <a href="?action=delete&id=<?php echo $post['id']; ?>" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
    </div>
<?php endforeach; ?>
<?php endif; ?>

<?php if ($action == 'view' && $id): ?>
<?php
    // 게시글 상세보기를 위한 데이터 로딩
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute(array($id));
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$post) {
        echo "Post not found."; // 게시글이 존재하지 않을 경우 메시지 출력
        exit; // 스크립트 종료
    }
?>
    <!-- 게시글 상세보기 -->
    <div>
        <h2><?php echo htmlspecialchars($post['title'], ENT_QUOTES); ?></h2>
        <p><strong>Author:</strong> <?php echo htmlspecialchars($post['author'], ENT_QUOTES); ?></p>
        <p><strong>Posted on:</strong> <?php echo $post['created_at']; ?></p>
        <div><?php echo nl2br(htmlspecialchars($post['content'], ENT_QUOTES)); ?></div>
        <a href="noticeboard.php?action=list">Back to list</a>
    </div>
<?php endif; ?>
