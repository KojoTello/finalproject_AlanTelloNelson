<?php
require 'db.php';
$result = $conn->query("DESCRIBE reviews");
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
?>
