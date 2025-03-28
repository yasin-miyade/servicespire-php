<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable display errors in production

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set content type to JSON
header('Content-Type: application/json');

require_once("../lib/function.php");
$db = new db_functions();

// Get helper's email from session
$helper_email = isset($_SESSION['helper_email']) ? $_SESSION['helper_email'] : "";

// If not logged in, return error
if (empty($helper_email)) {
    echo json_encode([
        'success' => false,
        'message' => 'Not logged in'
    ]);
    exit;
}

// Check if post ID is provided
if (!isset($_POST['post_id']) || empty($_POST['post_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Post ID is required'
    ]);
    exit;
}

$post_id = $_POST['post_id'];

try {
    // Log the action
    error_log("Cancelling help request for post ID: {$post_id} by helper: {$helper_email}");
    
    // Remove helper assignment (using both functions)
    $db->removePendingRequests($post_id); // Set status to cancelled
    $db->unassignHelper($post_id);         // Remove helper email assignment
    
    // Send notification about cancellation
    $db->sendNotification($post_id, "A helper has withdrawn their interest in your work post.");
    
    // Get updated stats to return to client
    $pending_requests = $db->getPendingRequests($helper_email);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Help request cancelled successfully',
        'pending_requests' => $pending_requests
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log("Error cancelling help request: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while cancelling the request',
        'error' => $e->getMessage()
    ]);
}
?>
