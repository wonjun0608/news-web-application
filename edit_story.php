<?php
require 'database.php';   
require 'function.php';   

if(!is_logged_in()){ 
    header("Location: login.php"); 
    exit; 
}

$story_id = (int)($_GET['id'] ?? 0);

// Bring the existing story
$stmt = $mysqli->prepare("SELECT user_id, title, body, link FROM stories WHERE id=?"); //from the database
$stmt->bind_param("i", $story_id);
$stmt->execute();
$stmt->bind_result($owner_id, $title, $body, $link);
$stmt->fetch();
$stmt->close();

if($owner_id !== $_SESSION['user_id']){
    die("You cannot edit someone else's story.");
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    verify_token($_POST['token']);

    $new_title = $_POST['title'] ?? '';
    $new_body  = $_POST['body'] ?? '';
    $new_link  = $_POST['link'] ?? '';

    if($new_title && $new_body){
        $stmt = $mysqli->prepare("UPDATE stories SET title=?, body=?, link=? WHERE id=? AND user_id=?");
        $stmt->bind_param("sssii", $new_title, $new_body, $new_link, $story_id, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();

        header("Location: story.php?id=".$story_id);
        exit;
    } else {
        $error = "Title and body are required.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Story</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Edit Story</h1>
<?php if(isset($error)) echo "<p style='color:red'>".h($error)."</p>"; ?>
<form method="post">
  <label>Title: <input type="text" name="title" value="<?php echo h($title); ?>" required></label><br>
  <label>Body: <textarea name="body" required><?php echo h($body); ?></textarea></label><br>
  <label>Link: <input type="url" name="link" value="<?php echo h($link); ?>"></label><br>
  <input type="hidden" name="token" value="<?php echo generate_token(); ?>">
  <button type="submit">Update</button>
</form>
</body>
</html>
