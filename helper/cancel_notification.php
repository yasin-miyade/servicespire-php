<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session
session_start();

// Include database functions
require_once("../lib/function.php");
require_once("notification.php");

$db = new db_functions();
$notification = new NotificationHelper($db);

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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_id'])) {
    $post_id = intval($_POST['post_id']);
    
    try {
        // First check if this helper is assigned to the post
        $query = "SELECT assigned_helper_email, name FROM work_posts WHERE id = ?";
        $stmt = $db->connect()->prepare($query);
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $post_data = $result->fetch_assoc();
        
        if (!$post_data || $post_data['assigned_helper_email'] !== $helper_email) {
            throw new Exception("You are not assigned to this post");
        }
        
        // Get helper's name for the notification
        $helper_query = "SELECT first_name, last_name FROM helper_sign_up WHERE email = ?";
        $helper_stmt = $db->connect()->prepare($helper_query);
        $helper_stmt->bind_param("s", $helper_email);
        $helper_stmt->execute();
        $helper_result = $helper_stmt->get_result();
        $helper_data = $helper_result->fetch_assoc();
        $helper_name = $helper_data ? $helper_data['first_name'] . ' ' . $helper_data['last_name'] : 'A helper';
        
        // Remove helper assignment
        $db->unassignHelper($post_id);
        
        // Also update status back to open
        $query = "UPDATE work_posts SET status = 'open' WHERE id = ?";
        $stmt = $db->connect()->prepare($query);
        $stmt->bind_param("i", $post_id);
        $success = $stmt->execute();
        
        // Send notification to the user
        $message = "$helper_name has cancelled their help request for your work post.";
        $notification->sendWorkPostNotification($post_id, $message, 'warning');
        
        // If a specific notification ID was provided, delete that notification
        if (isset($_POST['notification_id']) && !empty($_POST['notification_id'])) {
            $notification_id = intval($_POST['notification_id']);
            
            // Check if notifications table exists
            $check_table = $db->connect()->query("SHOW TABLES LIKE 'notifications'");
            if ($check_table->num_rows > 0) {
                $delete_query = "DELETE FROM notifications WHERE id = ?";
                $delete_stmt = $db->connect()->prepare($delete_query);
                $delete_stmt->bind_param("i", $notification_id);
                $delete_stmt->execute();
            }
        }
        
        // Return success response
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Request cancelled successfully'
        ]);
    } catch (Exception $e) {
        // Log and return error
        error_log("Error in cancel_notification: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to cancel request: ' . $e->getMessage()
        ]);
    }
} else {
    // Invalid request
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);
}
?>
