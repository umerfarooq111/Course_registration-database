<?php
require_once __DIR__ . '/helpers.php';
$student_id = require_student_session();

$conn = db_connect();

$stmt = $conn->prepare("
    SELECT s.student_id, s.name, s.email, s.status, s.semester, s.cgpa, s.credits_completed, s.batch,
           IFNULL(rl.max_courses, 5) as registration_limit
    FROM Student s
    LEFT JOIN Registration_Limits rl ON s.batch = rl.batch AND s.semester = rl.semester
    WHERE s.student_id = ?
");
$stmt->bind_param('i', $student_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Student not found']);
    exit;
}

$student = $res->fetch_assoc();

// Also count current registrations for the current semester
$countStmt = $conn->prepare('
    SELECT COUNT(*) as reg_count 
    FROM Registration r
    JOIN Course_Section cs ON r.section_id = cs.section_id
    JOIN Course_Offering co ON cs.course_id = co.course_id
    WHERE r.student_id = ? AND r.status = "REGISTERED" AND co.semester = ?
');
$countStmt->bind_param('ii', $student_id, $student['semester']);
$countStmt->execute();
$regCount = $countStmt->get_result()->fetch_assoc()['reg_count'];
$student['current_registrations'] = $regCount;

header('Content-Type: application/json; charset=utf-8');
echo json_encode($student);
$stmt->close();
$countStmt->close();
$conn->close();
