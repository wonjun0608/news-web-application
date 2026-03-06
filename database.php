<?php
// Content of database.php
$host = 'localhost';
$db_user = 'wonjun0608';
$db_pass = 'ab1123223@';
$db_name = 'module3group';  

$mysqli = new mysqli($host, $db_user, $db_pass, $db_name);
if($mysqli->connect_errno) {
    printf("Connection Failed: %s\n", $mysqli->connect_error);
    exit;
}
?>