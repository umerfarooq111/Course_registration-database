<?php
require_once __DIR__ . '/helpers.php';
$admin_id = require_admin_session();
$conn = db_connect_admin();

$method = $_SERVER['REQUEST_METHOD'];
header('Content-Type: application/json; charset=utf-8');

try {
    if ($method === 'GET') {
        $query = "SELECT i.*, d.department_name 
                  FROM Instructor i 
                  LEFT JOIN Department d ON i.department_id = d.department_id 
                  ORDER BY i.instructor_id ASC";
        $res = $conn->query($query);
        $data = [];
        while ($row = $res->fetch_assoc()) $data[] = $row;
        send_json($data);

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $name = trim($data['instructor_name'] ?? '');
        $email = trim($data['email'] ?? '');
        $dept_id = intval($data['department_id'] ?? 0);
        
        if (!$name || !$email || $dept_id <= 0) send_json(['error' => 'Invalid data'], 400);

        $stmt = $conn->prepare('INSERT INTO Instructor (instructor_name, email, department_id) VALUES (?, ?, ?)');
        $stmt->bind_param('ssi', $name, $email, $dept_id);
        $stmt->execute();
        send_json(['message' => 'Instructor created successfully']);

    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['instructor_id'] ?? 0);
        $name = trim($data['instructor_name'] ?? '');
        $email = trim($data['email'] ?? '');
        $dept_id = intval($data['department_id'] ?? 0);

        if ($id <= 0 || !$name || !$email || $dept_id <= 0) send_json(['error' => 'Invalid data'], 400);

        $stmt = $conn->prepare('UPDATE Instructor SET instructor_name = ?, email = ?, department_id = ? WHERE instructor_id = ?');
        $stmt->bind_param('ssii', $name, $email, $dept_id, $id);
        $stmt->execute();
        send_json(['message' => 'Instructor updated successfully']);

    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['instructor_id'] ?? 0);
        if ($id <= 0) send_json(['error' => 'Invalid ID'], 400);

        $stmt = $conn->prepare('DELETE FROM Instructor WHERE instructor_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        send_json(['message' => 'Instructor deleted successfully']);
    }
} catch (Exception $e) {
    if ($conn->errno == 1451) {
        send_json(['error' => 'Cannot delete. Instructor is linked to Course Sections.'], 400);
    }
    send_json(['error' => $e->getMessage()], 500);
}
?>
