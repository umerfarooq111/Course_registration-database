<?php
require_once __DIR__ . '/helpers.php';
$admin_id = require_admin_session();
$conn = db_connect_admin();

$method = $_SERVER['REQUEST_METHOD'];
header('Content-Type: application/json; charset=utf-8');

try {
    if ($method === 'GET') {
        $query = "SELECT o.offering_id, o.course_id, o.semester, c.title
                  FROM Course_Offering o
                  JOIN Course c ON o.course_id = c.course_id
                  ORDER BY o.semester ASC, o.course_id ASC";
        $res = $conn->query($query);
        $offerings = [];
        while ($row = $res->fetch_assoc()) {
            $offerings[] = $row;
        }
        send_json($offerings);

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $course_id = intval($data['course_id']);
        $semester = intval($data['semester']);
        
        if ($course_id <= 0 || $semester <= 0) {
            send_json(['error' => 'Invalid or missing offering data'], 400);
        }

        $stmt = $conn->prepare('INSERT INTO Course_Offering (course_id, semester) VALUES (?, ?)');
        $stmt->bind_param('ii', $course_id, $semester);
        $stmt->execute();
        $stmt->close();
        send_json(['message' => 'Offering created successfully']);

    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $offering_id = intval($data['offering_id']);
        $course_id = intval($data['course_id']);
        $semester = intval($data['semester']);

        if ($offering_id <= 0 || $course_id <= 0 || $semester <= 0) {
            send_json(['error' => 'Invalid or missing update data'], 400);
        }

        $stmt = $conn->prepare('UPDATE Course_Offering SET course_id = ?, semester = ? WHERE offering_id = ?');
        $stmt->bind_param('iii', $course_id, $semester, $offering_id);
        $stmt->execute();
        $stmt->close();
        send_json(['message' => 'Offering updated successfully']);

    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        $offering_id = intval($data['offering_id']);

        if ($offering_id <= 0) send_json(['error' => 'Invalid offering ID'], 400);

        $stmt = $conn->prepare('DELETE FROM Course_Offering WHERE offering_id = ?');
        $stmt->bind_param('i', $offering_id);
        $stmt->execute();
        $stmt->close();
        send_json(['message' => 'Offering deleted successfully']);
    }

} catch (Exception $e) {
    send_json(['error' => $e->getMessage()], 500);
}
?>
