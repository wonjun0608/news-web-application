<?php
require 'function.php';   
require 'database.php';   

$user_id = (int)($_GET['id'] ?? 0);

// --- Fetch user  ---
$stmt = $mysqli->prepare("SELECT id, username, bio FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$user){
    die("User not found"); // stop early if invalid user id
}

//Only allow edits by the owner with POST 
if(is_logged_in() && $_SESSION['user_id'] == $user_id && $_SERVER['REQUEST_METHOD'] === 'POST'){
    verify_token($_POST['token']); // CSRF protection

    // Password change (make sure verify current password before updating) 
    if(!empty($_POST['new_password'])){
        $current = $_POST['current_password'] ?? '';

        // Get current password hash
        $stmt = $mysqli->prepare("SELECT password_hash FROM users WHERE id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($current_hash);
        $stmt->fetch();
        $stmt->close();

        // Check current password
        if(password_verify($current, $current_hash)){
            // Hash new password and store
            $new_hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("UPDATE users SET password_hash=? WHERE id=?");
            $stmt->bind_param("si", $new_hash, $user_id);
            $stmt->execute();
            $stmt->close();
        } else {
            die("Current password is incorrect."); // fail fast on wrong current password
        }
    }

    // Users can update their bio(like introduce themselves)
    $bio = $_POST['bio'] ?? '';
    $stmt = $mysqli->prepare("UPDATE users SET bio=? WHERE id=?");
    $stmt->bind_param("si", $bio, $user_id);
    $stmt->execute();
    $stmt->close();

    // prevent form resubmission
    header("Location: profile.php?id=".$user_id);
    exit;
}

// Fetch user's stories (latest first) 
$stmt = $mysqli->prepare("SELECT id, title, created_at FROM stories WHERE user_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stories = $stmt->get_result();
$stmt->close();

// Fetch user's comments with their story titles 
$stmt = $mysqli->prepare("SELECT c.body, c.created_at, s.id AS story_id, s.title AS story_title
                          FROM comments c JOIN stories s ON c.story_id=s.id
                          WHERE c.user_id=? ORDER BY c.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$comments = $stmt->get_result();
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?php echo h($user['username']); ?>'s Profile</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <h1><?php echo h($user['username']); ?>'s Profile</h1>


  <p><strong>Bio:</strong> <?php echo $user['bio'] ? nl2br(h($user['bio'])) : "No bio yet."; ?></p>

  <?php if(is_logged_in() && $_SESSION['user_id'] == $user_id): ?>
    <h2>Edit Profile</h2>
    <form method="post">
      <label>If you want to change password, Type Current Password:
        <input type="password" name="current_password">
      </label><br>
      <label>New Password:
        <input type="password" name="new_password">
      </label><br>

      <label>Bio:<br>
        <textarea name="bio" rows="4" cols="40"><?php echo h($user['bio']); ?></textarea>
      </label><br>

      <!-- CSRF token -->
      <input type="hidden" name="token" value="<?php echo generate_token(); ?>">
      <button type="submit">Update Profile</button>
    </form>
  <?php endif; ?>

  <hr>
  <h2>Stories by <?php echo h($user['username']); ?></h2>
  <ul>
    <?php while($s = $stories->fetch_assoc()): ?>
      <li>
        <a href="story.php?id=<?php echo $s['id']; ?>">
          <?php echo h($s['title']); ?>
        </a> (<?php echo $s['created_at']; ?>)
      </li>
    <?php endwhile; ?>
  </ul>

  <hr>
  <h2>Comments by <?php echo h($user['username']); ?></h2>
  <ul>
    <?php while($c = $comments->fetch_assoc()): ?>
      <li>
        On <a href="story.php?id=<?php echo $c['story_id']; ?>">
          <?php echo h($c['story_title']); ?>
        </a>:
        "<?php echo h($c['body']); ?>" (<?php echo $c['created_at']; ?>)
      </li>
    <?php endwhile; ?>
  </ul>
</div>
</body>
</html>
