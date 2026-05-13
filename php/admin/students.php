<?php
require_once __DIR__ . '/helpers.php';
$admin_id = require_admin_session();
$conn = db_connect_admin();

$method = $_SERVER['REQUEST_METHOD'];
header('Content-Type: application/json; charset=utf-8');

try {
    if ($method === 'GET') {
        $stmt = $conn->prepare("SELECT student_id, name, email, phone_no, dob, enrollment_date, status, password, cgpa, semester, credits_completed, batch FROM Student ORDER BY student_id ASC");
        $stmt->execute();
        $res = $stmt->get_result();
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
        $cgpa = floatval($data['cgpa'] ?? 0);
        $semester = intval($data['semester'] ?? 1);
        $credits = intval($data['credits_completed'] ?? 0);
        $batch = intval($data['batch'] ?? 2024);

        if (!$name || !$email || !$password) send_json(['error' => 'Name, Email, and Password are required'], 400);
        if ($semester < 1 || $semester > 10) send_json(['error' => 'Semester must be between 1 and 10'], 400);
        if ($batch < 2000 || $batch > 2100) send_json(['error' => 'Batch must be between 2000 and 2100'], 400);

        $stmt = $conn->prepare('INSERT INTO Student (name, email, phone_no, password, enrollment_date, status, cgpa, semester, credits_completed, batch) VALUES (?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssssdiii', $name, $email, $phone, $password, $status, $cgpa, $semester, $credits, $batch);
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
        $cgpa = floatval($data['cgpa'] ?? 0);
        $semester = intval($data['semester'] ?? 1);
        $credits = intval($data['credits_completed'] ?? 0);
        $batch = intval($data['batch'] ?? 2024);

        if ($id <= 0 || !$name || !$email) send_json(['error' => 'Invalid data'], 400);
        if ($semester < 1 || $semester > 10) send_json(['error' => 'Semester must be between 1 and 10'], 400);
        if ($batch < 2000 || $batch > 2100) send_json(['error' => 'Batch must be between 2000 and 2100'], 400);

        if ($password !== '') {
            $stmt = $conn->prepare('UPDATE Student SET name = ?, email = ?, phone_no = ?, password = ?, status = ?, cgpa = ?, semester = ?, credits_completed = ?, batch = ? WHERE student_id = ?');
            $stmt->bind_param('sssssdiiii', $name, $email, $phone, $password, $status, $cgpa, $semester, $credits, $batch, $id);
        } else {
            $stmt = $conn->prepare('UPDATE Student SET name = ?, email = ?, phone_no = ?, status = ?, cgpa = ?, semester = ?, credits_completed = ?, batch = ? WHERE student_id = ?');
            $stmt->bind_param('ssssdiiii', $name, $email, $phone, $status, $cgpa, $semester, $credits, $batch, $id);
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
