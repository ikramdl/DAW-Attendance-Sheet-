<?php
require_once 'auth_check.php';
require_role('admin'); // Only admin can access

require_once 'db.php';
// Backend/update_student.php
require_once 'db.php'; // Include your database connection

$message = "";
$student = null; // Initialize student variable

// Check if an ID is provided in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    // Try to fetch the student's current data
    try {
        $stmt = $pdo->prepare("SELECT id, fullname, matricule, group_id FROM students WHERE id = ?");
        $stmt->execute([$id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            $message = "<div class='alert alert-warning'>Student not found.</div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Database error fetching student: " . $e->getMessage() . "</div>";
    }
} else {
    $message = "<div class='alert alert-danger'>No student ID provided for update.</div>";
}

// Handle form submission for updating
if ($_SERVER["REQUEST_METHOD"] == "POST" && $student) {
    $id = $_POST['id']; // Get ID from hidden field
    $fullname = trim($_POST['fullname'] ?? '');
    $matricule = trim($_POST['matricule'] ?? '');
    $group_id = trim($_POST['group_id'] ?? '');

    if (empty($fullname) || empty($matricule) || empty($group_id)) {
        $message = "<div class='alert alert-danger'>All fields are required.</div>";
    } else {
        try {
            // Check if matricule already exists for a *different* student
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE matricule = ? AND id != ?");
            $stmt->execute([$matricule, $id]);
            if ($stmt->fetchColumn() > 0) {
                $message = "<div class='alert alert-warning'>Error: Another student already has this Matricule.</div>";
            } else {
                $stmt = $pdo->prepare("UPDATE students SET fullname = ?, matricule = ?, group_id = ? WHERE id = ?");
                $stmt->execute([$fullname, $matricule, $group_id, $id]);
                $message = "<div class='alert alert-success'>Student '{$fullname}' updated successfully!</div>";
                
                // Update the $student array so the form shows the latest data
                $student['fullname'] = $fullname;
                $student['matricule'] = $matricule;
                $student['group_id'] = $group_id;
            }
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Database error updating student: " . $e->getMessage() . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="list_students.php">⬅️ Back to Student List</a>
            <span class="navbar-text text-white">Edit Student Details</span>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-white text-center py-3">
                        <h3 class="text-primary mb-0">✏️ Edit Student</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php echo $message; ?>
                        <?php if ($student): ?>
                        <form method="post" action="update_student.php?id=<?= $student['id'] ?>">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($student['id']) ?>">
                            <div class="mb-3">
                                <label for="fullname" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" value="<?= htmlspecialchars($student['fullname']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="matricule" class="form-label">Matricule (Student ID)</label>
                                <input type="text" class="form-control" id="matricule" name="matricule" value="<?= htmlspecialchars($student['matricule']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="group_id" class="form-label">Group ID</label>
                                <input type="text" class="form-control" id="group_id" name="group_id" value="<?= htmlspecialchars($student['group_id']) ?>" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Update Student</button>
                                <a href="list_students.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                        <?php else: ?>
                            <div class="alert alert-danger text-center">Could not load student data for editing.</div>
                            <div class="text-center mt-3"><a href="list_students.php" class="btn btn-primary">Go to Student List</a></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>