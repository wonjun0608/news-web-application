<?php
session_start();
require 'database.php';   
require 'function.php';   // login/Token functions 

if(!is_logged_in()){ 
    header("Location: login.php"); 
    exit; 
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    verify_token($_POST['token']); // CSRF check

    $title = $_POST['title'] ?? '';
    $body  = $_POST['body'] ?? '';
    $link  = $_POST['link'] ?? '';
    $uid   = $_SESSION['user_id'];


    if($title && $body){
        $stmt = $mysqli->prepare("INSERT INTO stories (user_id, title, body, link) VALUES (?, ?, ?, ?)");
        if(!$stmt){
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $stmt->bind_param('isss', $uid, $title, $body, $link);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php");
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
  <title>Submit Story</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Submit Story</h1>
<?php if(isset($error)) echo "<p style='color:red'>".h($error)."</p>"; ?>
<form method="post">
  <label>Title: <input type="text" name="title" required></label><br>
  <label>Body: <textarea name="body" required></textarea></label><br>
  <label>Link: <input type="url" name="link"></label><br>
  <input type="hidden" name="token" value="<?php echo generate_token(); ?>">
  <button type="submit">Post</button>
</form>
</body>
</html>
