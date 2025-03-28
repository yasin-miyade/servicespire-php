<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
session_start(); 

require_once("../lib/function.php");

echo "<h1>Database Connection Test</h1>";

try {
    $db = new db_functions();
    echo "<p style='color:green'>✓ Successfully created db_functions instance</p>";
    
    // Test database connection
    $conn = $db->connect();
    echo "<p style='color:green'>✓ Successfully connected to database</p>";
    
    // Test if work_posts table exists
    $result = $conn->query("SHOW TABLES LIKE 'work_posts'");
    if ($result->num_rows > 0) {
        echo "<p style='color:green'>✓ Table 'work_posts' exists</p>";
        
        // Check posts count
        $result = $conn->query("SELECT COUNT(*) as count FROM work_posts");
        $row = $result->fetch_assoc();
        echo "<p>Total posts in database: " . $row['count'] . "</p>";
        
        // Check if helper is logged in
        $helper_email = isset($_SESSION['helper_email']) ? $_SESSION['helper_email'] : null;
        if ($helper_email) {
            echo "<p>Current logged in helper: " . htmlspecialchars($helper_email) . "</p>";
            
            // Test each function
            echo "<h2>Testing getWorkPosts()</h2>";
            $posts = $db->getWorkPosts();
            echo "<p>Found " . count($posts) . " posts with getWorkPosts()</p>";
            
            echo "<h2>Testing getAvailableWorkPosts()</h2>";
            $available = $db->getAvailableWorkPosts($helper_email);
            echo "<p>Found " . count($available) . " posts with getAvailableWorkPosts()</p>";
            
            echo "<h2>Testing getOpenWorkPostsAndHelperRequests()</h2>";
            try {
                $open = $db->getOpenWorkPostsAndHelperRequests($helper_email);
                echo "<p>Found " . count($open) . " posts with getOpenWorkPostsAndHelperRequests()</p>";
            } catch (Exception $e) {
                echo "<p style='color:red'>Error with getOpenWorkPostsAndHelperRequests(): " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color:red'>No helper email found in session!</p>";
        }
    } else {
        echo "<p style='color:red'>× Table 'work_posts' does not exist!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
