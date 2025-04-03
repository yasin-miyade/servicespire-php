<?php
require_once("../lib/function.php");

$db = new db_functions();
$conn = $db->connect();

// Set header for JSON response
header('Content-Type: application/json');

$sql = "CREATE TABLE IF NOT EXISTS helper_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    penalty_until DATE NULL,
    last_penalty_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

try {
    if ($conn->query($sql) === TRUE) {
        // Insert records for existing helpers
        $insert_sql = "INSERT IGNORE INTO helper_profiles (email, first_name, last_name)
                       SELECT email, first_name, last_name FROM helper_sign_up";
        if ($conn->query($insert_sql) === TRUE) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Helper profiles table created and populated successfully'
            ]);
        } else {
            throw new Exception("Table created but failed to populate: " . $conn->error);
        }
    } else {
        throw new Exception("Error creating helper_profiles table: " . $conn->error);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
