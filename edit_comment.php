<?php
require 'function.php';
require 'database.php'; 
if(!is_logged_in()){ header("Location: login.php"); exit; }

$comment_id = (int)($_GET['id'] ?? 0);

$stmt = $mysqli->prepare("SELECT user_id, body, story_id FROM comments WHERE id=?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$stmt->bind_result($owner_id, $body, $story_id);
$stmt->fetch();
$stmt->close();

if($owner_id !== $_SESSION['user_id']){
    die("You cannot edit someone else's comment.");
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    verify_token($_POST['token']);
    $new_body = $_POST['body'] ?? '';

    $stmt = $mysqli->prepare("UPDATE comments SET body=? WHERE id=? AND user_id=?");
    $stmt->bind_param("sii", $new_body, $comment_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();

    header("Location: story.php?id=".$story_id);
    exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Edit Comment</title>
  <!-- ✅ CSS 연결 -->
  <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Edit Comment</h1>
<form method="post">
  <textarea name="body" required><?php echo h($body); ?></textarea><br>
  <input type="hidden" name="token" value="<?php echo generate_token(); ?>">
  <button type="submit">Update</button>
</form>
</body>
</html>
