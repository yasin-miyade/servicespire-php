<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

// Check if post_id was provided
if (!isset($_POST['post_id']) || empty($_POST['post_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No post ID provided']);
    exit;
}

require_once('../lib/function.php');
$db = new db_functions();
$conn = $db->connect();

// Get the post ID from the request
$post_id = intval($_POST['post_id']);
$user_email = $_SESSION['email'];

// First, verify that this post belongs to the logged-in user
$verify_query = "SELECT id FROM work_posts WHERE id = ? AND email = ? AND status = 'pending'";
$verify_stmt = $conn->prepare($verify_query);
$verify_stmt->bind_param("is", $post_id, $user_email);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Post not found or not authorized to cancel']);
    exit;
}

// Update the post to reset the status and unassign the helper
$update_query = "UPDATE work_posts SET status = 'open', assigned_helper_email = NULL WHERE id = ?";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param("i", $post_id);

if ($update_stmt->execute()) {
    // Get helper info to send notification
    $helper_query = "SELECT assigned_helper_email FROM work_posts WHERE id = ?";
    $helper_stmt = $conn->prepare($helper_query);
    $helper_stmt->bind_param("i", $post_id);
    $helper_stmt->execute();
    $helper_result = $helper_stmt->get_result()->fetch_assoc();
    
    // Optional: Add notification for the helper that the work was canceled
    // This would depend on your notification system
    
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to cancel work']);
}
