<?php
session_start();
session_unset(); // Clear session data
session_destroy(); // Destroy session

// Redirect to login page
header("Location: auth/login.php");
exit();
?>
