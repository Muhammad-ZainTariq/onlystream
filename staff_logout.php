<?php
session_start(); 


$_SESSION = array();


session_destroy();

header("Location: staff_login.php");
exit();
?>
