<?php
// Backend/add_user_process.php
require_once 'auth_check.php';
require_role('admin'); // Only admins can add users

require_once 'db.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? null); // Email can be null
    $role = trim($_POST['role'] ?? '');

    // Basic validation
    if (empty($username) || empty($password) || empty($first_name) || empty($last_name) || empty($role)) {
        header("Location: ../Frontend/add_user.php?message=All required fields must be filled.&type=danger");
        exit();
    }

    if (!in_array($role, ['admin', 'professor', 'student'])) {
        header("Location: ../Frontend/add_user.php?message=Invalid role selected.&type=danger");
        exit();
    }

    try {
        // Check if username already exists
        $stmt_check_username = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt_check_username->execute([$username]);
        if ($stmt_check_username->fetchColumn() > 0) {
            header("Location: ../Frontend/add_user.php?message=Username already exists. Please choose a different one.&type=danger");
            exit();
        }

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, first_name, last_name, email, role) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$username, $hashed_password, $first_name, $last_name, $email, $role])) {
            header("Location: ../Frontend/list_users.php?message=User added successfully!&type=success");
            exit();
        } else {
            header("Location: ../Frontend/add_user.php?message=Failed to add user.&type=danger");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error adding user: " . $e->getMessage());
        header("Location: ../Frontend/add_user.php?message=Database error: Could not add user.&type=danger");
        exit();
    }
} else {
    // If accessed directly without POST, redirect to add user page
    header("Location: ../Frontend/add_user.php");
    exit();
}