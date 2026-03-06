<?php
require 'function.php';
require 'database.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    verify_token($_POST['token']); // CSRF check

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $mysqli->prepare("SELECT id, password_hash FROM users WHERE username=?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->bind_result($id, $hash);

    if($stmt->fetch() && password_verify($password, $hash)){
        $_SESSION['user_id'] = $id;
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid login.";
    }
    $stmt->close();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Login</h1>
<?php if(isset($error)) echo "<p style='color:red'>".h($error)."</p>"; ?>
<form method="post">
  <label>Username: <input type="text" name="username" required></label><br>
  <label>Password: <input type="password" name="password" required></label><br>
  <input type="hidden" name="token" value="<?php echo generate_token(); ?>">
  <button type="submit">Login</button>
</form>
<a href="register.php">Register</a>
</body>
</html>
