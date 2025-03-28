<?php
session_start();
header('Content-Type: application/json');

// Check if helper is logged in
if (!isset($_SESSION['helper_email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit();
}

// Check if required parameters are provided
if (!isset($_POST['latitude']) || !isset($_POST['longitude']) || !isset($_POST['post_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit();
}

require_once("../lib/function.php");
$db = new db_functions();
$conn = $db->connect();

$helper_email = $_SESSION['helper_email'];
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];
$post_id = $_POST['post_id'];
$address = $_POST['address'] ?? null;

// Verify the helper is assigned to this work post
$verify_query = "SELECT id FROM work_posts WHERE id = ? AND assigned_helper_email = ? AND status = 'pending'";
$verify_stmt = $conn->prepare($verify_query);
$verify_stmt->bind_param("is", $post_id, $helper_email);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Not authorized or work not pending']);
    exit();
}

// If no address provided, try to get it using reverse geocoding
if (empty($address)) {
    $address = "Current location"; // Default if geocoding fails
    
    // You could implement reverse geocoding here using Google Maps API
    // Example: Call a reverse geocoding API endpoint
}

// Insert or update the helper's location
$query = "INSERT INTO helper_locations (helper_email, post_id, latitude, longitude, address) 
          VALUES (?, ?, ?, ?, ?)
          ON DUPLICATE KEY UPDATE 
          latitude = VALUES(latitude),
          longitude = VALUES(longitude),
          address = VALUES(address),
          updated_at = CURRENT_TIMESTAMP";

$stmt = $conn->prepare($query);
$stmt->bind_param("sidds", $helper_email, $post_id, $latitude, $longitude, $address);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Location updated successfully'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to update location: ' . $conn->error
    ]);
}
?>
