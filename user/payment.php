<?php
// No whitespace or output before this line
ob_start(); // Start output buffering at the very beginning
session_start();

// Include required files
require_once('../lib/function.php');

// Ensure user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Check payment type and set up variables
$isEdit = isset($_GET['type']) && $_GET['type'] == 'edit';
$postId = isset($_GET['id']) ? $_GET['id'] : null;
$showEditInfo = false;

// Check if we have the right session data
if ($isEdit && isset($_SESSION['edit_post_data']) && isset($_SESSION['amount_difference'])) {
    $postData = $_SESSION['edit_post_data'];
    $amountDifference = $_SESSION['amount_difference'];
    $postFee = (int)$amountDifference;
    $showEditInfo = true;
} elseif (isset($_SESSION['post_data'])) {
    $postData = $_SESSION['post_data'];
    // Use reward amount from the post as the payment amount
    $postFee = (int)preg_replace('/[^\d]/', '', $postData['reward']);
} else {
    // Redirect if no valid data
    header("Location: post_form.php");
    exit();
}

// Set a minimum fee if reward is zero or not a valid number
if ($postFee <= 0) {
    $postFee = 99; // Default minimum fee
}

// Handle payment processing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $paymentMethod = $_POST['payment_method'];
    
    // Process payment based on method
    $paymentSuccessful = false;
    $paymentId = "";
    
    // Mock payment processing (in a real app, integrate with a payment gateway)
    $paymentSuccessful = true;
    $paymentId = 'PAY' . rand(100000, 999999);
    
    if ($paymentSuccessful) {
        // Store payment info in session
        $_SESSION['payment_info'] = [
            'payment_id' => $paymentId,
            'payment_method' => $paymentMethod,
            'amount' => $postFee,
            'timestamp' => date('Y-m-d H:i:s'),
            'is_edit_payment' => $isEdit
        ];
        $_SESSION['payment_success'] = true;
        
        // For edit payment, update the post immediately
        if ($isEdit && isset($_SESSION['edit_post_data'])) {
            $editData = $_SESSION['edit_post_data'];
            $db = new db_functions();
            
            if ($db->updateUserWorkPost(
                $editData['id'],
                $editData['email'],
                $editData['work'],
                $editData['city'],
                $editData['deadline'],
                $editData['reward'],
                $editData['message'],
                $editData['from_location'],
                $editData['to_location']
            )) {
                $_SESSION['success'] = "Work post updated successfully!";
                unset($_SESSION['edit_post_data']);
                unset($_SESSION['amount_difference']);
                
                // Redirect to success page
                header("Location: payment_success.php?type=edit");
                exit();
            } else {
                $errorMessage = "Failed to update post. Payment was processed, but update failed.";
            }
        } else {
            // For new posts, redirect to payment success page
            header("Location: payment_success.php");
            exit();
        }
    } else {
        $errorMessage = "Payment failed. Please try again.";
    }
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment | ServiceSpire</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Razorpay Integration -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-md bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Payment Header -->
            <div class="bg-indigo-700 px-6 py-4">
                <h2 class="text-2xl font-bold text-white text-center">
                    <?php echo $isEdit ? 'Update Payment' : 'Complete Payment'; ?>
                </h2>
                <p class="text-indigo-100 text-center mt-1">
                    <?php echo $isEdit ? 'Pay additional amount for updated work request' : 'Pay posting fee to publish your work request'; ?>
                </p>
            </div>
            
            <div class="px-6 py-5">
                <!-- Order Summary -->
                <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-800 mb-2">Order Summary</h3>
                    
                    <?php if ($showEditInfo): ?>
                    <div class="mb-3 p-2 bg-blue-50 border border-blue-200 rounded text-blue-700 text-sm">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle mt-1 mr-2"></i>
                            <p>You're paying the difference because you increased the reward amount.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="flex justify-between mb-1">
                        <span class="text-gray-600">Work Post Title:</span>
                        <span class="font-medium"><?php echo htmlspecialchars($postData['work']); ?></span>
                    </div>
                    
                    <?php if ($showEditInfo): ?>
                    <div class="flex justify-between mb-1">
                        <span class="text-gray-600">Original Reward:</span>
                        <span class="font-medium">₹<?php echo htmlspecialchars($postData['reward'] - $amountDifference); ?></span>
                    </div>
                    <div class="flex justify-between mb-1">
                        <span class="text-gray-600">New Reward Amount:</span>
                        <span class="font-medium text-green-600">₹<?php echo htmlspecialchars($postData['reward']); ?></span>
                    </div>
                    <div class="flex justify-between mb-1">
                        <span class="text-gray-600">Additional Amount:</span>
                        <span class="font-medium text-indigo-600">₹<?php echo htmlspecialchars($amountDifference); ?></span>
                    </div>
                    <?php else: ?>
                    <div class="flex justify-between mb-1">
                        <span class="text-gray-600">Work Reward Amount:</span>
                        <span class="font-medium text-green-600">₹<?php echo htmlspecialchars($postData['reward']); ?></span>
                    </div>
                    <div class="flex justify-between mb-1">
                        <span class="text-gray-600">Service Fee:</span>
                        <span class="font-medium">₹<?php echo $postFee; ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="flex justify-between pt-2 border-t border-gray-200 mt-2">
                        <span class="text-gray-800 font-medium">Total Amount:</span>
                        <span class="text-indigo-700 font-bold">₹<?php echo $postFee; ?></span>
                    </div>
                </div>
                
                <?php if (isset($errorMessage)): ?>
                    <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-4">
                        <?php echo $errorMessage; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Payment Methods -->
                <form method="POST" id="paymentForm">
                    <div class="space-y-4 mb-6">
                        <!-- Razorpay Payment Option -->
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <div class="p-4 cursor-pointer hover:bg-gray-50 transition payment-method-selector" data-method="razorpay">
                                <label class="flex items-center cursor-pointer w-full">
                                    <input type="radio" name="payment_method" value="razorpay" class="w-5 h-5 text-indigo-600 mr-3 payment-radio">
                                    <div class="flex justify-between w-full">
                                        <span class="text-gray-800 font-medium">Pay with Razorpay</span>
                                        <img src="https://razorpay.com/assets/razorpay-logo.svg" alt="Razorpay" class="h-6">
                                    </div>
                                </label>
                            </div>
                            <!-- Razorpay fields (hidden initially) -->
                            <div id="razorpay-fields" class="hidden payment-fields bg-gray-50 p-4 border-t border-gray-200">
                                <p class="text-gray-700 mb-2">Click "Pay Now" to proceed with Razorpay payment gateway.</p>
                            </div>
                        </div>
                        
                        <!-- UPI Payment Option -->
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <div class="p-4 cursor-pointer hover:bg-gray-50 transition payment-method-selector" data-method="upi">
                                <label class="flex items-center cursor-pointer w-full">
                                    <input type="radio" name="payment_method" value="upi" class="w-5 h-5 text-indigo-600 mr-3 payment-radio">
                                    <div class="flex justify-between w-full">
                                        <span class="text-gray-800 font-medium">Pay with UPI</span>
                                        <div class="flex gap-2">
                                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e1/UPI-Logo-vector.svg/1200px-UPI-Logo-vector.svg.png" alt="UPI" class="h-6">
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <!-- UPI fields (hidden initially) -->
                            <div id="upi-fields" class="hidden payment-fields bg-gray-50 p-4 border-t border-gray-200">
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-medium mb-2">UPI ID <span class="text-red-600">*</span></label>
                                    <input type="text" name="upi_id" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="username@upi">
                                    <p class="text-xs text-gray-500 mt-1">Enter your UPI ID (e.g., yourname@okaxis)</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Card Payment Option -->
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <div class="p-4 cursor-pointer hover:bg-gray-50 transition payment-method-selector" data-method="card">
                                <label class="flex items-center cursor-pointer w-full">
                                    <input type="radio" name="payment_method" value="card" class="w-5 h-5 text-indigo-600 mr-3 payment-radio">
                                    <div class="flex justify-between w-full">
                                        <span class="text-gray-800 font-medium">Pay with Card</span>
                                        <div class="flex gap-2">
                                            <i class="fab fa-cc-visa text-blue-800 text-xl"></i>
                                            <i class="fab fa-cc-mastercard text-red-600 text-xl"></i>
                                            <i class="fab fa-cc-amex text-blue-500 text-xl"></i>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <!-- Card fields (hidden initially) -->
                            <div id="card-fields" class="hidden payment-fields bg-gray-50 p-4 border-t border-gray-200">
                                <div class="mb-3">
                                    <label class="block text-gray-700 text-sm font-medium mb-2">Card Number <span class="text-red-600">*</span></label>
                                    <input type="text" name="card_number" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="XXXX XXXX XXXX XXXX">
                                </div>
                                <div class="grid grid-cols-2 gap-4 mb-3">
                                    <div>
                                        <label class="block text-gray-700 text-sm font-medium mb-2">Expiry Date <span class="text-red-600">*</span></label>
                                        <input type="text" name="card_expiry" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="MM/YY">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 text-sm font-medium mb-2">CVV <span class="text-red-600">*</span></label>
                                        <input type="text" name="card_cvv" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="XXX">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2">Card Holder Name <span class="text-red-600">*</span></label>
                                    <input type="text" name="card_name" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Name on card">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex gap-4">
                        <a href="post_form.php" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 px-4 rounded-lg font-medium transition duration-200 text-center">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back
                        </a>
                        <button type="submit" id="payNowBtn" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-4 rounded-lg font-medium transition duration-200">
                            <i class="fas fa-lock mr-2"></i>
                            Pay Now ₹<?php echo $postFee; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentForm = document.getElementById('paymentForm');
        const paymentMethodSelectors = document.querySelectorAll('.payment-method-selector');
        const paymentRadios = document.querySelectorAll('.payment-radio');
        const paymentFields = document.querySelectorAll('.payment-fields');
        
        // Add click event to the payment method container divs
        paymentMethodSelectors.forEach(selector => {
            selector.addEventListener('click', function() {
                const method = this.dataset.method;
                const radio = this.querySelector('input[type="radio"]');
                
                // Check the radio button
                radio.checked = true;
                
                // Hide all payment fields
                paymentFields.forEach(field => field.classList.add('hidden'));
                
                // Show the selected payment fields
                const selectedFields = document.getElementById(`${method}-fields`);
                if (selectedFields) {
                    selectedFields.classList.remove('hidden');
                }
            });
        });
        
        // Also handle direct radio button clicks
        paymentRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    const method = this.value;
                    
                    // Hide all payment fields
                    paymentFields.forEach(field => field.classList.add('hidden'));
                    
                    // Show the selected payment fields
                    const selectedFields = document.getElementById(`${method}-fields`);
                    if (selectedFields) {
                        selectedFields.classList.remove('hidden');
                    }
                }
            });
        });
        
        // Form submission handler
        paymentForm.addEventListener('submit', function(e) {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
            
            if (!selectedMethod) {
                e.preventDefault();
                alert('Please select a payment method');
                return;
            }
            
            // Validate fields based on selected payment method
            const method = selectedMethod.value;
            
            switch(method) {
                case 'razorpay':
                    e.preventDefault();
                    // Launch Razorpay checkout
                    launchRazorpayCheckout();
                    break;
                    
                case 'upi':
                    const upiId = document.querySelector('input[name="upi_id"]').value.trim();
                    if (!upiId) {
                        e.preventDefault();
                        alert('Please enter your UPI ID');
                        return;
                    }
                    // Additional UPI validation if needed
                    break;
                    
                case 'card':
                    // Validate card fields
                    const cardNumber = document.querySelector('input[name="card_number"]').value.trim();
                    const cardExpiry = document.querySelector('input[name="card_expiry"]').value.trim();
                    const cardCvv = document.querySelector('input[name="card_cvv"]').value.trim();
                    const cardName = document.querySelector('input[name="card_name"]').value.trim();
                    
                    if (!cardNumber || !cardExpiry || !cardCvv || !cardName) {
                        e.preventDefault();
                        alert('Please fill in all card details');
                        return;
                    }
                    
                    // Basic card number format validation
                    if (!/^\d{16}$/.test(cardNumber.replace(/\s/g, ''))) {
                        e.preventDefault();
                        alert('Please enter a valid 16-digit card number');
                        return;
                    }
                    
                    // Basic expiry format validation
                    if (!/^\d{2}\/\d{2}$/.test(cardExpiry)) {
                        e.preventDefault();
                        alert('Please enter a valid expiry date (MM/YY)');
                        return;
                    }
                    
                    // Basic CVV validation
                    if (!/^\d{3}$/.test(cardCvv)) {
                        e.preventDefault();
                        alert('Please enter a valid 3-digit CVV');
                        return;
                    }
                    break;
            }
        });
        
        function launchRazorpayCheckout() {
            // Razorpay options
            const options = {
                key: 'rzp_test_YOUR_KEY_HERE', // Replace with your actual test key
                amount: <?php echo $postFee * 100; ?>, // Amount in paisa
                currency: 'INR',
                name: 'ServiceSpire',
                description: 'Work Post Fee',
                image: 'https://your-logo-url.png', // Replace with your logo URL
                handler: function(response) {
                    // On successful payment, add the payment ID to a hidden field and submit the form
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'razorpay_payment_id';
                    hiddenInput.value = response.razorpay_payment_id;
                    paymentForm.appendChild(hiddenInput);
                    paymentForm.submit();
                },
                prefill: {
                    name: '<?php echo htmlspecialchars($postData['name']); ?>',
                    email: '<?php echo htmlspecialchars($postData['email']); ?>',
                    contact: '<?php echo htmlspecialchars($postData['mobile']); ?>'
                },
                theme: {
                    color: '#4f46e5'
                }
            };
            
            const rzp = new Razorpay(options);
            rzp.open();
        }
        
        // Format card number with spaces
        const cardNumberInput = document.querySelector('input[name="card_number"]');
        if (cardNumberInput) {
            cardNumberInput.addEventListener('input', function(e) {
                let value = this.value.replace(/\D/g, '').substring(0, 16);
                let formattedValue = '';
                
                // Add a space after every 4 digits
                for (let i = 0; i < value.length; i++) {
                    if (i > 0 && i % 4 === 0) {
                        formattedValue += ' ';
                    }
                    formattedValue += value[i];
                }
                
                this.value = formattedValue;
            });
        }
    });
    </script>
</body>
</html>
