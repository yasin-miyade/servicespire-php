<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $username = $_GET['username'] ?? '';
    
    if ($username) {
        $conn = getDatabaseConnection();
        
        $stmt = $conn->prepare("SELECT latitude, longitude FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $response = [
                'success' => true,
                'latitude' => $row['latitude'],
                'longitude' => $row['longitude']
            ];
        }
        
        $stmt->close();
        $conn->close();
    }
}

echo json_encode($response);
?>