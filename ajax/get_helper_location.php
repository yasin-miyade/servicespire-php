<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit();
}

// Check if required parameters are provided
if (!isset($_POST['helper_email']) || !isset($_POST['post_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit();
}

require_once("../lib/function.php");
$db = new db_functions();
$conn = $db->connect();

$helper_email = $_POST['helper_email'];
$post_id = $_POST['post_id'];
$user_email = $_SESSION['email'];

// Verify that the user is the owner of the work post
$verify_query = "SELECT * FROM work_posts WHERE id = ? AND email = ? AND assigned_helper_email = ?";
$verify_stmt = $conn->prepare($verify_query);
$verify_stmt->bind_param("iss", $post_id, $user_email, $helper_email);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Not authorized']);
    exit();
}

// Get helper's current location from helper_locations table
$location_query = "SELECT latitude, longitude, address, updated_at FROM helper_locations 
                  WHERE helper_email = ? AND post_id = ? 
                  ORDER BY updated_at DESC LIMIT 1";
$location_stmt = $conn->prepare($location_query);
$location_stmt->bind_param("si", $helper_email, $post_id);
$location_stmt->execute();
$location_result = $location_stmt->get_result();

if ($location_result->num_rows > 0) {
    $location = $location_result->fetch_assoc();
    
    // Calculate how old the location data is
    $updated_at = strtotime($location['updated_at']);
    $current_time = time();
    $time_diff_minutes = ($current_time - $updated_at) / 60;
    
    // If location data is more than 30 minutes old, consider it stale
    if ($time_diff_minutes > 30) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Location data is outdated',
            'last_updated' => $location['updated_at']
        ]);
        exit();
    }
    
    echo json_encode([
        'status' => 'success',
        'lat' => $location['latitude'],
        'lng' => $location['longitude'],
        'address' => $location['address'],
        'updated_at' => $location['updated_at']
    ]);
} else {
    // For testing/development - generate dummy location data near the destination
    // In production, you would return an error instead
    
    // Get the destination coordinates if available
    $destination_query = "SELECT from_location, to_location FROM work_posts WHERE id = ?";
    $dest_stmt = $conn->prepare($destination_query);
    $dest_stmt->bind_param("i", $post_id);
    $dest_stmt->execute();
    $dest_result = $dest_stmt->get_result()->fetch_assoc();
    
    if ($dest_result) {
        // Use Google Geocoding API to get coordinates for the destination
        // For now, generate random coordinates near Kuala Lumpur as an example
        $base_lat = 3.139; // Approximate center of Kuala Lumpur
        $base_lng = 101.6869;
        
        // Generate slight random variation
        $lat = $base_lat + (mt_rand(-10, 10) / 1000);
        $lng = $base_lng + (mt_rand(-10, 10) / 1000);
        
        echo json_encode([
            'status' => 'success',
            'lat' => $lat,
            'lng' => $lng,
            'address' => 'Near destination (simulated location)',
            'updated_at' => date('Y-m-d H:i:s'),
            'is_simulated' => true
        ]);
        exit();
    }
    
    echo json_encode(['status' => 'error', 'message' => 'No location data available']);
}
