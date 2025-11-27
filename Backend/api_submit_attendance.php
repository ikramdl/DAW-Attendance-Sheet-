<?php
// api_submit_attendance.php
// This is an API endpoint, it should return JSON
header('Content-Type: application/json');
require_once 'auth_check.php';
require_role('professor'); // Security check
require_once 'db.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 1. Validate Inputs
        if (empty($_POST['group_id']) || empty($_POST['session_date']) || empty($_POST['status'])) {
            throw new Exception("Missing required data.");
        }

        $groupId = $_POST['group_id'];
        $sessionDate = $_POST['session_date'];
        $statuses = $_POST['status']; // Array of user_id => status
        $participations = $_POST['participation'] ?? []; // Array of user_id => score

        // Start Transaction (Important for integrity!)
        $pdo->beginTransaction();

        // 2. Create the Session record
        $stmtSession = $pdo->prepare("INSERT INTO sessions (group_id, session_date, status) VALUES (?, ?, 'closed')");
        $stmtSession->execute([$groupId, $sessionDate]);
        $sessionId = $pdo->lastInsertId();

        // 3. Insert individual attendance records
        $stmtRecord = $pdo->prepare("INSERT INTO attendance_records (session_id, student_id, status, participation_score) VALUES (?, ?, ?, ?)");

        foreach ($statuses as $studentId => $status) {
            $score = $participations[$studentId] ?? 0;
            // Basic validation against DB enum types
            if(!in_array($status, ['present', 'absent'])) $status = 'absent';
            $score = min(5, max(0, intval($score))); // Ensure 0-5 range

            $stmtRecord->execute([$sessionId, $studentId, $status, $score]);
        }

        // Commit transaction
        $pdo->commit();
        $response['success'] = true;
        $response['message'] = "Attendance taken for session ID: " . $sessionId;

    } catch (Exception $e) {
        // Rollback if anything went wrong
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $response['message'] = $e->getMessage();
        error_log("Attendance submission error: " . $e->getMessage()); // Log it
    }
} else {
    $response['message'] = "Invalid request method.";
}

echo json_encode($response);
?>