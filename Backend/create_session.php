<?php
require_once 'auth_check.php';
require_role('professor'); // Only professor can access

require_once 'db.php';
// Backend/create_session.php
require_once 'db.php'; // Include your database connection

$message = "";
$course_id = '';
$group_id = '';
$opened_by = '';

// --- Fetch existing courses, groups, and professors for dropdowns ---
$courses_options = [];
$groups_options = [];
$users_options = []; // For professors

try {
    // Fetch courses
    $stmt = $pdo->query("SELECT course_id, course_code, course_name FROM courses ORDER BY course_code");
    $courses_options = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch student groups
    $stmt = $pdo->query("SELECT group_id, group_name FROM student_groups ORDER BY group_name");
    $groups_options = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch professors (assuming 'users' table has 'professor' role)
    $stmt = $pdo->query("SELECT user_id, first_name, last_name FROM users WHERE role = 'professor' ORDER BY last_name, first_name");
    $users_options = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $message .= "<div class='alert alert-danger'>Error fetching options: " . $e->getMessage() . "</div>";
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = trim($_POST['course_id'] ?? '');
    $group_id = trim($_POST['group_id'] ?? '');
    $opened_by = trim($_POST['opened_by'] ?? ''); // Professor ID
    $session_date = date('Y-m-d'); // Current date

    if (empty($course_id) || empty($group_id) || empty($opened_by)) {
        $message = "<div class='alert alert-danger'>All fields are required.</div>";
    } else {
        try {
            // Check if a session for this course, group, and date already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance_sessions WHERE course_id = ? AND group_id = ? AND session_date = ? AND status = 'open'");
            $stmt->execute([$course_id, $group_id, $session_date]);
            if ($stmt->fetchColumn() > 0) {
                $message = "<div class='alert alert-warning'>An OPEN session for this Course and Group already exists for today.</div>";
            } else {
                $stmt = $pdo->prepare("INSERT INTO attendance_sessions (course_id, group_id, session_date, opened_by, status) VALUES (?, ?, ?, ?, 'open')");
                $stmt->execute([$course_id, $group_id, $session_date, $opened_by]);
                $new_session_id = $pdo->lastInsertId();
                $message = "<div class='alert alert-success'>Attendance Session (ID: {$new_session_id}) created successfully for today!</div>";
                // Clear form fields
                $course_id = $group_id = $opened_by = '';
            }
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Database error creating session: " . $e->getMessage() . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Attendance Session</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="../Frontend/index.html">⬅️ Back to Home</a>
            <span class="navbar-text text-white">Create New Attendance Session</span>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-white text-center py-3">
                        <h3 class="text-primary mb-0">🗓️ Create New Session (Today: <?= date('Y-m-d') ?>)</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php echo $message; ?>
                        <form method="post" action="create_session.php">
                            <div class="mb-3">
                                <label for="course_id" class="form-label">Course</label>
                                <select class="form-select" id="course_id" name="course_id" required>
                                    <option value="">Select Course</option>
                                    <?php foreach ($courses_options as $option): ?>
                                        <option value="<?= htmlspecialchars($option['course_id']) ?>" <?= ($course_id == $option['course_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($option['course_code'] . ' - ' . $option['course_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="group_id" class="form-label">Student Group</label>
                                <select class="form-select" id="group_id" name="group_id" required>
                                    <option value="">Select Group</option>
                                    <?php foreach ($groups_options as $option): ?>
                                        <option value="<?= htmlspecialchars($option['group_id']) ?>" <?= ($group_id == $option['group_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($option['group_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="opened_by" class="form-label">Opened By (Professor)</label>
                                <select class="form-select" id="opened_by" name="opened_by" required>
                                    <option value="">Select Professor</option>
                                    <?php foreach ($users_options as $option): ?>
                                        <option value="<?= htmlspecialchars($option['user_id']) ?>" <?= ($opened_by == $option['user_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($option['first_name'] . ' ' . $option['last_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Create Session</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>