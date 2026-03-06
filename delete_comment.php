<?php
session_start();                 
require 'database.php';          
require 'function.php';    // for is_logged_in(), verify_token().

// Require login before deleting a comment
if(!is_logged_in()){
    header("Location: login.php");
    exit;
}
// Read inputs
$comment_id = (int)($_POST['id'] ?? 0);
$story_id   = (int)($_POST['story_id'] ?? 0);
// verify token from the POST form
verify_token($_POST['token']);
// Delete only the current user's own comment
$stmt = $mysqli->prepare("DELETE FROM comments WHERE id=? AND user_id=?");
if(!$stmt){
    printf("Query Prep Failed: %s\n", $mysqli->error);
    exit;
}
$stmt->bind_param("ii", $comment_id, $_SESSION['user_id']);
$stmt->execute();
$stmt->close();
// Redirect back to the story page 
header("Location: story.php?id=".(int)$story_id);
exit;
?>
