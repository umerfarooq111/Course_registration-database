<?php
require_once __DIR__ . '/helpers.php';
$student_id = require_student_session();
$conn = db_connect();

header('Content-Type: application/json; charset=utf-8');

try {
    $stmt = $conn->prepare("SELECT title, content, created_at FROM Notices ORDER BY created_at DESC LIMIT 10");
    $stmt->execute();
    $res = $stmt->get_result();
    $notices = [];
    while ($row = $res->fetch_assoc()) {
        $notices[] = $row;
    }
    echo json_encode($notices);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
