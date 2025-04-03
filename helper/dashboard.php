<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable direct error output

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    header('Content-Type: application/json');
    
    try {
        if (empty($_SESSION['helper_email'])) {
            throw new Exception('Session expired. Please login again.');
        }

        $post_id = intval($_POST['help_post_id']);
        if ($post_id <= 0) {
            throw new Exception('Invalid post ID');
        }
        
        // Log the request attempt
        error_log("Help request attempt - Post ID: $post_id, Helper: $helper_email");
        
        // Check if post exists and is still open
        $post = $db->getWorkPostById($post_id);
        if (!$post) {
            throw new Exception('Post not found');
        }
        
        if ($post['status'] !== 'open' && !empty($post['status'])) {
            throw new Exception('This post is no longer available');
        }

        // Check if post is already assigned
        $isAssigned = $db->isHelperAssigned($post_id, $helper_email);
        if ($isAssigned) {
            throw new Exception('You have already requested this post');
        }

        // Attempt to assign helper
        $success = $db->assignHelper($post_id, $helper_email);
        if (!$success) {
            error_log("Failed to assign helper - Post ID: $post_id, Helper: $helper_email");
            throw new Exception('Failed to send request');
        }

        // Update post status to pending_approval
        $updateQuery = "UPDATE work_posts SET status = 'pending_approval' WHERE id = ?";
        $stmt = $db->con->prepare($updateQuery);
        $stmt->bind_param("i", $post_id);
        $stmt->execute();

        // Send notification to post owner
        $notificationSuccess = $db->sendNotification($post_id, "A helper has shown interest in your work post. Please review and accept/reject the request.");
        if (!$notificationSuccess) {
            error_log("Failed to send notification - Post ID: $post_id");
        }
        
        // Get updated pending requests count
        $pending_requests = $db->getPendingRequests($helper_email);
        
        echo json_encode([
            'success' => true,
            'message' => 'Request sent successfully. Waiting for user approval.',
            'pending_requests' => $pending_requests
        ]);
        
    } catch (Exception $e) {
        error_log("Help request error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Handle cancel help request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_help_post_id'])) {
    header('Content-Type: application/json');
    try {
        $post_id = $_POST['cancel_help_post_id'];
        
        // Check if post exists and helper is assigned
        if (!$db->isHelperAssigned($post_id, $helper_email)) {
            throw new Exception('No active request found for this post');
        }
        
        // Remove helper assignment (using both functions)
        $success1 = $db->removePendingRequests($post_id); // Set status to cancelled
        $success2 = $db->unassignHelper($post_id);        // Remove helper email assignment
        
        if (!$success1 || !$success2) {
            throw new Exception('Failed to cancel the request');
        }
        
        // Send notification about cancellation
        $db->sendNotification($post_id, "A helper has withdrawn their interest in your work post.");
        
        // Get updated stats
        $pending_requests = $db->getPendingRequests($helper_email);
        
        echo json_encode([
            'success' => true,
            'message' => 'Request cancelled successfully',
            'pending_requests' => $pending_requests
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
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
                    <!-- <?php if (!$show_debug): ?>
                        <a href="?debug=1" class="text-xs text-blue-600 underline">Debug</a>
                    <?php else: ?>
                        <a href="?" class="text-xs text-blue-600 underline">Hide Debug</a>
                    <?php endif; ?> -->
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
                            
                            <!-- Post Footer with Help Button -->
                            <div class="mt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                                <span class="text-gray-700 text-sm flex items-center gap-2">
                                    <i class="ph ph-calendar text-indigo-500"></i>
                                    Posted: <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                                </span>
                                <div class="help-button-container">
                                    <?php if ($isHelperAssigned): ?>
                                        <button onclick="cancelHelpRequest(this, <?php echo $post['id']; ?>)" 
                                                class="px-6 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg flex items-center gap-2">
                                            <i class="ph ph-x-circle"></i> Cancel Request
                                        </button>
                                    <?php else: ?>
                                        <button onclick="toggleHelpButton(this, <?php echo $post['id']; ?>)" 
                                                class="help-btn px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg flex items-center gap-2 transition-colors"
                                                data-state="help">
                                            <i class="ph ph-handshake"></i> Help Them
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

    <!-- Cancel Confirmation Modal -->
    <div id="cancelModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 transform transition-transform">
            <div class="text-center">
                <div class="mb-4 text-red-600">
                    <i class="ph ph-warning text-5xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Cancel Help Request</h3>
                <p class="text-gray-600 mb-6">Are you sure you want to cancel this help request? This action cannot be undone.</p>
                <div class="flex justify-center gap-4">
                    <button id="confirmCancel" class="px-6 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg">
                        Yes, Cancel
                    </button>
                    <button onclick="closeModal()" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg">
                        No, Keep It
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Debug mode to log all steps
    const DEBUG = true;

    function log(...args) {
        if (DEBUG) console.log(...args);
    }

    function toggleHelpButton(button, postId) {
        const currentState = button.getAttribute('data-state');
        
        if (currentState === 'help') {
            // Change to cancel state
            button.setAttribute('data-state', 'cancel');
            button.innerHTML = '<i class="ph ph-x-circle"></i> Cancel';
            button.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
            button.classList.add('bg-red-500', 'hover:bg-red-600');
            
            // Call help request function
            sendHelpRequest(postId);
        } else {
            // Change back to help state
            button.setAttribute('data-state', 'help');
            button.innerHTML = '<i class="ph ph-handshake"></i> Help Them';
            button.classList.remove('bg-red-500', 'hover:bg-red-600');
            button.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
            
            // Call cancel request function
            cancelHelpRequest(button, postId);
        }
    }

    function sendHelpRequest(postId) {
        const formData = new FormData();
        formData.append('help_post_id', postId);

        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            log('Help request response:', data); // Debug log
            
            if (data.success) {
                // Add the "Request Sent" badge
                const post = document.getElementById(`post-${postId}`);
                const badgesContainer = post?.querySelector('.absolute.top-4.right-4');
                if (badgesContainer) {
                    const badge = document.createElement('div');
                    badge.className = 'px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full flex items-center gap-1 shadow-sm';
                    badge.innerHTML = '<i class="ph ph-hourglass"></i> Request Sent';
                    badgesContainer.appendChild(badge);
                }

                // Update pending requests count
                const pendingRequestsElement = document.getElementById('pending-requests');
                if (pendingRequestsElement && data.pending_requests !== undefined) {
                    pendingRequestsElement.textContent = data.pending_requests;
                }

                showToast('Request sent successfully! Waiting for user approval.');
                
                // Update button state
                updateButtonState(postId, true);
            } else {
                showToast(data.message || 'Failed to send request');
                // Revert button state if failed
                updateButtonState(postId, false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error occurred while sending request');
            // Revert button state on error
            updateButtonState(postId, false);
        });
    }

    function cancelHelpRequest(button, postId) {
        // Show modal and setup confirmation
        const modal = document.getElementById('cancelModal');
        const confirmBtn = document.getElementById('confirmCancel');
        
        // Show modal with fade in
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('opacity-100'), 10);
        
        // Setup confirm button
        const handleConfirm = () => {
            closeModal();
            processCancelRequest(button, postId);
            // Remove event listener after use
            confirmBtn.removeEventListener('click', handleConfirm);
        };
        
        confirmBtn.addEventListener('click', handleConfirm);
    }

    function closeModal() {
        const modal = document.getElementById('cancelModal');
        modal.classList.remove('opacity-100');
        setTimeout(() => modal.style.display = 'none', 300);
    }

    function processCancelRequest(button, postId) {
        // Show loading state
        button.disabled = true;
        button.innerHTML = '<i class="ph ph-spinner-gap ph-spin"></i> Cancelling...';

        const formData = new FormData();
        formData.append('cancel_help_post_id', postId);

        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the button back to "Help Them"
                button.innerHTML = '<i class="ph ph-handshake"></i> Help Them';
                button.classList.remove('bg-red-500', 'hover:bg-red-600');
                button.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
                button.setAttribute('onclick', `toggleHelpButton(this, ${postId})`);

                // Update stats and UI
                updateUIAfterCancel(postId, data);
                
                // Show success message
                showToast(data.message || 'Request cancelled successfully');
                
                // Refresh page after delay
                setTimeout(() => location.reload(), 1500);
            } else {
                button.disabled = false;
                button.innerHTML = '<i class="ph ph-x-circle"></i> Cancel Request';
                showToast(data.message || 'Failed to cancel request');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            button.disabled = false;
            button.innerHTML = '<i class="ph ph-x-circle"></i> Cancel Request';
            showToast('Error occurred while cancelling request');
        });
    }

    function updateUIAfterCancel(postId, data) {
        // Update pending requests count
        const pendingRequestsElement = document.getElementById('pending-requests');
        if (pendingRequestsElement && data.pending_requests !== undefined) {
            pendingRequestsElement.textContent = data.pending_requests;
        }

        // Remove "Request Sent" badge
        const post = document.getElementById(`post-${postId}`);
        const badge = post?.querySelector('.bg-yellow-100');
        if (badge) {
            badge.remove();
        }
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

    // Add this helper function
    function updateButtonState(postId, isRequested) {
        const post = document.getElementById(`post-${postId}`);
        const button = post?.querySelector('button');
        if (button) {
            if (isRequested) {
                button.innerHTML = '<i class="ph ph-x-circle"></i> Cancel Request';
                button.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
                button.classList.add('bg-red-500', 'hover:bg-red-600');
                button.setAttribute('onclick', `cancelHelpRequest(this, ${postId})`);
            } else {
                button.innerHTML = '<i class="ph ph-handshake"></i> Help Them';
                button.classList.remove('bg-red-500', 'hover:bg-red-600');
                button.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
                button.setAttribute('onclick', `toggleHelpButton(this, ${postId})`);
            }
        }
    }
    </script>
</body>
</html>