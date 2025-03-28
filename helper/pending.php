<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ServiceSpire - Pending Jobs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.0.3/css/phosphor.min.css" />
    <style>
        body {
            background-color: #f9fafb;
        }
        .section-title {
            position: relative;
            padding-left: 15px;
        }
        .section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background-color: #4f46e5;
            border-radius: 4px;
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 50;
            align-items: center;
            justify-content: center;
        }
        .modal.show {
            display: flex;
        }
        .modal-content {
            animation: modalFade 0.3s;
        }
        @keyframes modalFade {
            from {opacity: 0; transform: translateY(-20px);}
            to {opacity: 1; transform: translateY(0);}
        }
    </style>
</head>
<body class="font-inter">
    <?php
    // session_start();
    require_once("../lib/function.php");
    
    // Check if helper is logged in
    if (!isset($_SESSION['helper_email'])) {
        header("Location: ../login.php");
        exit();
    }
    
    $db = new db_functions();
    $conn = $db->connect();
    $helper_email = $_SESSION['helper_email'];
    
    // Fix the query to use 'sign_up' table instead of 'user_sign_up'
    $query = "SELECT wp.*, su.first_name, su.last_name, su.mobile as phone_number 
              FROM work_posts wp 
              JOIN sign_up su ON wp.email = su.email
              WHERE wp.status = 'pending' AND wp.assigned_helper_email = ? 
              ORDER BY wp.created_at DESC";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $helper_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Get helper's stats
    $pending_count = $result->num_rows;
    $completed_count = $db->getCompletedTasks($helper_email);
    ?>

    <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <!-- Page Header/Title -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-900">Pending Jobs</h1>
            <p class="mt-2 text-gray-600">Your current ongoing tasks</p>
        </div>
        
        <!-- Stats overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
                <div class="flex items-center">
                    <div class="bg-indigo-100 p-3 rounded-full">
                        <i class="fas fa-tasks text-indigo-500"></i>
                    </div>
                    <div class="ml-5">
                        <h3 class="text-sm font-medium text-gray-500">Pending Jobs</h3>
                        <div class="mt-1 text-3xl font-semibold text-gray-900"><?php echo $pending_count; ?></div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-check-circle text-green-500"></i>
                    </div>
                    <div class="ml-5">
                        <h3 class="text-sm font-medium text-gray-500">Completed Jobs</h3>
                        <div class="mt-1 text-3xl font-semibold text-gray-900"><?php echo $completed_count; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($result->num_rows == 0): ?>
        <!-- Empty state -->
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <div class="inline-block p-4 bg-indigo-50 rounded-full mb-4">
                <i class="fas fa-inbox text-4xl text-indigo-400"></i>
            </div>
            <h2 class="text-xl font-semibold text-gray-800 mb-2">No pending jobs found</h2>
            <p class="text-gray-500 max-w-md mx-auto mb-6">When you accept jobs from clients, they will appear here for you to manage.</p>
            <a href="index.php?page=dashboard" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                <i class="fas fa-search mr-2"></i> Browse Available Jobs
            </a>
        </div>
        <?php else: ?>
        
        <!-- Section title -->
        <div class="mb-6">
            <h2 class="section-title text-xl font-semibold text-gray-800 pl-4">Pending Jobs (<?php echo $pending_count; ?>)</h2>
        </div>

        <!-- Pending Jobs Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12" id="jobs-container">
            <?php while($row = $result->fetch_assoc()): ?>
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition-all duration-300" id="job-card-<?php echo $row['id']; ?>">
                <!-- Job header with improved status indication -->
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-white border-b border-gray-100">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-lg text-gray-900 truncate max-w-xs" title="<?php echo htmlspecialchars($row['work'] ?? 'Untitled Job'); ?>">
                                <?php echo htmlspecialchars($row['work'] ?? 'Untitled Job'); ?>
                            </h3>
                            <div class="flex items-center mt-1">
                                <i class="fas fa-calendar-alt text-gray-400 text-xs mr-1"></i>
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
                            <div class="w-6 flex justify-center"><i class="fas fa-map-marker-alt text-gray-400"></i></div>
                            <span class="ml-2 text-gray-700 font-medium"><?php echo htmlspecialchars($row['city'] ?? 'Location not specified'); ?></span>
                        </div>
                        
                        <div class="flex items-center text-sm">
                            <div class="w-6 flex justify-center"><i class="fas fa-calendar-alt text-gray-400"></i></div>
                            <span class="ml-2 text-gray-700">Due: <span class="font-medium"><?php echo htmlspecialchars($row['deadline'] ?? 'Not specified'); ?></span></span>
                        </div>
                        
                        <div class="flex items-center text-sm">
                            <div class="w-6 flex justify-center"><i class="fas fa-rupee-sign text-gray-400"></i></div>
                            <span class="ml-2 text-gray-700">Budget: <span class="font-medium">₹<?php echo htmlspecialchars($row['reward'] ?? '0'); ?></span></span>
                        </div>
                        
                        <?php if (!empty($row['from_location']) && !empty($row['to_location'])): ?>
                        <div class="pt-2 mt-2 border-t border-dashed border-gray-100">
                            <div class="flex items-center text-sm">
                                <div class="w-6 flex justify-center"><i class="fas fa-map-marked-alt text-gray-400"></i></div>
                                <span class="ml-2 text-gray-700">From: <span class="font-medium"><?php echo htmlspecialchars($row['from_location']); ?></span></span>
                            </div>
                            <div class="flex items-center text-sm mt-2">
                                <div class="w-6 flex justify-center"><i class="fas fa-map-marked-alt text-gray-400"></i></div>
                                <span class="ml-2 text-gray-700">To: <span class="font-medium"><?php echo htmlspecialchars($row['to_location']); ?></span></span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Client info with professional styling -->
                <div class="px-6 py-4 bg-indigo-50 border-t border-gray-100">
                    <div class="flex items-center">
                        <div class="bg-indigo-500 rounded-full w-10 h-10 flex items-center justify-center">
                            <span class="text-sm font-medium text-white"><?php echo substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1); ?></span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['first_name'].' '.$row['last_name']); ?></p>
                            <div class="flex items-center text-xs text-gray-600 mt-1">
                                <i class="fas fa-phone mr-1 text-indigo-500"></i>
                                <span><?php echo htmlspecialchars($row['phone_number']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Status footer with improved action buttons -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="w-2.5 h-2.5 bg-yellow-400 rounded-full animate-pulse mr-2"></div>
                            <span class="text-sm font-medium text-gray-700">In Progress</span>
                        </div>
                        
                        <div class="flex gap-2">
                            <a href="tel:<?php echo htmlspecialchars($row['phone_number']); ?>" class="inline-flex items-center px-3 py-2 border border-indigo-300 shadow-sm text-xs font-medium rounded-md text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                <i class="fas fa-phone mr-1.5"></i> Contact Client
                            </a>
                            
                            <?php if (!empty($row['from_location']) && !empty($row['to_location'])): ?>
                            <a href="https://www.google.com/maps/dir/?api=1&origin=<?php echo urlencode($row['from_location'] . ', ' . $row['city']); ?>&destination=<?php echo urlencode($row['to_location'] . ', ' . $row['city']); ?>" 
                               target="_blank" 
                               class="inline-flex items-center px-3 py-2 border border-green-300 shadow-sm text-xs font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                <i class="fas fa-map-pin mr-1.5"></i> View Location
                            </a>
                            <?php elseif (!empty($row['city'])): ?>
                            <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($row['city']); ?>" 
                               target="_blank" 
                               class="inline-flex items-center px-3 py-2 border border-green-300 shadow-sm text-xs font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                <i class="fas fa-map-pin mr-1.5"></i> View Location
                            </a>
                            <?php endif; ?>
                            
                            <button 
                                class="inline-flex items-center px-3 py-2 border border-red-200 shadow-sm text-xs font-medium rounded-md text-red-600 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
                                onclick="cancelJob(<?php echo $row['id']; ?>)"
                            >
                                <i class="fas fa-times-circle mr-1.5"></i> Cancel
                            </button>
                        </div>
                    </div>
                    
                    <!-- Enhanced complete button that stands out more -->
                    <div class="mt-4">
                        <button 
                            class="w-full inline-flex items-center justify-center px-4 py-3 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors"
                            onclick="markAsCompleted(<?php echo $row['id']; ?>)"
                        >
                            <i class="fas fa-check-circle mr-2"></i> Mark Job as Completed
                        </button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Cancel Job Modal -->
    <div id="cancelJobModal" class="modal">
        <div class="modal-content bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-100 rounded-full p-3">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Cancel this job?</h3>
                        </div>
                    </div>
                    <button type="button" onclick="closeModal('cancelJobModal')" class="bg-white rounded-md p-2 inline-flex items-center justify-center text-gray-400 hover:text-gray-500 hover:bg-gray-100">
                        <span class="sr-only">Close</span>
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-gray-600 mb-4">
                        Are you sure you want to cancel this job? This will return the job to the available jobs list.
                    </p>
                    
                    <!-- Message to send to user -->
                    <div>
                        <label for="cancelMessage" class="block text-sm font-medium text-gray-700 mb-1">Message to user:</label>
                        <textarea 
                            id="cancelMessage" 
                            rows="3" 
                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-300 rounded-md p-2" 
                            placeholder="Please provide a reason for cancellation..."
                        ></textarea>
                        
                        <!-- Suggested Messages -->
                        <div class="mt-2">
                            <p class="text-xs text-gray-500 mb-1">Suggested reasons:</p>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" 
                                        onclick="useSuggestedMessage('I am unable to complete this job due to scheduling conflicts.')" 
                                        class="text-xs px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded-md text-gray-700 transition-colors">
                                    Scheduling conflict
                                </button>
                                <button type="button" 
                                        onclick="useSuggestedMessage('I need to cancel due to an unexpected personal emergency.')" 
                                        class="text-xs px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded-md text-gray-700 transition-colors">
                                    Personal emergency
                                </button>
                                <button type="button" 
                                        onclick="useSuggestedMessage('After reviewing the job details, I do not have the required skills/tools for this task.')" 
                                        class="text-xs px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded-md text-gray-700 transition-colors">
                                    Lack required skills
                                </button>
                                <button type="button" 
                                        onclick="useSuggestedMessage('The location is too far from my current area of operation.')" 
                                        class="text-xs px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded-md text-gray-700 transition-colors">
                                    Location issue
                                </button>
                                <button type="button" 
                                        onclick="useSuggestedMessage('I am unable to meet the timeline requirements for this job.')" 
                                        class="text-xs px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded-md text-gray-700 transition-colors">
                                    Cannot meet deadline
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('cancelJobModal')" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        No, Keep Job
                    </button>
                    <button type="button" id="confirmCancelBtn" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Yes, Cancel Job
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Modal handling functions
        function openModal(modalId, postId) {
            document.getElementById(modalId).classList.add('show');
            document.body.style.overflow = 'hidden'; // Prevent scrolling
            
            // Reset the message field
            document.getElementById('cancelMessage').value = '';
            
            // Set the post ID for the confirm button
            const confirmButton = document.getElementById('confirmCancelBtn');
            confirmButton.setAttribute('data-post-id', postId);
            
            // Set up the event listener for the confirm button
            confirmButton.onclick = function() {
                const jobId = this.getAttribute('data-post-id');
                const message = document.getElementById('cancelMessage').value.trim();
                
                // Check if message is entered
                if (message === '') {
                    // Highlight the textarea with a red border
                    const textarea = document.getElementById('cancelMessage');
                    textarea.classList.add('border-red-500', 'ring-1', 'ring-red-500');
                    
                    // Add error message
                    let errorMsg = document.getElementById('message-error');
                    if (!errorMsg) {
                        errorMsg = document.createElement('p');
                        errorMsg.id = 'message-error';
                        errorMsg.className = 'mt-1 text-sm text-red-600';
                        errorMsg.innerText = 'Please provide a reason for cancellation.';
                        textarea.parentNode.appendChild(errorMsg);
                    }
                    
                    // Remove the red highlighting after a delay
                    setTimeout(() => {
                        textarea.addEventListener('input', function() {
                            this.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
                            if (errorMsg) errorMsg.remove();
                        }, { once: true });
                    }, 100);
                    
                    return;
                }
                
                confirmCancelJob(jobId, message);
            };
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
            document.body.style.overflow = ''; // Re-enable scrolling
        }
        
        // Function to mark job as completed
        function markAsCompleted(postId) {
            if (confirm('Are you sure you want to mark this job as complete?')) {
                // Show loading indicator
                const button = event.target.closest('button');
                const originalText = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
                
                // Send ajax request to mark job as completed
                fetch('../ajax/complete_job.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'post_id=' + postId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Show success message with animation
                        const jobCard = document.getElementById('job-card-' + postId);
                        
                        // Add a success overlay
                        const overlay = document.createElement('div');
                        overlay.className = 'absolute inset-0 bg-green-500 bg-opacity-75 flex items-center justify-center text-white text-xl font-bold z-10';
                        overlay.innerHTML = '<div><i class="fas fa-check-circle text-4xl mb-2"></i><p>Job Completed!</p></div>';
                        
                        // Make the job card relative for positioning the overlay
                        jobCard.style.position = 'relative';
                        jobCard.appendChild(overlay);
                        
                        // Update the stats
                        const pendingCountEl = document.querySelector('.border-indigo-500 .text-3xl');
                        const completedCountEl = document.querySelector('.border-green-500 .text-3xl');
                        
                        if (pendingCountEl && completedCountEl) {
                            const pendingCount = parseInt(pendingCountEl.textContent) - 1;
                            const completedCount = parseInt(completedCountEl.textContent) + 1;
                            
                            pendingCountEl.textContent = pendingCount;
                            completedCountEl.textContent = completedCount;
                            
                            // Update section title count
                            const sectionTitle = document.querySelector('.section-title');
                            if (sectionTitle) {
                                sectionTitle.textContent = `Pending Jobs (${pendingCount})`;
                            }
                        }
                        
                        // Remove the job card with animation after 1.5 seconds
                        setTimeout(() => {
                            jobCard.style.transition = 'opacity 0.5s, transform 0.5s';
                            jobCard.style.opacity = '0';
                            jobCard.style.transform = 'scale(0.9)';
                            
                            setTimeout(() => {
                                jobCard.remove();
                                
                                // If no more pending jobs, show empty state
                                const pendingCount = parseInt(pendingCountEl.textContent);
                                if (pendingCount === 0) {
                                    const jobsContainer = document.getElementById('jobs-container');
                                    if (jobsContainer) {
                                        jobsContainer.innerHTML = `
                                        <div class="col-span-full bg-white rounded-lg shadow p-8 text-center">
                                            <div class="inline-block p-4 bg-indigo-50 rounded-full mb-4">
                                                <i class="fas fa-inbox text-4xl text-indigo-400"></i>
                                            </div>
                                            <h2 class="text-xl font-semibold text-gray-800 mb-2">No pending jobs found</h2>
                                            <p class="text-gray-500 max-w-md mx-auto mb-6">When you accept jobs from clients, they will appear here for you to manage.</p>
                                            <a href="dashboard.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                                                <i class="fas fa-search mr-2"></i> Browse Available Jobs
                                            </a>
                                        </div>`;
                                    }
                                }
                            }, 500);
                        }, 1500);
                    } else {
                        // Reset button and show error
                        button.disabled = false;
                        button.innerHTML = originalText;
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    // Reset button and show error
                    button.disabled = false;
                    button.innerHTML = originalText;
                    alert('Error: ' + error);
                });
            }
        }
        
        // Function to show cancel job modal
        function cancelJob(postId) {
            openModal('cancelJobModal', postId);
        }
        
        // Function to handle the actual job cancellation after confirmation
        function confirmCancelJob(postId, message) {
            // Get the button and show loading state
            const confirmButton = document.getElementById('confirmCancelBtn');
            const originalText = confirmButton.innerHTML;
            confirmButton.disabled = true;
            confirmButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Cancelling...';
            
            // Send ajax request to cancel job with message
            fetch('../ajax/cancel_job.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'post_id=' + postId + '&message=' + encodeURIComponent(message)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                // Close the modal first
                closeModal('cancelJobModal');
                
                if (data.status === 'success') {
                    // Remove the job card with animation
                    const jobCard = document.getElementById('job-card-' + postId);
                    if (!jobCard) {
                        throw new Error('Job card element not found. It may have been already removed.');
                    }
                    
                    jobCard.style.transition = 'opacity 0.5s, transform 0.5s';
                    jobCard.style.opacity = '0';
                    jobCard.style.transform = 'scale(0.9)';
                    
                    setTimeout(() => {
                        jobCard.remove();
                        
                        // Update the stats
                        const pendingCountEl = document.querySelector('.border-indigo-500 .text-3xl');
                        
                        if (pendingCountEl) {
                            const pendingCount = parseInt(pendingCountEl.textContent) - 1;
                            pendingCountEl.textContent = pendingCount;
                            
                            // Update section title count
                            const sectionTitle = document.querySelector('.section-title');
                            if (sectionTitle) {
                                sectionTitle.textContent = `Pending Jobs (${pendingCount})`;
                            }
                            
                            // If no more pending jobs, show empty state
                            if (pendingCount === 0) {
                                const jobsContainer = document.getElementById('jobs-container');
                                if (jobsContainer) {
                                    jobsContainer.innerHTML = `
                                    <div class="col-span-full bg-white rounded-lg shadow p-8 text-center">
                                        <div class="inline-block p-4 bg-indigo-50 rounded-full mb-4">
                                            <i class="fas fa-inbox text-4xl text-indigo-400"></i>
                                        </div>
                                        <h2 class="text-xl font-semibold text-gray-800 mb-2">No pending jobs found</h2>
                                        <p class="text-gray-500 max-w-md mx-auto mb-6">When you accept jobs from clients, they will appear here for you to manage.</p>
                                        <a href="dashboard.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                                            <i class="fas fa-search mr-2"></i> Browse Available Jobs
                                        </a>
                                    </div>`;
                                }
                            }
                        }
                        
                        // Show success message
                        const successMessage = document.createElement('div');
                        successMessage.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transition-opacity duration-500';
                        successMessage.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Job cancelled successfully!';
                        document.body.appendChild(successMessage);
                        
                        setTimeout(() => {
                            successMessage.style.opacity = '0';
                            setTimeout(() => successMessage.remove(), 500);
                        }, 3000);
                        
                    }, 500);
                } else {
                    // Show detailed error in a toast message
                    showErrorToast(data.message || 'An unknown error occurred');
                    
                    // Reset button
                    confirmButton.disabled = false;
                    confirmButton.innerHTML = originalText;
                    
                    // If the error indicates the job wasn't found or already processed,
                    // we should refresh the page to get the latest job list
                    if (data.message && (
                        data.message.includes('not found') || 
                        data.message.includes('not assigned to you') ||
                        data.message.includes('not in pending status')
                    )) {
                        setTimeout(() => {
                            showInfoToast('Refreshing page to update job list...');
                            setTimeout(() => window.location.reload(), 1500);
                        }, 1000);
                    }
                }
            })
            .catch(error => {
                // Close the modal
                closeModal('cancelJobModal');
                
                // Reset button
                confirmButton.disabled = false;
                confirmButton.innerHTML = originalText;
                
                // Show error in a toast message
                showErrorToast('Error: ' + error.message);
                console.error('Error cancelling job:', error);
            });
        }
        
        // Helper function to show error toast
        function showErrorToast(message) {
            const errorMessage = document.createElement('div');
            errorMessage.className = 'fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg transition-opacity duration-500 z-50';
            errorMessage.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i> ' + message;
            document.body.appendChild(errorMessage);
            
            setTimeout(() => {
                errorMessage.style.opacity = '0';
                setTimeout(() => errorMessage.remove(), 500);
            }, 5000);
        }
        
        // Helper function to show info toast
        function showInfoToast(message) {
            const infoMessage = document.createElement('div');
            infoMessage.className = 'fixed bottom-4 right-4 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg transition-opacity duration-500 z-50';
            infoMessage.innerHTML = '<i class="fas fa-info-circle mr-2"></i> ' + message;
            document.body.appendChild(infoMessage);
            
            setTimeout(() => {
                infoMessage.style.opacity = '0';
                setTimeout(() => infoMessage.remove(), 500);
            }, 3000);
        }
        
        // Function to use a suggested message
        function useSuggestedMessage(message) {
            const textarea = document.getElementById('cancelMessage');
            textarea.value = message;
            
            // Remove any error styling
            textarea.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
            
            // Remove any error message
            const errorMsg = document.getElementById('message-error');
            if (errorMsg) errorMsg.remove();
            
            // Focus the textarea and place cursor at end
            textarea.focus();
            textarea.setSelectionRange(message.length, message.length);
        }
    </script>
</body>
</html>