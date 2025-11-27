<?php
// Backend/login.php
require_once 'db.php'; // Include your database connection

session_start();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // --- DEBUGGING START ---
    error_log("Attempting login for username: " . $username); 
    // --- DEBUGGING END ---

    if (empty($username) || empty($password)) {
        $response['message'] = 'Please enter both username and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT user_id, username, password_hash, first_name, last_name, role FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // --- DEBUGGING START ---
            if ($user) {
                error_log("User found in DB. Role: " . $user['role']);
                if (isset($user['password_hash']) && is_string($user['password_hash'])) {
                    $storedHash = trim($user['password_hash']); // <<< CRITICAL: Trim the retrieved hash
                    error_log("DB hash (trimmed) starts with: " . substr($storedHash, 0, 10)); // Added for final debug confirmation
                    $isPasswordCorrect = password_verify($password, $storedHash);
                    error_log("Password verification result: " . ($isPasswordCorrect ? "TRUE" : "FALSE"));
                } else {
                    error_log("Password hash in DB is invalid or missing for user: " . $username);
                    $isPasswordCorrect = false;
                }
            } else {
                error_log("User NOT found in DB for username: " . $username);
                $isPasswordCorrect = false;
            }
            // --- DEBUGGING END ---

            if ($user && $isPasswordCorrect) { // Use the variable from debugging block
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['fullname'] = $user['first_name'] . ' ' . $user['last_name'];

                $response['success'] = true;
                $response['message'] = 'Login successful!';

                switch ($user['role']) {
                    case 'admin':
                        $response['redirect'] = '../Frontend/admin_dashboard.php';
                        break;
                    case 'professor':
                        $response['redirect'] = '../Frontend/professor_dashboard.php';
                        break;
                    case 'student':
                        $response['redirect'] = '../Frontend/student_dashboard.php';
                        break;
                    default:
                        $response['redirect'] = '../Frontend/index.html';
                }
            } else {
                $response['message'] = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Database error during login. Please try again later.';
            error_log("Login PDO Error: " . $e->getMessage());
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
exit();
?>
<form action="login.php" method="POST">
    </form>