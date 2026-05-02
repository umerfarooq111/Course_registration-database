<?php
require_once __DIR__ . '/php/admin/helpers.php';
$conn = db_connect_admin();
echo "Students API:\n";
$res = $conn->query("SELECT * FROM Student LIMIT 1");
print_r($res->fetch_assoc());
echo "\nCourses API:\n";
$query = "SELECT c.course_id, c.title, c.course_type, 
          (SELECT required_course_id FROM Pre_Requisite WHERE course_id = c.course_id LIMIT 1) AS prereq_id
          FROM Course c LIMIT 1";
$res = $conn->query($query);
print_r($res->fetch_assoc());
