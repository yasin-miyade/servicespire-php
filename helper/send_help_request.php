<?php
session_start();
require_once("../lib/function.php");

// Check if helper is logged in
if (!isset($_SESSION['helper_email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Check if required parameter exists
if (!isset($_POST['help_post_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameter']);
    exit;
}

$post_id = $_POST['help_post_id'];
$helper_email = $_SESSION['helper_email'];
$helper_id = $_SESSION['helper_id'] ?? null;

// If helper_id is not in session, try to get it
if (!$helper_id) {
    $db = new db_functions();
    $conn = $db->connect();
    $helper_query = "SELECT id FROM helper_sign_up WHERE email = ?";
    $helper_stmt = $conn->prepare($helper_query);
    $helper_stmt->bind_param("s", $helper_email);
    $helper_stmt->execute();
    $helper_result = $helper_stmt->get_result();
    if ($helper_result->num_rows > 0) {
        $helper = $helper_result->fetch_assoc();
        $helper_id = $helper['id'];
        $_SESSION['helper_id'] = $helper_id;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Helper profile not found']);
        exit;
    }
    $helper_stmt->close();
} else {
    $db = new db_functions();
    $conn = $db->connect();
}

// Begin transaction
$conn->begin_transaction();

try {
    // First check if the post exists and is available
    $check_query = "SELECT id, email, status FROM work_posts WHERE id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $post_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        throw new Exception('Work post not found');
    }
    
    $post = $check_result->fetch_assoc();
    if ($post['status'] !== 'open') {
        throw new Exception('This job is not available anymore');
    }
    
    $user_email = $post['email'];
    
    // Create an entry in help_requests table
    // Check if table exists first
    $check_table = $conn->query("SHOW TABLES LIKE 'help_requests'");
    if ($check_table->num_rows === 0) {
        // Create help_requests table if it doesn't exist
        $create_table = "CREATE TABLE help_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            helper_id INT NOT NULL,
            helper_email VARCHAR(255) NOT NULL,
            user_email VARCHAR(255) NOT NULL,
            status ENUM('pending', 'accepted', 'declined', 'cancelled') DEFAULT 'pending',
            message TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (post_id),
            INDEX (helper_id),
            INDEX (user_email),
            INDEX (status)
        )";
        $conn->query($create_table);
    }
    
    // Check if helper already has a pending request for this post
    $check_request = "SELECT id FROM help_requests WHERE post_id = ? AND helper_id = ? AND status = 'pending'";
    $request_stmt = $conn->prepare($check_request);
    $request_stmt->bind_param("ii", $post_id, $helper_id);
    $request_stmt->execute();
    $request_result = $request_stmt->get_result();
    
    if ($request_result->num_rows > 0) {
        throw new Exception('You already have a pending request for this job');
    }
    
    // Create the help request
    $helper_name = $_SESSION['helper_first_name'] . ' ' . $_SESSION['helper_last_name'];
    $message = "I'd like to help with your task.";
    
    $insert_request = "INSERT INTO help_requests (post_id, helper_id, helper_email, user_email, status, message) 
                       VALUES (?, ?, ?, ?, 'pending', ?)";
    $insert_stmt = $conn->prepare($insert_request);
    $insert_stmt->bind_param("iisss", $post_id, $helper_id, $helper_email, $user_email, $message);
    $insert_stmt->execute();
    $request_id = $insert_stmt->insert_id;
    
    // Create a notification for the user about this request
    $notification = "$helper_name has shown interest in helping with your task.";
    $notif_query = "UPDATE work_posts SET notification = ? WHERE id = ?";
    $notif_stmt = $conn->prepare($notif_query);
    $notif_stmt->bind_param("si", $notification, $post_id);
    $notif_stmt->execute();
    
    // Also add to notifications table if it exists
    $check_notif_table = $conn->query("SHOW TABLES LIKE 'notifications'");
    if ($check_notif_table->num_rows > 0) {
        $notif_insert = "INSERT INTO notifications (user_email, helper_id, post_id, notification, status, created_at)
                         VALUES (?, ?, ?, ?, 'pending', NOW())";
        $notif_insert_stmt = $conn->prepare($notif_insert);
        $notif_insert_stmt->bind_param("siis", $user_email, $helper_id, $post_id, $notification);
        $notif_insert_stmt->execute();
    }
    
    // Commit transaction
    $conn->commit();
    
    // Return success
    echo json_encode([
        'status' => 'success',
        'message' => 'Request sent to user successfully',
        'request_id' => $request_id
    ]);
    
} catch (Exception $e) {
    // Roll back transaction
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

// Close connection
$conn->close();
?>
