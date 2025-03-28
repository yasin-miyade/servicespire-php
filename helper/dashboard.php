<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
// session_start(); 

require_once("../lib/function.php");
$db = new db_functions();

// Verify helper is logged in
$helper_email = isset($_SESSION['helper_email']) ? $_SESSION['helper_email'] : "";
if (empty($helper_email)) {
    header("Location: login.php");
    exit;
}

// Collect debug information to help troubleshoot
$debug_info = $db->debugWorkPosts($helper_email);
error_log("Debug info for helper posts: " . json_encode($debug_info));

// Get available work posts with improved error handling
try {
    // First try with the preferred function
    $work_posts = $db->getOpenWorkPostsAndHelperRequests($helper_email);
    error_log("Fetched " . count($work_posts) . " posts with getOpenWorkPostsAndHelperRequests");
    
    // If no posts are found, try a direct query as a last resort
    if (empty($work_posts)) {
        error_log("No posts found with any method, trying direct SQL query");
        
        // Use a more inclusive query that picks up posts that should be shown
        $con = $db->connect();
        $direct_query = "SELECT * FROM work_posts 
                         WHERE (deleted = 0 OR deleted IS NULL) 
                         AND (status = 'open' OR status IS NULL OR status = '' OR assigned_helper_email IS NULL) 
                         ORDER BY id DESC";
        $direct_result = $con->query($direct_query);
        
        if ($direct_result && $direct_result->num_rows > 0) {
            $work_posts = $direct_result->fetch_all(MYSQLI_ASSOC);
            error_log("Direct query found " . count($work_posts) . " posts");
            
            // Fix posts that should be open but don't have the status set
            if (count($work_posts) > 0) {
                foreach ($work_posts as $key => $post) {
                    // If post is unassigned or has NULL/empty status but not explicitly marked as completed/pending
                    if (
                        (empty($post['status']) || $post['status'] === NULL || $post['status'] === '') && 
                        (empty($post['assigned_helper_email']) || $post['assigned_helper_email'] === NULL)
                    ) {
                        // Update the status to 'open' in the database
                        $update_query = "UPDATE work_posts SET status = 'open' WHERE id = ? AND (status IS NULL OR status = '')";
                        $update_stmt = $con->prepare($update_query);
                        $update_stmt->bind_param("i", $post['id']);
                        $update_stmt->execute();
                        
                        // Update the status in our array
                        $work_posts[$key]['status'] = 'open';
                    }
                }
                error_log("Updated status of posts that should be open");
            }
        } else {
            error_log("Even direct query found no posts. SQL error: " . ($con->error ?? 'None'));
            $work_posts = array();
        }
    }
    
    // Filter out completed posts - only show open posts with more inclusive criteria
    if (!empty($work_posts)) {
        $current_date = date('Y-m-d'); // Get current date for availability check
        $before_filter_count = count($work_posts);
        
        $work_posts = array_filter($work_posts, function($post) use ($current_date) {
            // Exclude posts with status 'completed'
            if (isset($post['status']) && $post['status'] === 'completed') {
                return false;
            }
            
            // Exclude posts with status 'pending_approval' or 'accepted'
            if (isset($post['status']) && ($post['status'] === 'pending_approval' || $post['status'] === 'accepted')) {
                return false;
            }
            
            // Hide posts that have passed their deadline
            if (isset($post['deadline']) && !empty($post['deadline']) && $post['deadline'] < $current_date) {
                return false;
            }
            
            // Consider posts with NULL/empty status as open posts if they're unassigned
            $isOpen = !isset($post['status']) || $post['status'] === '' || $post['status'] === 'open' || 
                     (empty($post['assigned_helper_email']) && $post['status'] !== 'completed');
            
            // Set availability flag based on date (if available)
            if (isset($post['available_from']) && !empty($post['available_from'])) {
                $post['is_available'] = ($current_date >= $post['available_from']);
            } else {
                $post['is_available'] = true; // If no available_from date, consider it available
            }
            
            // Explicitly mark as open post for UI highlighting
            $post['is_open'] = $isOpen;
            
            // Include post if it's open
            return $isOpen;
        });
        
        $after_filter_count = count($work_posts);
        error_log("Filtered posts: $before_filter_count before, $after_filter_count after");
        
        // Sort posts - show available posts first, then by date (newest first)
        usort($work_posts, function($a, $b) {
            // First sort by availability
            if (($a['is_available'] ?? true) && !($b['is_available'] ?? true)) return -1;
            if (!($a['is_available'] ?? true) && ($b['is_available'] ?? true)) return 1;
            
            // Then sort by created date (newest first)
            $date_a = strtotime($a['created_at'] ?? 'now');
            $date_b = strtotime($b['created_at'] ?? 'now');
            return $date_b - $date_a;
        });
    }
} catch (Exception $e) {
    error_log("Exception when getting posts: " . $e->getMessage());
    $work_posts = array(); // Set to empty array in case of error
}

// Log how many posts were found
error_log("Total posts found for display: " . count($work_posts));

// Add a flag to check if we need to refresh only the stats section
$refresh_stats = false;
if (isset($_GET['refresh_stats']) && $_GET['refresh_stats'] == 'true') {
    $refresh_stats = true;
    
    // Get updated statistics - pass helper email to only count their assignments
    $current_date = date('Y-m-d'); // Get current date to filter out expired posts
    $total_requests = $db->getTotalRequests($helper_email, $current_date);
    $pending_requests = $db->getPendingRequests($helper_email);
    $completed_tasks = $db->getCompletedTasks($helper_email);
    
    // Return JSON with updated stats
    if ($refresh_stats) {
        header('Content-Type: application/json');
        echo json_encode([
            'total_requests' => $total_requests,
            'pending_requests' => $pending_requests,
            'completed_tasks' => $completed_tasks
        ]);
        exit();
    }
}

// Handle help post request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['help_post_id'])) {
    $post_id = $_POST['help_post_id'];
    // Assign helper and set status to pending
    $db->assignHelper($post_id, $helper_email);
    $post_owner = $db->getUserByPostId($post_id);
    // Send notification
    $db->sendNotification($post_id, "A helper has shown interest in your work post.");
    
    // For AJAX requests
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        // Get updated stats to return to client - pass helper email
        $pending_requests = $db->getPendingRequests($helper_email);
        echo json_encode([
            'success' => true,
            'pending_requests' => $pending_requests
        ]);
        exit;
    }
        
    // For normal form submission, redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle cancel help request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_help_post_id'])) {
    $post_id = $_POST['cancel_help_post_id'];
    // Remove helper assignment (using both functions)
    $db->removePendingRequests($post_id); // Set status to cancelled
    $db->unassignHelper($post_id);         // Remove helper email assignment
    // Send notification about cancellation
    $db->sendNotification($post_id, "A helper has withdrawn their interest in your work post.");
    
    // For AJAX requests
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        // Get updated stats to return to client - pass helper email
        $pending_requests = $db->getPendingRequests($helper_email);
        echo json_encode([
            'success' => true,
            'pending_requests' => $pending_requests
        ]);
        exit;
    }
        
    // For normal form submission, redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle complete task request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['complete_post_id'])) {
    $post_id = $_POST['complete_post_id'];
    // Update task status to 'completed'
    $updateQuery = "UPDATE work_posts SET status = 'completed' WHERE id = ?";
    $stmt = $db->con->prepare($updateQuery);
    $stmt->bind_param("i", $post_id);
    $success = $stmt->execute();
    // For AJAX requests, return updated stats
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        // Get updated stats to return to client - pass helper email
        $completed_tasks = $db->getCompletedTasks($helper_email);
        $pending_requests = $db->getPendingRequests($helper_email);
        echo json_encode([
            'success' => $success,
            'completed_tasks' => $completed_tasks,
            'pending_requests' => $pending_requests
        ]);
        exit;
    }
    
    // Optionally, send a notification to the user
    $db->sendNotification($post_id, "Your work post has been marked as completed.");
    // For normal form submission, redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Add debug info to the page if necessary
$show_debug = isset($_GET['debug']) && $_GET['debug'] == '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Dashboard</title>
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="bg-gradient-to-br from-indigo-50 to-indigo-100 font-inter">
    <div class="max-w-6xl mx-auto p-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h2 class="text-3xl font-semibold mb-4 text-indigo-800">Dashboard</h2>
            <p class="text-gray-600">Overview of your activities and statistics.</p>
        </div>
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8" id="stats-container">
            <!-- Available Requests - now only showing open posts -->
            <div class="bg-white shadow-lg border-l-8 border-indigo-500 rounded-xl p-6">
                <h3 class="text-xl font-semibold text-indigo-700 mb-4 flex items-center gap-2">
                    <i class="ph ph-chart-bar text-indigo-600"></i> Available Requests
                </h3>
                <p class="text-3xl font-semibold" id="total-requests">
                    <?php 
                    // Get current date to filter out expired posts
                    $current_date = date('Y-m-d');
                    // Get only open requests that haven't expired
                    echo htmlspecialchars($db->getTotalRequests($helper_email, $current_date)); 
                    ?>
                </p>
            </div>
            <!-- Pending Requests - filter by helper email -->
            <div class="bg-white shadow-lg border-l-8 border-yellow-500 rounded-xl p-6">
                <h3 class="text-xl font-semibold text-yellow-700 mb-4 flex items-center gap-2">
                    <i class="ph ph-clock text-yellow-600"></i> Your Pending Requests
                </h3>
                <p class="text-3xl font-semibold" id="pending-requests">
                    <?php echo htmlspecialchars($db->getPendingRequests($helper_email)); ?>
                </p>
            </div>
            <!-- Completed Tasks - filter by helper email -->
            <div class="bg-white shadow-lg border-l-8 border-green-500 rounded-xl p-6">
                <h3 class="text-xl font-semibold text-green-700 mb-4 flex items-center gap-2">
                    <i class="ph ph-check-circle text-green-600"></i> Your Completed Tasks
                </h3>
                <p class="text-3xl font-semibold" id="completed-tasks">
                    <?php echo htmlspecialchars($db->getCompletedTasks($helper_email)); ?>
                </p>
            </div>
        </div>
        
        <!-- Debug Info Section (only shown with ?debug=1 parameter) -->
        <?php if ($show_debug): ?>
        <div class="mb-8 p-4 bg-gray-100 border border-gray-300 rounded-lg text-sm font-mono">
            <h4 class="font-bold mb-2">Debug Information</h4>
            <pre><?php echo json_encode($debug_info, JSON_PRETTY_PRINT); ?></pre>
            <p class="mt-2">Helper Email: <?php echo htmlspecialchars($helper_email); ?></p>
            <p>Total posts loaded: <?php echo count($work_posts); ?></p>
        </div>
        <?php endif; ?>
        
        <!-- Work Posts Section with Debug Info -->
        <?php if (empty($work_posts)) : ?>
            <div class="bg-white p-6 rounded-lg shadow mb-8 text-center">
                <div class="inline-block p-4 bg-indigo-50 rounded-full mb-4">
                    <i class="ph ph-clipboard text-4xl text-indigo-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">No work posts available</h3>
                <p class="text-gray-600 mb-4">Sorry, there are currently no available work posts.</p>
                <div class="text-sm text-gray-500">
                    <p><?php echo htmlspecialchars($helper_email); ?></p>
                    <p>Time: <?php echo date('Y-m-d H:i:s'); ?></p>
                    <?php if ($debug_info['total_posts'] > 0): ?>
                    <p class="mt-3">
                        <a href="?debug=1" class="text-blue-600 underline">View Debug Info</a>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php else : ?>
            <!-- Count information with debug link -->
            <div class="bg-white p-4 rounded-lg shadow mb-6">
                <div class="flex justify-between items-center">
                    <p class="text-gray-700">Found <?php echo count($work_posts); ?> available work posts</p>
                    <?php if (!$show_debug): ?>
                        <a href="?debug=1" class="text-xs text-blue-600 underline">Debug</a>
                    <?php else: ?>
                        <a href="?" class="text-xs text-blue-600 underline">Hide Debug</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Work Posts Grid -->
            <div class="grid grid-cols-1 gap-8">
                <?php foreach ($work_posts as $post) : ?>
                    <?php 
                    // Double-check: Skip completed or accepted/pending posts
                    if (isset($post['status']) && ($post['status'] === 'completed' || $post['status'] === 'pending_approval' || $post['status'] === 'accepted')) continue;
                    
                    $isHelperAssigned = $db->isHelperAssigned($post['id'], $helper_email);
                    $postStatus = $post['status'] ?? 'open';
                    $isAvailable = $post['is_available'] ?? true;
                    $isOpen = $post['is_open'] ?? ($postStatus === 'open');
                    
                    // Determine border color based on availability and open status
                    $borderColorClass = $isOpen 
                        ? ($isAvailable ? 'border-green-500' : 'border-orange-400')
                        : 'border-indigo-500';
                        
                    // Background highlight for open posts
                    $bgHighlightClass = $isOpen && $isAvailable ? 'bg-green-50' : 'bg-white';
                    ?>
                    <div id="post-<?php echo $post['id']; ?>" data-open="<?php echo $isOpen ? 'true' : 'false'; ?>" data-available="<?php echo $isAvailable ? 'true' : 'false'; ?>"
                        class="relative <?php echo $bgHighlightClass; ?> shadow-lg border-l-8 <?php echo $borderColorClass; ?> rounded-xl overflow-hidden transform hover:scale-105 hover:shadow-xl transition-all duration-300 ease-in-out">
                        <div class="p-8">
                            <!-- Status badges container with fixed positioning -->
                            <div class="absolute top-4 right-4 flex flex-col gap-2 z-10">
                                <?php if ($isOpen && $isAvailable): ?>
                                    <div class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full flex items-center gap-1 shadow-sm">
                                        <i class="ph ph-door-open"></i> Open for Help
                                    </div>
                                <?php elseif ($isOpen && !$isAvailable): ?>
                                    <div class="px-3 py-1 bg-orange-100 text-orange-800 text-xs font-semibold rounded-full flex items-center gap-1 shadow-sm">
                                        <i class="ph ph-clock"></i> Opens on: <?php echo date("d M Y", strtotime($post['available_from'])); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($isHelperAssigned && $postStatus == 'pending_approval'): ?>
                                    <div class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full flex items-center gap-1 shadow-sm">
                                        <i class="ph ph-hourglass"></i> Request Sent
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Post header with highlighted status for open posts -->
                            <div class="mb-6">
                                <h3 class="text-2xl font-semibold <?php echo $isOpen && $isAvailable ? 'text-green-700' : 'text-indigo-700'; ?> flex items-center gap-2">
                                    <?php if ($isOpen && $isAvailable): ?>
                                        <span class="p-1 rounded-full bg-green-100 text-green-700">
                                            <i class="ph ph-fire text-xl"></i>
                                        </span>
                                    <?php else: ?>
                                        <i class="ph ph-briefcase text-indigo-600"></i>
                                    <?php endif; ?>
                                    Work Opportunity
                                </h3>
                                <?php if ($isOpen && $isAvailable): ?>
                                    <div class="mt-1 text-sm text-green-600 flex items-center gap-1">
                                        <i class="ph ph-check-circle"></i> Available now and open for applications
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Post Details -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-gray-800">
                                <div class="flex items-center gap-3">
                                    <i class="ph ph-user text-indigo-600"></i>
                                    <strong>Name:</strong> <?php echo htmlspecialchars($post['name']); ?>
                                </div>
                                <div class="flex items-center gap-3">
                                    <i class="ph ph-phone text-indigo-600"></i>
                                    <strong>Mobile No:</strong>
                                    <span class="text-red-500">Hidden</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <i class="ph ph-map-pin text-indigo-600"></i>
                                    <strong>City:</strong> <?php echo htmlspecialchars($post['city']); ?>
                                </div>
                                <div class="flex items-center gap-3">
                                    <i class="ph ph-gear text-indigo-600"></i>
                                    <strong>Work:</strong> <?php echo htmlspecialchars($post['work']); ?>
                                </div>
                                <div class="flex items-center gap-3">
                                    <i class="ph ph-calendar text-indigo-600"></i>
                                    <strong>Deadline:</strong> <?php echo htmlspecialchars($post['deadline']); ?>
                                </div>
                                <div class="flex items-center gap-3">
                                    <i class="ph ph-currency-circle-rupee text-indigo-600"></i>
                                    <strong>Work Reward:</strong>
                                    <span class="text-green-600 font-bold"><?php echo htmlspecialchars($post['reward']); ?></span>
                                </div>
                                <div class="col-span-2 flex items-start gap-3">
                                    <i class="ph ph-chat-circle-dots text-indigo-600"></i>
                                    <strong>Message:</strong>
                                    <span class="italic text-gray-600">
                                        <?php 
                                        // Check if message field exists, otherwise use description or show a default message
                                        if (isset($post['message']) && !empty($post['message'])) {
                                            echo nl2br(htmlspecialchars($post['message']));
                                        } elseif (isset($post['description']) && !empty($post['description'])) {
                                            echo nl2br(htmlspecialchars($post['description']));
                                        } else {
                                            echo "No additional details provided";
                                        }
                                        ?>
                                    </span>
                                </div>
                                
                                <!-- Replace the unconditional location display with this conditional code -->
                                <?php if (!empty($post['from_location']) && !empty($post['to_location'])): ?>
                                <div class="flex items-center gap-3">
                                    <i class="ph ph-map-trifold text-blue-600"></i>
                                    <strong>From Location:</strong> <?php echo htmlspecialchars($post['from_location']); ?>
                                </div>
                                <div class="flex items-center gap-3">
                                    <i class="ph ph-map-trifold text-red-600"></i>
                                    <strong>To Location:</strong> <?php echo htmlspecialchars($post['to_location']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Post Footer with updated UI for open posts -->
                            <div class="mt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                                <span class="text-gray-700 text-sm flex items-center gap-2">
                                    <i class="ph ph-clock text-indigo-600"></i> Posted on: <?php echo date("d M Y", strtotime($post['created_at'] ?? 'now')); ?>
                                </span>
                                <div class="help-button-container">
                                    <?php if (!$isHelperAssigned) : ?>
                                        <?php if ($isAvailable) : ?>
                                            <?php if ($isOpen) : ?>
                                            <button onclick="sendHelpRequest(this, <?php echo $post['id']; ?>)"
                                                class="bg-green-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-green-700 transition-all flex items-center gap-2 shadow-md">
                                                <i class="ph ph-handshake"></i> Help With This Task
                                            </button>
                                            <?php else : ?>
                                            <button onclick="sendHelpRequest(this, <?php echo $post['id']; ?>)"
                                                class="bg-indigo-600 text-white font-semibold py-2 px-5 rounded-lg hover:bg-indigo-700 transition-all flex items-center gap-2">
                                                <i class="ph ph-handshake"></i> Help Them
                                            </button>
                                            <?php endif; ?>
                                        <?php else : ?>
                                        <button disabled
                                            class="bg-gray-400 cursor-not-allowed text-white font-semibold py-2 px-5 rounded-lg flex items-center gap-2">
                                            <i class="ph ph-clock"></i> Not Available Yet
                                        </button>
                                        <?php endif; ?>
                                    <?php else : ?>
                                    <button onclick="cancelHelpRequest(this, <?php echo $post['id']; ?>)"
                                        class="bg-red-500 text-white font-semibold py-2 px-5 rounded-lg hover:bg-red-600 transition-all flex items-center gap-2">
                                        <i class="ph ph-x"></i> Cancel Request
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
    // Debug mode to log all steps
    const DEBUG = true;

    function log(...args) {
        if (DEBUG) console.log(...args);
    }

    // Function to send help request
    function sendHelpRequest(button, postId) {
        log('Sending help request for post ID:', postId);

        // Prepare form data
        const formData = new FormData();
        formData.append('help_post_id', postId);

        // Show sending indicator on button
        const originalButtonText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="ph ph-circle-notch ph-spin"></i> Sending...';

        // Send direct POST request instead of AJAX
        fetch('help_them_notification.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            log('Response status:', response.status);
            return response.text().then(text => {
                log('Raw response:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    log('Error parsing JSON:', e);
                    throw new Error('Invalid JSON response: ' + text);
                }
            });
        })
        .then(data => {
            log('Parsed response data:', data);
            // Re-enable button
            button.disabled = false;
            if (data && data.status === 'success') {
                // Get the post element
                const postElement = document.getElementById(`post-${postId}`);
                
                // Update UI to show request sent
                button.innerHTML = '<i class="ph ph-clock"></i> Request Sent';
                button.classList.remove('bg-green-600', 'bg-indigo-600', 'hover:bg-green-700', 'hover:bg-indigo-700');
                button.classList.add('bg-yellow-500', 'hover:bg-yellow-600');
                button.setAttribute('onclick', `cancelHelpRequest(this, ${postId}, ${data.notification_id || 0})`);

                // Add status indicator to the post
                if (postElement) {
                    // Find or create the status badges container
                    let badgesContainer = postElement.querySelector('.absolute.top-4.right-4');
                    if (!badgesContainer) {
                        badgesContainer = document.createElement('div');
                        badgesContainer.className = 'absolute top-4 right-4 flex flex-col gap-2 z-10';
                        postElement.querySelector('.p-8').appendChild(badgesContainer);
                    }
                    
                    // Add the "Request Sent" badge
                    const requestBadge = document.createElement('div');
                    requestBadge.className = 'px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full flex items-center gap-1 shadow-sm';
                    requestBadge.innerHTML = '<i class="ph ph-hourglass"></i> Request Sent';
                    badgesContainer.appendChild(requestBadge);
                }
                
                // Update the pending requests count if available
                if (data.pending_requests !== undefined) {
                    document.getElementById('pending-requests').textContent = data.pending_requests;
                }

                // Show toast notification
                showToast(data.message || "Request sent to user");
            } else {
                // Show error and restore original button
                button.innerHTML = originalButtonText;
                showToast(data.message || "Failed to send request. Please try again.");
            }
        })
        .catch(error => {
            log('Error:', error);
            button.disabled = false;
            button.innerHTML = originalButtonText;
            showToast("Failed to send request. Please try again. Error: " + error.message);
        });
    }

    function cancelHelpRequest(button, postId, notificationId) {
        log('Cancelling help request for post ID:', postId);

        // Prepare form data
        const formData = new FormData();
        formData.append('post_id', postId);

        // Show canceling indicator on button
        const originalButtonText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="ph ph-circle-notch ph-spin"></i> Canceling...';

        // Use the dedicated cancel endpoint instead
        fetch('cancel_help_request.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            log('Response status:', response.status);
            return response.text().then(text => {
                log('Raw response:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    log('Error parsing JSON:', e);
                    throw new Error('Server response is not valid JSON');
                }
            });
        })
        .then(data => {
            log('Parsed response data:', data);
            // Re-enable button
            button.disabled = false;
            if (data && data.success) {
                // Get the post element
                const postElement = document.getElementById(`post-${postId}`);
                const isOpen = postElement?.dataset.open === 'true';
                const isAvailable = postElement?.dataset.available === 'true';
                
                // Update UI back to help button with proper styling based on post status
                if (isOpen && isAvailable) {
                    button.innerHTML = '<i class="ph ph-handshake"></i> Help With This Task';
                    button.classList.remove('bg-red-500', 'hover:bg-red-600');
                    button.classList.add('bg-green-600', 'hover:bg-green-700');
                } else {
                    button.innerHTML = '<i class="ph ph-handshake"></i> Help Them';
                    button.classList.remove('bg-red-500', 'hover:bg-red-600');
                    button.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
                }
                button.setAttribute('onclick', `sendHelpRequest(this, ${postId})`);

                // Remove request sent badge
                if (postElement) {
                    const badgesContainer = postElement.querySelector('.absolute.top-4.right-4');
                    if (badgesContainer) {
                        const requestBadge = Array.from(badgesContainer.children).find(badge => 
                            badge.textContent.includes('Request Sent'));
                        if (requestBadge) {
                            requestBadge.remove();
                        }
                    }
                }

                // Update the pending requests count
                if (data.pending_requests !== undefined) {
                    document.getElementById('pending-requests').textContent = data.pending_requests;
                }

                // Show toast notification
                showToast(data.message || "Help request cancelled successfully");
            } else {
                // Show error and restore original button
                button.innerHTML = originalButtonText;
                showToast(data.message || "Failed to cancel request. Please try again.");
            }
        })
        .catch(error => {
            log('Error:', error);
            button.disabled = false;
            button.innerHTML = originalButtonText;
            showToast("Error: " + error.message);
        });
    }

    function showToast(message) {
        // Create toast if it doesn't exist
        if (!document.getElementById('toast-notification')) {
            const toast = document.createElement('div');
            toast.id = 'toast-notification';
            toast.className = 'fixed bottom-4 right-4 bg-gray-800 text-white px-4 py-2 rounded shadow-lg opacity-0 transition-opacity duration-300 z-50';
            document.body.appendChild(toast);
        }
        const toast = document.getElementById('toast-notification');
        toast.textContent = message;
        toast.classList.remove('opacity-0');
        toast.classList.add('opacity-100');
        setTimeout(() => {
            toast.classList.remove('opacity-100');
            toast.classList.add('opacity-0');
        }, 3000);
    }

    // Add a function to check for accepted requests
    function checkForAcceptedRequests() {
        fetch('check_accepted_requests.php')
            .then(response => {
                return response.json();
            })
            .then(data => {
                if (data.status === 'success' && data.has_accepted) {
                    // Show notification with link to pending page - updated link
                    const message = `${data.accepted_requests.length} request(s) have been accepted! <a href="index.php?page=pending" class="underline font-bold">View pending jobs</a>`;
                    
                    // Create toast if it doesn't exist with HTML support
                    if (!document.getElementById('accepted-toast')) {
                        const toast = document.createElement('div');
                        toast.id = 'accepted-toast';
                        toast.className = 'fixed top-4 center-x left-1/2 transform -translate-x-1/2 bg-green-600 text-white px-6 py-3 rounded shadow-lg z-50';
                        document.body.appendChild(toast);
                    }
                    const toast = document.getElementById('accepted-toast');
                    toast.innerHTML = message;

                    // Auto-hide after 10 seconds
                    setTimeout(() => {
                        toast.style.display = 'none';
                    }, 10000);
                }
            })
            .catch(error => {
                log('Error checking for accepted requests:', error);
            });
    }

    // Check for accepted requests every 30 seconds
    setInterval(checkForAcceptedRequests, 30000);
    
    // Initial check when page loads
    document.addEventListener('DOMContentLoaded', function() {
        // Check once after page loads
        setTimeout(checkForAcceptedRequests, 2000);

        // Check if there are no posts, try to reload after a short delay
        const postsContainer = document.querySelector('.grid-cols-1.gap-8');
        if (postsContainer && postsContainer.children.length === 0) {
            console.log("No posts found, attempting reload in 3 seconds");
            setTimeout(reloadPostsDirectly, 3000);
        }
    });

    // Add a function to direct fetch posts if none are showing
    function reloadPostsDirectly() {
        fetch('get_posts_direct.php')
        .then(response => response.json())
        .then(data => {
            if (data.posts && data.posts.length > 0) {
                location.reload(); // Reload page if posts were found
            }
        });
    }
    </script>
</body>
</html>