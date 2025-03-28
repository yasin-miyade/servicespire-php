<?php
session_start();
require_once("../lib/function.php");

if (!isset($_SESSION['email'])) {
    echo "<p class='text-red-500'>Access denied: Please login first.</p>";
    exit;
}

$user_email = $_SESSION['email'];
$db = new db_functions();
$conn = $db->connect();

// Modified query to catch all types of helper requests:
// 1. Either status='open' with assigned_helper_email (helper sent request but not accepted yet)
// 2. Or status='pending_approval' (legacy status some systems may use)
$query = "SELECT wp.*, hs.first_name, hs.last_name, hs.mobile, hs.id as helper_id 
          FROM work_posts wp
          JOIN helper_sign_up hs ON wp.assigned_helper_email = hs.email
          WHERE wp.email = ? 
          AND (
              (wp.status = 'open' AND wp.assigned_helper_email IS NOT NULL)
              OR wp.status = 'pending_approval'
          )
          ORDER BY wp.updated_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

// Debug query to find ANY requests
if ($result->num_rows == 0) {
    // Fallback query that will find ANY work with assigned helpers regardless of status
    $fallback_query = "SELECT wp.*, hs.first_name, hs.last_name, hs.mobile, hs.id as helper_id 
              FROM work_posts wp
              JOIN helper_sign_up hs ON wp.assigned_helper_email = hs.email
              WHERE wp.email = ? AND wp.assigned_helper_email IS NOT NULL 
              AND wp.status != 'completed' AND wp.status != 'pending'
              ORDER BY wp.updated_at DESC";
    
    $fallback_stmt = $conn->prepare($fallback_query);
    $fallback_stmt->bind_param("s", $user_email);
    $fallback_stmt->execute();
    $result = $fallback_stmt->get_result();
}

if ($result->num_rows == 0) {
    echo "<div class='flex flex-col items-center justify-center py-12 bg-gray-50 rounded-lg'>
        <div class='flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4'>
            <i class='ph ph-bell-slash text-2xl text-blue-600'></i>
        </div>
        <h3 class='text-lg font-medium text-gray-900'>No notifications</h3>
        <p class='mt-1 text-sm text-gray-500'>You're all caught up! No new notifications at this time.</p>
    </div>";
} else {
    while ($row = $result->fetch_assoc()) {
        // Format date
        $date = new DateTime($row['updated_at']);
        $formatted_date = $date->format("M j, Y, g:i a");
        
        echo "<li id='notification-{$row['id']}' class='bg-blue-50 border-l-4 border-blue-500 p-4 mb-4 rounded-lg shadow-sm'>
            <div class='flex items-start'>
                <div class='flex-shrink-0'>
                    <div class='bg-blue-100 rounded-full p-2'>
                        <i class='ph ph-bell text-blue-600 text-xl'></i>
                    </div>
                </div>
                <div class='ml-3 flex-1'>
                    <div class='flex items-center justify-between'>
                        <p class='text-sm font-medium text-blue-800'>Helper Request</p>
                        <span class='text-xs text-gray-500'>{$formatted_date}</span>
                    </div>
                    <div class='mt-2 text-sm text-gray-700'>
                        <p><span class='font-semibold'>{$row['first_name']} {$row['last_name']}</span> wants to help with your work request: <span class='font-semibold'>\"{$row['work']}\"</span></p>
                    </div>
                    <div class='mt-3'>
                        <button onclick='showHelperProfile({$row['helper_id']})' class='mr-2 text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium py-1 px-2 border border-blue-300 rounded-md transition'>
                            <i class='ph ph-user mr-1'></i> View Profile
                        </button>
                        <button onclick='handleNotificationAction({$row['id']}, \"accept\")' class='mr-2 text-xs bg-green-100 hover:bg-green-200 text-green-800 font-medium py-1 px-2 border border-green-300 rounded-md transition'>
                            <i class='ph ph-check mr-1'></i> Accept
                        </button>
                        <button onclick='handleNotificationAction({$row['id']}, \"decline\")' class='text-xs bg-red-100 hover:bg-red-200 text-red-800 font-medium py-1 px-2 border border-red-300 rounded-md transition'>
                            <i class='ph ph-x mr-1'></i> Decline
                        </button>
                    </div>
                </div>
            </div>
        </li>";
    }
}

$stmt->close();
$conn->close();
?>