<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session
session_start();

// Include database functions
require_once("../lib/function.php");

$db = new db_functions();

// Verify helper is logged in
$helper_email = isset($_SESSION['helper_email']) ? $_SESSION['helper_email'] : "";
if (empty($helper_email)) {
    // Return JSON error if not logged in
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Helper not logged in'
    ]);
    exit;
}

// Check if POST request and post_id is provided
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['help_post_id'])) {
    $post_id = intval($_POST['help_post_id']);
    
    try {
        // Get helper's name for the notification
        $helper_query = "SELECT first_name, last_name FROM helper_sign_up WHERE email = ?";
        $helper_stmt = $db->connect()->prepare($helper_query);
        $helper_stmt->bind_param("s", $helper_email);
        $helper_stmt->execute();
        $helper_result = $helper_stmt->get_result();
        $helper_data = $helper_result->fetch_assoc();
        $helper_name = $helper_data ? $helper_data['first_name'] . ' ' . $helper_data['last_name'] : 'A helper';
        
        // Get post owner before assigning helper
        $post_owner = $db->getUserByPostId($post_id);
        
        if (!$post_owner) {
            throw new Exception("Post not found");
        }
        
        // Try to assign helper to the post
        $success = $db->assignHelper($post_id, $helper_email);
        
        if ($success) {
            // Create notification for the user directly
            $user_email = $post_owner['email'];
            $conn = $db->connect();
            
            // Check if notifications table exists
            $check_table = $conn->query("SHOW TABLES LIKE 'notifications'");
            
            if ($check_table->num_rows > 0) {
                // Create notification
                $message = "$helper_name has requested to help with your work post.";
                $notify_query = "INSERT INTO notifications (user_email, message, post_id, status) 
                                VALUES (?, ?, ?, 'unread')";
                $notify_stmt = $conn->prepare($notify_query);
                $notify_stmt->bind_param("ssi", $user_email, $message, $post_id);
                $notify_stmt->execute();
            }
            
            // Return success response
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'message' => 'Request sent successfully!'
            ]);
            exit;
        } else {
            throw new Exception("Failed to assign helper to post");
        }
    } catch (Exception $e) {
        // Log and return error
        error_log("Error in help_them_notification: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to process request: ' . $e->getMessage()
        ]);
        exit;
    }
} else {
    // Invalid request
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);
    exit;
}
?>
