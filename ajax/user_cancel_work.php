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
            'message' => 'Post not found or not authorized to cancel'
        ]);
        exit();
    }
    
    $post_data = $result->fetch_assoc();
    $helper_email = $post_data['assigned_helper_email'];
    
    // Update the post to remove helper assignment and set status back to open
    // Also clear the verification code when cancelling
    $update_query = "UPDATE work_posts SET assigned_helper_email = NULL, status = 'open', verification_code = NULL WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $post_id);
    $success = $update_stmt->execute();
    
    if ($success) {
        // If notification system exists, create a notification for the helper
        // ...code for notifications if needed...
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Work has been canceled successfully'
        ]);
    } else {
        throw new Exception("Failed to cancel work");
    }
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
