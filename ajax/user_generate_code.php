<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the database functions
require_once("../lib/function.php");

// Check if user is logged in
if (!isset($_SESSION['email'])) {
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
$user_email = $_SESSION['email'];

// Connect to database
$db = new db_functions();
$conn = $db->connect();

try {
    // First, check if the verification_code column exists
    $check_column_query = "SHOW COLUMNS FROM `work_posts` LIKE 'verification_code'";
    $column_result = $conn->query($check_column_query);
    
    if ($column_result->num_rows === 0) {
        // Column doesn't exist, try to add it
        $alter_query = "ALTER TABLE `work_posts` ADD COLUMN `verification_code` VARCHAR(6) NULL DEFAULT NULL COMMENT 'Verification code for job completion'";
        if (!$conn->query($alter_query)) {
            throw new Exception("Unable to add verification_code column. Please run database migrations.");
        }
    }
    
    // Check if the post exists and belongs to this user
    $check_query = "SELECT * FROM work_posts WHERE id = ? AND email = ? AND status = 'pending'";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("is", $post_id, $user_email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Post not found or not in pending status'
        ]);
        exit();
    }
    
    // Generate a random 6-digit code
    $verification_code = sprintf("%06d", mt_rand(0, 999999));
    
    // Save the verification code to the database
    $update_query = "UPDATE work_posts SET verification_code = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("si", $verification_code, $post_id);
    $success = $update_stmt->execute();
    
    if ($success) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Verification code generated successfully',
            'code' => $verification_code
        ]);
    } else {
        throw new Exception("Failed to save verification code");
    }
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
