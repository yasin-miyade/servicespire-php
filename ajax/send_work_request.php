<?php
require_once("../lib/function.php");

session_start();

if (!isset($_SESSION['helper_email'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'You must be logged in to send a work request.'
    ]);
    exit;
}

$db = new db_functions();
$conn = $db->connect();

// Check for active penalty
$penalty_check = "SELECT penalty_until, last_penalty_reason 
                 FROM helper_profiles 
                 WHERE email = ? AND penalty_until >= CURRENT_DATE";
$stmt = $conn->prepare($penalty_check);
$stmt->bind_param("s", $_SESSION['helper_email']);
$stmt->execute();
$penalty_result = $stmt->get_result();

if ($penalty_result->num_rows > 0) {
    $penalty = $penalty_result->fetch_assoc();
    $days_left = ceil((strtotime($penalty['penalty_until']) - time()) / (60 * 60 * 24));
    echo json_encode([
        'status' => 'error',
        'message' => "You cannot send requests for {$days_left} more days due to: {$penalty['last_penalty_reason']}"
    ]);
    exit;
}

// Continue with existing request sending logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $work_id = $_POST['work_id'] ?? null;

    if (!$work_id) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid work ID.'
        ]);
        exit;
    }

    $helper_email = $_SESSION['helper_email'];

    // Check if the work post exists and is available
    $query = "SELECT * FROM work_posts WHERE id = ? AND status = 'available'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $work_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'The requested work is no longer available.'
        ]);
        exit;
    }

    // Send work request
    $insert_query = "INSERT INTO work_requests (work_id, helper_email, status, created_at) VALUES (?, ?, 'pending', NOW())";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("is", $work_id, $helper_email);

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Work request sent successfully.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to send work request. Please try again later.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}
?>