<?php
require_once __DIR__ . '/helpers.php';
$admin_id = require_admin_session();
$conn = db_connect_admin();

$method = $_SERVER['REQUEST_METHOD'];
header('Content-Type: application/json; charset=utf-8');

try {
    if ($method === 'GET') {
        $res = $conn->query("SELECT * FROM Department ORDER BY department_id ASC");
        $data = [];
        while ($row = $res->fetch_assoc()) $data[] = $row;
        send_json($data);

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $name = trim($data['department_name'] ?? '');
        if (!$name) send_json(['error' => 'Invalid data'], 400);

        $stmt = $conn->prepare('INSERT INTO Department (department_name) VALUES (?)');
        $stmt->bind_param('s', $name);
        $stmt->execute();
        send_json(['message' => 'Department created successfully']);

    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['department_id'] ?? 0);
        $name = trim($data['department_name'] ?? '');
        if ($id <= 0 || !$name) send_json(['error' => 'Invalid data'], 400);

        $stmt = $conn->prepare('UPDATE Department SET department_name = ? WHERE department_id = ?');
        $stmt->bind_param('si', $name, $id);
        $stmt->execute();
        send_json(['message' => 'Department updated successfully']);

    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['department_id'] ?? 0);
        if ($id <= 0) send_json(['error' => 'Invalid ID'], 400);

        $stmt = $conn->prepare('DELETE FROM Department WHERE department_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        send_json(['message' => 'Department deleted successfully']);
    }
} catch (Exception $e) {
    // If FK constraint fails on delete
    if ($conn->errno == 1451) {
        send_json(['error' => 'Cannot delete. Department is linked to existing Courses or Instructors.'], 400);
    }
    send_json(['error' => $e->getMessage()], 500);
}
?>
