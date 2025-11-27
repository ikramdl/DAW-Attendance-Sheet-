<?php
// Backend/login_process.php
session_start(); // Start the session at the very beginning
require_once 'db.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        header("Location: ../login.php?error=Username and password are required.");
        exit();
    }

    try {
        // Fetch user by username, including first_name, last_name, and password_hash
        $stmt = $pdo->prepare("SELECT user_id, username, password_hash, first_name, last_name, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Password is correct, set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name']; // Construct full name

            // Redirect based on role
            if ($user['role'] == 'admin') {
                header("Location: ../Frontend/admin_dashboard.php");
            } elseif ($user['role'] == 'professor') {
                header("Location: ../Frontend/professor_dashboard.php");
            } else { // Student
                header("Location: ../Frontend/student_dashboard.php"); // Assuming this page will be created
            }
            exit();
        } else {
            header("Location: ../login.php?error=Invalid username or password.");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        header("Location: ../login.php?error=A database error occurred. Please try again later.");
        exit();
    }
} else {
    // If accessed directly without POST, redirect to login page
    header("Location: ../login.php");
    exit();
}