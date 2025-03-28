<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database functions
require_once("../lib/function.php");

// Set default response
$response = [
    'status' => 'error',
    'message' => 'Unknown error occurred'
];

// Check if user is logged in
if (!isset($_SESSION['user_email']) && !isset($_SESSION['helper_email'])) {
    $response['message'] = 'You must be logged in to perform this action';
    echo json_encode($response);
    exit();
}

// Get the current user's email
$user_email = $_SESSION['user_email'] ?? $_SESSION['helper_email'];

// Check if notification_id is provided
if (!isset($_POST['notification_id']) || empty($_POST['notification_id'])) {
    $response['message'] = 'Notification ID is required';
    echo json_encode($response);
    exit();
}

$notification_id = $_POST['notification_id'];

// Initialize database connection
$db = new db_functions();
$conn = $db->connect();

try {
    // Get notification details to check ownership and get post_id
    $query = "SELECT * FROM notifications WHERE id = ? AND user_email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $notification_id, $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        throw new Exception("Notification not found or does not belong to current user");
    }
    
    $notification = $result->fetch_assoc();
    
    // Mark notification as read
    $update_query = "UPDATE notifications SET is_read = 1 WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $notification_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update notification status");
    }
    
    $response['status'] = 'success';
    $response['message'] = 'Notification marked as read';
    
    // If it's related to a post, include the redirect URL
    if (!empty($notification['post_id'])) {
        if (isset($_SESSION['user_email'])) {
            $response['redirect_url'] = "../job_details.php?id=" . $notification['post_id'];
        } else {
            $response['redirect_url'] = "job_details.php?id=" . $notification['post_id'];
        }
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    $conn->close();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
