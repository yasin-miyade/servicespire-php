<?php
/**
 * Notifications component for ServiceSpire
 * This file handles displaying user notifications throughout the application
 */

function getUnreadNotificationsCount($email, $db) {
    $conn = $db->connect();
    $query = "SELECT COUNT(*) as count FROM notifications WHERE user_email = ? AND is_read = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] ?? 0;
}

function getLatestNotifications($email, $db, $limit = 5) {
    $conn = $db->connect();
    $query = "SELECT * FROM notifications WHERE user_email = ? ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $email, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    return $notifications;
}

function markNotificationAsRead($id, $db) {
    $conn = $db->connect();
    $query = "UPDATE notifications SET is_read = 1 WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function renderNotificationItem($notification) {
    $isRead = $notification['is_read'] ? 'bg-white' : 'bg-blue-50';
    $dot = $notification['is_read'] ? '' : '<span class="absolute top-2 right-2 h-2 w-2 bg-blue-500 rounded-full"></span>';
    
    // Determine icon based on notification type (if available)
    $icon = 'info-circle';
    $iconColor = 'text-blue-500';
    
    // Only check type if the column exists
    if (isset($notification['type'])) {
        if ($notification['type'] == 'job_cancelled') {
            $icon = 'times-circle';
            $iconColor = 'text-red-500';
        } elseif ($notification['type'] == 'job_completed') {
            $icon = 'check-circle';
            $iconColor = 'text-green-500';
        } elseif ($notification['type'] == 'payment') {
            $icon = 'money-bill-wave';
            $iconColor = 'text-green-500';
        }
    }
    
    // Format time
    $time = date('M j, g:i a', strtotime($notification['created_at']));
    
    // Get notification content from whatever field is available
    $notificationContent = '';
    if (isset($notification['message'])) {
        $notificationContent = $notification['message'];
    } elseif (isset($notification['content'])) {
        $notificationContent = $notification['content'];
    } elseif (isset($notification['notification_text'])) {
        $notificationContent = $notification['notification_text'];
    } else {
        $notificationContent = "New notification";
        if (isset($notification['post_id']) && $notification['post_id']) {
            $notificationContent = "Notification about job #" . $notification['post_id'];
        }
    }
    
    // Extract first line as title
    $messageLines = explode("\n", $notificationContent);
    $title = trim($messageLines[0]);
    
    // Extract the actual cancellation message if this is a job cancellation
    $messageBody = "";
    $userMessage = "";
    $isJobCancellation = false;
    
    if (isset($notification['type']) && $notification['type'] == 'job_cancelled' || 
        (stripos($title, 'job cancelled') !== false)) {
        $isJobCancellation = true;
        
        // Try to find the quoted message
        $inQuote = false;
        $messageStarted = false;
        
        foreach ($messageLines as $index => $line) {
            if ($index === 0) continue; // Skip title
            
            $line = trim($line);
            
            if (empty($line)) continue;
            
            // Look for message from helper indicator
            if (stripos($line, 'message from') !== false) {
                $messageStarted = true;
                continue;
            }
            
            // Look for quotes to find the user's message
            if ($messageStarted && !$inQuote && strpos($line, '"') === 0) {
                $inQuote = true;
                $userMessage = substr($line, 1); // Remove opening quote
                continue;
            }
            
            if ($inQuote) {
                // If line has ending quote, this is the end of the message
                if (substr($line, -1) === '"') {
                    $userMessage .= ' ' . substr($line, 0, -1); // Remove closing quote
                    $inQuote = false;
                } else {
                    $userMessage .= ' ' . $line;
                }
                continue;
            }
            
            // Add other lines to the message body
            if (!empty($messageBody)) {
                $messageBody .= "\n";
            }
            $messageBody .= $line;
        }
    } else {
        // For non-cancellation notifications, just use the regular body
        array_shift($messageLines); // Remove first line
        $messageBody = implode("\n", $messageLines);
    }
    
    // Create the HTML
    $html = <<<HTML
    <div class="notification-item relative p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors cursor-pointer {$isRead}" 
         data-notification-id="{$notification['id']}" onclick="viewNotification({$notification['id']})">
        {$dot}
        <div class="flex">
            <div class="flex-shrink-0 mr-3">
                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-{$icon} {$iconColor}"></i>
                </div>
            </div>
            <div class="flex-grow">
                <div class="font-medium">{$title}</div>
HTML;

    // For job cancellations, show the helper's message prominently
    if ($isJobCancellation && !empty($userMessage)) {
        $html .= <<<HTML
                <div class="mt-1 p-2 bg-gray-50 border-l-2 border-red-400 text-sm text-gray-700 italic rounded">
                    "{$userMessage}"
                </div>
HTML;
    }

    $html .= <<<HTML
                <div class="text-sm text-gray-500 line-clamp-2 mt-1">{$messageBody}</div>
                <div class="text-xs text-gray-400 mt-1">{$time}</div>
            </div>
        </div>
    </div>
HTML;

    return $html;
}

function renderNotificationsDropdown($email, $db) {
    $count = getUnreadNotificationsCount($email, $db);
    $notifications = getLatestNotifications($email, $db);
    
    $badge = $count > 0 ? "<span class=\"absolute top-0 right-0 -mt-1 -mr-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center\">{$count}</span>" : "";
    
    $notificationItems = '';
    foreach ($notifications as $notification) {
        $notificationItems .= renderNotificationItem($notification);
    }
    
    $emptyState = count($notifications) === 0 ? 
        '<div class="py-8 text-center text-gray-500">
            <i class="fas fa-bell-slash text-3xl mb-2 opacity-30"></i>
            <p>No notifications yet</p>
        </div>' : '';
    
    return <<<HTML
    <div class="notifications-dropdown-container relative inline-block">
        <button id="notificationsButton" class="relative p-2 text-gray-600 hover:text-indigo-600 rounded-full hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <i class="fas fa-bell text-xl"></i>
            {$badge}
        </button>
        
        <div id="notificationsDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-30">
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h3 class="font-semibold">Notifications</h3>
                <button class="text-sm text-blue-600 hover:text-blue-800" onclick="markAllAsRead()">Mark all as read</button>
            </div>
            
            <div class="max-h-96 overflow-y-auto">
                {$notificationItems}
                {$emptyState}
            </div>
            
            <div class="p-3 border-t border-gray-200 text-center">
                <a href="notifications.php" class="text-sm text-indigo-600 hover:text-indigo-800">View all notifications</a>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const button = document.getElementById('notificationsButton');
        const dropdown = document.getElementById('notificationsDropdown');
        
        if (button && dropdown) {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdown.classList.toggle('hidden');
            });
            
            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target) && e.target !== button) {
                    dropdown.classList.add('hidden');
                }
            });
        }
    });
    
    function viewNotification(id) {
        fetch('ajax/read_notification.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'notification_id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Redirect to relevant page if needed
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }
    
    function markAllAsRead() {
        fetch('ajax/mark_all_read.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Remove all unread indicators
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.classList.remove('bg-blue-50');
                    item.classList.add('bg-white');
                    const dot = item.querySelector('.absolute.top-2.right-2');
                    if (dot) dot.remove();
                });
                
                // Remove the badge count
                const badge = document.querySelector('#notificationsButton .absolute');
                if (badge) badge.remove();
            }
        })
        .catch(error => console.error('Error:', error));
    }
    </script>
HTML;
}
?>
