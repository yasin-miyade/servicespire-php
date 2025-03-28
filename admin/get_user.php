<?php
header("Content-Type: application/json");
require_once('../lib/function.php');
$db = new db_functions();

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Missing ID']);
    exit;
}

$id = intval($_GET['id']);
$user = $db->getUserById($id);

if ($user) {
    // Ensure the id_proof_file is correctly formatted
    if (!empty($user['id_proof_file'])) {
        $filename = basename($user['id_proof_file']);
        $file_path = "../user/uploads/" . $filename;
        
        if (file_exists($file_path)) {
            // Use absolute URL for frontend
            $user['id_proof_file'] = "http://localhost/project/ServiceSpire/user/uploads/" . $filename;
        } else {
            error_log("File not found: " . realpath($file_path)); // Debugging log
            $user['id_proof_file'] = null;
        }
    } else {
        error_log("No file path found in database."); // Debugging log
        $user['id_proof_file'] = null;
    }

    echo json_encode($user);
} else {
    echo json_encode(['error' => 'User not found']);
}
?>