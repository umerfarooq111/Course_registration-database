<?php
require_once __DIR__ . '/helpers.php';
$student_id = require_student_session();

$conn = db_connect();

$stmt = $conn->prepare("SELECT student_id, name, email, status, semester, cgpa, credits_completed FROM Student WHERE student_id = ?");
$stmt->bind_param('i', $student_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Student not found']);
    exit;
}

$student = $res->fetch_assoc();
header('Content-Type: application/json; charset=utf-8');
echo json_encode($student);
$stmt->close();
$conn->close();
