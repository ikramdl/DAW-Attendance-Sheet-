<?php
// Frontend/edit_course.php
require_once '../Backend/auth_check.php';
require_role('admin'); // Only admins can edit courses

require_once '../Backend/db.php'; // Include database connection

$course_id = $_GET['id'] ?? null;
$course = null;
$message = '';
$message_type = '';
$professor_id = null; // Initialize to prevent undefined variable warning

// Fetch course details if ID is provided
if ($course_id) {
    try {
        $stmt_course = $pdo->prepare("SELECT course_id, course_name, description, professor_id FROM courses WHERE course_id = ?");
        $stmt_course->execute([$course_id]);
        $course = $stmt_course->fetch(PDO::FETCH_ASSOC);

        if ($course) {
            // Populate variables for form
            $course_name = $course['course_name'];
            $description = $course['description'];
            $professor_id = $course['professor_id'];
        } else {
            $message = "Course not found.";
            $message_type = 'danger';
            $course_id = null; // Invalidate ID if course not found
        }
    } catch (PDOException $e) {
        error_log("Error fetching course for edit: " . $e->getMessage());
        $message = "Database error: Could not load course details.";
        $message_type = 'danger';
        $course_id = null;
    }
} else {
    $message = "No course ID provided.";
    $message_type = 'danger';
}

// Handle form submission for updating course
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $course_id) {
    $course_name_post = trim($_POST['course_name'] ?? '');
    $description_post = trim($_POST['description'] ?? '');
    $professor_id_post = $_POST['professor_id'] ?? null;

    if (empty($course_name_post) || empty($professor_id_post)) {
        $message = "Course name and Assigned Professor are required.";
        $message_type = 'danger';
    } else {
        try {
            // Check if course name already exists for a DIFFERENT course_id
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE course_name = ? AND course_id != ?");
            $stmt_check->execute([$course_name_post, $course_id]);
            if ($stmt_check->fetchColumn() > 0) {
                $message = "A course with this name already exists for another course.";
                $message_type = 'warning';
            } else {
                $stmt_update = $pdo->prepare("UPDATE courses SET course_name = ?, description = ?, professor_id = ? WHERE course_id = ?");
                if ($stmt_update->execute([$course_name_post, $description_post, $professor_id_post, $course_id])) {
                    $message = "Course updated successfully!";
                    $message_type = 'success';
                    // Update current course object and variables to reflect changes without re-fetching
                    $course['course_name'] = $course_name = $course_name_post;
                    $course['description'] = $description = $description_post;
                    $course['professor_id'] = $professor_id = $professor_id_post;
                } else {
                    $message = "Failed to update course.";
                    $message_type = 'danger';
                }
            }
        } catch (PDOException $e) {
            error_log("Error updating course: " . $e->getMessage());
            $message = "Database error: Could not update course. Please try again later.";
            $message_type = 'danger';
        }
    }
}

// Fetch list of professors for the dropdown (needed on initial load and after post)
$professors = [];
try {
    $stmt_prof = $pdo->prepare("SELECT user_id, first_name, last_name FROM users WHERE role = 'professor' ORDER BY last_name, first_name");
    $stmt_prof->execute();
    $professors = $stmt_prof->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching professors for course form: " . $e->getMessage());
    $message = "Could not load professors list.";
    $message_type = 'danger'; // This will override other messages if profs fail
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course</title>
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
        <h1 class="mb-4">Edit Course</h1>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($course_id && $course): ?>
            <div class="card">
                <div class="card-body">
                    <form action="edit_course.php?id=<?= htmlspecialchars($course_id) ?>" method="POST">
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
                        <button type="submit" class="btn btn-primary">Update Course</button>
                        <a href="list_courses.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        <?php elseif (!$course_id): ?>
            <div class="alert alert-info">Please select a course to edit from the <a href="list_courses.php">Manage Courses</a> page.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>