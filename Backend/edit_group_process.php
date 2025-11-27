<?php
// Backend/edit_group_process.php
require_once 'auth_check.php';
require_role('admin'); // Only admins can edit groups

require_once 'db.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $group_id = $_POST['group_id'] ?? null;
    $group_name = trim($_POST['group_name'] ?? '');

    if (empty($group_id) || empty($group_name)) {
        header("Location: ../Frontend/edit_group.php?id=" . urlencode($group_id) . "&message=Group ID and name are required.&type=danger");
        exit();
    }

    try {
        // Check if group name already exists for a *different* group
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM student_groups WHERE group_name = ? AND group_id != ?");
        $stmt_check->execute([$group_name, $group_id]);
        if ($stmt_check->fetchColumn() > 0) {
            header("Location: ../Frontend/edit_group.php?id=" . urlencode($group_id) . "&message=A group with this name already exists.&type=danger");
            exit();
        }

        $stmt = $pdo->prepare("UPDATE student_groups SET group_name = ? WHERE group_id = ?");
        if ($stmt->execute([$group_name, $group_id])) {
            header("Location: ../Frontend/list_groups.php?message=Student group updated successfully!&type=success");
            exit();
        } else {
            header("Location: ../Frontend/edit_group.php?id=" . urlencode($group_id) . "&message=Failed to update student group.&type=danger");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error updating student group: " . $e->getMessage());
        header("Location: ../Frontend/edit_group.php?id=" . urlencode($group_id) . "&message=Database error: Could not update student group.&type=danger");
        exit();
    }
} else {
    // If accessed directly without POST, redirect to list groups page
    header("Location: ../Frontend/list_groups.php");
    exit();
}