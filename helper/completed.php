<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ServiceSpire - Completed Jobs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.1/css/all.min.css">
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
    
    // Query for completed jobs
    $query = "SELECT wp.*, su.first_name, su.last_name, su.mobile as phone_number 
              FROM work_posts wp 
              JOIN sign_up su ON wp.email = su.email
              WHERE wp.status = 'completed' AND wp.assigned_helper_email = ? 
              ORDER BY wp.completed_at DESC";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $helper_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Get helper's stats
    $pending_count = $db->getPendingRequests($helper_email);
    $completed_count = $db->getCompletedTasks($helper_email);
    ?>

    <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <!-- Page Header/Title -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-900">Completed Jobs</h1>
            <p class="mt-2 text-gray-600">Your successfully finished tasks</p>
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
                <i class="fas fa-clipboard-check text-4xl text-indigo-400"></i>
            </div>
            <h2 class="text-xl font-semibold text-gray-800 mb-2">No completed jobs yet</h2>
            <p class="text-gray-500 max-w-md mx-auto mb-6">When you complete jobs, they will appear here for your reference.</p>
            <a href="pending.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                <i class="fas fa-clock mr-2"></i> View Pending Jobs
            </a>
        </div>
        <?php else: ?>
        
        <!-- Section title -->
        <div class="mb-6">
            <h2 class="section-title text-xl font-semibold text-gray-800 pl-4">Completed Jobs (<?php echo $completed_count; ?>)</h2>
        </div>

        <!-- Completed Jobs Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <?php while($row = $result->fetch_assoc()): ?>
            <div class="bg-white rounded-lg shadow overflow-hidden transition-shadow hover:shadow-md">
                <!-- Job header -->
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-lg text-gray-900"><?php echo htmlspecialchars($row['work'] ?? 'Untitled Job'); ?></h3>
                            <span class="text-sm text-gray-500">Completed on <?php echo date('M j, Y', strtotime($row['completed_at'] ?? $row['created_at'])); ?></span>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Completed
                        </span>
                    </div>
                </div>
                
                <!-- Job description -->
                <div class="px-6 py-4">
                    <p class="text-gray-700 text-sm mb-4 line-clamp-3"><?php echo htmlspecialchars($row['message'] ?? 'No description provided'); ?></p>
                    
                    <!-- Job details -->
                    <div class="space-y-3">
                        <div class="flex items-center text-sm">
                            <i class="fas fa-map-marker-alt w-5 text-gray-400"></i>
                            <span class="text-gray-600"><?php echo htmlspecialchars($row['city'] ?? 'Location not specified'); ?></span>
                        </div>
                        
                        <div class="flex items-center text-sm">
                            <i class="fas fa-rupee-sign w-5 text-gray-400"></i>
                            <span class="text-gray-600">Earned: ₹<?php echo htmlspecialchars($row['reward'] ?? '0'); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Client info -->
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-100">
                    <div class="flex items-center">
                        <div class="bg-gray-200 rounded-full w-8 h-8 flex items-center justify-center">
                            <span class="text-sm font-medium text-gray-600"><?php echo substr($row['first_name'] ?? 'U', 0, 1) . substr($row['last_name'] ?? 'U', 0, 1); ?></span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['first_name'].' '.$row['last_name']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>