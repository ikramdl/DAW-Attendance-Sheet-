<?php
// Frontend/add_course.php
require_once '../Backend/auth_check.php';
require_role('admin'); // Only admins can add courses

require_once '../Backend/db.php'; // Include database connection

$message = '';
$message_type = '';
$professor_id = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_name = trim($_POST['course_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $professor_id = $_POST['professor_id'] ?? null; // Get professor ID from form

    if (empty($course_name) || empty($professor_id)) {
        $message = "Course name and Professor are required.";
        $message_type = 'danger';
    } else {
        try {
            // Check if course name already exists
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE course_name = ?");
            $stmt_check->execute([$course_name]);
            if ($stmt_check->fetchColumn() > 0) {
                $message = "A course with this name already exists.";
                $message_type = 'warning';
            } else {
                $stmt = $pdo->prepare("INSERT INTO courses (course_name, description, professor_id) VALUES (?, ?, ?)");
                if ($stmt->execute([$course_name, $description, $professor_id])) {
                    $message = "Course added successfully!";
                    $message_type = 'success';
                    // Clear form fields after success
                    $course_name = '';
                    $description = '';
                    $professor_id = '';
                } else {
                    $message = "Failed to add course.";
                    $message_type = 'danger';
                }
            }
        } catch (PDOException $e) {
            error_log("Error adding course: " . $e->getMessage());
            $message = "Database error: Could not add course. Please try again later.";
            $message_type = 'danger';
        }
    }
}

// Fetch list of professors for the dropdown
$professors = [];
try {
    $stmt_prof = $pdo->prepare("SELECT user_id, first_name, last_name FROM users WHERE role = 'professor' ORDER BY last_name, first_name");
    $stmt_prof->execute();
    $professors = $stmt_prof->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching professors for course form: " . $e->getMessage());
    $message = "Could not load professors list.";
    $message_type = 'danger';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Course</title>
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
        <h1 class="mb-4">Add New Course</h1>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form action="add_course.php" method="POST">
                    <div class="mb-3">
                        <label for="course_name" class="form-label">Course Name</label>
                        <input type="text" class="form-control" id="course_name" name="course_name" value="<?= htmlspecialchars($course_name ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($description ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="professor_id" class="form-label">Assigned Professor</label>
                        <select class="form-select" id="professor_id" name="professor_id" required>
                            <option value="">Select a Professor</option>
                            <?php foreach ($professors as $prof): ?>
                                <option value="<?= htmlspecialchars($prof['user_id']) ?>" <?= ($professor_id == $prof['user_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($prof['first_name'] . ' ' . $prof['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Course</button>
                    <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>