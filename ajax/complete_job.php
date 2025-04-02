<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the database functions
require_once("../lib/function.php");

// Check if helper is logged in
if (!isset($_SESSION['helper_email'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'You must be logged in to perform this action'
    ]);
    exit();
}

// Check if post_id is provided
if (!isset($_POST['post_id']) || empty($_POST['post_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required parameter: post_id'
    ]);
    exit();
}

$post_id = $_POST['post_id'];
$helper_email = $_SESSION['helper_email'];
$note = isset($_POST['note']) ? $_POST['note'] : '';
$verification_code = isset($_POST['verification_code']) ? $_POST['verification_code'] : '';

// Connect to database
$db = new db_functions();
$conn = $db->connect();

try {
    // Add any missing columns that we need
    $columns_to_check = [
        'verification_code' => "VARCHAR(6) NULL DEFAULT NULL COMMENT 'Verification code for job completion'",
        'completion_note' => "TEXT NULL DEFAULT NULL COMMENT 'Note added when completing the job'",
        'completed_at' => "DATETIME NULL DEFAULT NULL COMMENT 'When the job was completed'"
    ];
    
    foreach ($columns_to_check as $column => $definition) {
        // Check if column exists
        $check_column_query = "SHOW COLUMNS FROM `work_posts` LIKE '$column'";
        $column_result = $conn->query($check_column_query);
        
        if ($column_result->num_rows === 0) {
            // Add column if it doesn't exist
            $alter_query = "ALTER TABLE `work_posts` ADD COLUMN `$column` $definition";
            $conn->query($alter_query);
        }
    }
    
    // Check if verification_code column exists after our attempt to create it
    $check_column_query = "SHOW COLUMNS FROM `work_posts` LIKE 'verification_code'";
    $column_result = $conn->query($check_column_query);
    $verification_enabled = ($column_result->num_rows > 0);
    
    // Check if the post exists and is assigned to this helper
    $check_query = "SELECT * FROM work_posts WHERE id = ? AND assigned_helper_email = ? AND status = 'pending'";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("is", $post_id, $helper_email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Post not found or not assigned to you, or not in pending status'
        ]);
        exit();
    }
    
    $post = $result->fetch_assoc();
    
    // If verification is enabled in the database and a code was sent with the request
    if ($verification_enabled && !empty($verification_code)) {
        // Validate the verification code
        if (empty($post['verification_code']) || $verification_code !== $post['verification_code']) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid verification code. Please ensure the code is correct and try again.'
            ]);
            exit();
        }
    }
    
    // If verification is enabled but no code was provided and the post has a code, require verification
    if ($verification_enabled && empty($verification_code) && !empty($post['verification_code'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Verification code required. Please enter the code provided by the client.'
        ]);
        exit();
    }
    
    // Check if completion_note and completed_at columns exist
    $check_column_query = "SHOW COLUMNS FROM `work_posts` LIKE 'completion_note'";
    $completion_note_exists = ($conn->query($check_column_query)->num_rows > 0);
    
    $check_column_query = "SHOW COLUMNS FROM `work_posts` LIKE 'completed_at'";
    $completed_at_exists = ($conn->query($check_column_query)->num_rows > 0);
    
    // Prepare update query based on available columns
    if ($completion_note_exists && $completed_at_exists) {
        $update_query = "UPDATE work_posts SET status = 'completed', completion_note = ?, completed_at = NOW() WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $note, $post_id);
    } else if ($completion_note_exists) {
        $update_query = "UPDATE work_posts SET status = 'completed', completion_note = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $note, $post_id);
    } else {
        $update_query = "UPDATE work_posts SET status = 'completed' WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("i", $post_id);
    }
    
    $success = $update_stmt->execute();
    
    if ($success) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Job marked as completed successfully'
        ]);
    } else {
        throw new Exception("Failed to mark job as completed");
    }
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
    
    // Check if the error suggests we need to run migrations
    if (strpos($error_message, "Unknown column") !== false) {
        $error_message = "Database setup required. Please run the migration script: <a href='../migrations/add_verification_code.php'>Run Migration</a>";
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $error_message
    ]);
}
?>
