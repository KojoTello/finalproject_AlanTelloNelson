<?php
require 'db.php';

// SQL to add columns
$sql = "ALTER TABLE reviews 
        ADD COLUMN rating_clarity INT(1) AFTER rating_score,
        ADD COLUMN rating_helpfulness INT(1) AFTER rating_clarity,
        ADD COLUMN rating_difficulty INT(1) AFTER rating_helpfulness,
        ADD COLUMN take_again TINYINT(1) AFTER rating_difficulty";

if ($conn->query($sql) === TRUE) {
    echo "Table 'reviews' updated successfully.\n";
} else {
    // Check if duplicate column error (meaning already run)
    if (strpos($conn->error, "Duplicate column") !== false) {
        echo "Columns already exist.\n";
    } else {
        echo "Error updating table: " . $conn->error . "\n";
    }
}

$conn->close();
?>
