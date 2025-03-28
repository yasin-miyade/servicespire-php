<?php
session_start();
require_once("lib/function.php");

// Check if user is logged in
if (!isset($_SESSION['user_email']) && !isset($_SESSION['helper_email'])) {
    header("Location: login.php");
    exit();
}

// Get the current user's email
$user_email = $_SESSION['user_email'] ?? $_SESSION['helper_email'];
$is_helper = isset($_SESSION['helper_email']);

$db = new db_functions();
$conn = $db->connect();

// Get all notifications for this user
$query = "SELECT * FROM notifications WHERE user_email = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

// Mark all notifications as read
$update_query = "UPDATE notifications SET is_read = 1 WHERE user_email = ? AND is_read = 0";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param("s", $user_email);
$update_stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Notifications - ServiceSpire</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.1/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
    </style>
</head>
<body class="antialiased">
    <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <header class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
                    <p class="mt-1 text-sm text-gray-500">Stay updated on your activity</p>
                </div>
                <div>
                    <a href="<?php echo $is_helper ? 'helper/' : ''; ?>index.php" class="text-indigo-600 hover:text-indigo-800">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </header>

        <?php if (empty($notifications)): ?>
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <div class="inline-block p-6 bg-gray-100 rounded-full mb-4">
                <i class="fas fa-bell-slash text-4xl text-gray-400"></i>
            </div>
            <h2 class="text-xl font-medium text-gray-700 mb-2">No notifications yet</h2>
            <p class="text-gray-500 max-w-md mx-auto">
                When there's activity related to your account or jobs, you'll see notifications here.
            </p>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-lg shadow-sm">
            <div class="divide-y divide-gray-200">
                <?php foreach ($notifications as $notification): ?>
                <div class="p-6 notification-item hover:bg-gray-50 transition-all">
                    <?php
                    // Get notification content from whatever field is available
                    $notificationContent = '';
                    if (isset($notification['message'])) {
                        $notificationContent = $notification['message'];
                    } elseif (isset($notification['content'])) {
                        $notificationContent = $notification['content'];
                    } elseif (isset($notification['notification_text'])) {
                        $notificationContent = $notification['notification_text'];
                    }
                    
                    // Extract first line as title
                    $messageLines = explode("\n", $notificationContent);
                    $title = trim($messageLines[0]);
                    
                    // Process notification type
                    $icon = 'info-circle';
                    $iconColor = 'text-blue-500';
                    $bgColor = 'bg-blue-100';
                    
                    if (isset($notification['type'])) {
                        if ($notification['type'] == 'job_cancelled') {
                            $icon = 'times-circle';
                            $iconColor = 'text-red-500';
                            $bgColor = 'bg-red-100';
                        } elseif ($notification['type'] == 'job_completed') {
                            $icon = 'check-circle';
                            $iconColor = 'text-green-500';
                            $bgColor = 'bg-green-100';
                        } elseif ($notification['type'] == 'payment') {
                            $icon = 'money-bill-wave';
                            $iconColor = 'text-green-500';
                            $bgColor = 'bg-green-100';
                        }
                    }
                    
                    // Extract the actual message if this is a job cancellation
                    $isJobCancellation = false;
                    $userMessage = "";
                    
                    if ((isset($notification['type']) && $notification['type'] == 'job_cancelled') || 
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
                            }
                        }
                    }
                    
                    // Format time
                    $time = date('M j, Y \a\t g:i a', strtotime($notification['created_at']));
                    ?>
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-full <?php echo $bgColor; ?> flex items-center justify-center">
                                <i class="fas fa-<?php echo $icon; ?> text-xl <?php echo $iconColor; ?>"></i>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($title); ?></p>
                            
                            <?php if ($isJobCancellation && !empty($userMessage)): ?>
                            <div class="mt-3 p-3 bg-gray-50 border-l-4 border-red-400 text-gray-700 italic rounded">
                                "<?php echo htmlspecialchars($userMessage); ?>"
                            </div>
                            <?php endif; ?>
                            
                            <div class="mt-3 text-gray-700 space-y-2 leading-relaxed">
                                <?php 
                                array_shift($messageLines); // Remove the first line (title)
                                foreach ($messageLines as $line):
                                    if (trim($line) === "") continue;
                                    // Skip the line if it's part of the extracted message
                                    if ($isJobCancellation && 
                                       (stripos($line, 'message from') !== false || 
                                        strpos(trim($line), '"') === 0 || 
                                        ($inQuote && substr(trim($line), -1) === '"')))
                                        continue;
                                ?>
                                <p><?php echo htmlspecialchars($line); ?></p>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="mt-2 text-sm text-gray-500">
                                <?php echo $time; ?>
                                
                                <?php if (!empty($notification['post_id'])): ?>
                                <a href="<?php echo $is_helper ? '' : ''; ?>job_details.php?id=<?php echo $notification['post_id']; ?>" 
                                   class="ml-3 text-indigo-600 hover:text-indigo-800">
                                    View Job <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
