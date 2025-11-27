<?php
// Backend/close_session.php
require_once 'auth_check.php';
require_role('professor'); // Only professor can access

require_once 'db.php';

$message = "";
$open_sessions = [];

// Fetch currently open sessions for the dropdown
try {
    $stmt = $pdo->prepare("
        SELECT 
            s.session_id, 
            c.course_code, 
            g.group_name, 
            s.session_date, 
            u.first_name, 
            u.last_name
        FROM attendance_sessions s
        JOIN courses c ON s.course_id = c.course_id
        JOIN student_groups g ON s.group_id = g.group_id
        JOIN users u ON s.opened_by = u.user_id
        WHERE s.status = 'open' 
        ORDER BY s.session_date DESC, c.course_code, g.group_name
    ");
    $stmt->execute();
    $open_sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message .= "<div class='alert alert-danger'>Error fetching open sessions: " . $e->getMessage() . "</div>";
}


// Handle POST submission to close a session
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $session_to_close_id = trim($_POST['session_id'] ?? '');

    if (empty($session_to_close_id)) {
        $message = "<div class='alert alert-danger'>Please select a session to close.</div>";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE attendance_sessions SET status = 'closed' WHERE session_id = ? AND status = 'open'");
            $stmt->execute([$session_to_close_id]);

            if ($stmt->rowCount() > 0) {
                $message = "<div class='alert alert-success'>Session ID {$session_to_close_id} has been successfully closed.</div>";
                // Refresh open sessions list after closing one
                // (Re-run the fetch query or remove the closed session from $open_sessions array)
                $open_sessions = array_filter($open_sessions, function($session) use ($session_to_close_id) {
                    return $session['session_id'] != $session_to_close_id;
                });
            } else {
                $message = "<div class='alert alert-warning'>Session ID {$session_to_close_id} not found or was already closed.</div>";
            }
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Database error closing session: " . $e->getMessage() . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Close Attendance Session</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="../Frontend/index.html">⬅️ Back to Home</a>
            <span class="navbar-text text-white">Close Active Attendance Session</span>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-white text-center py-3">
                        <h3 class="text-primary mb-0">🔒 Close Session</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php echo $message; ?>
                        <?php if (empty($open_sessions)): ?>
                            <div class="alert alert-info text-center" role="alert">
                                No open sessions found to close. <a href="create_session.php" class="alert-link">Create a new session</a>.
                            </div>
                        <?php else: ?>
                            <form method="post" action="close_session.php">
                                <div class="mb-3">
                                    <label for="session_id" class="form-label">Select Open Session to Close</label>
                                    <select class="form-select" id="session_id" name="session_id" required>
                                        <option value="">-- Select a Session --</option>
                                        <?php foreach ($open_sessions as $session): ?>
                                            <option value="<?= htmlspecialchars($session['session_id']) ?>">
                                                [ID:<?= htmlspecialchars($session['session_id']) ?>] 
                                                <?= htmlspecialchars($session['course_code'] . ' - ' . $session['group_name']) ?> 
                                                (<?= htmlspecialchars($session['session_date']) ?>) by 
                                                <?= htmlspecialchars($session['first_name'] . ' ' . $session['last_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-danger btn-lg" onclick="return confirm('Are you sure you want to CLOSE this session? This action cannot be undone.');">Close Session Now</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>