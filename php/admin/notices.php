<?php
require_once __DIR__ . '/helpers.php';
$admin_id = require_admin_session();
$conn = db_connect_admin();

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = $conn->prepare("SELECT * FROM Notices ORDER BY created_at DESC");
        $stmt->execute();
        $res = $stmt->get_result();
        $notices = [];
        while ($row = $res->fetch_assoc()) {
            $notices[] = $row;
        }
        send_json($notices);

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $title = trim($data['title'] ?? '');
        $content = trim($data['content'] ?? '');

        if (!$title || !$content) {
            send_json(['error' => 'Title and Content are required'], 400);
        }

        $stmt = $conn->prepare("INSERT INTO Notices (title, content) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $content);
        $stmt->execute();
        send_json(['message' => 'Notice posted successfully']);

    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        if ($id <= 0) send_json(['error' => 'Invalid ID'], 400);

        $stmt = $conn->prepare("DELETE FROM Notices WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        send_json(['message' => 'Notice deleted successfully']);
    }
} catch (Exception $e) {
    send_json(['error' => $e->getMessage()], 500);
}
?>
