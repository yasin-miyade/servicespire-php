<?php
session_start();
require_once("../lib/function.php");

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    die("Unauthorized access. Please log in first.");
}

$email = $_SESSION['email'];

// Initialize database connection
$db = new db_functions();
$conn = $db->connect();

// Function to display table info
function checkTable($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    if ($result->num_rows > 0) {
        echo "<p>✅ Table '$tableName' exists</p>";
        
        // Show columns
        $columns = $conn->query("SHOW COLUMNS FROM $tableName");
        echo "<p>Columns in '$tableName':</p>";
        echo "<ul>";
        while ($col = $columns->fetch_assoc()) {
            echo "<li>{$col['Field']} - {$col['Type']} " . ($col['Null'] === 'NO' ? '(NOT NULL)' : '') . "</li>";
        }
        echo "</ul>";
        
        // Count records
        $count = $conn->query("SELECT COUNT(*) as total FROM $tableName")->fetch_assoc();
        echo "<p>Total records: {$count['total']}</p>";
        
        // Show the most recent records
        $records = $conn->query("SELECT * FROM $tableName ORDER BY id DESC LIMIT 5");
        if ($records->num_rows > 0) {
            echo "<p>Latest records:</p>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr>";
            $first = $records->fetch_assoc();
            foreach (array_keys($first) as $key) {
                echo "<th>$key</th>";
            }
            echo "</tr>";
            
            // Reset pointer
            $records->data_seek(0);
            
            while ($row = $records->fetch_assoc()) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p>❌ Table '$tableName' doesn't exist</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #333; }
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <h1>Database Structure Check</h1>
    
    <h2>Work Posts Table</h2>
    <?php checkTable($conn, 'work_posts'); ?>
    
    <h2>User Posts for <?php echo htmlspecialchars($email); ?></h2>
    <?php
    $user_posts = $conn->query("SELECT * FROM work_posts WHERE email = '$email' ORDER BY created_at DESC");
    if ($user_posts && $user_posts->num_rows > 0) {
        echo "<p>Found {$user_posts->num_rows} posts for this user</p>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Work</th><th>Status</th><th>Created At</th></tr>";
        while ($post = $user_posts->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$post['id']}</td>";
            echo "<td>{$post['work']}</td>";
            echo "<td>" . ($post['status'] ?? 'NULL') . "</td>";
            echo "<td>{$post['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No posts found for this user</p>";
    }
    ?>
    
    <p><a href="index.php">Back to Dashboard</a></p>
</body>
</html>
