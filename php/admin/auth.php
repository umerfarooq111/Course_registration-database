<?php
session_start();
require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin_login.php');
    exit;
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$password = trim($_POST['password'] ?? '');

if (!$email || empty($password)) {
    header('Location: ../../admin_login.php?error=invalid_credentials');
    exit;
}

try {
    $conn = db_connect_admin();

    $stmt = $conn->prepare('SELECT * FROM Admin WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $adminUser = $result->fetch_assoc();
    $stmt->close();

    // Check plaintext per user's earlier explicit preference against hashing
    if ($adminUser && $password === $adminUser['password']) {
        $_SESSION['admin_id'] = $adminUser['admin_id'];
        $_SESSION['admin_name'] = $adminUser['admin_name'];

        header('Location: ../../admin_dashboard.html');
        exit;
    } else {
        header('Location: ../../admin_login.php?error=invalid_credentials');
        exit;
    }
} catch (Exception $e) {
    error_log("Admin Login error: " . $e->getMessage());
    header('Location: ../../admin_login.php?error=error');
    exit;
}
?>
