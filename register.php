<?php
session_start();                 
require 'function.php';          
require 'database.php';          

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // CSRF protection
    if (!isset($_POST['token'])) {
        die("Invalid request: missing CSRF token.");
    }
    verify_token($_POST['token']);

    // Read credentials (server-side required check)
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if($username && $password){
        // Hash password using strong one-way hash
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Use prepared statement to prevent SQL injection
        $stmt = $mysqli->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
        if(!$stmt){
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $stmt->bind_param('ss', $username, $hash);

        if($stmt->execute()){
            // store session and redirect
            $_SESSION['user_id'] = $mysqli->insert_id;  
            $_SESSION['username'] = $username;
            header("Location: index.php");
            exit;
        } else {
            $error = "Username already exists.";
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Register</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Register</h1>

<?php if(isset($error)) echo "<p style='color:red'>".h($error)."</p>"; ?>

<form method="post">
  <label>Username: <input type="text" name="username" required></label><br>
  <label>Password: <input type="password" name="password" required></label><br>

 
  <input type="hidden" name="token" value="<?php echo generate_token(); ?>">

  <button type="submit">Register</button>
</form>
</body>
</html>
