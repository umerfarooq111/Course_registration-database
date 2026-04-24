<?php
include 'db.php';

$student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
$section_id = isset($_POST['section_id']) ? intval($_POST['section_id']) : 0;

if ($student_id <= 0 || $section_id <= 0) {
    echo json_encode(['error' => 'Missing student or section information.']);
    exit;
}

$stmt = $conn->prepare(
    'SELECT cs.enrollment_count, c.max_capacity
     FROM Course_Section cs
     JOIN Course c ON cs.course_id = c.course_id
     WHERE cs.section_id = ?'
);
$stmt->bind_param('i', $section_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode(['error' => 'Section not found.']);
    exit;
}

if ($row['enrollment_count'] >= $row['max_capacity']) {
    echo json_encode(['error' => 'Course Full']);
    exit;
}

$check = $conn->prepare(
    'SELECT registration_id FROM Registration WHERE student_id = ? AND section_id = ? AND status = "REGISTERED"'
);
$check->bind_param('ii', $student_id, $section_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(['error' => 'Already Enrolled']);
    exit;
}
$check->close();

$insert = $conn->prepare(
    'INSERT INTO Registration (student_id, section_id, status, registration_at) VALUES (?, ?, "REGISTERED", NOW())'
);
$insert->bind_param('ii', $student_id, $section_id);
$insert->execute();
$insert->close();

$update = $conn->prepare(
    'UPDATE Course_Section SET enrollment_count = enrollment_count + 1 WHERE section_id = ?'
);
$update->bind_param('i', $section_id);
$update->execute();
$update->close();

echo json_encode(['message' => 'Enrolled Successfully']);
?>