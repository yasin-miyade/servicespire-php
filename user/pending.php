<?php
// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit();
}

require_once("../lib/function.php");
$db = new db_functions();
$conn = $db->connect();
$user_email = $_SESSION['email'];

// Get pending work posts - modified to ensure assigned_helper_email is not empty
$query = "SELECT * FROM work_posts WHERE email = ? AND status = 'pending' AND assigned_helper_email IS NOT NULL ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

// Get stats
$pending_count = $result->num_rows;

// Get completed count
$completed_query = "SELECT COUNT(*) as count FROM work_posts WHERE email = ? AND status = 'completed'";
$completed_stmt = $conn->prepare($completed_query);
$completed_stmt->bind_param("s", $user_email);
$completed_stmt->execute();
$completed_result = $completed_stmt->get_result()->fetch_assoc();
$completed_count = $completed_result['count'];
?>

<div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    <!-- Page Header/Title -->
    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-gray-900">Pending Work</h1>
        <p class="mt-2 text-gray-600">Your work currently in progress by helpers</p>
    </div>
    
    <!-- Stats overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
            <div class="flex items-center">
                <div class="bg-indigo-100 p-3 rounded-full">
                    <i class="ph ph-hourglass text-indigo-500 text-xl"></i>
                </div>
                <div class="ml-5">
                    <h3 class="text-sm font-medium text-gray-500">Pending Work</h3>
                    <div class="mt-1 text-3xl font-semibold text-gray-900"><?php echo $pending_count; ?></div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="ph ph-check-circle text-green-500 text-xl"></i>
                </div>
                <div class="ml-5">
                    <h3 class="text-sm font-medium text-gray-500">Completed Work</h3>
                    <div class="mt-1 text-3xl font-semibold text-gray-900"><?php echo $completed_count; ?></div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($result->num_rows == 0): ?>
    <!-- Empty state -->
    <div class="bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="inline-block p-4 bg-indigo-50 rounded-full mb-4">
            <i class="ph ph-inbox text-4xl text-indigo-400"></i>
        </div>
        <h2 class="text-xl font-semibold text-gray-800 mb-2">No pending work</h2>
        <p class="text-gray-500 max-w-md mx-auto mb-6">When helpers accept your work requests, they'll appear here so you can track their progress.</p>
        <a href="?page=post_form" class="btn inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
            <i class="ph ph-plus-circle mr-2"></i> Create New Work Post
        </a>
    </div>
    <?php else: ?>
    
    <!-- Section title -->
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-800 pl-4 border-l-4 border-indigo-500">Pending Work (<?php echo $pending_count; ?>)</h2>
    </div>

    <!-- Pending Jobs Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12" id="jobs-container">
        <?php while($row = $result->fetch_assoc()): 
            // Get helper info if available
            $helper_info = null;
            if (!empty($row['assigned_helper_email'])) {
                $helper_query = "SELECT first_name, last_name, mobile FROM helper_sign_up WHERE email = ?";
                $helper_stmt = $conn->prepare($helper_query);
                $helper_stmt->bind_param("s", $row['assigned_helper_email']);
                $helper_stmt->execute();
                $helper_info = $helper_stmt->get_result()->fetch_assoc();
            }
        ?>
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition-all duration-300" id="job-card-<?php echo $row['id']; ?>">
            <!-- Job header with improved status indication -->
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-white border-b border-gray-100">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-bold text-lg text-gray-900 truncate max-w-xs" title="<?php echo htmlspecialchars($row['work'] ?? 'Untitled Work'); ?>">
                            <?php echo htmlspecialchars($row['work'] ?? 'Untitled Work'); ?>
                        </h3>
                        <div class="flex items-center mt-1">
                            <i class="ph ph-calendar-blank text-gray-400 text-xs mr-1"></i>
                            <span class="text-xs text-gray-500">Posted <?php echo date('M j, Y', strtotime($row['created_at'] ?? 'now')); ?></span>
                        </div>
                    </div>
                    <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200 flex items-center">
                        <span class="w-2 h-2 bg-yellow-500 rounded-full mr-1.5 animate-pulse"></span>
                        In Progress
                    </span>
                </div>
            </div>
            
            <!-- Job description with improved layout -->
            <div class="px-6 py-4">
                <div class="mb-4 text-gray-700 text-sm line-clamp-3 h-12">
                    <?php echo htmlspecialchars($row['message'] ?? 'No description provided'); ?>
                </div>
                
                <!-- Job details with clear organization -->
                <div class="space-y-3 border-t border-gray-100 pt-3">
                    <div class="flex items-center text-sm">
                        <div class="w-6 flex justify-center"><i class="ph ph-map-pin text-gray-400"></i></div>
                        <span class="ml-2 text-gray-700 font-medium"><?php echo htmlspecialchars($row['city'] ?? 'Location not specified'); ?></span>
                    </div>
                    
                    <div class="flex items-center text-sm">
                        <div class="w-6 flex justify-center"><i class="ph ph-calendar text-gray-400"></i></div>
                        <span class="ml-2 text-gray-700">Due: <span class="font-medium"><?php echo htmlspecialchars($row['deadline'] ?? 'Not specified'); ?></span></span>
                    </div>
                    
                    <div class="flex items-center text-sm">
                        <div class="w-6 flex justify-center"><i class="ph ph-currency-circle-dollar text-gray-400"></i></div>
                        <span class="ml-2 text-gray-700">Budget: <span class="font-medium">₹<?php echo htmlspecialchars($row['reward'] ?? '0'); ?></span></span>
                    </div>
                    
                    <?php if (!empty($row['from_location']) && !empty($row['to_location'])): ?>
                    <div class="pt-2 mt-2 border-t border-dashed border-gray-100">
                        <div class="flex items-center text-sm">
                            <div class="w-6 flex justify-center"><i class="ph ph-map-trifold text-gray-400"></i></div>
                            <span class="ml-2 text-gray-700">From: <span class="font-medium"><?php echo htmlspecialchars($row['from_location']); ?></span></span>
                        </div>
                        <div class="flex items-center text-sm mt-2">
                            <div class="w-6 flex justify-center"><i class="ph ph-map-trifold text-gray-400"></i></div>
                            <span class="ml-2 text-gray-700">To: <span class="font-medium"><?php echo htmlspecialchars($row['to_location']); ?></span></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($helper_info): ?>
            <!-- Helper info with professional styling -->
            <div class="px-6 py-4 bg-indigo-50 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="bg-indigo-500 rounded-full w-10 h-10 flex items-center justify-center">
                            <span class="text-sm font-medium text-white"><?php echo substr($helper_info['first_name'], 0, 1) . substr($helper_info['last_name'], 0, 1); ?></span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($helper_info['first_name'].' '.$helper_info['last_name']); ?></p>
                            <div class="flex items-center text-xs text-gray-600 mt-1">
                                <i class="ph ph-phone mr-1 text-indigo-500"></i>
                                <span><?php echo htmlspecialchars($helper_info['mobile']); ?></span>
                            </div>
                        </div>
                    </div>
                    <!-- Add button to track helper's location only if location is available -->
                    <?php if (!empty($row['from_location']) && !empty($row['to_location'])): ?>
                    <a href="../user/track_helper.php?helper_email=<?php echo urlencode($row['assigned_helper_email']); ?>&post_id=<?php echo $row['id']; ?>&from=<?php echo urlencode($row['from_location']); ?>&to=<?php echo urlencode($row['to_location']); ?>" 
                       class="btn inline-flex items-center px-3 py-1.5 border border-blue-300 shadow-sm text-xs font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <i class="ph ph-map-pin mr-1.5"></i> Track Location
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Status footer with improved action buttons -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="w-2.5 h-2.5 bg-yellow-400 rounded-full animate-pulse mr-2"></div>
                        <span class="text-sm font-medium text-gray-700">In Progress</span>
                    </div>
                    
                    <div class="flex gap-2">
                        <?php if ($helper_info): ?>
                        <a href="tel:<?php echo htmlspecialchars($helper_info['mobile']); ?>" class="btn inline-flex items-center px-3 py-2 border border-indigo-300 shadow-sm text-xs font-medium rounded-md text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            <i class="ph ph-phone mr-1.5"></i> Call Helper
                        </a>
                        <?php endif; ?>
                        
                        <button onclick="cancelWork(<?php echo $row['id']; ?>)" class="btn inline-flex items-center px-3 py-2 border border-red-200 shadow-sm text-xs font-medium rounded-md text-red-600 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                            <i class="ph ph-x-circle mr-1.5"></i> Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        
        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div id="modal-icon" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i id="modal-icon-symbol" class="ph ph-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Success
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="modal-message">
                                Work has been canceled successfully.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="closeSuccessModal()" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        
        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="ph ph-warning text-red-600 text-xl"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Cancel Work
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Are you sure you want to cancel this work? The helper will be unassigned and the job will be available for others.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="confirmCancelBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Yes, Cancel Work
                </button>
                <button type="button" onclick="closeConfirmationModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    No, Keep It
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add JavaScript for cancel functionality -->
<script>
    let currentPostId = null;
    
    function cancelWork(postId) {
        // Store the post ID for later use
        currentPostId = postId;
        
        // Show confirmation modal instead of browser confirm
        document.getElementById('confirmationModal').classList.remove('hidden');
    }
    
    // Set up the confirmation button click handler
    document.getElementById('confirmCancelBtn').addEventListener('click', function() {
        if (currentPostId !== null) {
            // Close the confirmation modal
            closeConfirmationModal();
            
            // Process the cancellation
            processCancelWork(currentPostId);
        }
    });
    
    function processCancelWork(postId) {
        // Send AJAX request to cancel the work
        fetch('../ajax/user_cancel_work.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'post_id=' + postId
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Remove the work card from the UI
                document.getElementById('job-card-' + postId).remove();
                
                // Update the counters
                const pendingCountEl = document.querySelector('.border-indigo-500 .text-3xl');
                const pendingCount = parseInt(pendingCountEl.textContent) - 1;
                pendingCountEl.textContent = pendingCount;
                
                // Update the title counter
                const sectionTitle = document.querySelector('[class*="border-l-4 border-indigo-500"]');
                if (sectionTitle) {
                    sectionTitle.textContent = `Pending Work (${pendingCount})`;
                }
                
                // Show empty state if no more jobs
                if (pendingCount === 0) {
                    document.getElementById('jobs-container').innerHTML = `
                        <div class="col-span-3 bg-white rounded-lg shadow-lg p-8 text-center">
                            <div class="inline-block p-4 bg-indigo-50 rounded-full mb-4">
                                <i class="ph ph-inbox text-4xl text-indigo-400"></i>
                            </div>
                            <h2 class="text-xl font-semibold text-gray-800 mb-2">No pending work</h2>
                            <p class="text-gray-500 max-w-md mx-auto mb-6">When helpers accept your work requests, they'll appear here so you can track their progress.</p>
                            <a href="?page=post_form" class="btn inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                                <i class="ph ph-plus-circle mr-2"></i> Create New Work Post
                            </a>
                        </div>
                    `;
                }
                
                // Show success modal
                showResultModal('Success', 'Work has been canceled successfully.', 'success');
            } else {
                // Handle specific error case for post not found
                if (data.message && data.message.includes('Post not found or not authorized')) {
                    // Show specialized error modal with more helpful message
                    showResultModal('Cannot Cancel Work', 'This post cannot be canceled. The helper may have already started the work or the post status has changed. Please refresh the page to see the latest status.', 'warning');
                    
                    // Refresh the job card status or hide it after a delay
                    setTimeout(() => {
                        // Try to refresh the page to get latest status
                        window.location.reload();
                    }, 3000);
                } else {
                    // Show general error in modal with specific message
                    const errorMessage = data.message || 'Failed to cancel work.';
                    showResultModal('Error', errorMessage, 'error');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Show error in modal
            showResultModal('Error', 'An error occurred. Please try again.', 'error');
        });
    }
    
    function showResultModal(title, message, type) {
        // Set modal title and message
        document.getElementById('modal-title').textContent = title;
        document.getElementById('modal-message').textContent = message;
        
        // Set appropriate icon based on type
        const iconContainer = document.getElementById('modal-icon');
        const iconSymbol = document.getElementById('modal-icon-symbol');
        
        if (type === 'success') {
            iconContainer.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10';
            iconSymbol.className = 'ph ph-check-circle text-green-600 text-xl';
        } else if (type === 'error') {
            iconContainer.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10';
            iconSymbol.className = 'ph ph-x-circle text-red-600 text-xl';
        } else if (type === 'warning') {
            iconContainer.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10';
            iconSymbol.className = 'ph ph-warning text-yellow-600 text-xl';
        }
        
        // Show modal
        document.getElementById('successModal').classList.remove('hidden');
    }
    
    function closeSuccessModal() {
        document.getElementById('successModal').classList.add('hidden');
    }
    
    function closeConfirmationModal() {
        document.getElementById('confirmationModal').classList.add('hidden');
        currentPostId = null;
    }
</script>
