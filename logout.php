<?php
require 'function.php';
session_destroy();
header("Location: index.php");
exit;
?>
