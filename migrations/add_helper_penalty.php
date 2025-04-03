<?php
require_once("../lib/function.php");

$db = new db_functions();
$conn = $db->connect();

$sql = "ALTER TABLE helper_profiles 
        ADD COLUMN penalty_until DATE NULL,
        ADD COLUMN last_penalty_reason TEXT NULL";

if ($conn->query($sql) === TRUE) {
    echo "Helper penalty columns added successfully";
} else {
    echo "Error adding penalty columns: " . $conn->error;
}
