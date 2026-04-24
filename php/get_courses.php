<?php
include 'db.php';

$query = "
SELECT cs.section_id, c.title, c.credit_hr, c.max_capacity, cs.enrollment_count,
       COALESCE(i.instructor_name, 'TBA') AS instructor_name
FROM Course_Section cs
JOIN Course c ON cs.course_id = c.course_id
LEFT JOIN Instructor i ON cs.instructor_id = i.instructor_id
";

$result = $conn->query($query);

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>