<?php
// No whitespace or output before this line
ob_start(); // Start output buffering at the very beginning
session_start();
require_once('../lib/function.php');

// Ensure user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Check if payment was successful and post data exists
if (!isset($_SESSION['payment_success']) || !isset($_SESSION['post_data']) || !isset($_SESSION['payment_info'])) {
    header("Location: post_form.php");
    exit();
}

$paymentInfo = $_SESSION['payment_info'];
$postData = $_SESSION['post_data'];

// Insert work post into database
$db = new db_functions();
$success = $db->insertWorkPost(
    $postData['name'],
    $postData['email'],
    $postData['mobile'],
    $postData['city'],
    $postData['work'],
    $postData['deadline'],
    $postData['reward'],
    $postData['message'],
    $postData['from_location'],
    $postData['to_location']
);

// Store the result in session variables
if ($success) {
    $_SESSION['success'] = "Work post created successfully!";
} else {
    $_SESSION['error'] = "Failed to create work post. Please contact support.";
}

// Clear the session data
unset($_SESSION['post_data']);
unset($_SESSION['payment_success']);

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful | ServiceSpire</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-md bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-green-600 px-6 py-8 text-center">
                <div class="bg-white rounded-full h-20 w-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check-circle text-green-600 text-5xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-white">Payment Successful!</h2>
                <p class="text-green-100 mt-2">Your work post has been published.</p>
            </div>
            
            <div class="px-6 py-5">
                <!-- Payment Details -->
                <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-800 mb-3">Payment Details</h3>
                    
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600">Payment ID:</span>
                        <span class="font-medium"><?php echo htmlspecialchars($paymentInfo['payment_id']); ?></span>
                    </div>
                    
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600">Amount Paid:</span>
                        <span class="font-medium">₹<?php echo htmlspecialchars($paymentInfo['amount']); ?></span>
                    </div>
                    
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600">Payment Method:</span>
                        <span class="font-medium"><?php echo htmlspecialchars(ucfirst($paymentInfo['payment_method'])); ?></span>
                    </div>
                    
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600">Date & Time:</span>
                        <span class="font-medium"><?php echo htmlspecialchars($paymentInfo['timestamp']); ?></span>
                    </div>
                </div>
                
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-center">
                    <p class="text-green-800 mb-0">
                        <i class="fas fa-info-circle mr-2"></i>
                        A confirmation email has been sent to your registered email address.
                    </p>
                    <p class="text-green-700 mt-2 font-medium">
                        Redirecting to dashboard in <span id="countdown">3</span> seconds...
                    </p>
                </div>
                
                <div class="flex gap-4">
                    <a href="index.php" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-4 rounded-lg font-medium transition duration-200 text-center">
                        <i class="fas fa-home mr-2"></i>
                        Go to Dashboard
                    </a>
                    <a href="my_posts.php" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 px-4 rounded-lg font-medium transition duration-200 text-center">
                        <i class="fas fa-list mr-2"></i>
                        View My Posts
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Countdown timer for automatic redirect
    let count = 3;
    const countdownElement = document.getElementById('countdown');
    
    // Update countdown every second
    const countdownInterval = setInterval(function() {
        count--;
        if (countdownElement) {
            countdownElement.textContent = count;
        }
        
        // When countdown reaches 0, redirect to dashboard
        if (count <= 0) {
            clearInterval(countdownInterval);
            window.location.href = "index.php"; // Redirect to user dashboard
        }
    }, 1000); // 1 second interval
    </script>
</body>
</html>
