<?php
require 'function.php';   // for is_logged_in(), h(), generate_token(), verify_token()
require 'database.php';   


$story_id = (int)($_GET['id'] ?? 0);

$stmt = $mysqli->prepare("
  SELECT s.id, s.user_id, s.title, s.body, s.link, s.created_at, u.username
  FROM stories s
  JOIN users u ON s.user_id = u.id
  WHERE s.id = ?
");
$stmt->bind_param('i', $story_id);
$stmt->execute();
$story = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$story){
    die("Story not found");  
}

// Handle new comment submit
// Only for logged-in users, POST only, and requires valid CSRF token.
if($_SERVER['REQUEST_METHOD'] === 'POST' && is_logged_in() && isset($_POST['new_comment'])){
    verify_token($_POST['token']);             // CSRF check 
    $body = $_POST['body'] ?? '';              // raw comment text (escape on output)
    $uid  = $_SESSION['user_id'];



    $stmt = $mysqli->prepare("INSERT INTO comments (story_id, user_id, body) VALUES (?, ?, ?)");
    $stmt->bind_param('iis', $story_id, $uid, $body);
    $stmt->execute();
    $stmt->close();

    // redirect to avoid duplicate form resubmits on reload
    header("Location: story.php?id=".$story_id);
    exit;
}
// Fetch comments for this story(from oldest to newest)
$stmt = $mysqli->prepare("
  SELECT c.id, c.user_id, c.body, c.created_at, u.username
  FROM comments c
  JOIN users u ON c.user_id = u.id
  WHERE c.story_id = ?
  ORDER BY c.created_at ASC
");
$stmt->bind_param('i', $story_id);
$stmt->execute();
$comments = $stmt->get_result();
$stmt->close();


// Fetch list of users who liked this story
// Used to show like count & whether current user already liked.
$stmt = $mysqli->prepare("
  SELECT u.id, u.username
  FROM story_likes l
  JOIN users u ON l.user_id = u.id
  WHERE l.story_id = ?
  ORDER BY l.created_at ASC
");
$stmt->bind_param("i", $story_id);
$stmt->execute();
$likes_result = $stmt->get_result();

$liked_users = [];     // usernames to display
$liked_user_ids = [];  // for quick in_array() check below
while($row = $likes_result->fetch_assoc()){
    $liked_users[]    = $row['username'];
    $liked_user_ids[] = (int)$row['id'];
}
$like_count = count($liked_users);
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?php echo h($story['title']); ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">

  <!-- Story header -->
  <h1><?php echo h($story['title']); ?></h1>

  <!-- Story body: escape + preserve line breaks -->
  <p><?php echo nl2br(h($story['body'])); ?></p>

  <?php if($story['link']): ?>
    <!-- External link: escape URL; add rel to prevent reverse tabnabbing -->
    <p>
      <a href="<?php echo h($story['link']); ?>" target="_blank" rel="noopener noreferrer">
        Read more
      </a>
    </p>
  <?php endif; ?>
  <p>
    by
    <a href="profile.php?id=<?php echo (int)$story['user_id']; ?>">
      <?php echo h($story['username']); ?>
    </a>
    at <?php echo h($story['created_at']); ?>
  </p>

  <!-- Likes summary (simple display; consider pluralization i18n if needed) -->
  <p class="likes">❤️ <?php echo (int)$like_count; ?> Likes</p>

  <?php if($like_count > 0): ?>
    <!-- Show who liked (usernames escaped; array joined by comma) -->
    <p>❤️ Liked by: <?php echo h(implode(", ", $liked_users)); ?></p>
  <?php endif; ?>

  <?php if(is_logged_in()): ?>
    <!-- Like/Unlike form: POST + CSRF; server toggles state in like_story.php -->
    <form action="like_story.php" method="post" class="like-form">
      <input type="hidden" name="id" value="<?php echo (int)$story['id']; ?>">
      <input type="hidden" name="token" value="<?php echo generate_token(); ?>">
      <?php if(in_array((int)$_SESSION['user_id'], $liked_user_ids, true)): ?>
        <button type="submit" class="like-button">💔 Unlike</button>
      <?php else: ?>
        <button type="submit" class="like-button">❤️ Like</button>
      <?php endif; ?>
    </form>
  <?php endif; ?>

  <?php if(is_logged_in() && $_SESSION['user_id'] == $story['user_id']): ?>
    <!-- Edit button -->
    <form action="edit_story.php" method="get" style="display:inline">
      <input type="hidden" name="id" value="<?php echo (int)$story['id']; ?>">
      <button type="submit">Edit Story</button>
    </form>

    <!-- Delete button: POST + CSRF + confirm -->
    <form action="delete_story.php" method="post" style="display:inline"
          onsubmit="return confirm('Delete this story?');">
      <input type="hidden" name="id" value="<?php echo (int)$story['id']; ?>">
      <input type="hidden" name="token" value="<?php echo generate_token(); ?>">
      <button type="submit">Delete Story</button>
    </form>
  <?php endif; ?>

  <hr>

  <h2>Comments</h2>
  <?php while($c = $comments->fetch_assoc()): ?>
    <p>
      <strong>
        <a href="profile.php?id=<?php echo (int)$c['user_id']; ?>">
          <?php echo h($c['username']); ?>
        </a>
      </strong>:
      <?php echo nl2br(h($c['body'])); ?>
      (<?php echo h($c['created_at']); ?>)
    </p>

    <?php if(is_logged_in() && $_SESSION['user_id'] == $c['user_id']): ?>
      <!-- Edit with GET (view/edit form page) -->
      <form action="edit_comment.php" method="get" style="display:inline">
        <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
        <button type="submit">Edit</button>
      </form>

      <!-- Delete with POST (state change) + CSRF + confirm -->
      <form action="delete_comment.php" method="post" style="display:inline"
            onsubmit="return confirm('Delete this comment?');">
        <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
        <input type="hidden" name="story_id" value="<?php echo (int)$story_id; ?>">
        <input type="hidden" name="token" value="<?php echo generate_token(); ?>">
        <button type="submit">Delete</button>
      </form>
    <?php endif; ?>
  <?php endwhile; ?>

  <?php if(is_logged_in()): ?>
    <h3>Add Comment</h3>
    <form method="post">
      <textarea name="body" required></textarea><br>
      <input type="hidden" name="new_comment" value="1">
      <input type="hidden" name="token" value="<?php echo generate_token(); ?>">
      <button type="submit">Add Comment</button>
    </form>
  <?php else: ?>
    <p><a href="login.php">Log in</a> to comment.</p>
  <?php endif; ?>

</div>
</body>
</html>
