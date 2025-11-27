<?php
// Frontend/list_groups.php
require_once '../Backend/auth_check.php';
require_role('admin'); // Only admins can view/manage groups

require_once '../Backend/db.php'; // Include database connection

$groups = [];
$message = '';
$message_type = '';

try {
    // Fetch all student groups
    $stmt = $pdo->prepare("SELECT group_id, group_name FROM student_groups ORDER BY group_name");
    $stmt->execute();
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("PDOException in list_groups.php: " . $e->getMessage());
    $message = "Database error: Could not load student groups.";
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
    <title>Manage Student Groups</title>
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
        <h1 class="mb-4">Manage Student Groups</h1>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <p>
            <a href="add_group.php" class="btn btn-success">Add New Group</a>
            <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </p>

        <?php if (empty($groups)): ?>
            <div class="alert alert-info">No student groups found. Add a new group to get started.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Group Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groups as $group): ?>
                            <tr>
                                <td><?= htmlspecialchars($group['group_id']) ?></td>
                                <td><?= htmlspecialchars($group['group_name']) ?></td>
                                <td>
                                    <a href="edit_group.php?id=<?= htmlspecialchars($group['group_id']) ?>" class="btn btn-sm btn-warning me-2">Edit</a>
                                    <a href="../Backend/delete_group.php?id=<?= htmlspecialchars($group['group_id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this group?');">Delete</a>
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