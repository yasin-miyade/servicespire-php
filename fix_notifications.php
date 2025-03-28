<?php
require_once("lib/function.php");

// Initialize database connection
$db = new db_functions();
$conn = $db->connect();

echo "<h1>Notifications Table Fix Utility</h1>";

// Check if notifications table exists
$table_check = $conn->query("SHOW TABLES LIKE 'notifications'");
if ($table_check->num_rows == 0) {
    echo "<p>Notifications table does not exist. Creating it now...</p>";
    
    // Create notifications table with standard schema
    $create_table_query = "CREATE TABLE `notifications` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_email` varchar(255) NOT NULL,
        `content` text NOT NULL,
        `post_id` int(11) DEFAULT NULL,
        `is_read` tinyint(1) DEFAULT 0,
        `type` varchar(50) DEFAULT 'info',
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`),
        KEY `user_email` (`user_email`),
        KEY `post_id` (`post_id`),
        KEY `created_at` (`created_at`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($conn->query($create_table_query)) {
        echo "<p style='color:green'>Table created successfully!</p>";
    } else {
        echo "<p style='color:red'>Error creating table: " . $conn->error . "</p>";
    }
} else {
    echo "<p>Notifications table exists. Checking columns...</p>";
    
    // Get current columns
    $cols = $conn->query("SHOW COLUMNS FROM notifications");
    $columns = [];
    while($col = $cols->fetch_assoc()) {
        $columns[$col['Field']] = $col;
    }
    
    echo "<p>Found columns: " . implode(", ", array_keys($columns)) . "</p>";
    
    // Check for message field
    $hasMessageField = false;
    $messageFieldName = "";
    
    if (isset($columns['message'])) {
        $hasMessageField = true;
        $messageFieldName = "message";
    } else if (isset($columns['content'])) {
        $hasMessageField = true;
        $messageFieldName = "content";
    } else if (isset($columns['notification_text'])) {
        $hasMessageField = true;
        $messageFieldName = "notification_text";
    }
    
    if (!$hasMessageField) {
        echo "<p>No message field found. Adding 'content' column...</p>";
        $alter_query = "ALTER TABLE notifications ADD COLUMN content TEXT NOT NULL AFTER user_email";
        
        if ($conn->query($alter_query)) {
            echo "<p style='color:green'>Column 'content' added successfully!</p>";
            $messageFieldName = "content";
        } else {
            echo "<p style='color:red'>Error adding column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>Message field found: '{$messageFieldName}'</p>";
    }
    
    // Make sure necessary columns exist
    $requiredColumns = [
        'user_email' => "VARCHAR(255) NOT NULL",
        'post_id' => "INT DEFAULT NULL",
        'is_read' => "TINYINT(1) DEFAULT 0",
        'type' => "VARCHAR(50) DEFAULT 'info'",
        'created_at' => "DATETIME NOT NULL"
    ];
    
    foreach($requiredColumns as $colName => $colDef) {
        if (!isset($columns[$colName])) {
            echo "<p>Column '{$colName}' not found. Adding it...</p>";
            $alter_query = "ALTER TABLE notifications ADD COLUMN {$colName} {$colDef}";
            
            if ($conn->query($alter_query)) {
                echo "<p style='color:green'>Column '{$colName}' added successfully!</p>";
            } else {
                echo "<p style='color:red'>Error adding column: " . $conn->error . "</p>";
            }
        }
    }
}

echo "<p>Notification table fix completed.</p>";
echo "<p><a href='index.php'>Return to homepage</a></p>";
$conn->close();
?>
