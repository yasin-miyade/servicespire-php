<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable error display, use JSON responses instead

require_once("../lib/function.php");
$db = new db_functions();

// Check if helper is logged in using existing session
$helper_email = isset($_SESSION['helper_email']) ? $_SESSION['helper_email'] : "";
if (empty($helper_email)) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Check if post ID is provided
if (!isset($_POST['help_post_id'])) {
    echo json_encode(['success' => false, 'message' => 'No post ID provided']);
    exit;
}

$post_id = $_POST['help_post_id'];

try {
    // Get post details using direct query instead of getWorkPost
    $con = $db->connect();
    $query = "SELECT * FROM work_posts WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();

    if (!$post) {
        throw new Exception('Post not found');
    }

    if ($post['status'] === 'completed' || $post['status'] === 'pending_approval' || $post['status'] === 'accepted') {
        throw new Exception('This post is no longer available');
    }

    // Assign helper and set status
    if ($db->assignHelper($post_id, $helper_email)) {
        // Send notification
        $db->sendNotification($post_id, "A helper has shown interest in your work post.");
        
        // Get updated pending requests count
        $pending_requests = $db->getPendingRequests($helper_email);
        
        echo json_encode([
            'success' => true,
            'message' => 'Request sent successfully',
            'pending_requests' => $pending_requests
        ]);
    } else {
        throw new Exception('Failed to assign helper');
    }
} catch (Exception $e) {
    error_log("Help request error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to process request: ' . $e->getMessage()
    ]);
}
?>
