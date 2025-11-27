<?php
// Frontend/mark_attendance.php
require_once '../Backend/auth_check.php';
require_role('professor'); // Only professors can mark attendance

require_once '../Backend/db.php'; // Include database connection

$session_id = $_GET['session_id'] ?? null;
$session_details = null;
$students_in_group = [];
$attendance_statuses = []; // To store existing attendance if any

if ($session_id) {
    try {
        // Fetch session details to ensure it's valid and open, and belongs to this professor
        $stmt_session = $pdo->prepare("
            SELECT 
                s.session_id, 
                s.session_date, 
                s.status, 
                c.course_name, 
                sg.group_name, 
                s.group_id,
                s.professor_id
            FROM attendance_sessions s
            JOIN courses c ON s.course_id = c.course_id
            JOIN student_groups sg ON s.group_id = sg.group_id
            WHERE s.session_id = ? AND s.status = 'open' AND s.professor_id = ?
        ");
        $stmt_session->execute([$session_id, $_SESSION['user_id']]);
        $session_details = $stmt_session->fetch(PDO::FETCH_ASSOC);

        if ($session_details) {
            // Fetch students belonging to this session's group
            $stmt_students = $pdo->prepare("
                SELECT st.student_id, st.first_name, st.last_name, st.student_card_id
                FROM students st
                WHERE st.group_id = ?
                ORDER BY st.last_name, st.first_name
            ");
            $stmt_students->execute([$session_details['group_id']]);
            $students_in_group = $stmt_students->fetchAll(PDO::FETCH_ASSOC);

            // Fetch any existing attendance records for this session
            $stmt_existing_attendance = $pdo->prepare("
                SELECT student_id, status 
                FROM attendance_records 
                WHERE session_id = ?
            ");
            $stmt_existing_attendance->execute([$session_id]);
            foreach ($stmt_existing_attendance->fetchAll(PDO::FETCH_ASSOC) as $record) {
                $attendance_statuses[$record['student_id']] = $record['status'];
            }

        } else {
            $error = "Invalid or unauthorized session ID, or session is not open.";
        }
    } catch (PDOException $e) {
        error_log("Error fetching session or student details for attendance marking: " . $e->getMessage());
        $error = "Database error: Could not load details for attendance marking.";
    }
} else {
    $error = "No session ID provided.";
}

// Handle form submission for marking attendance
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $session_id && $session_details) {
    try {
        // Prepare statement for inserting/updating attendance records
        // Using ON DUPLICATE KEY UPDATE to handle re-submission gracefully
        $stmt_mark_attendance = $pdo->prepare("
            INSERT INTO attendance_records (session_id, student_id, status, attendance_date)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE status = VALUES(status), attendance_date = NOW();
        ");

        $pdo->beginTransaction(); // Start transaction for atomicity

        foreach ($_POST['attendance'] as $student_id => $status) {
            // Validate status (basic check)
            if (in_array($status, ['present', 'absent', 'late'])) {
                $stmt_mark_attendance->execute([$session_id, $student_id, $status]);
            }
        }

        $pdo->commit(); // Commit transaction
        $success_message = "Attendance marked successfully!";
        // After marking, refresh the page to show latest statuses or redirect
        header('Location: mark_attendance.php?session_id=' . htmlspecialchars($session_id) . '&message=' . urlencode($success_message));
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack(); // Rollback transaction on error
        error_log("Error marking attendance: " . $e->getMessage());
        $error = "Failed to mark attendance: Database error.";
        if ($e->getCode() == 23000) { // Foreign key constraint or duplicate entry, often due to bad data
            $error .= " Please ensure data integrity.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-check-inline .form-check-input {
            margin-right: 0.5rem;
        }
    </style>
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
        <h1 class="mb-4">Mark Attendance</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
            <a href="professor_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
        <?php elseif (!$session_details): ?>
             <div class="alert alert-warning">No valid session found for marking attendance.</div>
             <a href="professor_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
        <?php else: ?>
            <?php if (isset($_GET['message'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_GET['message']) ?></div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Session Details
                </div>
                <div class="card-body">
                    <p><strong>Course:</strong> <?= htmlspecialchars($session_details['course_name']) ?></p>
                    <p><strong>Group:</strong> <?= htmlspecialchars($session_details['group_name']) ?></p>
                    <p><strong>Date:</strong> <?= htmlspecialchars($session_details['session_date']) ?></p>
                    <p><strong>Status:</strong> <span class="badge bg-success"><?= htmlspecialchars(ucfirst($session_details['status'])) ?></span></p>
                </div>
            </div>

            <h2 class="mb-3">Students in Group (<?= htmlspecialchars($session_details['group_name']) ?>)</h2>

            <?php if (empty($students_in_group)): ?>
                <div class="alert alert-info">No students found in this group.</div>
                <a href="professor_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
            <?php else: ?>
                <form action="mark_attendance.php?session_id=<?= htmlspecialchars($session_id) ?>" method="POST">
                    <ul class="list-group mb-3">
                        <?php foreach ($students_in_group as $student): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <?= htmlspecialchars($student['last_name']) ?>, <?= htmlspecialchars($student['first_name']) ?> 
                                    <small class="text-muted">(<?= htmlspecialchars($student['student_card_id']) ?>)</small>
                                </div>
                                <div class="btn-group" role="group" aria-label="Attendance Status">
                                    <?php 
                                        $current_status = $attendance_statuses[$student['student_id']] ?? 'absent'; // Default to absent if not marked
                                    ?>
                                    <input type="radio" class="btn-check" name="attendance[<?= $student['student_id'] ?>]" id="present_<?= $student['student_id'] ?>" value="present" <?= $current_status == 'present' ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-success" for="present_<?= $student['student_id'] ?>">Present</label>

                                    <input type="radio" class="btn-check" name="attendance[<?= $student['student_id'] ?>]" id="absent_<?= $student['student_id'] ?>" value="absent" <?= $current_status == 'absent' ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-danger" for="absent_<?= $student['student_id'] ?>">Absent</label>

                                    <input type="radio" class="btn-check" name="attendance[<?= $student['student_id'] ?>]" id="late_<?= $student['student_id'] ?>" value="late" <?= $current_status == 'late' ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-warning" for="late_<?= $student['student_id'] ?>">Late</label>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="submit" class="btn btn-primary btn-lg">Save Attendance</button>
                    <a href="professor_dashboard.php" class="btn btn-secondary btn-lg">Cancel</a>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>