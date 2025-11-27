<?php
// Backend/logout.php
session_start(); // Start the session to access session variables

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the login page
header('Location: ../Frontend/login.html?message=' . urlencode('You have been logged out.'));
exit();
?>