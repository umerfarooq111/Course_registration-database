<?php
header('Content-Type: application/json');
require_once '../student/helpers.php';

$data = json_decode(file_get_contents('php://input'), true);
$name = trim($data['name'] ?? '');
$email = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$password = $data['password'] ?? '';

if (!$name || !$email || strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$conn = db_connect();

$stmt = $conn->prepare('SELECT student_id FROM Student WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['error' => 'Email already exists']);
    exit;
}
$stmt->close();

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO Student (name, email, password, enrollment_date) VALUES (?, ?, ?, CURDATE())');
$stmt->bind_param('sss', $name, $email, $hash);
$stmt->execute();

session_start();
$_SESSION['student_id'] = $conn->insert_id;
$_SESSION['name'] = $name;

echo json_encode(['message' => 'Registered successfully']);
?>
