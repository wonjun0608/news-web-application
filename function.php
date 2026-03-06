<?php
session_start();             
require 'database.php';       

// Check login state (session-based)
function is_logged_in(){
    return isset($_SESSION['user_id']);
}

// HTML escape to prevent XSS when outputting user data
// ref : https://www.php.net/manual/en/function.htmlspecialchars.php
function h($str){
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

// Generate CSRF token 
// ref : https://stackoverflow.com/questions/72637335/csrf-token-two-random-tokens-instead-of-one-after-refreshing for '(!isset($_SESSION['token']' part
function generate_token(){
    if(!isset($_SESSION['token'])){
        $_SESSION['token'] = bin2hex(random_bytes(32)); // '32-byte random token → hex' from wiki
    }
    return $_SESSION['token'];
}

// Verify CSRF token 
function verify_token($token){
    if(!isset($_SESSION['token']) || !hash_equals($_SESSION['token'], $token)){
        die("Request forgery detected"); // fail fast (could also send 403)
    }
}
?>
