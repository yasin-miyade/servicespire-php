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

// Initialize database connection
$db = new db_functions();
$conn = $db->connect();

try {
    // Mark all notifications as read for this user
    $update_query = "UPDATE notifications SET is_read = 1 WHERE user_email = ? AND is_read = 0";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("s", $user_email);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update notification status");
    }
    
    $affected_rows = $conn->affected_rows;
    
    $response['status'] = 'success';
    $response['message'] = 'All notifications marked as read';
    $response['count'] = $affected_rows;
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    $conn->close();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
