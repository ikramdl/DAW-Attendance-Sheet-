<?php
// Backend/delete_student.php
require_once 'auth_check.php';
require_role('admin'); // Only admins can delete students

require_once 'db.php'; // Include database connection

$student_id = $_GET['id'] ?? null;

if (!$student_id) {
    header("Location: ../Frontend/list_students.php?message=No student ID provided for deletion.&type=danger");
    exit();
}

try {
    // IMPORTANT: Check for dependencies first!
    // A student cannot be deleted if they have attendance records.
    $stmt_check_attendance = $pdo->prepare("SELECT COUNT(*) FROM attendance_records WHERE student_id = ?");
    $stmt_check_attendance->execute([$student_id]);
    if ($stmt_check_attendance->fetchColumn() > 0) {
        header("Location: ../Frontend/list_students.php?message=Cannot delete student: There are attendance records linked to this student. Please remove related attendance records first.&type=danger");
        exit();
    }

    // If no dependencies, proceed with deletion
    $stmt_delete = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
    if ($stmt_delete->execute([$student_id])) {
        header("Location: ../Frontend/list_students.php?message=Student deleted successfully!&type=success");
        exit();
    } else {
        header("Location: ../Frontend/list_students.php?message=Failed to delete student.&type=danger");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error deleting student: " . $e->getMessage());
    header("Location: ../Frontend/list_students.php?message=Database error: Could not delete student.&type=danger");
    exit();
}