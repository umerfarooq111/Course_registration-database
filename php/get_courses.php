<?php
header('Content-Type: application/json; charset=utf-8');
include 'db.php';
session_start();

try {
    $studentSemester = null;
    if (!empty($_SESSION['user_id'])) {
        $studentId = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT semester FROM Student WHERE student_id = ?");
        $stmt->bind_param('i', $studentId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $studentSemester = $row['semester'];
        }
        $stmt->close();
    }

    // Base query
    $query = "
        SELECT 
            cs.section_id, 
            c.course_id, 
            c.title, 
            c.credit_hr, 
            c.max_capacity, 
            cs.enrollment_count,
            COALESCE(i.instructor_name, 'TBA') AS instructor_name,
            COALESCE(
                (SELECT GROUP_CONCAT(p.title SEPARATOR ', ')
                 FROM Pre_Requisite pr
                 JOIN Course p ON pr.required_course_id = p.course_id
                 WHERE pr.course_id = c.course_id), 'None') AS prereq
        FROM Course_Section cs
        JOIN Course c ON cs.course_id = c.course_id
        LEFT JOIN Instructor i ON cs.instructor_id = i.instructor_id
    ";

    // If student is logged in and has a semester, filter by it
    if ($studentSemester !== null) {
        $query .= " JOIN Course_Offering co ON co.course_id = c.course_id ";
        $query .= " WHERE co.semester = " . intval($studentSemester);
    }

    $query .= " ORDER BY c.course_id ASC, cs.section_id ASC";

    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        // Output Sanitization to prevent XSS when displayed on the frontend
        $row['title'] = htmlspecialchars((string) $row['title'], ENT_QUOTES, 'UTF-8');
        $row['instructor_name'] = htmlspecialchars((string) $row['instructor_name'], ENT_QUOTES, 'UTF-8');
        $row['prereq'] = htmlspecialchars((string) $row['prereq'], ENT_QUOTES, 'UTF-8');

        $data[] = $row;
    }

    echo json_encode($data, JSON_THROW_ON_ERROR);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while fetching available courses.']);
    // For debugging, we log the actual error securely: error_log($e->getMessage());
}
?>