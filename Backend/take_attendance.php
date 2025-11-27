<?php
header('Content-Type: text/html; charset=utf-8');

// IMPORTANT: Fix paths relative to the Backend folder (up one level ../)
$students_file = '../Data and Config/students.json';
$attendance_path = '../Data and Config/'; // Folder to store attendance
$today_date = date('Y-m-d');
$attendance_file = $attendance_path . "attendance_{$today_date}.json";

// Helper function for nicely formatted errors
function show_error($msg) {
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">';
    echo '<div class="container mt-5"><div class="alert alert-danger shadow-sm">';
    echo "<h4 class='alert-heading'>⚠️ Error</h4><p>$msg</p>";
    // Fix path back to home
    echo '<hr><a href="../Frontend/index.html" class="btn btn-outline-danger">Go Home</a>';
    echo '</div></div>';
    exit();
}

// Function to load students
function load_students($file_path) {
    if (!file_exists($file_path)) show_error("Student data file not found at: $file_path");
    $json_data = file_get_contents($file_path);
    $students = json_decode($json_data, true);
    if (empty($students)) show_error("No students found in database. Add some first.");
    return $students;
}

// --- POST Handling (Saving Attendance) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (file_exists($attendance_file)) show_error("Attendance for today ($today_date) has already been taken.");

    $attendance_records = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'status_') === 0) {
            $student_id = substr($key, 7); 
            $attendance_records[] = ["student_id" => $student_id, "status" => $value];
        }
    }
    
    if (empty($attendance_records)) show_error("No data submitted.");
    
    if (file_put_contents($attendance_file, json_encode($attendance_records, JSON_PRETTY_PRINT))) {
        // Success Message
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">';
        echo '<div class="container mt-5"><div class="card p-5 text-center shadow-sm border-success">';
        echo '<h1 class="text-success display-4">✅ Attendance Saved!</h1>';
        echo "<p class='lead'>Records for <strong>{$today_date}</strong> saved successfully.</p>";
        // Fix path back to home
        echo '<div class="mt-4"><a href="../Frontend/index.html" class="btn btn-primary btn-lg">Return to Dashboard</a></div>';
        echo '</div></div>';
        exit();
    } else {
        show_error("Could not save file. Check permissions.");
    }
}

// --- GET Handling (Showing the form) ---
$students = load_students($students_file);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Take Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Make radio buttons look like toggle switches */
        .btn-check:checked + .btn-outline-success, .btn-check:checked + .btn-outline-danger { color: white; }
        .btn-check:checked + .btn-outline-success { background-color: #198754; border-color: #198754; }
        .btn-check:checked + .btn-outline-danger { background-color: #dc3545; border-color: #dc3545; }
    </style>
</head>
<body class="bg-light">
     <nav class="navbar navbar-dark bg-primary mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="../Frontend/index.html">⬅️ Back to Home</a>
            <span class="navbar-text text-white">Date: <?php echo $today_date; ?></span>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white p-3">
                 <h2 class="text-primary text-center m-0">🖊️ Take Today's Attendance</h2>
            </div>
            <div class="card-body p-0">
                <form method="POST" action="take_attendance.php">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead class="table-primary text-white">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Group</th>
                                    <th class="text-center">Mark Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): 
                                    $id = htmlspecialchars($student['student_id']);
                                    $name = htmlspecialchars($student['name'] ?? $student['first_name'] . ' ' . $student['last_name']);
                                    $status_name = "status_{$id}"; ?>
                                <tr>
                                    <td class="fw-bold"><?php echo $id; ?></td>
                                    <td><?php echo $name; ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($student['group'] ?? '-'); ?></span></td>
                                    <td class="text-center py-3">
                                        <div class="btn-group" role="group">
                                            <input type="radio" class="btn-check" name="<?php echo $status_name; ?>" id="p_<?php echo $id; ?>" value="present" checked>
                                            <label class="btn btn-outline-success px-4" for="p_<?php echo $id; ?>">Present</label>

                                            <input type="radio" class="btn-check" name="<?php echo $status_name; ?>" id="a_<?php echo $id; ?>" value="absent">
                                            <label class="btn btn-outline-danger px-4" for="a_<?php echo $id; ?>">Absent</label>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-white p-3 text-end">
                         <button type="submit" class="btn btn-primary btn-lg px-5">💾 Save Attendance Records</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>