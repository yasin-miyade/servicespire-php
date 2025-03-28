<?php
session_start();
require_once("../lib/function.php");

// Check if helper is logged in
if (!isset($_SESSION['helper_email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$helper_email = $_SESSION['helper_email'];
$db = new db_functions();
$conn = $db->connect();

try {
    // Check for newly accepted requests
    $query = "SELECT id, work FROM work_posts 
              WHERE assigned_helper_email = ? AND status = 'pending' 
              ORDER BY updated_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $helper_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $accepted_requests = [];
    while ($row = $result->fetch_assoc()) {
        $accepted_requests[] = [
            'id' => $row['id'],
            'work' => $row['work']
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'has_accepted' => count($accepted_requests) > 0,
        'accepted_requests' => $accepted_requests
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error checking accepted requests: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
