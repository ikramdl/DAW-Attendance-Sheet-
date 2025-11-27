<?php
// Backend/add_group_process.php
require_once 'auth_check.php';
require_role('admin'); // Only admins can add groups

require_once 'db.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $group_name = trim($_POST['group_name'] ?? '');

    if (empty($group_name)) {
        header("Location: ../Frontend/add_group.php?message=Group name is required.&type=danger");
        exit();
    }

    try {
        // Check if group name already exists
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM student_groups WHERE group_name = ?");
        $stmt_check->execute([$group_name]);
        if ($stmt_check->fetchColumn() > 0) {
            header("Location: ../Frontend/add_group.php?message=A group with this name already exists.&type=danger");
            exit();
        }

        $stmt = $pdo->prepare("INSERT INTO student_groups (group_name) VALUES (?)");
        if ($stmt->execute([$group_name])) {
            header("Location: ../Frontend/list_groups.php?message=Student group added successfully!&type=success");
            exit();
        } else {
            header("Location: ../Frontend/add_group.php?message=Failed to add student group.&type=danger");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error adding student group: " . $e->getMessage());
        header("Location: ../Frontend/add_group.php?message=Database error: Could not add student group.&type=danger");
        exit();
    }
} else {
    // If accessed directly without POST, redirect to add group page
    header("Location: ../Frontend/add_group.php");
    exit();
}