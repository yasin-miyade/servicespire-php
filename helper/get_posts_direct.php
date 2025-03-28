<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verify helper is logged in
$helper_email = isset($_SESSION['helper_email']) ? $_SESSION['helper_email'] : "";
if (empty($helper_email)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

require_once("../lib/function.php");
$db = new db_functions();

try {
    // Connect directly to the database
    $con = $db->connect();
    
    // Get current date for deadline check
    $current_date = date('Y-m-d');
    
    // First fix any posts with missing status values
    $fix_query = "UPDATE work_posts 
                  SET status = 'open' 
                  WHERE (status IS NULL OR status = '') 
                  AND (assigned_helper_email IS NULL OR assigned_helper_email = '')
                  AND deleted = 0";
    $con->query($fix_query);
    
    // Direct query - more inclusive to get posts that should be displayed
    // Added deadline filter to hide expired posts
    $direct_query = "SELECT * FROM work_posts 
                    WHERE (deleted = 0 OR deleted IS NULL)
                    AND (deadline >= ? OR deadline IS NULL)
                    AND (
                        status = 'open' OR status IS NULL OR status = '' 
                        OR (assigned_helper_email IS NULL AND status != 'completed')
                    )
                    ORDER BY id DESC LIMIT 50";
    $stmt = $con->prepare($direct_query);
    $stmt->bind_param("s", $current_date);
    $stmt->execute();
    $direct_result = $stmt->get_result();
    
    $posts = [];
    if ($direct_result && $direct_result->num_rows > 0) {
        $posts = $direct_result->fetch_all(MYSQLI_ASSOC);
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'posts' => $posts,
        'count' => count($posts),
        'debug' => [
            'total_posts' => $con->query("SELECT COUNT(*) as count FROM work_posts")->fetch_assoc()['count'],
            'open_posts' => $con->query("SELECT COUNT(*) as count FROM work_posts WHERE status = 'open'")->fetch_assoc()['count']
        ]
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
