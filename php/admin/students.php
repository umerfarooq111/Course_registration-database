<?php
require_once __DIR__ . '/helpers.php';
$admin_id = require_admin_session();
$conn = db_connect_admin();

$method = $_SERVER['REQUEST_METHOD'];
header('Content-Type: application/json; charset=utf-8');

try {
    if ($method === 'GET') {
        $res = $conn->query("SELECT student_id, name, email, phone_no, dob, enrollment_date, status, password FROM Student ORDER BY student_id ASC");
        $data = [];
        while ($row = $res->fetch_assoc()) $data[] = $row;
        send_json($data);

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone_no'] ?? '');
        $password = trim($data['password'] ?? '');
        $status = 'CURRENT'; // Explicitly set starting status

        if (!$name || !$email || !$password) send_json(['error' => 'Name, Email, and Password are required'], 400);

        $stmt = $conn->prepare('INSERT INTO Student (name, email, phone_no, password, enrollment_date, status) VALUES (?, ?, ?, ?, CURDATE(), ?)');
        $stmt->bind_param('sssss', $name, $email, $phone, $password, $status);
        $stmt->execute();
        send_json(['message' => 'Student created successfully']);

    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['student_id'] ?? 0);
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone_no'] ?? '');
        $password = trim($data['password'] ?? '');
        $status = trim($data['status'] ?? 'CURRENT');

        if ($id <= 0 || !$name || !$email) send_json(['error' => 'Invalid data'], 400);

        if ($password !== '') {
            $stmt = $conn->prepare('UPDATE Student SET name = ?, email = ?, phone_no = ?, password = ?, status = ? WHERE student_id = ?');
            $stmt->bind_param('sssssi', $name, $email, $phone, $password, $status, $id);
        } else {
            $stmt = $conn->prepare('UPDATE Student SET name = ?, email = ?, phone_no = ?, status = ? WHERE student_id = ?');
            $stmt->bind_param('ssssi', $name, $email, $phone, $status, $id);
        }
        $stmt->execute();
        send_json(['message' => 'Student updated successfully']);

    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['student_id'] ?? 0);
        if ($id <= 0) send_json(['error' => 'Invalid ID'], 400);

        $stmt = $conn->prepare('DELETE FROM Student WHERE student_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        send_json(['message' => 'Student deleted successfully']);
    }
} catch (Exception $e) {
    if ($conn->errno == 1451) {
        send_json(['error' => 'Cannot delete. Student is already enrolled in classes.'], 400);
    }
    // Duplicate emails catch
    if ($conn->errno == 1062) {
        send_json(['error' => 'Email address is already in use.'], 400);
    }
    send_json(['error' => $e->getMessage()], 500);
}
?>
