<?php
session_start();
require_once("../lib/function.php");

if (!isset($_SESSION['helper_email'])) {
    echo "<p class='text-red-500'>Access denied: Please login first.</p>";
    exit;
}

$helper_email = $_SESSION['helper_email'];
$db = new db_functions();
$conn = $db->connect();

// Check if notifications table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'");
if ($tableCheck->num_rows === 0) {
    // Create notifications table if it doesn't exist
    $createTable = "CREATE TABLE notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        recipient_email VARCHAR(255) NULL,
        message TEXT NOT NULL,
        post_id INT NULL,
        type VARCHAR(50) DEFAULT 'general',
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($createTable);
    
    // Add an index for faster queries
    $conn->query("ALTER TABLE notifications ADD INDEX (recipient_email)");
}

// Get notifications for this helper based on work_posts
$query = "SELECT wp.id as post_id, wp.work as post_title, wp.message as post_description, 
          wp.status, wp.email as user_email, wp.created_at,
          s.id as user_id, s.first_name as user_first_name, s.last_name as user_last_name,
          'job_notification' as type, 
          CONCAT('You have been assigned to work: ', wp.work) as message
          FROM work_posts wp
          LEFT JOIN sign_up s ON wp.email = s.email
          WHERE wp.assigned_helper_email = ?
          AND wp.status IN ('open', 'pending', 'accepted')
          ORDER BY wp.updated_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $helper_email);
$stmt->execute();
$result = $stmt->get_result();

// Also check if there are any actual notifications in notifications table
$notifQuery = "SELECT n.*, 
              wp.id as post_id, wp.work as post_title, wp.message as post_description,
              s.id as user_id, s.first_name as user_first_name, s.last_name as user_last_name
              FROM notifications n
              LEFT JOIN work_posts wp ON n.post_id = wp.id
              LEFT JOIN sign_up s ON wp.email = s.email
              WHERE n.recipient_email = ?
              ORDER BY n.created_at DESC";

$notifStmt = $conn->prepare($notifQuery);
$notifStmt->bind_param("s", $helper_email);
$notifStmt->execute();
$notifResult = $notifStmt->get_result();

// Combine results from both queries if we have notifications
$hasNotifications = ($result->num_rows > 0 || $notifResult->num_rows > 0);

if (!$hasNotifications) {
    echo "<div class='flex flex-col items-center justify-center py-12 bg-gray-50 rounded-lg'>
        <div class='flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4'>
            <svg xmlns='http://www.w3.org/2000/svg' class='h-8 w-8 text-blue-600' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' />
            </svg>
        </div>
        <h3 class='text-lg font-medium text-gray-900'>No notifications</h3>
        <p class='mt-1 text-sm text-gray-500'>You're all caught up! No new notifications at this time.</p>
    </div>";
} else {
    // Process notifications from work_posts first
    while ($row = $result->fetch_assoc()) {
        // Format date
        $date = new DateTime($row['created_at']);
        $formatted_date = $date->format("M j, Y, g:i a");
        
        // Determine notification background color based on status
        $bgColor = 'bg-blue-50';
        $borderColor = 'border-blue-500';
        $iconBgColor = 'bg-blue-100';
        $iconColor = 'text-blue-600';
        $headerText = 'Work Assignment';
        
        if ($row['status'] == 'accepted') {
            $bgColor = 'bg-green-50';
            $borderColor = 'border-green-500';
            $iconBgColor = 'bg-green-100';
            $iconColor = 'text-green-600';
            $headerText = 'Accepted Work';
        } else if ($row['status'] == 'pending') {
            $bgColor = 'bg-yellow-50';
            $borderColor = 'border-yellow-500';
            $iconBgColor = 'bg-yellow-100';
            $iconColor = 'text-yellow-600';
            $headerText = 'Pending Work';
        }
        
        // Construct notification item
        echo "<li id='notification-wp-{$row['post_id']}' class='{$bgColor} border-l-4 {$borderColor} p-4 mb-4 rounded-lg shadow-sm'>
            <div class='flex items-start'>
                <div class='flex-shrink-0'>
                    <div class='{$iconBgColor} rounded-full p-2'>
                        <svg xmlns='http://www.w3.org/2000/svg' class='h-6 w-6 {$iconColor}' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' />
                        </svg>
                    </div>
                </div>
                <div class='ml-3 flex-1'>
                    <div class='flex items-center justify-between'>
                        <p class='text-sm font-medium {$iconColor}'>{$headerText}</p>
                        <span class='text-xs text-gray-500'>{$formatted_date}</span>
                    </div>
                    <div class='mt-2 text-sm text-gray-700'>
                        <p>Work: <span class='font-semibold'>" . htmlspecialchars($row['post_title']) . "</span></p>
                    </div>";
                    
        // Add user profile link if user data is available
        if (!empty($row['user_id'])) {
            $userName = htmlspecialchars($row['user_first_name'] . ' ' . $row['user_last_name']);
            echo "<div class='mt-3'>
                <button onclick='showUserProfile({$row['user_id']})' class='mr-2 text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium py-1 px-2 border border-blue-300 rounded-md transition'>
                    <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4 inline mr-1' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' />
                    </svg>
                    View User Profile: {$userName}
                </button>";
            
            // Change the link to button that opens the modal
            echo "<button onclick='showWorkDetails({$row['post_id']})' class='inline-block text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-medium py-1 px-2 border border-indigo-300 rounded-md transition'>
                <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4 inline mr-1' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 12a3 3 0 11-6 0 3 3 0 016 0z' />
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' />
                </svg>
                View Work Details
            </button>";
            
            echo "</div>";
        }
        
        echo "
                </div>
                <button onclick='hideNotification(\"wp-{$row['post_id']}\")' class='ml-2 text-gray-400 hover:text-gray-600 focus:outline-none'>
                    <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12' />
                    </svg>
                </button>
            </div>
        </li>";
    }
    
    // Process notifications from notifications table
    while ($row = $notifResult->fetch_assoc()) {
        // Format date
        $date = new DateTime($row['created_at']);
        $formatted_date = $date->format("M j, Y, g:i a");
        
        // Determine notification type styling
        $bgColor = 'bg-blue-50';
        $borderColor = 'border-blue-500';
        $iconBgColor = 'bg-blue-100';
        $iconColor = 'text-blue-600';
        $headerText = 'Notification';
        
        if ($row['type'] == 'acceptance') {
            $bgColor = 'bg-green-50';
            $borderColor = 'border-green-500';
            $iconBgColor = 'bg-green-100';
            $iconColor = 'text-green-600';
            $headerText = 'Offer Accepted';
        } else if ($row['type'] == 'decline') {
            $bgColor = 'bg-gray-50';
            $borderColor = 'border-red-500';
            $iconBgColor = 'bg-red-100';
            $iconColor = 'text-red-600';
            $headerText = 'Offer Declined';
        }
        
        // Construct notification item
        echo "<li id='notification-{$row['id']}' class='{$bgColor} border-l-4 {$borderColor} p-4 mb-4 rounded-lg shadow-sm'>
            <div class='flex items-start'>
                <div class='flex-shrink-0'>
                    <div class='{$iconBgColor} rounded-full p-2'>
                        <svg xmlns='http://www.w3.org/2000/svg' class='h-6 w-6 {$iconColor}' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' />
                        </svg>
                    </div>
                </div>
                <div class='ml-3 flex-1'>
                    <div class='flex items-center justify-between'>
                        <p class='text-sm font-medium {$iconColor}'>{$headerText}</p>
                        <span class='text-xs text-gray-500'>{$formatted_date}</span>
                    </div>
                    <div class='mt-2 text-sm text-gray-700'>
                        <p>" . htmlspecialchars($row['message']) . "</p>
                    </div>";
                    
        // Add user profile link if user data is available
        if (!empty($row['user_id'])) {
            $userName = htmlspecialchars($row['user_first_name'] . ' ' . $row['user_last_name']);
            echo "<div class='mt-3'>
                <button onclick='showUserProfile({$row['user_id']})' class='mr-2 text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium py-1 px-2 border border-blue-300 rounded-md transition'>
                    <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4 inline mr-1' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' />
                    </svg>
                    View User Profile: {$userName}
                </button>";
            
            // If post_id is available, add the button to view work details in modal
            if (!empty($row['post_id'])) {
                echo "<button onclick='showWorkDetails({$row['post_id']})' class='inline-block text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-medium py-1 px-2 border border-indigo-300 rounded-md transition'>
                    <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4 inline mr-1' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 12a3 3 0 11-6 0 3 3 0 016 0z' />
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' />
                    </svg>
                    View Work Details
                </button>";
            }
            
            echo "</div>";
        }
        
        echo "
                </div>
                <button onclick='hideNotification({$row['id']})' class='ml-2 text-gray-400 hover:text-gray-600 focus:outline-none'>
                    <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12' />
                    </svg>
                </button>
            </div>
        </li>";
    }
}

$stmt->close();
$notifStmt->close();
$conn->close();
?>
