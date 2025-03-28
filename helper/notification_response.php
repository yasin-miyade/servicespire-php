<?php
session_start();
header('Content-Type: application/json');

// Check helper authentication
if (!isset($_SESSION['helper_email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Helper not authenticated']);
    exit;
}

// Verify required parameters
if (!isset($_POST['post_id']) || !isset($_POST['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit;
}

$post_id = intval($_POST['post_id']);
$action = $_POST['action'];
$helper_email = $_SESSION['helper_email'];

require_once('../lib/function.php');
$db = new db_functions();
$conn = $db->connect();

try {
    // Check if the helper is eligible to respond to this post
    $check_query = "SELECT wp.*, s.id as user_id, s.first_name, s.last_name, s.email as user_email 
                   FROM work_posts wp 
                   LEFT JOIN sign_up s ON wp.email = s.email
                   WHERE wp.id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $post_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Post not found']);
        exit;
    }
    
    $post = $result->fetch_assoc();
    $user_email = $post['user_email'];
    
    // Process the action
    if ($action === 'accept') {
        // Update the post as the assigned helper
        $update_query = "UPDATE work_posts SET assigned_helper_email = ?, status = 'pending' WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $helper_email, $post_id);
        
        if ($update_stmt->execute()) {
            // Check if notifications table exists
            $table_check = $conn->query("SHOW TABLES LIKE 'notifications'");
            if ($table_check->num_rows === 0) {
                // Create notifications table if it doesn't exist
                $create_table = "CREATE TABLE notifications (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    recipient_email VARCHAR(255) NULL,
                    message TEXT NOT NULL,
                    post_id INT NULL,
                    type VARCHAR(50) DEFAULT 'general',
                    is_read TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                $conn->query($create_table);
            }
            
            // Create notification for the user
            $message = "A helper has accepted your work request: " . $post['work'];
            
            // Send notification to user
            $notify_query = "INSERT INTO notifications (recipient_email, message, type, post_id, created_at) 
                           VALUES (?, ?, 'helper_accept', ?, NOW())";
            $notify_stmt = $conn->prepare($notify_query);
            $notify_stmt->bind_param("ssi", $user_email, $message, $post_id);
            $notify_stmt->execute();
            
            echo json_encode(['status' => 'success', 'message' => 'Work request accepted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update post status']);
        }
    } else if ($action === 'decline') {
        // Mark notification as read if notification_id is provided
        if (isset($_POST['notification_id'])) {
            $notification_id = intval($_POST['notification_id']);
            $mark_read_query = "UPDATE notifications SET is_read = 1 WHERE id = ?";
            $mark_read_stmt = $conn->prepare($mark_read_query);
            $mark_read_stmt->bind_param("i", $notification_id);
            $mark_read_stmt->execute();
        }
        
        echo json_encode(['status' => 'success', 'message' => 'Notification acknowledged']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
?>
