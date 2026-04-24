<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}
echo json_encode(['student_id' => $_SESSION['student_id'], 'name' => $_SESSION['name']]);
?>
