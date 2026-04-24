<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'university.db';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli($host, $user, $password, $database);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
?>