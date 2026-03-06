<?php
require 'function.php'; // for is_logged_in(), h().
require 'database.php';   
// Fetch latest stories with author info
$stmt = $mysqli->prepare("SELECT s.id, s.title, s.body, s.link, s.created_at, u.id as user_id, u.username 
                          FROM stories s JOIN users u ON s.user_id=u.id 
                          ORDER BY s.created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>News Site</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
  <h1>Simple News</h1>
  <?php if(is_logged_in()): ?>
    <p>
      Welcome, 
      <a href="profile.php?id=<?php echo $_SESSION['user_id']; ?>">
        <?php echo h($_SESSION['username']); ?>
      </a> 
      | <a href="logout.php">Logout</a>
    </p>
    <a href="new_story.php">Submit Story</a>
  <?php else: ?>
    <a href="login.php">Login</a> | <a href="register.php">Register</a>
  <?php endif; ?>
</header>

<section>
  <h2>Latest Stories</h2>
  <?php while($row = $result->fetch_assoc()): ?>
    <article>
      <h3><?php echo h($row['title']); ?></h3>
      <p><?php echo nl2br(h($row['body'])); ?></p>
      <?php if($row['link']): ?>
        <p><a href="<?php echo h($row['link']); ?>" target="_blank">Read more</a></p>
      <?php endif; ?>
      <p>
      by <a href="profile.php?id=<?php echo $row['user_id']; ?>">
        <?php echo h($row['username']); ?>
      </a>
      at <?php echo $row['created_at']; ?>
    </p>
    <a href="story.php?id=<?php echo $row['id']; ?>">View / Comment</a>
  </article>
  <hr>
<?php endwhile; ?>
</section>
</body>
</html>
