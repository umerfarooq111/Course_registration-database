<?php
require 'c:/xampp/htdocs/course_registration_system/php/db.php';
global $conn;

function show_columns($table) {
    global $conn;
    echo "<h3>$table Columns:</h3>";
    $res = $conn->query("SHOW COLUMNS FROM $table");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            echo $row['Field'] . " (" . $row['Type'] . ")<br>";
        }
    } else {
        echo "Error: " . $conn->error . "<br>";
    }
}

show_columns('Course_Section');
show_columns('Registration');
show_columns('Student');
show_columns('Course');
show_columns('Instructor');
?>
