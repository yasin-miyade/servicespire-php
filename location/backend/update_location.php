<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $username = $data['username'] ?? '';
    $latitude = $data['latitude'] ?? null;
    $longitude = $data['longitude'] ?? null;
    
    // Strict location validation for Solapur region
    $isValidLocation = 
        $latitude >= 17.5 && $latitude <= 18.5 &&
        $longitude >= 75.5 && $longitude <= 76.5;
    
    if ($isValidLocation) {
        $conn = getDatabaseConnection();
        
        $stmt = $conn->prepare("UPDATE users SET latitude = ?, longitude = ? WHERE username = ?");
        $stmt->bind_param("dds", $latitude, $longitude, $username);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Location updated successfully';
        }
        
        $stmt->close();
        $conn->close();
    } else {
        $response['message'] = 'Invalid location coordinates';
    }
}

echo json_encode($response);
?>