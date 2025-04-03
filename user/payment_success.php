<?php
session_start();
require_once("../lib/function.php");

// Check if user is logged in and payment was successful
if (!isset($_SESSION['email']) || !isset($_SESSION['payment_success']) || $_SESSION['payment_success'] !== true) {
    header("Location: index.php");
    exit();
}

// Keep payment_success flag for post_form.php to process
// It will be cleared after post creation

$isEditPayment = isset($_GET['type']) && $_GET['type'] == 'edit';
$paymentInfo = isset($_SESSION['payment_info']) ? $_SESSION['payment_info'] : null;

// Clear success flags after redirection
$_SESSION['payment_success'] = true; // Keep true until post is created
unset($_SESSION['payment_info']); // Clear payment info after showing
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success | ServiceSpire</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-green-500 px-6 py-4">
                <h2 class="text-2xl font-bold text-white text-center">Payment Successful!</h2>
                <p class="text-green-100 text-center mt-1">
                    <?php echo $isEditPayment ? 'Your work post has been updated successfully' : 'Your work post has been published successfully'; ?>
                </p>
            </div>
            
            <div class="px-6 py-8 text-center">
                <div class="mb-6">
                    <div class="w-20 h-20 bg-green-100 rounded-full mx-auto flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-500 text-5xl"></i>
                    </div>
                </div>
                
                <h3 class="text-2xl font-bold text-gray-800 mb-4">
                    <?php echo $isEditPayment ? 'Update Complete!' : 'Thank You!'; ?>
                </h3>
                
                <p class="text-gray-600 mb-6">
                    <?php echo $isEditPayment ? 'Your work post has been updated with the new reward amount.' : 'Your payment has been processed and your work post is now live.'; ?>
                </p>
                
                <!-- Always show countdown -->
                <p class="text-sm text-gray-600 mb-4">Redirecting to dashboard in <span id="countdown">3</span> seconds...</p>
                
                <?php if ($paymentInfo): ?>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6 text-left">
                    <h4 class="font-medium text-gray-700 mb-2">Payment Details:</h4>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-500">Payment ID:</span>
                        <span class="font-medium"><?php echo $paymentInfo['payment_id']; ?></span>
                    </div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-500">Amount:</span>
                        <span class="font-medium">₹<?php echo $paymentInfo['amount']; ?></span>
                    </div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-500">Payment Method:</span>
                        <span class="font-medium"><?php echo ucfirst($paymentInfo['payment_method']); ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Date:</span>
                        <span class="font-medium"><?php echo date('d M Y, h:i A', strtotime($paymentInfo['timestamp'])); ?></span>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="flex flex-col gap-3">
                    <a href="index.php" class="bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-4 rounded-lg font-medium transition duration-200">
                        <i class="fas fa-home mr-2"></i> Go to Dashboard
                    </a>
                </div>
                
                <p class="text-sm text-gray-500 mt-6">
                    A confirmation has been sent to your email address.
                </p>
            </div>
        </div>
    </div>
    
    <script>
    // Clear session storage to prevent back navigation issues
    if (window.history && window.history.pushState) {
        window.history.pushState('', '', window.location.href);
        window.onpopstate = function() {
            window.history.pushState('', '', window.location.href);
        };
    }
    
    // Redirect with cache-busting
    document.addEventListener('DOMContentLoaded', function() {
        let count = 3;
        const countdownElement = document.getElementById('countdown');
        
        const countdownInterval = setInterval(function() {
            count--;
            countdownElement.textContent = count;
            
            if (count <= 0) {
                clearInterval(countdownInterval);
                window.location.href = 'post_form.php?t=' + Date.now();
            }
        }, 1000);
    });
    </script>
</body>
</html>