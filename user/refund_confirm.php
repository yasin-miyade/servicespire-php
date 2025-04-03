<?php
require_once('before_index.php');
require_once("../lib/function.php");

if (!isset($_SESSION['email'])) {
    die("Unauthorized access. Please log in first.");
}

$email = $_SESSION['email'];
$db = new db_functions();

if (!isset($_GET['post_id'])) {
    header("Location: index.php");
    exit();
}

$post_id = $_GET['post_id'];

// Get post details
$get_post_query = "SELECT * FROM work_posts WHERE id = ? AND email = ? AND deleted = 0";
$stmt = $db->connect()->prepare($get_post_query);
$stmt->bind_param("is", $post_id, $email);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    $_SESSION['error'] = "Post not found or unauthorized!";
    header("Location: index.php");
    exit();
}

// Check if users table exists, if not create it
$check_table = $db->connect()->query("SHOW TABLES LIKE 'users'");
if ($check_table->num_rows === 0) {
    // Include and execute the SQL script
    $sql = file_get_contents('../database/bank_details.sql');
    $db->connect()->multi_query($sql);
    while ($db->connect()->more_results()) {
        $db->connect()->next_result();
    }
}

// Handle refund and deletion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db->connect()->begin_transaction();
    
    try {
        // Get bank details from form
        $bank_name = $_POST['bank_name'];
        $account_number = $_POST['account_number'];
        $ifsc_code = $_POST['ifsc_code'];
        
        // Update user's bank details first
        $update_bank = "UPDATE users SET bank_name = ?, account_number = ?, ifsc_code = ? WHERE email = ?";
        $bank_stmt = $db->connect()->prepare($update_bank);
        $bank_stmt->bind_param("ssss", $bank_name, $account_number, $ifsc_code, $email);
        
        if ($bank_stmt->execute()) {
            // Process refund
            $refund_query = "UPDATE users SET wallet_balance = wallet_balance + ? WHERE email = ?";
            $refund_stmt = $db->connect()->prepare($refund_query);
            $refund_stmt->bind_param("ds", $post['reward'], $email);
            
            if ($refund_stmt->execute()) {
                if ($db->deleteWorkPost($post_id)) {
                    $db->connect()->commit();
                    echo json_encode(['success' => true, 'message' => "₹" . $post['reward'] . " refunded successfully!"]);
                    exit();
                }
            }
        }
        throw new Exception("Failed to process refund");
    } catch (Exception $e) {
        $db->connect()->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit();
    }
}

// Get existing bank details if any
$bank_query = "SELECT bank_name, account_number, ifsc_code FROM users WHERE email = ?";
$bank_stmt = $db->connect()->prepare($bank_query);
$bank_stmt->bind_param("s", $email);
$bank_stmt->execute();
$bank_details = $bank_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://unpkg.com/phosphor-icons" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-indigo-50 via-white to-purple-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-xl bg-white rounded-2xl shadow-lg p-8">
            <!-- Header with Animation -->
            <div class="text-center mb-8 animate-fade-in">
                <div class="inline-flex p-4 bg-green-100 rounded-full mb-4">
                    <i class="ph-money text-4xl text-green-600"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Refund Processing</h1>
                <div class="mt-2 text-3xl font-bold text-green-600">₹<?php echo htmlspecialchars($post['reward']); ?></div>
            </div>

            <!-- Progress Steps -->
            <div class="flex justify-between mb-8 relative">
                <div class="absolute top-4 left-[20%] w-[60%] h-0.5 bg-gray-200">
                    <div class="progress-bar h-full bg-green-500 w-0 transition-all duration-500"></div>
                </div>
                <div class="flex-1 text-center z-10">
                    <div class="step w-8 h-8 bg-green-500 text-white rounded-full inline-flex items-center justify-center mb-2">1</div>
                    <div class="text-sm font-medium text-gray-600">Details</div>
                </div>
                <div class="flex-1 text-center z-10">
                    <div class="step w-8 h-8 bg-gray-200 text-gray-500 rounded-full inline-flex items-center justify-center mb-2">2</div>
                    <div class="text-sm font-medium text-gray-600">Verify</div>
                </div>
                <div class="flex-1 text-center z-10">
                    <div class="step w-8 h-8 bg-gray-200 text-gray-500 rounded-full inline-flex items-center justify-center mb-2">3</div>
                    <div class="text-sm font-medium text-gray-600">Complete</div>
                </div>
            </div>

            <!-- Multi-step form -->
            <div class="steps-content">
                <!-- Step 1: Enter Details -->
                <div id="step1" class="step-content">
                    <form id="bankDetailsForm" class="space-y-6">
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700">Bank Name</label>
                            <div class="mt-1 relative">
                                <input type="text" name="bank_name" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="Enter bank name">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700">Account Number</label>
                            <div class="mt-1 relative">
                                <input type="text" name="account_number" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="Enter account number">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700">IFSC Code</label>
                            <div class="mt-1 relative">
                                <input type="text" name="ifsc_code" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg uppercase focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="Enter IFSC code">
                            </div>
                        </div>

                        <div class="flex gap-4 mt-8">
                            <a href="index.php" class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-center">
                                Cancel
                            </a>
                            <button type="submit" class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                Next Step
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Step 2: Verify Details -->
                <div id="step2" class="step-content hidden">
                    <div class="space-y-6">
                        <div class="p-6 bg-gray-50 rounded-lg">
                            <h3 class="font-medium text-gray-900 mb-4">Please verify your bank details</h3>
                            <dl class="space-y-4" id="verifyDetails">
                                <!-- Will be filled by JavaScript -->
                            </dl>
                        </div>
                        <div class="flex gap-4">
                            <button onclick="prevStep()" class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                                Back
                            </button>
                            <button onclick="nextStep()" class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                Confirm Details
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Processing -->
                <div id="step3" class="step-content hidden">
                    <div class="text-center py-8">
                        <div class="animate-spin rounded-full h-12 w-12 border-4 border-green-500 border-t-transparent mx-auto mb-4"></div>
                        <p class="text-gray-600">Processing your refund...</p>
                    </div>
                </div>

                <!-- Step 4: Complete -->
                <div id="step4" class="step-content hidden animate-bounce-in">
                    <div class="text-center py-8">
                        <div class="w-20 h-20 bg-green-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                            <i class="ph ph-check-circle text-5xl text-green-500"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Refund Successful!</h3>
                        <p class="text-green-600 text-lg mb-4" id="refundAmount"></p>
                        <p class="text-gray-500">Redirecting to dashboard in <span id="countdown" class="font-bold">3</span> seconds...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.5s ease-out;
    }
    @keyframes bounceIn {
        0% { transform: scale(0.3); opacity: 0; }
        50% { transform: scale(1.05); }
        70% { transform: scale(0.9); }
        100% { transform: scale(1); opacity: 1; }
    }
    .animate-bounce-in {
        animation: bounceIn 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }
    </style>

    <script>
    let currentStep = 1;
    let bankDetails = {};

    $(document).ready(function() {
        $('#bankDetailsForm').on('submit', function(e) {
            e.preventDefault();
            bankDetails = {
                bank_name: $('[name="bank_name"]').val(),
                account_number: $('[name="account_number"]').val(),
                ifsc_code: $('[name="ifsc_code"]').val()
            };
            nextStep();
        });
    });

    function updateProgress() {
        $('.progress-bar').css('width', ((currentStep - 1) * 50) + '%');
        $('.step').each(function(index) {
            if (index + 1 <= currentStep) {
                $(this).removeClass('bg-gray-200 text-gray-500').addClass('bg-green-500 text-white');
            }
        });
    }

    function nextStep() {
        if (currentStep === 1) {
            $('#step1').addClass('hidden');
            $('#step2').removeClass('hidden');
            updateVerificationDetails();
        } else if (currentStep === 2) {
            $('#step2').addClass('hidden');
            $('#step3').removeClass('hidden');
            submitRefund();
        } else if (currentStep === 3) {
            $('#step3').addClass('hidden');
            $('#step4').removeClass('hidden');
        }
        currentStep++;
        updateProgress();
    }

    function prevStep() {
        currentStep--;
        if (currentStep === 1) {
            $('#step2').addClass('hidden');
            $('#step1').removeClass('hidden');
        }
        updateProgress();
    }

    function updateVerificationDetails() {
        const details = `
            <div class="grid grid-cols-3 gap-4 text-sm">
                <div class="font-medium text-gray-500">Bank Name:</div>
                <div class="col-span-2">${bankDetails.bank_name}</div>
                
                <div class="font-medium text-gray-500">Account Number:</div>
                <div class="col-span-2">${bankDetails.account_number}</div>
                
                <div class="font-medium text-gray-500">IFSC Code:</div>
                <div class="col-span-2">${bankDetails.ifsc_code}</div>
            </div>
        `;
        $('#verifyDetails').html(details);
    }

    function submitRefund() {
        $.ajax({
            type: 'POST',
            url: window.location.href,
            data: bankDetails,
            success: function(response) {
                const data = JSON.parse(response);
                if (data.success) {
                    setTimeout(() => {
                        $('#step3').addClass('hidden');
                        $('#step4').removeClass('hidden');
                        $('#refundAmount').text(data.message);
                        startCountdown();
                    }, 1500);
                } else {
                    alert(data.message);
                    location.reload();
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                location.reload();
            }
        });
    }

    function startCountdown() {
        let count = 3;
        const countdown = setInterval(() => {
            count--;
            $('#countdown').text(count);
            if (count <= 0) {
                clearInterval(countdown);
                window.location.href = 'index.php';
            }
        }, 1000);
    }

    function showError(message) {
        alert(message);
    }
    </script>
</body>
</html>
