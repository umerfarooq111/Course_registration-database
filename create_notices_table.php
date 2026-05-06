<?php
require_once 'php/db.php';

$sql = "CREATE TABLE IF NOT EXISTS Notices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table Notices created successfully or already exists.";
} else {
    echo "Error creating table: " . $conn->error;
}
?>
