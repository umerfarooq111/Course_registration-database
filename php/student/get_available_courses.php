<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/helpers.php';

try {
    $conn = db_connect();

    $query = "
        SELECT cs.section_id,
               c.course_id,
               c.title,
               c.credit_hr,
               c.max_capacity,
               cs.enrollment_count,
               CONCAT('Section ', cs.section_id) AS section_label,
               COALESCE(i.instructor_name, 'TBA') AS instructor_name
        FROM Course_Section cs
        JOIN Course c ON cs.course_id = c.course_id
        LEFT JOIN Instructor i ON cs.instructor_id = i.instructor_id
        ORDER BY c.title, cs.section_id
    ";
    $result = $conn->query($query);

    $sections = [];
    $courseIds = [];
    while ($row = $result->fetch_assoc()) {
        $sections[] = $row;
        $courseIds[$row['course_id']] = true;
    }

    if (!empty($courseIds)) {
        $ids = implode(',', array_map('intval', array_keys($courseIds)));
        $prereqMap = [];

        $prereqSql = "
            SELECT pr.course_id,
                   GROUP_CONCAT(DISTINCT p.title SEPARATOR ', ') AS prereq_titles
            FROM Pre_Requisite pr
            JOIN Course p ON pr.required_course_id = p.course_id
            WHERE pr.course_id IN ($ids)
            GROUP BY pr.course_id
        ";

        $prereqResult = $conn->query($prereqSql);
        while ($row = $prereqResult->fetch_assoc()) {
            $prereqMap[$row['course_id']] = $row['prereq_titles'];
        }

        foreach ($sections as &$section) {
            $section['prereq_titles'] = $prereqMap[$section['course_id']] ?? '';
        }
    }

    echo json_encode($sections);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to load courses: ' . $e->getMessage()]);
}
