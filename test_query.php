<?php
require 'c:/xampp/htdocs/course_registration_system/php/db.php';
global $conn;
$query = "SELECT o.offering_id, o.course_id, o.semester, c.title,
                  cs.instructor_id, i.instructor_name
                  FROM Course_Offering o
                  JOIN Course c ON o.course_id = c.course_id
                  LEFT JOIN Course_Section cs ON c.course_id = cs.course_id
                  LEFT JOIN Instructor i ON cs.instructor_id = i.instructor_id
                  GROUP BY o.offering_id
                  ORDER BY o.semester ASC, o.course_id ASC";
$res = $conn->query($query);
if(!$res) echo $conn->error;
else echo "Query OK";
?>
