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

// Get completed work posts
$query = "SELECT * FROM work_posts WHERE email = ? AND status = 'completed' ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

// Get stats
$completed_count = $result->num_rows;

// Get pending count
$pending_query = "SELECT COUNT(*) as count FROM work_posts WHERE email = ? AND status = 'pending'";
$pending_stmt = $conn->prepare($pending_query);
$pending_stmt->bind_param("s", $user_email);
$pending_stmt->execute();
$pending_result = $pending_stmt->get_result()->fetch_assoc();
$pending_count = $pending_result['count'];
?>

<div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    <!-- Page Header/Title -->
    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-gray-900">Completed Work</h1>
        <p class="mt-2 text-gray-600">Your work that has been successfully completed by helpers</p>
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
            <i class="ph ph-check-square text-4xl text-indigo-400"></i>
        </div>
        <h2 class="text-xl font-semibold text-gray-800 mb-2">No completed work yet</h2>
        <p class="text-gray-500 max-w-md mx-auto mb-6">When helpers finish your work requests, they'll appear here with completion details.</p>
        <a href="?page=post_form" class="btn inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
            <i class="ph ph-plus-circle mr-2"></i> Create New Work Post
        </a>
    </div>
    <?php else: ?>
    
    <!-- Section title -->
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-800 pl-4 border-l-4 border-green-500">Completed Work (<?php echo $completed_count; ?>)</h2>
    </div>

    <!-- Completed Jobs Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        <?php while($row = $result->fetch_assoc()): 
            // Get helper info if available
            $helper_info = null;
            if (!empty($row['assigned_helper_email'])) {
                $helper_query = "SELECT first_name, last_name FROM helper_sign_up WHERE email = ?";
                $helper_stmt = $conn->prepare($helper_query);
                $helper_stmt->bind_param("s", $row['assigned_helper_email']);
                $helper_stmt->execute();
                $helper_info = $helper_stmt->get_result()->fetch_assoc();
            }
        ?>
        <div class="bg-white rounded-lg shadow-lg overflow-hidden transition-shadow hover:shadow-xl">
            <!-- Job header -->
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-bold text-lg text-gray-900"><?php echo htmlspecialchars($row['work'] ?? 'Untitled Work'); ?></h3>
                        <span class="text-sm text-gray-500">Posted on <?php echo date('M j, Y', strtotime($row['created_at'] ?? 'now')); ?></span>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Completed
                    </span>
                </div>
            </div>
            
            <!-- Job description -->
            <div class="px-6 py-4">
                <p class="text-gray-700 text-sm mb-4"><?php echo htmlspecialchars($row['message'] ?? 'No description provided'); ?></p>
                
                <!-- Job details -->
                <div class="space-y-3">
                    <div class="flex items-center text-sm">
                        <i class="ph ph-map-pin w-5 text-gray-400"></i>
                        <span class="text-gray-600"><?php echo htmlspecialchars($row['city'] ?? 'Location not specified'); ?></span>
                    </div>
                    
                    <div class="flex items-center text-sm">
                        <i class="ph ph-calendar w-5 text-gray-400"></i>
                        <span class="text-gray-600">Completed on: <?php echo htmlspecialchars(date('M j, Y', strtotime($row['updated_at'] ?? 'now'))); ?></span>
                    </div>
                    
                    <div class="flex items-center text-sm">
                        <i class="ph ph-currency-inr w-5 text-gray-400"></i>
                        <span class="text-gray-600">Budget: ₹<?php echo htmlspecialchars($row['reward'] ?? '0'); ?></span>
                    </div>
                </div>
            </div>
            
            <?php if ($helper_info): ?>
            <!-- Helper info -->
            <div class="px-6 py-3 bg-gray-50 border-t border-gray-100">
                <div class="flex items-center">
                    <div class="bg-green-100 rounded-full w-10 h-10 flex items-center justify-center">
                        <span class="text-sm font-medium text-green-600"><?php echo substr($helper_info['first_name'], 0, 1) . substr($helper_info['last_name'], 0, 1); ?></span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Completed by: <?php echo htmlspecialchars($helper_info['first_name'].' '.$helper_info['last_name']); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Status footer -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                <div class="flex justify-between items-center">
                    <span class="inline-flex items-center text-sm text-gray-700">
                        <i class="ph ph-check-circle mr-1.5"></i> Status: <span class="font-semibold ml-1 text-green-600">Completed</span>
                    </span>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
</div>
