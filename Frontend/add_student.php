<?php
// Frontend/add_student.php
require_once '../Backend/auth_check.php';
require_role('admin'); // Only admins can add students

require_once '../Backend/db.php'; // Include database connection

$groups = [];
$message = '';
$message_type = '';

try {
    // Fetch all student groups for the dropdown
    $stmt = $pdo->prepare("SELECT group_id, group_name FROM student_groups ORDER BY group_name");
    $stmt->execute();
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching groups for add_student.php: " . $e->getMessage());
    $message = "Database error: Could not load student groups.";
    $message_type = 'danger';
}

// Handle messages from the add_student_process.php script
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
    <title>Add New Student</title>
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
        <h1 class="mb-4">Add New Student</h1>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="card p-4 shadow-sm">
            <form action="../Backend/add_student_process.php" method="POST">
                <div class="mb-3">
                    <label for="fullname" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="fullname" name="fullname" required>
                </div>
                <div class="mb-3">
                    <label for="matricule" class="form-label">Matricule</label>
                    <input type="text" class="form-control" id="matricule" name="matricule" required>
                </div>
                <div class="mb-3">
                    <label for="group_id" class="form-label">Assign Group</label>
                    <select class="form-select" id="group_id" name="group_id" required>
                        <option value="">-- Select a Group --</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= htmlspecialchars($group['group_id']) ?>">
                                <?= htmlspecialchars($group['group_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Add Student</button>
                <a href="list_students.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>