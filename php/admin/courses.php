<?php
require_once __DIR__ . '/helpers.php';
$admin_id = require_admin_session();
$conn = db_connect_admin();

$method = $_SERVER['REQUEST_METHOD'];
header('Content-Type: application/json; charset=utf-8');

try {
    if ($method === 'GET') {
        $query = "SELECT c.course_id, c.title, c.credit_hr, c.max_capacity, d.department_name, c.department_id 
                  FROM Course c
                  LEFT JOIN Department d ON c.department_id = d.department_id
                  ORDER BY c.course_id ASC";
        $res = $conn->query($query);
        $courses = [];
        while ($row = $res->fetch_assoc()) {
            $courses[] = $row;
        }
        send_json($courses);

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $title = trim($data['title']);
        $credit_hr = intval($data['credit_hr']);
        $max_capacity = intval($data['max_capacity']);
        $department_id = intval($data['department_id']);
        
        if (!$title || $credit_hr <= 0 || $max_capacity <= 0 || $department_id <= 0) {
            send_json(['error' => 'Invalid or missing course data'], 400);
        }

        $stmt = $conn->prepare('INSERT INTO Course (title, credit_hr, max_capacity, department_id, admin_id) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('siiii', $title, $credit_hr, $max_capacity, $department_id, $admin_id);
        $stmt->execute();
        $stmt->close();
        send_json(['message' => 'Course created successfully']);

    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $course_id = intval($data['course_id']);
        $title = trim($data['title']);
        $credit_hr = intval($data['credit_hr']);
        $max_capacity = intval($data['max_capacity']);
        $department_id = intval($data['department_id']);

        if ($course_id <= 0 || !$title || $credit_hr <= 0 || $max_capacity <= 0 || $department_id <= 0) {
            send_json(['error' => 'Invalid or missing update data'], 400);
        }

        $stmt = $conn->prepare('UPDATE Course SET title = ?, credit_hr = ?, max_capacity = ?, department_id = ? WHERE course_id = ?');
        $stmt->bind_param('siiii', $title, $credit_hr, $max_capacity, $department_id, $course_id);
        $stmt->execute();
        $stmt->close();
        send_json(['message' => 'Course updated successfully']);

    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        $course_id = intval($data['course_id']);

        if ($course_id <= 0) send_json(['error' => 'Invalid course ID'], 400);

        $stmt = $conn->prepare('DELETE FROM Course WHERE course_id = ?');
        $stmt->bind_param('i', $course_id);
        $stmt->execute();
        $stmt->close();
        send_json(['message' => 'Course deleted successfully']);
    }

} catch (Exception $e) {
    send_json(['error' => $e->getMessage()], 500);
}
?>
