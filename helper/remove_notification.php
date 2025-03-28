<?php
session_start();
require_once("../lib/function.php");

// Check if helper is logged in
if(!isset($_SESSION['helper_email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$helper_email = $_SESSION['helper_email'];
$db = new db_functions();
$conn = $db->connect();

// Get notification ID
$notification_id = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : 0;
$is_work_post = isset($_POST['is_work_post']) ? filter_var($_POST['is_work_post'], FILTER_VALIDATE_BOOLEAN) : false;

if(!$notification_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid notification ID']);
    exit;
}

try {
    if ($is_work_post) {
        // If it's a work post notification, just mark it as seen
        $updateQuery = "UPDATE work_posts SET seen_by_helper = 1 WHERE id = ? AND assigned_helper_email = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("is", $notification_id, $helper_email);
        $updateStmt->execute();
        echo json_encode(['status' => 'success', 'message' => 'Work post notification hidden']);
    } else {
        // If it's a regular notification, we can delete it from the notifications table
        // Check if the notifications table exists first
        $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'");
        if ($tableCheck->num_rows > 0) {
            // Check if the recipient_email column exists
            $columnCheck = $conn->query("SHOW COLUMNS FROM notifications LIKE 'recipient_email'");
            if ($columnCheck->num_rows > 0) {
                $deleteQuery = "DELETE FROM notifications WHERE id = ? AND recipient_email = ?";
            } else {
                // Fallback to just deleting by ID if we can't find the right column
                $deleteQuery = "DELETE FROM notifications WHERE id = ?";
            }
            
            $deleteStmt = $conn->prepare($deleteQuery);
            
            if ($columnCheck->num_rows > 0) {
                $deleteStmt->bind_param("is", $notification_id, $helper_email);
            } else {
                $deleteStmt->bind_param("i", $notification_id);
            }
            
            $deleteStmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'Notification removed']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Notifications table not found']);
        }
    }
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
