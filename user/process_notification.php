<?php
session_start();
require_once("../lib/function.php");

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Check if required parameters are present
if (!isset($_POST['notification_id']) || !isset($_POST['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit;
}

$notification_id = $_POST['notification_id'];
$action = $_POST['action'];
$user_email = $_SESSION['email'];

$db = new db_functions();
$conn = $db->connect();

// First fetch the notification to get helper_id and post_id
$query = "SELECT helper_id, post_id FROM notifications WHERE id = ? AND user_email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $notification_id, $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Notification not found']);
    exit;
}

$notification = $result->fetch_assoc();
$helper_id = $notification['helper_id'];
$post_id = $notification['post_id'];

// Begin transaction
$conn->begin_transaction();

try {
    // Update notification status
    $notificationStatus = ($action === 'accept') ? 'accepted' : 'declined';
    $updateNotif = "UPDATE notifications SET status = ? WHERE id = ?";
    $stmtNotif = $conn->prepare($updateNotif);
    $stmtNotif->bind_param("si", $notificationStatus, $notification_id);
    $stmtNotif->execute();
    
    // If accepted, update work post as well
    if ($action === 'accept') {
        $updatePost = "UPDATE work_posts SET status = 'assigned', assigned_helper_id = ? WHERE id = ?";
        $stmtPost = $conn->prepare($updatePost);
        $stmtPost->bind_param("ii", $helper_id, $post_id);
        $stmtPost->execute();
    }
    
    // Commit the transaction
    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Action processed successfully']);
    
} catch (Exception $e) {
    // Roll back the transaction in case of error
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>
