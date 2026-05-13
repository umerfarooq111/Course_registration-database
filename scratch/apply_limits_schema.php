<?php
require 'php/db.php';

try {
    $conn->query("ALTER TABLE Student ADD COLUMN batch INT DEFAULT 2024");
    echo "Added batch column to Student table (or it already exists).\n";
} catch (Exception $e) {
    echo "Note: " . $e->getMessage() . "\n";
}

try {
    $conn->query("CREATE TABLE IF NOT EXISTS Registration_Limits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        batch INT NOT NULL,
        semester INT NOT NULL,
        max_courses INT NOT NULL,
        UNIQUE KEY unique_batch_semester (batch, semester)
    )");
    echo "Created Registration_Limits table.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

try {
    $conn->query("INSERT IGNORE INTO Registration_Limits (batch, semester, max_courses) VALUES 
    (2024, 1, 5),
    (2024, 2, 6),
    (2023, 1, 5),
    (2023, 2, 6)");
    echo "Inserted default limits.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
