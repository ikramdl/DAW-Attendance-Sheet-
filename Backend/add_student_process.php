<?php
// Backend/add_student_process.php
require_once 'auth_check.php';
require_role('admin'); // Only admins can add students

require_once 'db.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $matricule = trim($_POST['matricule'] ?? '');
    $group_id = $_POST['group_id'] ?? null;

    if (empty($fullname) || empty($matricule) || empty($group_id)) {
        header("Location: ../Frontend/add_student.php?message=All fields are required.&type=danger");
        exit();
    }

    try {
        // Check if matricule already exists
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM students WHERE matricule = ?");
        $stmt_check->execute([$matricule]);
        if ($stmt_check->fetchColumn() > 0) {
            header("Location: ../Frontend/add_student.php?message=Student with this matricule already exists.&type=danger");
            exit();
        }

        $stmt = $pdo->prepare("INSERT INTO students (fullname, matricule, group_id) VALUES (?, ?, ?)");
        if ($stmt->execute([$fullname, $matricule, $group_id])) {
            header("Location: ../Frontend/list_students.php?message=Student added successfully!&type=success");
            exit();
        } else {
            header("Location: ../Frontend/add_student.php?message=Failed to add student.&type=danger");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error adding student: " . $e->getMessage());
        header("Location: ../Frontend/add_student.php?message=Database error: Could not add student.&type=danger");
        exit();
    }
} else {
    // If accessed directly without POST, redirect to add student page
    header("Location: ../Frontend/add_student.php");
    exit();
}