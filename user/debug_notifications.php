<?php
session_start();
require_once("../lib/function.php");

// Security check - only show for authenticated users
if (!isset($_SESSION['email'])) {
    die("Unauthorized access");
}

$user_email = $_SESSION['email'];
$db = new db_functions();
$conn = $db->connect();

// Function to check database structure
function checkDatabaseStructure($conn) {
    $output = "<h3>Database Structure Check:</h3>";
    
    // Check if notifications table exists
    $result = $conn->query("SHOW TABLES LIKE 'notifications'");
    if ($result->num_rows > 0) {
        $output .= "<p class='text-green-500'>✅ Notifications table exists.</p>";
        
        // Check notifications table structure
        $columns = $conn->query("DESCRIBE notifications");
        $output .= "<p>Notifications table columns:</p><ul>";
        while ($column = $columns->fetch_assoc()) {
            $output .= "<li>{$column['Field']} - {$column['Type']}</li>";
        }
        $output .= "</ul>";
    } else {
        $output .= "<p class='text-amber-500'>⚠️ Notifications table doesn't exist - using work_posts status for notifications.</p>";
    }
    
    // Check work_posts structure
    $columns = $conn->query("DESCRIBE work_posts");
    $output .= "<p>Work Posts table columns:</p><ul>";
    while ($column = $columns->fetch_assoc()) {
        $output .= "<li>{$column['Field']} - {$column['Type']}</li>";
    }
    $output .= "</ul>";
    
    return $output;
}

// Query all work posts that might be relevant to notifications
$all_posts_query = "SELECT wp.*, hs.first_name, hs.last_name, hs.id as helper_id 
                    FROM work_posts wp 
                    LEFT JOIN helper_sign_up hs ON wp.assigned_helper_email = hs.email
                    WHERE wp.email = ?
                    ORDER BY wp.updated_at DESC LIMIT 20";
$all_posts_stmt = $conn->prepare($all_posts_query);
$all_posts_stmt->bind_param("s", $user_email);
$all_posts_stmt->execute();
$all_posts = $all_posts_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Debugging</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Notification Debug Tool</h1>
        
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Database Information</h2>
            <?php echo checkDatabaseStructure($conn); ?>
        </div>
        
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Your Work Posts</h2>
            
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border p-2">ID</th>
                        <th class="border p-2">Work</th>
                        <th class="border p-2">Status</th>
                        <th class="border p-2">Assigned Helper</th>
                        <th class="border p-2">Last Updated</th>
                        <th class="border p-2">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($all_posts->num_rows === 0): ?>
                    <tr>
                        <td colspan="6" class="border p-4 text-center">No work posts found</td>
                    </tr>
                    <?php else: ?>
                        <?php while ($post = $all_posts->fetch_assoc()): ?>
                        <tr class="<?php echo ($post['assigned_helper_email'] && $post['status'] === 'open') ? 'bg-yellow-50' : ''; ?>">
                            <td class="border p-2"><?php echo $post['id']; ?></td>
                            <td class="border p-2"><?php echo htmlspecialchars($post['work']); ?></td>
                            <td class="border p-2">
                                <span class="<?php 
                                    if ($post['status'] === 'pending') echo 'bg-blue-100 text-blue-800';
                                    elseif ($post['status'] === 'completed') echo 'bg-green-100 text-green-800';
                                    elseif ($post['status'] === 'open' && $post['assigned_helper_email']) echo 'bg-yellow-100 text-yellow-800';
                                    else echo 'bg-gray-100 text-gray-800';
                                ?> px-2 py-1 rounded-full text-xs"><?php echo $post['status']; ?></span>
                            </td>
                            <td class="border p-2">
                                <?php if ($post['assigned_helper_email']): ?>
                                    <?php if ($post['first_name']): ?>
                                        <?php echo htmlspecialchars($post['first_name'].' '.$post['last_name']); ?>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($post['assigned_helper_email']); ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    None
                                <?php endif; ?>
                            </td>
                            <td class="border p-2"><?php echo date('Y-m-d H:i:s', strtotime($post['updated_at'])); ?></td>
                            <td class="border p-2">
                                <?php if ($post['assigned_helper_email'] && $post['status'] === 'open'): ?>
                                    <div class="flex space-x-2">
                                        <button onclick="handleAction(<?php echo $post['id']; ?>, 'accept')" class="bg-green-100 text-green-700 px-2 py-1 rounded text-sm">Accept</button>
                                        <button onclick="handleAction(<?php echo $post['id']; ?>, 'decline')" class="bg-red-100 text-red-700 px-2 py-1 rounded text-sm">Decline</button>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400">No action needed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        function handleAction(postId, action) {
            if (!confirm(`Are you sure you want to ${action} this helper request?`)) {
                return;
            }
            
            fetch('notification_response.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `post_id=${postId}&action=${action}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(`Helper request ${action}ed successfully!`);
                    location.reload();
                } else {
                    alert(`Error: ${data.message || 'Failed to process request'}`);
                }
            })
            .catch(error => {
                alert(`Error: ${error.message || 'Something went wrong'}`);
            });
        }
    </script>
</body>
</html>
