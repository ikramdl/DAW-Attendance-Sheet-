<?php
// Backend/delete_course.php
require_once 'auth_check.php';
require_role('admin'); // Only admins can delete courses

require_once 'db.php'; // Include database connection

$course_id = $_GET['id'] ?? null;
$message = '';
$message_type = '';

if ($course_id) {
    try {
        // IMPORTANT: Check for related attendance sessions/records
        // If there are dependencies, you might want to:
        // 1. Prevent deletion (recommended for data integrity)
        // 2. Cascade delete (more complex, might need ON DELETE CASCADE in DB)
        // For now, let's prevent deletion if sessions exist for this course.

        $stmt_check_sessions = $pdo->prepare("SELECT COUNT(*) FROM attendance_sessions WHERE course_id = ?");
        $stmt_check_sessions->execute([$course_id]);
        if ($stmt_check_sessions->fetchColumn() > 0) {
            $message = "Cannot delete course: There are active or past attendance sessions linked to it. Please close/delete all related sessions first.";
            $message_type = 'danger';
        } else {
            $stmt_delete = $pdo->prepare("DELETE FROM courses WHERE course_id = ?");
            if ($stmt_delete->execute([$course_id])) {
                $message = "Course deleted successfully!";
                $message_type = 'success';
            } else {
                $message = "Failed to delete course.";
                $message_type = 'danger';
            }
        }
    } catch (PDOException $e) {
        error_log("Error deleting course: " . $e->getMessage());
        $message = "Database error: Could not delete course.";
        $message_type = 'danger';
    }
} else {
    $message = "No course ID provided for deletion.";
    $message_type = 'danger';
}

// Redirect back to the list_courses page with a message
header('Location: ../Frontend/list_courses.php?message=' . urlencode($message) . '&type=' . urlencode($message_type));
exit();
?>