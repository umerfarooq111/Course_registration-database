<?php
require_once __DIR__ . '/helpers.php';
$admin_id = require_admin_session();

// Ensure we have a valid connection
require_once dirname(__DIR__) . '/db.php';
global $conn;

header('Content-Type: application/json; charset=utf-8');

try {
    $stats = [
        'total_students' => 0,
        'total_courses' => 0,
        'total_instructors' => 0,
        'recent_registrations' => []
    ];

    // Total Students
    $res = $conn->query("SELECT COUNT(*) as count FROM Student");
    if ($res) {
        $stats['total_students'] = (int)$res->fetch_assoc()['count'];
    }

    // Total Courses
    $res = $conn->query("SELECT COUNT(*) as count FROM Course");
    if ($res) {
        $stats['total_courses'] = (int)$res->fetch_assoc()['count'];
    }

    // Total Instructors
    $res = $conn->query("SELECT COUNT(*) as count FROM Instructor");
    if ($res) {
        $stats['total_instructors'] = (int)$res->fetch_assoc()['count'];
    }

    // Recent Registrations
    $query = "SELECT s.name as student_name, c.title as course_title, r.registration_at 
              FROM Registration r
              JOIN Student s ON r.student_id = s.student_id
              JOIN Course_Section cs ON r.section_id = cs.section_id
              JOIN Course c ON cs.course_id = c.course_id
              WHERE r.status = 'REGISTERED'
              ORDER BY r.registration_at DESC LIMIT 5";
    $res = $conn->query($query);
    if ($res) {
        while($row = $res->fetch_assoc()) {
            $stats['recent_registrations'][] = $row;
        }
    }

    echo json_encode($stats);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
