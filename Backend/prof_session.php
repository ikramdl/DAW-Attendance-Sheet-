<?php
// prof_session.php
require_once 'auth_check.php';
require_role('professor');
require_once 'db.php';

$group_id = $_GET['group_id'] ?? null;
if(!$group_id) die("Group ID missing.");

// Fetch students in this group
$stmt = $pdo->prepare("
    SELECT u.user_id, u.first_name, u.last_name, u.username 
    FROM users u 
    JOIN enrollments e ON u.user_id = e.student_id 
    WHERE e.group_id = ? AND u.role = 'student'
    ORDER BY u.last_name ASC
");
$stmt->execute([$group_id]);
$students = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h2>Mark Attendance</h2>
        
        <div class="card p-3 mb-3">
             <label for="sessionDate" class="form-label">Session Date:</label>
             <input type="date" id="sessionDate" class="form-control mb-2" value="<?php echo date('Y-m-d'); ?>">
        </div>

        <form id="attendanceForm">
            <input type="hidden" name="group_id" value="<?php echo htmlspecialchars($group_id); ?>">
            
            <div class="table-responsive card p-2">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Participation (0-5)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['username']); ?></td>
                        <td><?php echo htmlspecialchars($student['last_name'] . ' ' . $student['first_name']); ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="status[<?php echo $student['user_id']; ?>]" id="p_<?php echo $student['user_id']; ?>" value="present" checked>
                                <label class="btn btn-outline-success" for="p_<?php echo $student['user_id']; ?>">Present</label>

                                <input type="radio" class="btn-check" name="status[<?php echo $student['user_id']; ?>]" id="a_<?php echo $student['user_id']; ?>" value="absent">
                                <label class="btn btn-outline-danger" for="a_<?php echo $student['user_id']; ?>">Absent</label>
                            </div>
                        </td>
                        <td>
                            <input type="number" name="participation[<?php echo $student['user_id']; ?>]" class="form-control" min="0" max="5" value="0" style="width: 80px;">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <button type="button" id="submitAttendanceBtn" class="btn btn-primary btn-lg mt-3 w-100">Save Attendance</button>
        </form>
        <div id="responseMessage" class="mt-3"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
    $(document).ready(function() {
        $('#submitAttendanceBtn').click(function() {
            // Collect form data
            var formData = $('#attendanceForm').serialize();
            var sessionDate = $('#sessionDate').val();

            // Add date to formData
            formData += '&session_date=' + sessionDate;

            $.ajax({
                url: 'api_submit_attendance.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    $('#submitAttendanceBtn').prop('disabled', true).text('Saving...');
                },
                success: function(response) {
                    if(response.success) {
                         $('#responseMessage').html('<div class="alert alert-success">Attendance saved successfully!</div>');
                         // Optional: redirect after a few seconds
                    } else {
                         $('#responseMessage').html('<div class="alert alert-danger">Error: ' + response.message + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#responseMessage').html('<div class="alert alert-danger">An unexpected system error occurred.</div>');
                    console.error(error);
                },
                complete: function() {
                     $('#submitAttendanceBtn').prop('disabled', false).text('Save Attendance');
                }
            });
        });
    });
    </script>
</body>
</html>