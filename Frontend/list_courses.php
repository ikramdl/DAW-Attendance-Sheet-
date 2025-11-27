<?php
// Frontend/list_courses.php
require_once '../Backend/auth_check.php';
require_role('admin'); // Only admins can view/manage courses

require_once '../Backend/db.php'; // Include database connection

$courses = [];
$message = '';
$message_type = '';

try {
    // --- DEBUGGING START ---
    error_log("Attempting to fetch courses from database.");
    // --- DEBUGGING END ---

    // Fetch all courses, joining with users table to get professor names
    $stmt = $pdo->prepare("
        SELECT 
            c.course_id, 
            c.course_name, 
            c.description, 
            u.first_name AS professor_first_name, 
            u.last_name AS professor_last_name
        FROM courses c
        LEFT JOIN users u ON c.professor_id = u.user_id
        ORDER BY c.course_name
    ");
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- DEBUGGING START ---
    error_log("Successfully fetched " . count($courses) . " courses.");
    // --- DEBUGGING END ---

} catch (PDOException $e) {
    // --- DEBUGGING START ---
    error_log("PDOException in list_courses.php: " . $e->getMessage());
    // --- DEBUGGING END ---
    $message = "Database error: Could not load courses.";
    $message_type = 'danger';
}

// Handle messages from other pages (e.g., successful edit/delete)
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
    <title>Manage Courses</title>
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
        <h1 class="mb-4">Manage Courses</h1>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <p>
            <a href="add_course.php" class="btn btn-success">Add New Course</a>
            <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </p>

        <?php if (empty($courses)): ?>
            <div class="alert alert-info">No courses found. Add a new course to get started.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Course Name</th>
                            <th>Description</th>
                            <th>Assigned Professor</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?= htmlspecialchars($course['course_id']) ?></td>
                                <td><?= htmlspecialchars($course['course_name']) ?></td>
                                <td><?= htmlspecialchars($course['description'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($course['professor_first_name'] . ' ' . $course['professor_last_name'] ?: 'Unassigned') ?></td>
                                <td>
                                    <a href="edit_course.php?id=<?= htmlspecialchars($course['course_id']) ?>" class="btn btn-sm btn-warning me-2">Edit</a>
                                    <a href="../Backend/delete_course.php?id=<?= htmlspecialchars($course['course_id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this course?');">Delete</a>
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