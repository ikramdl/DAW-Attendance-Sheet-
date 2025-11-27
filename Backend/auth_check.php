<?php
// Backend/auth_check.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Wrap functions in `function_exists` to prevent redeclaration errors
if (!function_exists('require_login')) {
    function require_login() {
        if (!isset($_SESSION['user_id'])) {
            $message = "You must be logged in to access this page.";
            header('Location: ../Frontend/login.html?message=' . urlencode($message));
            exit();
        }
    }
}

if (!function_exists('require_role')) {
    function require_role($requiredRole) {
        require_login();

        $userRole = $_SESSION['role'];

        if (!is_array($requiredRole)) {
            $requiredRole = [$requiredRole];
        }

        if (!in_array($userRole, $requiredRole)) {
            $message = "You do not have permission to access this page. Your role is " . ucfirst($userRole) . ".";
            header('Location: ../Frontend/login.html?message=' . urlencode($message));
            exit();
        }
    }
}

// Optional: Function to get current user data easily
if (!function_exists('get_current_user')) { // <<< This is the line that caused the error
    function get_current_user() {
        if (isset($_SESSION['user_id'])) {
            return [
                'user_id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role'],
                'fullname' => $_SESSION['fullname'] ?? ''
            ];
        }
        return null;
    }
}