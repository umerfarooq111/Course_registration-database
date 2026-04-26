<?php
session_start();
require_once '../student/helpers.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../login.php');
    exit;
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$password = trim($_POST['password'] ?? '');

if (!$email || empty($password)) {
    header('Location: ../../login.php?error=invalid_credentials');
    exit;
}

try {
    $conn = db_connect();

    // Query: SELECT * FROM students WHERE email = ?
    // Using actual table name 'Student' to prevent database casing errors without changing structure
    $stmt = $conn->prepare('SELECT * FROM Student WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // Verify password securely using simple plain-text comparison as requested
    if ($user && $password === $user['password']) {
        // Start a PHP session & Store user data in session exactly as requested
        $_SESSION['user_id'] = $user['student_id'];
        $_SESSION['user_name'] = $user['name'];

        // Redirect user to dashboard/home page
        header('Location: ../../dashboard.html');
        exit;
    } else {
        // If login fails: Show error message: "Invalid credentials"
        header('Location: ../../login.php?error=invalid_credentials');
        exit;
    }

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    header('Location: ../../login.php?error=error');
    exit;
}
?>
