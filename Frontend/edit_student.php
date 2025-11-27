<?php
// Frontend/edit_student.php
require_once '../Backend/auth_check.php';
require_role('admin'); // Only admins can edit students

require_once '../Backend/db.php'; // Include database connection

$student_id = $_GET['id'] ?? null;
$student = null;
$groups = [];
$message = '';
$message_type = '';

if (!$student_id) {
    header("Location: list_students.php?message=No student ID provided for editing.&type=danger");
    exit();
}

try {
    // Fetch student details
    $stmt_student = $pdo->prepare("SELECT student_id, fullname, matricule, group_id FROM students WHERE student_id = ?");
    $stmt_student->execute([$student_id]);
    $student = $stmt_student->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        header("Location: list_students.php?message=Student not found.&type=danger");
        exit();
    }

    // Fetch all student groups for the dropdown
    $stmt_groups = $pdo->prepare("SELECT group_id, group_name FROM student_groups ORDER BY group_name");
    $stmt_groups->execute();
    $groups = $stmt_groups->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error fetching student/groups for edit_student.php: " . $e->getMessage());
    $message = "Database error: Could not load student details or groups.";
    $message_type = 'danger';
}

// Handle messages from the edit_student_process.php script
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
    <title>Edit Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary mb-4 shadow-sm">
        <div class="container">
            <span class="navbar-brand">Admin Dashboard</span>
            <div class="d-flex align-items-center">
                <span class="navbar-text text-white me-3">Welcome, <?= htmlspecialchars($_SESSION['']) ?>!</span>
                <a href="../Backend/logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 class="mb-4">Edit Student: <?= htmlspecialchars($student['fullname']) ?></h1>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="card p-4 shadow-sm">
            <form action="../Backend/edit_student_process.php" method="POST">
                <input type="hidden" name="student_id" value="<?= htmlspecialchars($student['student_id']) ?>">
                <div class="mb-3">
                    <label for="fullname" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="fullname" name="fullname" value="<?= htmlspecialchars($student['fullname']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="matricule" class="form-label">Matricule</label>
                    <input type="text" class="form-control" id="matricule" name="matricule" value="<?= htmlspecialchars($student['matricule']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="group_id" class="form-label">Assign Group</label>
                    <select class="form-select" id="group_id" name="group_id" required>
                        <option value="">-- Select a Group --</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= htmlspecialchars($group['group_id']) ?>"
                                <?= ($group['group_id'] == $student['group_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($group['group_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update Student</button>
                <a href="list_students.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>