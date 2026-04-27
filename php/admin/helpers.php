<?php
// helpers.php inside admin
require_once dirname(__DIR__) . '/db.php'; // get standard db connection handling

function db_connect_admin() {
    global $conn; // Connection is already established and strictly checked by db.php
    return $conn;
}

function require_admin_session() {
    session_start();
    if (empty($_SESSION['admin_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required. Invalid Admin Session.']);
        exit;
    }
    return $_SESSION['admin_id'];
}

/* Fallback simple REST helper to output JSON and safely exit */
function send_json($data, int $code = 200) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($code);
    echo json_encode($data);
    exit;
}
?>
