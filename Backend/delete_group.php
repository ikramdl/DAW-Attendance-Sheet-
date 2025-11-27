<?php
// Backend/delete_group.php
require_once 'auth_check.php';
require_role('admin'); // Only admins can delete groups

require_once 'db.php'; // Include database connection

$group_id = $_GET['id'] ?? null;

if (!$group_id) {
    header("Location: ../Frontend/list_groups.php?message=No group ID provided for deletion.&type=danger");
    exit();
}

try {
    // IMPORTANT: Check for dependencies first!
    // A group cannot be deleted if:
    // 1. There are students assigned to it.
    // 2. There are attendance sessions created for it.
    // 3. There are courses linked to it (though in your schema courses link to professors, not groups directly).

    // Check for students assigned to this group
    $stmt_check_students = $pdo->prepare("SELECT COUNT(*) FROM students WHERE group_id = ?");
    $stmt_check_students->execute([$group_id]);
    if ($stmt_check_students->fetchColumn() > 0) {
        header("Location: ../Frontend/list_groups.php?message=Cannot delete group: There are students assigned to this group. Please reassign or delete them first.&type=danger");
        exit();
    }

    // Check for attendance sessions created for this group
    $stmt_check_sessions = $pdo->prepare("SELECT COUNT(*) FROM attendance_sessions WHERE group_id = ?");
    $stmt_check_sessions->execute([$group_id]);
    if ($stmt_check_sessions->fetchColumn() > 0) {
        header("Location: ../Frontend/list_groups.php?message=Cannot delete group: There are attendance sessions linked to this group. Please close or delete them first.&type=danger");
        exit();
    }

    // If no dependencies, proceed with deletion
    $stmt_delete = $pdo->prepare("DELETE FROM student_groups WHERE group_id = ?");
    if ($stmt_delete->execute([$group_id])) {
        header("Location: ../Frontend/list_groups.php?message=Student group deleted successfully!&type=success");
        exit();
    } else {
        header("Location: ../Frontend/list_groups.php?message=Failed to delete student group.&type=danger");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error deleting student group: " . $e->getMessage());
    header("Location: ../Frontend/list_groups.php?message=Database error: Could not delete student group.&type=danger");
    exit();
}