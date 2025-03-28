<?php
session_start();
header('Content-Type: application/json');

// Check user authentication
if (!isset($_SESSION['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

// Verify required parameters
if (!isset($_POST['post_id']) || !isset($_POST['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit;
}

$post_id = intval($_POST['post_id']);
$action = $_POST['action'];
$user_email = $_SESSION['email'];

require_once('../lib/function.php');
$db = new db_functions();
$conn = $db->connect();

// First verify that this post belongs to the current user
$verify_query = "SELECT id, assigned_helper_email FROM work_posts WHERE id = ? AND email = ?";
$verify_stmt = $conn->prepare($verify_query);
$verify_stmt->bind_param("is", $post_id, $user_email);
$verify_stmt->execute();
$result = $verify_stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Post not found or you are not authorized']);
    exit;
}

$post_data = $result->fetch_assoc();

// Process the action
if ($action === 'accept') {
    // Set the post status to pending only when accepting
    $update_query = "UPDATE work_posts SET status = 'pending' WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $post_id);
    
    if ($update_stmt->execute()) {
        // Also mark notification as read if notification_id provided
        if (isset($_POST['notification_id']) && !empty($_POST['notification_id'])) {
            $notification_id = intval($_POST['notification_id']);
            // If you have a notifications table, update it here
            // $mark_read_query = "UPDATE notifications SET read_status = 1 WHERE id = ?";
            // ...
        }
        
        echo json_encode(['status' => 'success', 'message' => 'Request accepted']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update post status']);
    }
} else if ($action === 'decline') {
    // When declining, unassign the helper but keep the post status as open
    $update_query = "UPDATE work_posts SET assigned_helper_email = NULL, status = 'open' WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $post_id);
    
    if ($update_stmt->execute()) {
        // Also mark notification as read if notification_id provided
        if (isset($_POST['notification_id']) && !empty($_POST['notification_id'])) {
            $notification_id = intval($_POST['notification_id']);
            // If you have a notifications table, update it here
            // $mark_read_query = "UPDATE notifications SET read_status = 1 WHERE id = ?";
            // ...
        }
        
        echo json_encode(['status' => 'success', 'message' => 'Request declined']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update post status']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
