<?php
// Frontend/admin_dashboard.php
require_once '../Backend/auth_check.php';
require_role('admin'); // Ensure only admins can access this dashboard
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        <h1 class="mb-4">Admin Home Page</h1>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        Student Management
                    </div>
                    <ul class="list-group list-group-flush">
                        <a href="add_student.php" class="list-group-item list-group-item-action">Add New Student</a>
                        <a href="list_students.php" class="list-group-item list-group-item-action">View/Manage Students</a>
                    </ul>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        Course Management
                    </div>
                    <ul class="list-group list-group-flush">
                        <a href="add_course.php" class="list-group-item list-group-item-action">Add New Course</a>
                        <a href="list_courses.php" class="list-group-item list-group-item-action">View/Manage Courses</a>
                    </ul>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-warning text-dark"> Group Management
                    </div>
                    <ul class="list-group list-group-flush">
                        <a href="add_group.php" class="list-group-item list-group-item-action">Add New Group</a>
                        <a href="list_groups.php" class="list-group-item list-group-item-action">View/Manage Groups</a>
                    </ul>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-danger text-white">
                        User Management
                    </div>
                    <ul class="list-group list-group-flush">
                        <a href="add_user.php" class="list-group-item list-group-item-action">Add New User</a>
                        <a href="list_users.php" class="list-group-item list-group-item-action">View/Manage Users</a>
                    </ul>
                </div>
            </div>

            <div class="col-12 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-secondary text-white">
                        Reports & Overviews
                    </div>
                    <ul class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action disabled">View All Attendance Sessions</a>
                        <a href="#" class="list-group-item list-group-item-action disabled">Overall Attendance Statistics</a>
                    </ul>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>