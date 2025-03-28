<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../lib/function.php");
$db = new db_functions();

// Only allow authenticated users to run this
if (!isset($_SESSION['helper_email']) && !isset($_SESSION['email'])) {
    echo "Authentication required";
    exit;
}

// Check if the action parameter is present
$action = isset($_GET['action']) ? $_GET['action'] : 'status';

// Define a function to display the current status
function displayStatus($db) {
    $con = $db->connect();
    
    // Get post counts by status
    $total = $con->query("SELECT COUNT(*) as count FROM work_posts")->fetch_assoc()['count'];
    $open = $con->query("SELECT COUNT(*) as count FROM work_posts WHERE status = 'open'")->fetch_assoc()['count'];
    $completed = $con->query("SELECT COUNT(*) as count FROM work_posts WHERE status = 'completed'")->fetch_assoc()['count'];
    $pending = $con->query("SELECT COUNT(*) as count FROM work_posts WHERE status = 'pending'")->fetch_assoc()['count'];
    $null_status = $con->query("SELECT COUNT(*) as count FROM work_posts WHERE status IS NULL OR status = ''")->fetch_assoc()['count'];
    $unassigned = $con->query("SELECT COUNT(*) as count FROM work_posts WHERE assigned_helper_email IS NULL")->fetch_assoc()['count'];
    
    echo "<h2>Work Post Status</h2>";
    echo "<ul>";
    echo "<li>Total posts: $total</li>";
    echo "<li>Open posts: $open</li>";
    echo "<li>Completed posts: $completed</li>";
    echo "<li>Pending posts: $pending</li>";
    echo "<li>NULL/empty status: $null_status</li>";
    echo "<li>Unassigned posts: $unassigned</li>";
    echo "</ul>";
    
    // Get the first few posts to show examples
    $sample = $con->query("SELECT id, status, assigned_helper_email FROM work_posts LIMIT 10");
    if ($sample->num_rows > 0) {
        echo "<h3>Sample Posts:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Status</th><th>Assigned Helper</th></tr>";
        
        while ($row = $sample->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>" . ($row['status'] ?? "NULL") . "</td>";
            echo "<td>" . ($row['assigned_helper_email'] ?? "NULL") . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
}

// Fix missing status values
if ($action == 'fix') {
    $con = $db->connect();
    
    // Fix NULL or empty status for unassigned posts
    $fix_query = "UPDATE work_posts SET status = 'open' WHERE (status IS NULL OR status = '') AND (assigned_helper_email IS NULL OR assigned_helper_email = '') AND deleted = 0";
    $result = $con->query($fix_query);
    $affected = $con->affected_rows;
    
    echo "<h2>Status Fix Results</h2>";
    echo "<p>Fixed $affected posts</p>";
    
    displayStatus($db);
} else {
    displayStatus($db);
    
    echo "<p><a href='?action=fix'>Fix Post Status</a></p>";
    echo "<p><a href='dashboard.php'>Return to Dashboard</a></p>";
}
?>
