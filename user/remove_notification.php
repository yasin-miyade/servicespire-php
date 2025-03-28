<?php
session_start();
require_once("../lib/function.php");

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Check if notification_id is provided
if (!isset($_POST['notification_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing notification ID']);
    exit;
}

$notification_id = $_POST['notification_id'];
$user_email = $_SESSION['email'];
$db = new db_functions();
$conn = $db->connect();

// Log the operation for debugging
error_log("Removing notification: ID=$notification_id, User=$user_email");

// Check if notification exists in notifications table
$check_table = $conn->query("SHOW TABLES LIKE 'notifications'");
if ($check_table->num_rows > 0) {
    // We have a notifications table, remove from there
    $query = "DELETE FROM notifications WHERE id = ? AND user_email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $notification_id, $user_email);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        error_log("Deleted from notifications table. Affected rows: " . $stmt->affected_rows);
        echo json_encode(['status' => 'success', 'message' => 'Notification removed']);
    } else {
        error_log("No rows deleted from notifications table, trying work_posts");
        // Could be a work post notification
        $query = "UPDATE work_posts SET notification = NULL, status = 'open' WHERE id = ? AND email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $notification_id, $user_email);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            error_log("Updated work_posts table. Affected rows: " . $stmt->affected_rows);
            echo json_encode(['status' => 'success', 'message' => 'Notification cleared']);
        } else {
            error_log("Failed to delete notification. No matching records found.");
            echo json_encode(['status' => 'error', 'message' => 'Notification not found']);
        }
    }
} else {
    // No notifications table, so clear from work_posts
    $query = "UPDATE work_posts SET notification = NULL, status = 'open' WHERE id = ? AND email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $notification_id, $user_email);
    $stmt->execute();
    
    error_log("Updated work_posts table. Affected rows: " . $stmt->affected_rows);
    
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Notification cleared']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Notification not found']);
    }
}

$stmt->close();
$conn->close();
?>
