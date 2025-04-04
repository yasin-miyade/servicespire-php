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

// Get pending work posts - modified to ensure assigned_helper_email is not empty and include verification_code
$query = "SELECT * FROM work_posts WHERE email = ? AND status = 'pending' AND assigned_helper_email IS NOT NULL ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

// Check if the verification_code column exists
$has_verification = false;
$column_check = $conn->query("SHOW COLUMNS FROM `work_posts` LIKE 'verification_code'");
if ($column_check->num_rows > 0) {
    $has_verification = true;
}

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
            
            // Check if this post has a verification code AND it's still valid (not previously canceled/reassigned)
            // Only consider verification code valid if it's not empty and the post status is still "pending"
            $has_code = $has_verification && !empty($row['verification_code']) && $row['status'] == 'pending';
        ?>
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition-all duration-300" id="job-card-<?php echo $row['id']; ?>">
            <!-- Job header with improved status indication -->
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-white border-b border-gray-100">
                <div class="flex justify-between items-start gap-4">
                    <div class="flex-1 min-w-0"> <!-- Container for title -->
                        <h3 class="font-bold text-lg text-gray-900 break-words" title="<?php echo htmlspecialchars($row['work'] ?? 'Untitled Work'); ?>">
                            <?php echo htmlspecialchars($row['work'] ?? 'Untitled Work'); ?>
                        </h3>
                        <div class="flex items-center mt-1">
                            <i class="ph ph-calendar-blank text-gray-400 text-xs mr-1"></i>
                            <span class="text-xs text-gray-500">Posted <?php echo date('M j, Y', strtotime($row['created_at'] ?? 'now')); ?></span>
                        </div>
                    </div>
                    <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200 flex items-center flex-shrink-0">
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
                        
                        <?php if ($has_code): ?>
                        <button onclick="showVerificationCode(<?php echo $row['id']; ?>, '<?php echo $row['verification_code']; ?>')" class="btn inline-flex items-center px-3 py-2 border border-green-300 shadow-sm text-xs font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                            <i class="ph ph-key mr-1.5"></i> View Code
                        </button>
                        <?php else: ?>
                        <button onclick="generateVerificationCode(<?php echo $row['id']; ?>)" id="generate-code-btn-<?php echo $row['id']; ?>" class="btn inline-flex items-center px-3 py-2 border border-blue-300 shadow-sm text-xs font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="ph ph-key-return mr-1.5"></i> Generate Code
                        </button>
                        <?php endif; ?>
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

<!-- Verification Code Modal -->
<div id="verificationModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        
        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="ph ph-key text-blue-600 text-xl"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Verification Code
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Share this code with your helper when they've completed the job. They'll need it to mark the job as complete.
                            </p>
                            <div class="mt-4 flex justify-center">
                                <div class="py-3 px-8 bg-gray-100 rounded-md">
                                    <span id="verification-code" class="text-2xl font-mono font-bold tracking-wider text-gray-800 select-all"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="closeVerificationModal()" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Function to show verification code
    function showVerificationCode(postId, code) {
        // Set the verification code in the modal
        document.getElementById('verification-code').textContent = code;
        
        // Show the modal
        document.getElementById('verificationModal').classList.remove('hidden');
    }
    
    // Function to close verification modal
    function closeVerificationModal() {
        document.getElementById('verificationModal').classList.add('hidden');
    }
    
    // Function to generate verification code
    function generateVerificationCode(postId) {
        const button = document.getElementById('generate-code-btn-' + postId);
        const originalHTML = button.innerHTML;
        
        // Show loading state
        button.disabled = true;
        button.innerHTML = '<i class="ph ph-spinner ph-spin mr-1.5"></i> Generating...';
        
        // Send AJAX request to generate code
        fetch('../ajax/user_generate_code.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'post_id=' + postId
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Show verification code in modal
                if (data.code) {
                    showVerificationCode(postId, data.code);
                    
                    // Replace the button with a "View Code" button
                    button.innerHTML = '<i class="ph ph-key mr-1.5"></i> View Code';
                    button.classList.remove('bg-blue-50', 'text-blue-700', 'border-blue-300', 'hover:bg-blue-100');
                    button.classList.add('bg-green-50', 'text-green-700', 'border-green-300', 'hover:bg-green-100');
                    
                    // Update onclick handler
                    button.onclick = function() {
                        showVerificationCode(postId, data.code);
                    };
                }
            } else {
                // Restore button and show error
                button.disabled = false;
                button.innerHTML = originalHTML;
                
                // Show error in modal
                showResultModal('Error', data.message || 'Failed to generate verification code.', 'error');
            }
        })
        .catch(error => {
            // Restore button and show error
            button.disabled = false;
            button.innerHTML = originalHTML;
            
            console.error('Error:', error);
            showResultModal('Error', 'An error occurred. Please try again.', 'error');
        });
    }
</script>
