<?php
require 'php/db.php';
$res = $conn->query("DESCRIBE Student");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
