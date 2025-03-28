<?php
session_start();
require_once("../lib/function.php");

// Check if helper is logged in
if (!isset($_SESSION['helper_email'])) {
    echo "<p class='text-red-500'>Unauthorized access</p>";
    exit;
}

// Check if user_id is provided
if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    echo "<p class='text-red-500'>User ID is required</p>";
    exit;
}

$user_id = intval($_GET['user_id']);
$db = new db_functions();

// Get user details
$user = $db->get_user_by_id($user_id);

if (!$user) {
    echo "<p class='text-red-500'>User not found</p>";
    exit;
}

// Calculate age from DOB if available
$age = "";
if (!empty($user['dob'])) {
    $birthdate = new DateTime($user['dob']);
    $now = new DateTime();
    $age = $now->diff($birthdate)->y;
}

// Count only completed posts
$completed_posts = 0;
$conn = $db->connect();
$completed_query = "SELECT COUNT(*) as count FROM work_posts WHERE email = ? AND status = 'completed'";
$stmt = $conn->prepare($completed_query);
if ($stmt) {
    $stmt->bind_param("s", $user['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $completed_posts = $row['count'];
    }
    $stmt->close();
}

// Calculate registration date
$registration_date = "";
if (!empty($user['created_at'])) {
    $date = new DateTime($user['created_at']);
    $registration_date = $date->format('F j, Y');
}

// Generate HTML for user profile - matching the helper profile design
?>

<div class="bg-white rounded-lg overflow-hidden">
    <!-- Header with profile photo and name -->
    <div class="relative bg-gradient-to-r from-indigo-600 to-purple-700 text-white p-6 pb-28">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                <p class="text-indigo-100 mt-1">Service User</p>
            </div>
            <?php if (!empty($age)): ?>
            <div class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full">
                <p class="text-white text-sm font-medium"><?php echo $age; ?> years old</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="relative px-6">
        <div class="absolute -top-16 left-6">
            <?php if (!empty($user['profile_photo'])): ?>
                <?php
                // Process the profile photo path
                $photo_path = $user['profile_photo'];
                
                // Fix common path issues
                if (strpos($photo_path, '../') === 0) {
                    // Remove the first "../" if it exists
                    $photo_path = substr($photo_path, 3);
                }
                
                // Ensure path includes base directory if needed
                if (strpos($photo_path, '/') !== 0 && strpos($photo_path, 'http') !== 0) {
                    $photo_path = "../" . $photo_path;
                }
                
                error_log("Final user photo path: $photo_path");
                ?>
                <img src="<?= htmlspecialchars($photo_path) ?>" 
                     alt="<?= htmlspecialchars($user['first_name']) ?>'s Photo" 
                     class="h-32 w-32 rounded-full object-cover border-4 border-white shadow-lg"
                     onerror="this.onerror=null; this.src='../assets/images/default_user.jpg'; console.log('Error loading image, using default');">
            <?php else: ?>
                <div class="h-32 w-32 rounded-full bg-gradient-to-br from-indigo-100 to-purple-200 flex items-center justify-center text-indigo-500 text-4xl border-4 border-white shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="pt-20 pb-4">
            <!-- Status Box - Only showing completed posts -->
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg p-4 mb-4 border border-indigo-100 flex items-center justify-between">
                <div class="flex items-center">
                    <div class="bg-indigo-500 h-3 w-3 rounded-full mr-2"></div>
                    <span class="text-gray-700 font-medium">Active User</span>
                </div>
                <?php if($completed_posts > 0): ?>
                <div class="flex space-x-2">
                    <div class="bg-green-100 px-3 py-1 rounded-full">
                        <span class="text-green-800 font-medium text-sm"><?php echo $completed_posts; ?> completed</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Contact Information -->
            <div class="bg-white rounded-lg shadow-sm p-4 mb-4 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Contact Information
                </h3>
                <div class="space-y-2 text-gray-700">
                    <p class="flex items-center">
                        <span class="font-medium w-20">Email:</span>
                        <span class="text-indigo-600"><?php echo htmlspecialchars($user['email']); ?></span>
                    </p>
                    <p class="flex items-center">
                        <span class="font-medium w-20">Mobile:</span>
                        <span><?php echo htmlspecialchars($user['mobile'] ?? 'Not provided'); ?></span>
                    </p>
                </div>
            </div>
            
            <!-- Personal Information -->
            <div class="bg-white rounded-lg shadow-sm p-4 mb-4 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Personal Details
                </h3>
                <div class="space-y-2 text-gray-700">
                    <p class="flex items-center">
                        <span class="font-medium w-20">Gender:</span>
                        <span><?php echo htmlspecialchars($user['gender'] ?? 'Not specified'); ?></span>
                    </p>
                    <?php if (!empty($registration_date)): ?>
                    <p class="flex items-center">
                        <span class="font-medium w-20">Member since:</span>
                        <span><?php echo $registration_date; ?></span>
                    </p>
                    <?php endif; ?>
                    <?php if (!empty($user['address'])): ?>
                    <p class="flex items-start">
                        <span class="font-medium w-20">Address:</span>
                        <span class="flex-1"><?php echo htmlspecialchars($user['address']); ?></span>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($user['bio'])): ?>
            <!-- About Section -->
            <div class="bg-white rounded-lg shadow-sm p-4 mb-4 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    About Me
                </h3>
                <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
            </div>
            <?php endif; ?>
            
            <!-- Contact Button -->
            <div class="mt-6">
                <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 rounded-lg text-center transition duration-200">
                    Contact User
                </a>
            </div>
        </div>
    </div>
</div>
