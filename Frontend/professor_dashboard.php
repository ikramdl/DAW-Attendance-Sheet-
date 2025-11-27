<?php
// Frontend/professor_dashboard.php
require_once '../Backend/auth_check.php';
require_role('professor'); // Ensure only professors can access this dashboard

require_once '../Backend/db.php'; // Include database connection

// Fetch active sessions associated with this professor
$current_user_id = $_SESSION['user_id'];
$active_sessions = [];
try {
    // Select sessions that are 'open' and created by the current professor
    // We also join to get course and group names for better display
    $stmt = $pdo->prepare("
        SELECT 
            s.session_id, 
            s.session_date, 
            c.course_name, 
            sg.group_name 
        FROM attendance_sessions s
        JOIN courses c ON s.course_id = c.course_id
        JOIN student_groups sg ON s.group_id = sg.group_id
        WHERE s.status = 'open' AND s.professor_id = ?
        ORDER BY s.session_date DESC, c.course_name, sg.group_name
    ");
    $stmt->execute([$current_user_id]);
    $active_sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching active sessions for professor: " . $e->getMessage());
    // Optionally display a user-friendly error message
    $error_message = "Could not load active sessions. Please try again later.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-info mb-4 shadow-sm">
        <div class="container">
            <span class="navbar-brand">Professor Dashboard</span>
            <div class="d-flex align-items-center">
                <span class="navbar-text text-white me-3">Welcome, <?= htmlspecialchars($_SESSION['fullname']) ?>!</span>
                <a href="../Backend/logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 class="mb-4">Professor Home Page</h1>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        Attendance Session Management
                    </div>
                    <ul class="list-group list-group-flush">
                        <a href="../Backend/create_session.php" class="list-group-item list-group-item-action">Create New Attendance Session</a>
                        <a href="../Backend/close_session.php" class="list-group-item list-group-item-action">Close Attendance Session</a>
                    </ul>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        Open Sessions for Marking
                    </div>
                    <div class="card-body">
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger"><?= $error_message ?></div>
                        <?php elseif (empty($active_sessions)): ?>
                            <p class="text-muted">No attendance sessions are currently open for marking.</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($active_sessions as $session): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($session['course_name']) ?></strong> (<?= htmlspecialchars($session['group_name']) ?>)
                                            <small class="text-muted d-block"><?= htmlspecialchars($session['session_date']) ?></small>
                                        </div>
                                        <a href="mark_attendance.php?session_id=<?= htmlspecialchars($session['session_id']) ?>" class="btn btn-sm btn-success">Mark Attendance</a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="mt-5 mb-3">Other Professor Actions</h2>
        <div class="list-group">
            <a href="#" class="list-group-item list-group-item-action disabled">View My Courses (Coming Soon)</a>
            <a href="#" class="list-group-item list-group-item-action disabled">View Attendance Summaries (Coming Soon)</a>
            <a href="#" class="list-group-item list-group-item-action disabled">Manage Justifications (Coming Soon)</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>