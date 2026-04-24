<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/helpers.php';

$raw = json_decode(file_get_contents('php://input'), true);
$sectionId = isset($raw['section_id']) ? intval($raw['section_id']) : 0;

if ($sectionId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid section selected.']);
    exit;
}

try {
    $studentId = require_student_session();
    $conn = db_connect();
    $conn->begin_transaction();

    $stmt = $conn->prepare(
        'SELECT registration_id FROM Registration WHERE student_id = ? AND section_id = ? AND status = "REGISTERED" FOR UPDATE'
    );
    $stmt->bind_param('ii', $studentId, $sectionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $registration = $result->fetch_assoc();
    $stmt->close();

    if (!$registration) {
        throw new Exception('You are not currently enrolled in this section.');
    }

    $updateReg = $conn->prepare(
        'UPDATE Registration SET status = "dropped", dropped_at = NOW() WHERE registration_id = ?'
    );
    $updateReg->bind_param('i', $registration['registration_id']);
    $updateReg->execute();

    $updateSection = $conn->prepare(
        'UPDATE Course_Section SET enrollment_count = GREATEST(enrollment_count - 1, 0) WHERE section_id = ?'
    );
    $updateSection->bind_param('i', $sectionId);
    $updateSection->execute();

    $conn->commit();
    echo json_encode(['message' => 'Course dropped successfully']);
} catch (Exception $e) {
    if (isset($conn) && $conn->connect_errno === 0 && $conn->in_transaction) {
        $conn->rollback();
    }
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
