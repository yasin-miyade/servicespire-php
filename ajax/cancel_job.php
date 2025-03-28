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

// Check if helper is logged in
if (!isset($_SESSION['helper_email'])) {
    $response['message'] = 'You must be logged in to perform this action';
    echo json_encode($response);
    exit();
}

// Check if post_id is provided
if (!isset($_POST['post_id']) || empty($_POST['post_id'])) {
    $response['message'] = 'Job ID is required';
    echo json_encode($response);
    exit();
}

// Check if message is provided
if (!isset($_POST['message']) || empty($_POST['message'])) {
    $response['message'] = 'Cancellation message is required';
    echo json_encode($response);
    exit();
}

$post_id = $_POST['post_id'];
$helper_email = $_SESSION['helper_email'];
$message = $_POST['message'];

// Initialize database connection
$db = new db_functions();
$conn = $db->connect();

try {
    // Begin transaction
    $conn->begin_transaction();
    
    // Check if messages table exists, create it if it doesn't
    $table_check = $conn->query("SHOW TABLES LIKE 'messages'");
    if ($table_check->num_rows == 0) {
        // Create messages table
        $create_table_query = "CREATE TABLE `messages` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `sender_email` varchar(255) NOT NULL,
            `recipient_email` varchar(255) NOT NULL,
            `message` text NOT NULL,
            `post_id` int(11) DEFAULT NULL,
            `is_read` tinyint(1) DEFAULT 0,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `sender_email` (`sender_email`),
            KEY `recipient_email` (`recipient_email`),
            KEY `post_id` (`post_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if (!$conn->query($create_table_query)) {
            throw new Exception("Failed to create messages table: " . $conn->error);
        }
    }
    
    // Check if notifications table exists, create it if it doesn't
    $table_check = $conn->query("SHOW TABLES LIKE 'notifications'");
    if ($table_check->num_rows == 0) {
        // Create notifications table
        $create_table_query = "CREATE TABLE `notifications` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_email` varchar(255) NOT NULL,
            `message` text NOT NULL,
            `post_id` int(11) DEFAULT NULL,
            `is_read` tinyint(1) DEFAULT 0,
            `type` varchar(50) DEFAULT 'info',
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `user_email` (`user_email`),
            KEY `post_id` (`post_id`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if (!$conn->query($create_table_query)) {
            throw new Exception("Failed to create notifications table: " . $conn->error);
        }
    }
    
    // Verify the job belongs to this helper - debug info
    error_log("Checking job assignment for post_id: $post_id and helper_email: $helper_email");
    
    // First, just get the work_post to see if it exists
    $check_job_query = "SELECT id, email, status, assigned_helper_email FROM work_posts WHERE id = ?";
    $check_job_stmt = $conn->prepare($check_job_query);
    $check_job_stmt->bind_param("i", $post_id);
    $check_job_stmt->execute();
    $job_result = $check_job_stmt->get_result();
    
    if ($job_result->num_rows == 0) {
        throw new Exception("Job with ID $post_id not found in database");
    }
    
    $job_info = $job_result->fetch_assoc();
    if ($job_info['assigned_helper_email'] !== $helper_email) {
        throw new Exception("Job is assigned to '{$job_info['assigned_helper_email']}', not to you ($helper_email)");
    }
    
    if ($job_info['status'] !== 'pending') {
        throw new Exception("Job is not in pending status (current status: {$job_info['status']})");
    }
    
    // Now we know the job exists and is assigned to this helper
    $client_email = $job_info['email']; // The job poster's email
    
    // Update job status to 'open' and remove helper assignment
    $update_query = "UPDATE work_posts SET status = 'open', assigned_helper_email = NULL WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $post_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update job status: " . $conn->error);
    }
    
    // Get helper's name - first try sign_up table (more likely to have helper data)
    $name_query = "SELECT first_name, last_name FROM sign_up WHERE email = ?";
    $name_stmt = $conn->prepare($name_query);
    $name_stmt->bind_param("s", $helper_email);
    $name_stmt->execute();
    $name_result = $name_stmt->get_result();
    
    if ($name_result->num_rows > 0) {
        $helper_data = $name_result->fetch_assoc();
        $helper_name = $helper_data['first_name'] . ' ' . $helper_data['last_name'];
    } else {
        // Fall back to helpers table if not found in sign_up
        $alt_name_query = "SELECT first_name, last_name FROM helpers WHERE email = ?";
        $alt_name_stmt = $conn->prepare($alt_name_query);
        $alt_name_stmt->bind_param("s", $helper_email);
        $alt_name_stmt->execute();
        $alt_name_result = $alt_name_stmt->get_result();
        
        if ($alt_name_result->num_rows > 0) {
            $helper_data = $alt_name_result->fetch_assoc();
            $helper_name = $helper_data['first_name'] . ' ' . $helper_data['last_name'];
        } else {
            $helper_name = "Service Provider";
        }
    }
    
    // Format the message - make it more structured and readable for notifications
    $full_message = "Job cancellation notice from {$helper_name}:\n\n{$message}\n\nThis job has been returned to the available jobs list.";
    
    // Send message to the user
    $insert_message_query = "INSERT INTO messages (sender_email, recipient_email, message, post_id, created_at) 
                           VALUES (?, ?, ?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_message_query);
    $insert_stmt->bind_param("ssis", $helper_email, $client_email, $full_message, $post_id);
    
    if (!$insert_stmt->execute()) {
        throw new Exception("Failed to send cancellation message: " . $conn->error);
    }
    
    // Debugging: check the actual table structure
    $check_columns = $conn->query("SHOW COLUMNS FROM notifications");
    $columns = [];
    if ($check_columns) {
        while($col = $check_columns->fetch_assoc()) {
            $columns[] = $col['Field'];
        }
        error_log("Notification columns: " . implode(", ", $columns));
    }
    
    // Fix: Get job title/name for the notification content
    $job_title = "Job Cancelled";
    $work_query = "SELECT work FROM work_posts WHERE id = ?";
    $work_stmt = $conn->prepare($work_query);
    $work_stmt->bind_param("i", $post_id);
    $work_stmt->execute();
    $work_result = $work_stmt->get_result();
    if ($work_result->num_rows > 0) {
        $work_row = $work_result->fetch_assoc();
        if (!empty($work_row['work'])) {
            $job_title .= ": " . substr($work_row['work'], 0, 40) . (strlen($work_row['work']) > 40 ? '...' : '');
        }
    }
    
    // Format notification content to highlight the cancellation message
    $notification_content = "{$job_title}\n\n";
    $notification_content .= "Message from {$helper_name}:\n";
    $notification_content .= "\"{$message}\"\n\n";
    $notification_content .= "This job has been returned to the available jobs list.";
    
    // Create notification with the available columns
    $hasType = in_array('type', $columns);
    $hasContent = in_array('content', $columns);
    $hasMessage = in_array('message', $columns);
    $hasNotificationText = in_array('notification_text', $columns);
    
    if ($hasContent) {
        // Content field exists
        if ($hasType) {
            $insert_notification_query = "INSERT INTO notifications (user_email, content, post_id, type, created_at) 
                                     VALUES (?, ?, ?, 'job_cancelled', NOW())";
        } else {
            $insert_notification_query = "INSERT INTO notifications (user_email, content, post_id, created_at) 
                                     VALUES (?, ?, ?, NOW())";
        }
        $insert_notif_stmt = $conn->prepare($insert_notification_query);
        $insert_notif_stmt->bind_param("ssi", $client_email, $notification_content, $post_id);
    } 
    else if ($hasMessage) {
        // Message field exists
        if ($hasType) {
            $insert_notification_query = "INSERT INTO notifications (user_email, message, post_id, type, created_at) 
                                     VALUES (?, ?, ?, 'job_cancelled', NOW())";
        } else {
            $insert_notification_query = "INSERT INTO notifications (user_email, message, post_id, created_at) 
                                     VALUES (?, ?, ?, NOW())";
        }
        $insert_notif_stmt = $conn->prepare($insert_notification_query);
        $insert_notif_stmt->bind_param("ssi", $client_email, $notification_content, $post_id);
    }
    else if ($hasNotificationText) {
        // notification_text field exists
        if ($hasType) {
            $insert_notification_query = "INSERT INTO notifications (user_email, notification_text, post_id, type, created_at) 
                                     VALUES (?, ?, ?, 'job_cancelled', NOW())";
        } else {
            $insert_notification_query = "INSERT INTO notifications (user_email, notification_text, post_id, created_at) 
                                     VALUES (?, ?, ?, NOW())";
        }
        $insert_notif_stmt = $conn->prepare($insert_notification_query);
        $insert_notif_stmt->bind_param("ssi", $client_email, $notification_content, $post_id);
    }
    else {
        // No message field exists, use minimal notification
        if ($hasType) {
            $insert_notification_query = "INSERT INTO notifications (user_email, post_id, type, created_at) 
                                     VALUES (?, ?, 'job_cancelled', NOW())";
        } else {
            $insert_notification_query = "INSERT INTO notifications (user_email, post_id, created_at) 
                                     VALUES (?, ?, NOW())";
        }
        $insert_notif_stmt = $conn->prepare($insert_notification_query);
        $insert_notif_stmt->bind_param("si", $client_email, $post_id);
    }
    
    if (!$insert_notif_stmt->execute()) {
        throw new Exception("Failed to create notification: " . $conn->error);
    }
    
    // Commit transaction
    $conn->commit();
    
    $response['status'] = 'success';
    $response['message'] = 'Job cancelled successfully';
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $response['message'] = $e->getMessage();
    // Log the error for server-side debugging
    error_log("Job cancellation error: " . $e->getMessage());
} finally {
    // Close the connection
    $conn->close();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
