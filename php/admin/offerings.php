<?php
require_once __DIR__ . '/helpers.php';
$admin_id = require_admin_session();
$conn = db_connect_admin();

$method = $_SERVER['REQUEST_METHOD'];
header('Content-Type: application/json; charset=utf-8');

try {
    if ($method === 'GET') {
        $query = "SELECT o.offering_id, o.course_id, o.semester, c.title,
                  cs.instructor_id, i.instructor_name
                  FROM Course_Offering o
                  JOIN Course c ON o.course_id = c.course_id
                  LEFT JOIN Course_Section cs ON c.course_id = cs.course_id
                  LEFT JOIN Instructor i ON cs.instructor_id = i.instructor_id
                  GROUP BY o.offering_id
                  ORDER BY o.semester ASC, o.course_id ASC";
        $res = $conn->query($query);
        $offerings = [];
        while ($row = $res->fetch_assoc()) {
            $offerings[] = $row;
        }
        send_json($offerings);

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $course_id = intval($data['course_id'] ?? 0);
        $semester = intval($data['semester'] ?? 0);
        $instructor_id = intval($data['instructor_id'] ?? 0);
        
        if ($course_id <= 0 || $semester <= 0 || $instructor_id <= 0) {
            send_json(['error' => 'Invalid or missing offering data. Please select valid Course and Instructor.'], 400);
        }

        // Validate course
        $res = $conn->query("SELECT course_id FROM Course WHERE course_id = $course_id");
        if ($res->num_rows === 0) send_json(['error' => 'The selected course does not exist.'], 400);

        // Validate instructor
        $res = $conn->query("SELECT instructor_id FROM Instructor WHERE instructor_id = $instructor_id");
        if ($res->num_rows === 0) send_json(['error' => 'The selected instructor does not exist.'], 400);

        $conn->begin_transaction();

        $stmt = $conn->prepare('INSERT INTO Course_Offering (course_id, semester) VALUES (?, ?)');
        $stmt->bind_param('ii', $course_id, $semester);
        $stmt->execute();
        $stmt->close();

        // Insert into Course_Section
        $stmt = $conn->prepare('INSERT INTO Course_Section (course_id, instructor_id, enrollment_count) VALUES (?, ?, 0)');
        $stmt->bind_param('ii', $course_id, $instructor_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        send_json(['message' => 'Offering created successfully']);

    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $offering_id = intval($data['offering_id'] ?? 0);
        $course_id = intval($data['course_id'] ?? 0);
        $semester = intval($data['semester'] ?? 0);
        $instructor_id = intval($data['instructor_id'] ?? 0);

        if ($offering_id <= 0 || $course_id <= 0 || $semester <= 0 || $instructor_id <= 0) {
            send_json(['error' => 'Invalid or missing update data. Please select valid Course and Instructor.'], 400);
        }

        // Validate course
        $res = $conn->query("SELECT course_id FROM Course WHERE course_id = $course_id");
        if ($res->num_rows === 0) send_json(['error' => 'The selected course does not exist.'], 400);

        // Validate instructor
        $res = $conn->query("SELECT instructor_id FROM Instructor WHERE instructor_id = $instructor_id");
        if ($res->num_rows === 0) send_json(['error' => 'The selected instructor does not exist.'], 400);

        $conn->begin_transaction();

        $stmt = $conn->prepare('UPDATE Course_Offering SET course_id = ?, semester = ? WHERE offering_id = ?');
        $stmt->bind_param('iii', $course_id, $semester, $offering_id);
        $stmt->execute();
        $stmt->close();

        // Update Course_Section (create if not exists)
        $res = $conn->query("SELECT section_id FROM Course_Section WHERE course_id = $course_id LIMIT 1");
        if ($row = $res->fetch_assoc()) {
            $section_id = $row['section_id'];
            $stmt = $conn->prepare('UPDATE Course_Section SET instructor_id = ? WHERE section_id = ?');
            $stmt->bind_param('ii', $instructor_id, $section_id);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $conn->prepare('INSERT INTO Course_Section (course_id, instructor_id, enrollment_count) VALUES (?, ?, 0)');
            $stmt->bind_param('ii', $course_id, $instructor_id);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();
        send_json(['message' => 'Offering updated successfully']);

    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        $offering_id = intval($data['offering_id'] ?? 0);

        if ($offering_id <= 0) send_json(['error' => 'Invalid offering ID'], 400);

        // We could also delete the associated Course_Section, but keeping it simple as requested
        $stmt = $conn->prepare('DELETE FROM Course_Offering WHERE offering_id = ?');
        $stmt->bind_param('i', $offering_id);
        $stmt->execute();
        $stmt->close();
        send_json(['message' => 'Offering deleted successfully']);
    }

} catch (Exception $e) {
    if (isset($conn) && $conn->in_transaction) $conn->rollback();
    send_json(['error' => $e->getMessage()], 500);
}
?>
