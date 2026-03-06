<?php
require 'function.php';   
require 'database.php';   

// Require login before toggling a like
if(!is_logged_in()){
    header("Location: login.php");
    exit;
}

// CSRF protection
verify_token($_POST['token']);

$story_id = (int)($_POST['id'] ?? 0);  // cast to int to avoid injection via id
$uid = $_SESSION['user_id'];

// Check if the user already liked this story 
$stmt = $mysqli->prepare("SELECT id FROM story_likes WHERE story_id=? AND user_id=?");
$stmt->bind_param("ii", $story_id, $uid);
$stmt->execute();
$stmt->store_result();     

if($stmt->num_rows > 0){
    // Already liked -> toggle OFF 
    $stmt->close();
    $del = $mysqli->prepare("DELETE FROM story_likes WHERE story_id=? AND user_id=?");
    $del->bind_param("ii", $story_id, $uid);
    $del->execute();
    $del->close();
} else {
    // Not liked yet and then insert the like
    $stmt->close();
    $ins = $mysqli->prepare("INSERT INTO story_likes (story_id, user_id) VALUES (?, ?)");
    $ins->bind_param("ii", $story_id, $uid);
    $ins->execute();
    $ins->close();
}

// Redirect back to the story (PRG pattern to avoid resubmission)
header("Location: story.php?id=".$story_id);
exit;
