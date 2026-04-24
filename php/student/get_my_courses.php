<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/helpers.php';

try {
    $studentId = require_student_session();
    $conn = db_connect();

    $stmt = $conn->prepare(
        'SELECT r.section_id,
                c.title,
                c.credit_hr,
                CONCAT("Section ", cs.section_id) AS section_label,
                COALESCE(i.instructor_name, "TBA") AS instructor_name,
                r.status,
                r.grade
         FROM Registration r
         JOIN Course_Section cs ON r.section_id = cs.section_id
         JOIN Course c ON cs.course_id = c.course_id
         LEFT JOIN Instructor i ON cs.instructor_id = i.instructor_id
         WHERE r.student_id = ?
         ORDER BY FIELD(r.status, "REGISTERED", "DROPPED"), c.title'
    );
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    echo json_encode($rows);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to load your courses: ' . $e->getMessage()]);
}
