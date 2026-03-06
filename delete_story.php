<?php
session_start();
require 'database.php';   
require 'function.php';  

if(!is_logged_in()){ 
    header("Location: login.php"); 
    exit; 
}

$story_id = (int)($_POST['id'] ?? 0);
verify_token($_POST['token']); // CSRF check

// Delete after checking ownership 
$stmt = $mysqli->prepare("DELETE FROM stories WHERE id=? AND user_id=?");
if(!$stmt){
    printf("Query Prep Failed: %s\n", $mysqli->error);
    exit;
}
$stmt->bind_param("ii", $story_id, $_SESSION['user_id']);
$stmt->execute();
$stmt->close();
header("Location: index.php");
exit;
?>
