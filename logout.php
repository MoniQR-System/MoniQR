<?php
session_start();
session_unset();
session_destroy();

// Redirect to your login page (replace login.php with your actual login filename)
header("Location: index.php"); 
exit();
?>