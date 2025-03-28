<?php
session_start();
require_once("../lib/function.php");

// Check if helper is logged in
if (!isset($_SESSION['helper_email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Check if required parameters exist
if (!isset($_POST['post_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing post_id parameter']);
    exit;
}

$post_id = $_POST['post_id'];
$helper_email = $_SESSION['helper_email'];

$db = new db_functions();
$conn = $db->connect();

// Verify if this work post is assigned to the current helper
$verify_query = "SELECT id FROM work_posts WHERE id = ? AND assigned_helper_email = ? AND status = 'pending'";
$verify_stmt = $conn->prepare($verify_query);
$verify_stmt->bind_param("is", $post_id, $helper_email);
$verify_stmt->execute();
$result = $verify_stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Work post not found or not assigned to you']);
    exit;
}

// Begin transaction
$conn->begin_transaction();

try {
    // Update post status to 'completed' with current timestamp
    $current_time = date('Y-m-d H:i:s');
    $update_post = "UPDATE work_posts SET status = 'completed', completed_at = ? WHERE id = ?";
    $stmt_post = $conn->prepare($update_post);
    $stmt_post->bind_param("si", $current_time, $post_id);
    $stmt_post->execute();
    
    // Get user email to send notification
    $user_query = "SELECT email FROM work_posts WHERE id = ?";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param("i", $post_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user_data = $user_result->fetch_assoc();
    
    if ($user_data) {
        // Set notification for the user
        $notification = "Your job has been marked as completed by the helper.";
        $notif_query = "UPDATE work_posts SET notification = ? WHERE id = ?";
        $notif_stmt = $conn->prepare($notif_query);
        $notif_stmt->bind_param("si", $notification, $post_id);
        $notif_stmt->execute();
    }
    
    // Commit the transaction
    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Job marked as completed successfully']);
    
} catch (Exception $e) {
    // Roll back the transaction in case of error
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

// Close connections
$verify_stmt->close();
$conn->close();
?>
