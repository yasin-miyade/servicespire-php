<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the database functions
require_once("../lib/function.php");

// Check if helper is logged in
if (!isset($_SESSION['helper_email'])) {
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
$helper_email = $_SESSION['helper_email'];
$message = isset($_POST['message']) ? $_POST['message'] : 'Job cancelled by helper.';

// Connect to database
$db = new db_functions();
$conn = $db->connect();

try {
    // Check if the post exists and is assigned to this helper
    $check_query = "SELECT * FROM work_posts WHERE id = ? AND assigned_helper_email = ? AND status = 'pending'";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("is", $post_id, $helper_email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Post not found or not assigned to you, or not in pending status'
        ]);
        exit();
    }
    
    // Get post data including client email to notify them
    $post_data = $result->fetch_assoc();
    $client_email = $post_data['email'];
    
    // Update the post to remove helper assignment and set status back to open
    // Also clear any existing verification code
    $update_query = "UPDATE work_posts SET assigned_helper_email = NULL, status = 'open', notification = ?, verification_code = NULL WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    
    // Create notification message with helper's name
    $helper_name = isset($_SESSION['helper_first_name']) ? $_SESSION['helper_first_name'] : 'Helper';
    $notification_message = "Your job was cancelled by " . $helper_name . ". Reason: " . $message;
    
    $update_stmt->bind_param("si", $notification_message, $post_id);
    $success = $update_stmt->execute();
    
    if ($success) {
        // Check if notifications table exists and get its structure
        $check_table = $conn->query("SHOW TABLES LIKE 'notifications'");
        if ($check_table->num_rows > 0) {
            // First check the column structure of notifications table
            $check_columns = $conn->query("SHOW COLUMNS FROM notifications");
            $columns = [];
            while($column = $check_columns->fetch_assoc()) {
                $columns[] = $column['Field'];
            }
            
            // Determine which insert query to use based on available columns
            if (in_array('content', $columns)) {
                // If table has 'content' column instead of 'message'
                $notify_query = "INSERT INTO notifications (user_email, content, post_id, created_at) 
                                VALUES (?, ?, ?, NOW())";
            } else if (in_array('notification', $columns)) {
                // If table has 'notification' column
                $notify_query = "INSERT INTO notifications (user_email, notification, post_id, created_at) 
                                VALUES (?, ?, ?, NOW())";
            } else {
                // Try a simple structure as fallback
                $notify_query = "INSERT INTO notifications (user_email, post_id, created_at) 
                                VALUES (?, ?, NOW())";
                $notify_stmt = $conn->prepare($notify_query);
                $notify_stmt->bind_param("si", $client_email, $post_id);
                $notify_stmt->execute();
                
                // Exit notification creation - we've done our best with the available columns
                goto notification_complete;
            }
            
            // Execute the notification insert with message field
            $notify_stmt = $conn->prepare($notify_query);
            $notify_stmt->bind_param("ssi", $client_email, $notification_message, $post_id);
            $notify_stmt->execute();
        }
        
        notification_complete:
        
        // Add 7 day penalty to helper - with error handling
        try {
            $penalty_until = date('Y-m-d', strtotime('+7 days'));
            
            // First check if helper_profiles table exists
            $table_exists = $conn->query("SHOW TABLES LIKE 'helper_profiles'");
            if ($table_exists->num_rows == 0) {
                // Redirect to create table
                error_log("helper_profiles table doesn't exist - attempting to create");
                include_once("../migrations/add_helper_profiles.php");
            }
            
            // Try to insert/update the helper profile
            $penalty_sql = "INSERT INTO helper_profiles (email, penalty_until, last_penalty_reason) 
                           VALUES (?, ?, 'Cancelled job without completion')
                           ON DUPLICATE KEY UPDATE 
                           penalty_until = ?, 
                           last_penalty_reason = 'Cancelled job without completion'";
            $penalty_stmt = $conn->prepare($penalty_sql);
            $penalty_stmt->bind_param("sss", $_SESSION['helper_email'], $penalty_until, $penalty_until);
            $penalty_stmt->execute();
        } catch (Exception $e) {
            error_log("Failed to add penalty, but job was cancelled: " . $e->getMessage());
            // Continue with success response since the job was cancelled
        }

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Job cancelled successfully'
        ]);
    } else {
        throw new Exception("Failed to cancel job");
    }
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
