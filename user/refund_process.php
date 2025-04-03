<?php
session_start();
require_once("../lib/function.php");

// Ensure user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$db = new db_functions();
$refundProcessed = false;
$postUpdated = false;
$errorMessage = '';

// Ensure we have the required data
if (!isset($_SESSION['edit_post_data']) || !isset($_SESSION['refund_amount'])) {
    header("Location: index.php");
    exit();
}

$postData = $_SESSION['edit_post_data'];
$refundAmount = $_SESSION['refund_amount'];
$postId = isset($_GET['id']) ? $_GET['id'] : $postData['id'];

// Handle form submission for bank details
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Collect bank details for refund
    $bankName = $_POST['bank_name'];
    $accountName = $_POST['account_name'];
    $accountNumber = $_POST['account_number'];
    $ifscCode = $_POST['ifsc_code'];
    
    // In a real application, you would:
    // 1. Validate these details
    // 2. Store them securely
    // 3. Integrate with a payment gateway to process the refund
    
    // For this demo, we'll simulate a successful refund
    // and update the post with the new details
    
    // Store refund details in database or send to payment processor
    // This is a placeholder - you would implement actual refund processing logic
    $refundProcessed = true; // Simulate successful refund
    
    if ($refundProcessed) {
        // Update the work post with new details
        $postUpdated = $db->updateUserWorkPost(
            $postData['id'],
            $email,
            $postData['work'],
            $postData['city'],
            $postData['deadline'],
            $postData['reward'],
            $postData['message'],
            $postData['from_location'],
            $postData['to_location']
        );
        
        if ($postUpdated) {
            // Store refund record in database (you would add this function to db_functions.php)
            // $db->storeRefundRecord($postId, $email, $refundAmount, $accountNumber, $ifscCode);
            
            // Clear session data
            unset($_SESSION['edit_post_data']);
            unset($_SESSION['refund_amount']);
            
            $_SESSION['success'] = "Your work post has been updated and a refund of ₹{$refundAmount} has been initiated to your account.";
        } else {
            $errorMessage = "Failed to update the post. Please try again.";
        }
    } else {
        $errorMessage = "Failed to process the refund. Please contact support.";
    }
}

// Get original post data
$post = $db->getWorkPostById($postId);
if (!$post) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Process | ServiceSpire</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-md bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Refund Header -->
            <div class="bg-indigo-700 px-6 py-4">
                <h2 class="text-2xl font-bold text-white text-center">Refund Process</h2>
                <p class="text-indigo-100 text-center mt-1">
                    Your reward amount has decreased, and you're eligible for a refund
                </p>
            </div>
            
            <div class="px-6 py-5">
                <?php if ($postUpdated && $refundProcessed): ?>
                    <!-- Success Message -->
                    <div class="p-6 bg-green-100 border border-green-300 text-green-700 rounded-lg mb-6 text-center">
                        <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
                        <h3 class="text-xl font-bold mb-2">Refund Initiated Successfully!</h3>
                        <p class="mb-2">Your work post has been updated and a refund of <span class="font-semibold">₹<?php echo $refundAmount; ?></span> has been initiated.</p>
                        <p class="text-sm text-gray-600 mb-4">Refunds typically take 3-5 business days to process.</p>
                        <p class="text-sm text-gray-600 mb-4">Redirecting to homepage in <span id="countdown">3</span> seconds...</p>
                        
                        <a href="index.php" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-6 rounded-lg transition duration-200 font-medium">
                            <i class="fas fa-home mr-2"></i> Go to Dashboard
                        </a>
                    </div>

                    <script>
                        // Countdown and redirect
                        document.addEventListener('DOMContentLoaded', function() {
                            let count = 3;
                            const countdownElement = document.getElementById('countdown');
                            
                            const countdownInterval = setInterval(function() {
                                count--;
                                countdownElement.textContent = count;
                                
                                if (count <= 0) {
                                    clearInterval(countdownInterval);
                                    window.location.href = 'index.php';
                                }
                            }, 1000);
                        });
                    </script>
                <?php elseif ($errorMessage): ?>
                    <!-- Error Message -->
                    <div class="p-6 bg-red-100 border border-red-300 text-red-700 rounded-lg mb-6 text-center">
                        <i class="fas fa-exclamation-circle text-red-500 text-5xl mb-4"></i>
                        <h3 class="text-xl font-bold mb-2">Processing Error</h3>
                        <p class="mb-4"><?php echo $errorMessage; ?></p>
                        
                        <a href="index.php" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-6 rounded-lg transition duration-200 font-medium">
                            <i class="fas fa-home mr-2"></i> Return to Dashboard
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Refund Information -->
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-start text-blue-700 mb-2">
                            <i class="fas fa-info-circle mt-1 mr-2 text-blue-500"></i>
                            <p>You've decreased the reward amount for your work post. Please provide your bank details for the refund.</p>
                        </div>
                        
                        <h3 class="text-lg font-medium text-gray-800 mt-3 mb-2">Refund Summary</h3>
                        <div class="flex justify-between mb-1">
                            <span class="text-gray-600">Work Post:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($post['work']); ?></span>
                        </div>
                        <div class="flex justify-between mb-1">
                            <span class="text-gray-600">Original Reward:</span>
                            <span class="font-medium">₹<?php echo htmlspecialchars($post['reward']); ?></span>
                        </div>
                        <div class="flex justify-between mb-1">
                            <span class="text-gray-600">New Reward Amount:</span>
                            <span class="font-medium">₹<?php echo htmlspecialchars($postData['reward']); ?></span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-blue-200 mt-2">
                            <span class="text-gray-800 font-medium">Refund Amount:</span>
                            <span class="text-green-600 font-bold">₹<?php echo $refundAmount; ?></span>
                        </div>
                    </div>
                    
                    <!-- Bank Details Form -->
                    <form method="POST" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $postId; ?>">
                        <div class="space-y-4 mb-6">
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2">Bank Name <span class="text-red-600">*</span></label>
                                <input type="text" name="bank_name" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Enter your bank name">
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2">Account Holder Name <span class="text-red-600">*</span></label>
                                <input type="text" name="account_name" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Enter account holder name">
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2">Account Number <span class="text-red-600">*</span></label>
                                <input type="text" name="account_number" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Enter your account number">
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2">IFSC Code <span class="text-red-600">*</span></label>
                                <input type="text" name="ifsc_code" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Enter IFSC code">
                            </div>
                        </div>
                        
                        <div class="flex gap-4">
                            <a href="index.php" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 px-4 rounded-lg font-medium transition duration-200 text-center">
                                Cancel
                            </a>
                            <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-4 rounded-lg font-medium transition duration-200">
                                <i class="fas fa-check mr-2"></i>
                                Process Refund & Update Post
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
