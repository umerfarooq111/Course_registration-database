<?php
header('Content-Type: application/json');
require_once '../student/helpers.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$password = $data['password'] ?? '';

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$conn = db_connect();

$stmt = $conn->prepare('SELECT student_id, name, password FROM Student WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode(['error' => 'Invalid credentials']);
    exit;
}

session_start();
$_SESSION['student_id'] = $user['student_id'];
$_SESSION['name'] = $user['name'];

echo json_encode(['message' => 'Logged in successfully']);
?>
