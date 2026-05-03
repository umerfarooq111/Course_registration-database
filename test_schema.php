<?php
require 'c:/xampp/htdocs/course_registration_system/php/db.php';
global $conn;
$res = $conn->query("DESCRIBE Course_Section");
while($row = $res->fetch_assoc()) print_r($row);
?>
