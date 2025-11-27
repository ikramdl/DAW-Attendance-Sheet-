<?php
// test_connection.php
// This script is in the root, so it goes into Backend/ to find db.php
require_once 'Backend/db.php'; 

if (isset($pdo)) {
    echo "<h1 style='color:green;'>✅ Database Connection Successful!</h1>";
    echo "<p>Connected to database: <strong>" . DB_NAME . "</strong></p>";
    // You can even try a simple query to prove it
    try {
        $stmt = $pdo->query("SELECT 1"); // A very simple query that always works
        if ($stmt) {
            echo "<p>Simple query also worked. Database is ready!</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Error running test query: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<h1 style='color:red;'>❌ Database Connection Failed!</h1>";
    echo "<p>Check your `Backend/config.php` credentials and `Backend/db.php`.</p>";
}
?>