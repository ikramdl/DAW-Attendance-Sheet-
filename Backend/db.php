<?php
// Backend/db.php
require_once 'config.php'; // Load credentials from config.php

try {
    // Data Source Name (DSN)
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

    // Options for secure and efficient connection
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Return results as associative arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Use real prepared statements
    ];

    // Create the actual connection object
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch (\PDOException $e) {
    // Log the error (for debugging)
    error_log("[" . date('Y-m-d H:i:s') . "] DB Connection Error: " . $e->getMessage() . "\n", 3, 'db_errors.log');

    // Display a generic error message to the user
    die("<h1>Database connection failed. Please contact the administrator.</h1>");
}
// The $pdo variable is now available for use in other PHP scripts that include this file.
?>