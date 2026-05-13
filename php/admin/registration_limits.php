<?php
require_once __DIR__ . '/helpers.php';
$admin_id = require_admin_session();
$conn = db_connect_admin();

$method = $_SERVER['REQUEST_METHOD'];
header('Content-Type: application/json; charset=utf-8');

try {
    if ($method === 'GET') {
        $stmt = $conn->prepare("SELECT * FROM Registration_Limits ORDER BY batch DESC, semester ASC");
        $stmt->execute();
        $res = $stmt->get_result();
        $limits = [];
        while ($row = $res->fetch_assoc()) {
            $limits[] = $row;
        }
        send_json($limits);

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $batch = intval($data['batch'] ?? 0);
        $semester = intval($data['semester'] ?? 0);
        $max_courses = intval($data['max_courses'] ?? 0);

        if ($batch < 2000 || $batch > 2100 || $semester < 1 || $semester > 10 || $max_courses <= 0) {
            send_json(['error' => 'Invalid data. Semester must be 1-10, Batch must be 2000-2100.'], 400);
        }

        $stmt = $conn->prepare('INSERT INTO Registration_Limits (batch, semester, max_courses) VALUES (?, ?, ?)');
        $stmt->bind_param('iii', $batch, $semester, $max_courses);
        $stmt->execute();
        $stmt->close();
        send_json(['message' => 'Limit added successfully']);

    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        $batch = intval($data['batch'] ?? 0);
        $semester = intval($data['semester'] ?? 0);
        $max_courses = intval($data['max_courses'] ?? 0);

        if ($id <= 0 || $batch < 2000 || $batch > 2100 || $semester < 1 || $semester > 10 || $max_courses <= 0) {
            send_json(['error' => 'Invalid data. Semester must be 1-10, Batch must be 2000-2100.'], 400);
        }

        $stmt = $conn->prepare('UPDATE Registration_Limits SET batch = ?, semester = ?, max_courses = ? WHERE id = ?');
        $stmt->bind_param('iiii', $batch, $semester, $max_courses, $id);
        $stmt->execute();
        $stmt->close();
        send_json(['message' => 'Limit updated successfully']);

    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);

        if ($id <= 0) send_json(['error' => 'Invalid ID'], 400);

        $stmt = $conn->prepare('DELETE FROM Registration_Limits WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        send_json(['message' => 'Limit deleted successfully']);
    }

} catch (Exception $e) {
    if ($conn->errno == 1062) {
        send_json(['error' => 'A limit for this Batch and Semester already exists.'], 400);
    }
    send_json(['error' => $e->getMessage()], 500);
}
?>
