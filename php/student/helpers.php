<?php
function db_connect() {
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $database = 'university.db';

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli($host, $user, $password, $database);
    $conn->set_charset('utf8mb4');

    return $conn;
}

function require_student_session() {
    session_start();
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required. Please log in again.']);
        exit;
    }
    return $_SESSION['user_id'];
}

function send_json($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}
