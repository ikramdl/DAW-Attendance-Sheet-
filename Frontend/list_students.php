<?php
// Frontend/list_students.php
require_once '../Backend/auth_check.php';
require_role('admin'); // Only admins can view/manage students

require_once '../Backend/db.php'; // Include database connection

$students = [];
$message = '';
$message_type = '';

try {
    // Fetch all students, joining with student_groups to get group name
    $stmt = $pdo->prepare("
        SELECT 
            s.student_id, 
            s.fullname, 
            s.matricule, 
            sg.group_name
        FROM students s
        LEFT JOIN student_groups sg ON s.group_id = sg.group_id
        ORDER BY s.fullname
    ");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("PDOException in list_students.php: " . $e->getMessage());
    $message = "Database error: Could not load students.";
    $message_type = 'danger';
}

// Handle messages from other pages (e.g., successful add/edit/delete)
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type'] ?? 'info');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary mb-4 shadow-sm">
        <div class="container">
            <span class="navbar-brand">Admin Dashboard</span>
            <div class="d-flex align-items-center">
                <span class="navbar-text text-white me-3">Welcome, <?= htmlspecialchars($_SESSION['fullname']) ?>!</span>
                <a href="../Backend/logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 class="mb-4">Manage Students</h1>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <p>
            <a href="add_student.php" class="btn btn-success">Add New Student</a>
            <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </p>

        <?php if (empty($students)): ?>
            <div class="alert alert-info">No students found. Add a new student to get started.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Matricule</th>
                            <th>Group</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['student_id']) ?></td>
                                <td><?= htmlspecialchars($student['fullname']) ?></td>
                                <td><?= htmlspecialchars($student['matricule']) ?></td>
                                <td><?= htmlspecialchars($student['group_name'] ?: 'Unassigned') ?></td>
                                <td>
                                    <a href="edit_student.php?id=<?= htmlspecialchars($student['student_id']) ?>" class="btn btn-sm btn-warning me-2">Edit</a>
                                    <a href="../Backend/delete_student.php?id=<?= htmlspecialchars($student['student_id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>