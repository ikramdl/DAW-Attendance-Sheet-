<?php
// Backend/edit_student_process.php
require_once 'auth_check.php';
require_role('admin'); // Only admins can edit students

require_once 'db.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'] ?? null;
    $fullname = trim($_POST['fullname'] ?? '');
    $matricule = trim($_POST['matricule'] ?? '');
    $group_id = $_POST['group_id'] ?? null;

    if (empty($student_id) || empty($fullname) || empty($matricule) || empty($group_id)) {
        header("Location: ../Frontend/edit_student.php?id=" . urlencode($student_id) . "&message=All fields are required.&type=danger");
        exit();
    }

    try {
        // Check if matricule already exists for a *different* student
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM students WHERE matricule = ? AND student_id != ?");
        $stmt_check->execute([$matricule, $student_id]);
        if ($stmt_check->fetchColumn() > 0) {
            header("Location: ../Frontend/edit_student.php?id=" . urlencode($student_id) . "&message=Student with this matricule already exists for another student.&type=danger");
            exit();
        }

        $stmt = $pdo->prepare("UPDATE students SET fullname = ?, matricule = ?, group_id = ? WHERE student_id = ?");
        if ($stmt->execute([$fullname, $matricule, $group_id, $student_id])) {
            header("Location: ../Frontend/list_students.php?message=Student updated successfully!&type=success");
            exit();
        } else {
            header("Location: ../Frontend/edit_student.php?id=" . urlencode($student_id) . "&message=Failed to update student.&type=danger");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error updating student: " . $e->getMessage());
        header("Location: ../Frontend/edit_student.php?id=" . urlencode($student_id) . "&message=Database error: Could not update student.&type=danger");
        exit();
    }
} else {
    // If accessed directly without POST, redirect to list students page
    header("Location: ../Frontend/list_students.php");
    exit();
}