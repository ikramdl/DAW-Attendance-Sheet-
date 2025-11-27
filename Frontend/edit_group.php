<?php
// Frontend/edit_group.php
require_once '../Backend/auth_check.php';
require_role('admin'); // Only admins can edit groups

require_once '../Backend/db.php'; // Include database connection

$group_id = $_GET['id'] ?? null;
$group = null;
$message = '';
$message_type = '';

if (!$group_id) {
    header("Location: list_groups.php?message=No group ID provided for editing.&type=danger");
    exit();
}

try {
    // Fetch group details
    $stmt_group = $pdo->prepare("SELECT group_id, group_name FROM student_groups WHERE group_id = ?");
    $stmt_group->execute([$group_id]);
    $group = $stmt_group->fetch(PDO::FETCH_ASSOC);

    if (!$group) {
        header("Location: list_groups.php?message=Student group not found.&type=danger");
        exit();
    }

} catch (PDOException $e) {
    error_log("Error fetching group for edit_group.php: " . $e->getMessage());
    $message = "Database error: Could not load group details.";
    $message_type = 'danger';
}

// Handle messages from the edit_group_process.php script
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
    <title>Edit Student Group</title>
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
        <h1 class="mb-4">Edit Student Group: <?= htmlspecialchars($group['group_name']) ?></h1>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="card p-4 shadow-sm">
            <form action="../Backend/edit_group_process.php" method="POST">
                <input type="hidden" name="group_id" value="<?= htmlspecialchars($group['group_id']) ?>">
                <div class="mb-3">
                    <label for="group_name" class="form-label">Group Name</label>
                    <input type="text" class="form-control" id="group_name" name="group_name" value="<?= htmlspecialchars($group['group_name']) ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Group</button>
                <a href="list_groups.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>