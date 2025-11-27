<?php
// Frontend/list_users.php
require_once '../Backend/auth_check.php';
require_role('admin'); // Only admins can view/manage users

require_once '../Backend/db.php'; // Include database connection

// Fetch user roles for display
$user_roles = [
    'admin' => 'Administrator',
    'professor' => 'Professor',
    'student' => 'Student'
];

$users = [];
$message = '';
$message_type = '';

try {
    // Fetch all users, now selecting first_name and last_name
    $sql_query = "SELECT user_id, username, first_name, last_name, role FROM users ORDER BY role, first_name, last_name";
    error_log("list_users.php: Executing query: " . $sql_query); // Log the query
    $stmt = $pdo->prepare($sql_query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("list_users.php: Query executed successfully. Fetched " . count($users) . " users.");
    if (empty($users)) {
        error_log("list_users.php: No users found after fetchAll().");
    } else {
        foreach ($users as $idx => $user) {
            error_log("list_users.php: User " . $idx . ": ID=" . $user['user_id'] . ", Username=" . $user['username'] . ", Name=" . $user['first_name'] . " " . $user['last_name'] . ", Role=" . $user['role']);
        }
    }

} catch (PDOException $e) {
    error_log("PDOException in list_users.php: " . $e->getMessage());
    $message = "Database error: Could not load users.";
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
    <title>Manage System Users</title>
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
        <h1 class="mb-4">Manage System Users</h1>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <p>
            <a href="add_user.php" class="btn btn-success">Add New User</a>
            <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </p>

        <?php if (empty($users)): ?>
            <div class="alert alert-info">No users found. Add a new user to get started.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['user_id']) ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
            <td><?= htmlspecialchars($user_roles[$user['role']] ?? $user['role']) ?></td>
            <td>
                <a href="edit_user.php?id=<?= htmlspecialchars($user['user_id']) ?>" class="btn btn-sm btn-warning me-2">Edit</a>
                <?php
                // Check if current user is trying to delete themselves
                // Use null coalescing operator for safe access to $_SESSION['user_id']
                $current_user_id = $_SESSION['user_id'] ?? null;
                if ($user['user_id'] != $current_user_id):
                ?>
                    <a href="../Backend/delete_user.php?id=<?= htmlspecialchars($user['user_id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                <?php else: ?>
                    <button class="btn btn-sm btn-danger" disabled>Delete Self</button>
                <?php endif; ?>
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