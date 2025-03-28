<?php
require_once("lib/function.php");

// Initialize database connection
$db = new db_functions();
$conn = $db->connect();

echo "<h1>Fix Notification Type Column Utility</h1>";

try {
    // Check if notifications table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'notifications'");
    if ($table_check->num_rows == 0) {
        echo "<p>Notifications table does not exist. Please run fix_notifications.php first.</p>";
    } else {
        // Check if type column exists
        $column_check = $conn->query("SHOW COLUMNS FROM notifications LIKE 'type'");
        if ($column_check->num_rows == 0) {
            echo "<p>Adding 'type' column to notifications table...</p>";
            
            // Add the type column
            $alter_query = "ALTER TABLE notifications ADD COLUMN type VARCHAR(50) DEFAULT 'info' AFTER post_id";
            
            if ($conn->query($alter_query)) {
                echo "<p style='color:green'>Column 'type' added successfully!</p>";
                
                // Update existing records - set cancelled job notifications
                $update_query = "UPDATE notifications SET type = 'job_cancelled' WHERE content LIKE 'Job Cancelled%' OR message LIKE 'Job Cancelled%'";
                if ($conn->query($update_query)) {
                    $affected = $conn->affected_rows;
                    echo "<p style='color:green'>Updated {$affected} existing job cancellation notifications.</p>";
                }
            } else {
                echo "<p style='color:red'>Error adding column: " . $conn->error . "</p>";
            }
        } else {
            echo "<p style='color:green'>The 'type' column already exists in the notifications table.</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='index.php'>Return to homepage</a></p>";
echo "<p><a href='fix_notifications.php'>Run the full notifications fixer</a></p>";

$conn->close();
?>
