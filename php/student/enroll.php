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
        'SELECT cs.course_id, cs.enrollment_count, c.max_capacity
         FROM Course_Section cs
         JOIN Course c ON cs.course_id = c.course_id
         WHERE cs.section_id = ? FOR UPDATE'
    );
    $stmt->bind_param('i', $sectionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $section = $result->fetch_assoc();
    $stmt->close();

    if (!$section) {
        throw new Exception('Section does not exist.');
    }

    if ($section['enrollment_count'] >= $section['max_capacity']) {
        throw new Exception('This section is full.');
    }

    // --- REGISTRATION LIMIT CHECK ---
    // 1. Get Student's current Batch and Semester
    $stuStmt = $conn->prepare('SELECT batch, semester FROM Student WHERE student_id = ?');
    $stuStmt->bind_param('i', $studentId);
    $stuStmt->execute();
    $studentData = $stuStmt->get_result()->fetch_assoc();
    $stuStmt->close();

    if (!$studentData) throw new Exception('Student data not found.');

    $batch = $studentData['batch'];
    $studentSemester = $studentData['semester'];

    // 2. Get the Limit for this Batch/Semester
    $limitStmt = $conn->prepare('SELECT max_courses FROM Registration_Limits WHERE batch = ? AND semester = ?');
    $limitStmt->bind_param('ii', $batch, $studentSemester);
    $limitStmt->execute();
    $limitResult = $limitStmt->get_result()->fetch_assoc();
    $limitStmt->close();

    $maxAllowed = $limitResult ? $limitResult['max_courses'] : 5; // Default to 5 if no limit set

    // 3. Count current active registrations for this student
    // We count all courses where status is REGISTERED and no grade is assigned yet (NULL or empty)
    $countStmt = $conn->prepare('
        SELECT COUNT(*) as reg_count 
        FROM Registration
        WHERE student_id = ? AND status = "REGISTERED" AND (grade IS NULL OR grade = "")
    ');
    $countStmt->bind_param('i', $studentId);
    $countStmt->execute();
    $regCount = $countStmt->get_result()->fetch_assoc()['reg_count'];
    $countStmt->close();

    if ($regCount >= $maxAllowed) {
        throw new Exception("Registration limit reached. Your current limit is $maxAllowed courses.");
    }
    // --- END LIMIT CHECK ---

    $existing = $conn->prepare(
        'SELECT registration_id, status FROM Registration WHERE student_id = ? AND section_id = ? FOR UPDATE'
    );
    $existing->bind_param('ii', $studentId, $sectionId);
    $existing->execute();
    $existingResult = $existing->get_result();
    $registrationRecord = $existingResult->fetch_assoc();
    $existing->close();

    if ($registrationRecord && $registrationRecord['status'] === 'REGISTERED') {
        throw new Exception('You are already enrolled in this section.');
    }

    $missingPrereqs = [];
    $prereqStmt = $conn->prepare(
        'SELECT p.course_id, p.title
         FROM Pre_Requisite pr
         JOIN Course p ON pr.required_course_id = p.course_id
         WHERE pr.course_id = ?'
    );
    $prereqStmt->bind_param('i', $section['course_id']);
    $prereqStmt->execute();
    $prereqResult = $prereqStmt->get_result();
    $prereqStmt->close();

    while ($prereq = $prereqResult->fetch_assoc()) {
        $checkCompleted = $conn->prepare(
            'SELECT 1
             FROM Registration r
             JOIN Course_Section cs2 ON r.section_id = cs2.section_id
             WHERE r.student_id = ?
               AND cs2.course_id = ?
               AND r.status = "REGISTERED"
               AND r.grade IS NOT NULL
             LIMIT 1'
        );
        $checkCompleted->bind_param('ii', $studentId, $prereq['course_id']);
        $checkCompleted->execute();
        $checkCompleted->store_result();

        if ($checkCompleted->num_rows === 0) {
            $missingPrereqs[] = $prereq['title'];
        }
        $checkCompleted->close();
    }

    if (!empty($missingPrereqs)) {
        $conn->rollback();
        http_response_code(400);
        echo json_encode(['error' => 'Prerequisite courses required: ' . implode(', ', $missingPrereqs)]);
        exit;
    }

    if ($registrationRecord && $registrationRecord['status'] === 'DROPPED') {
        $updateRegistration = $conn->prepare(
            'UPDATE Registration SET status = "REGISTERED", drop_at = NULL, registration_at = NOW() WHERE registration_id = ?'
        );
        $updateRegistration->bind_param('i', $registrationRecord['registration_id']);
        $updateRegistration->execute();
    } else {
        $insert = $conn->prepare(
            'INSERT INTO Registration (student_id, section_id, status, registration_at) VALUES (?, ?, "REGISTERED", NOW())'
        );
        $insert->bind_param('ii', $studentId, $sectionId);
        $insert->execute();
    }

    $update = $conn->prepare(
        'UPDATE Course_Section SET enrollment_count = enrollment_count + 1 WHERE section_id = ?'
    );
    $update->bind_param('i', $sectionId);
    $update->execute();

    $conn->commit();
    echo json_encode(['message' => 'Enrolled successfully']);
} catch (Exception $e) {
    if (isset($conn) && $conn->connect_errno === 0 && $conn->in_transaction) {
        $conn->rollback();
    }
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
