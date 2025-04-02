<?php
// No whitespace or output before this line
ob_start(); // Start output buffering at the very beginning
// session_start(); // Make sure session is started

// Include required files
require_once('../lib/function.php');

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email']; // Get logged-in user's email

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $city = trim($_POST['city']);
    $work = trim($_POST['work']);
    $deadline = trim($_POST['deadline']);
    $reward = trim($_POST['reward']);
    $message = trim($_POST['message']);
    $from_location = isset($_POST['from_location']) ? trim($_POST['from_location']) : '';
    $to_location = isset($_POST['to_location']) ? trim($_POST['to_location']) : '';

    // Store form data in session for later use after payment
    $_SESSION['post_data'] = [
        'name' => $name,
        'email' => $email,
        'mobile' => $mobile,
        'city' => $city,
        'work' => $work,
        'deadline' => $deadline,
        'reward' => $reward,
        'message' => $message,
        'from_location' => $from_location,
        'to_location' => $to_location,
    ];

    // Redirect to payment page
    header("Location: payment.php");
    exit();
}

// If returning from payment_success.php with success flag
if (isset($_SESSION['payment_success']) && $_SESSION['payment_success'] === true && isset($_SESSION['post_data'])) {
    $postData = $_SESSION['post_data'];
    $db = new db_functions();
    
    if ($db->insertWorkPost(
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
    )) {
        $_SESSION['success'] = "Work posted successfully!";
        // Clear the session data
        unset($_SESSION['post_data']);
        unset($_SESSION['payment_success']);
        // Success message will be displayed via JavaScript
    } else {
        $_SESSION['error'] = "Work post failed!";
        // Clear the session data
        unset($_SESSION['post_data']);
        unset($_SESSION['payment_success']);
    }
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a New Work Request</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-step {
            transition: all 0.3s ease;
        }
        .step-indicator {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-2xl bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Form Header -->
            <div class="bg-indigo-700 px-6 py-4">
                <h2 class="text-2xl font-bold text-white text-center">Create a Work Post</h2>
                <p class="text-indigo-100 text-center mt-1">Fill out the details to post your work request</p>
            </div>
            
            <!-- Step Indicators -->
            <div class="flex justify-between px-6 pt-5 pb-3">
                <div class="flex flex-col items-center">
                    <div id="step1-indicator" class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold mb-2">
                        1
                    </div>
                    <span class="text-sm text-gray-600">Profile</span>
                </div>
                <div class="relative flex items-center flex-1 mx-4">
                    <div class="border-t-2 border-indigo-200 flex-1"></div>
                </div>
                <div class="flex flex-col items-center">
                    <div id="step2-indicator" class="w-10 h-10 rounded-full bg-indigo-200 flex items-center justify-center text-gray-700 font-bold mb-2">
                        2
                    </div>
                    <span class="text-sm text-gray-600">Details</span>
                </div>
                <div class="relative flex items-center flex-1 mx-4">
                    <div class="border-t-2 border-indigo-200 flex-1"></div>
                </div>
                <div class="flex flex-col items-center">
                    <div id="step3-indicator" class="w-10 h-10 rounded-full bg-indigo-200 flex items-center justify-center text-gray-700 font-bold mb-2">
                        3
                    </div>
                    <span class="text-sm text-gray-600">Location</span>
                </div>
            </div>

            <!-- Main Form Container -->
            <div class="px-6 py-5">
                <!-- Success Message (initially hidden) -->
                <div id="successMessage" class="hidden bg-green-100 border border-green-300 text-green-700 px-6 py-8 rounded-lg mb-6 text-center">
                    <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Work Posted Successfully!</h3>
                    <p class="mb-2">Your work request has been submitted.</p>
                    <p class="text-sm text-gray-600">Redirecting to homepage in <span id="countdown">3</span> seconds...</p>
                </div>

                <!-- Form -->
                <form id="multiStepForm" method="POST" class="<?php echo ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['success'])) ? 'hidden' : ''; ?>">
                    <!-- Step 1: Personal Information -->
                    <div class="form-step" id="step1">
                        <div class="mb-5">
                            <label class="block text-gray-700 font-medium mb-2">Full Name <span class="text-red-600">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" name="name" required class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Enter your full name" />
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="block text-gray-700 font-medium mb-2">Email Address <span class="text-red-600">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" name="email" required readonly class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed" value="<?php echo $email; ?>" />
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Email is pre-filled from your account <span class="text-red-600">*</span></p>
                        </div>

                        <div class="mb-5">
                            <label class="block text-gray-700 font-medium mb-2">Mobile Number <span class="text-red-600">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <input type="tel" id="mobileInput" name="mobile" required class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Enter your mobile number" oninput="validateMobileNumber(this)" />
                            </div>
                            <p id="mobileError" class="text-red-500 text-xs mt-1 hidden">Please enter only numbers in the mobile field</p>
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 font-medium mb-2">City <span class="text-red-600">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                                    <i class="fas fa-map-marker-alt"></i>
                                </span>
                                <input type="text" name="city" required class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Enter your city" />
                            </div>
                        </div>

                        <button type="button" onclick="nextStep()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-4 rounded-lg font-medium transition duration-200 transform hover:scale-[1.02]">
                            Continue to Work Details
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>

                    <!-- Step 2: Work Details -->
                    <div class="form-step hidden" id="step2">
                        <div class="mb-5">
                            <label class="block text-gray-700 font-medium mb-2">Work Title <span class="text-red-600">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                                    <i class="fas fa-briefcase"></i>
                                </span>
                                <input type="text" name="work" required class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Enter work title" />
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="block text-gray-700 font-medium mb-2">Deadline <span class="text-red-600">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                                    <i class="fas fa-calendar-alt"></i>
                                </span>
                                <input type="date" name="deadline" id="deadline" required class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Select today or a future date</p>
                        </div>

                        <div class="mb-5">
                            <label class="block text-gray-700 font-medium mb-2">Reward/Budget <span class="text-red-600">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                                    <i class="fas fa-rupee-sign"></i>
                                </span>
                                <input type="number" name="reward" required class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Enter reward amount" />
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="block text-gray-700 font-medium mb-2">Work Description <span class="text-red-600">*</span></label>
                            <div class="relative">
                                <textarea name="message" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 min-h-32" placeholder="Describe the work requirements in detail..."></textarea>
                            </div>
                        </div>

                        <!-- Checkbox to enable location fields -->
                        <div class="flex items-center gap-3 p-3 border border-indigo-100 bg-indigo-50 rounded-lg mb-6">
                            <input type="checkbox" id="enableLocations" class="w-5 h-5 text-indigo-600 focus:ring-indigo-500 rounded" onchange="toggleLocationStep()">
                            <label for="enableLocations" class="text-gray-800 font-medium">
                                <i class="fas fa-map-marked-alt text-indigo-500 mr-2"></i>
                                This work involves transportation (from one location to another)
                            </label>
                        </div>

                        <div class="flex justify-between gap-4">
                            <button type="button" onclick="prevStep()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 px-4 rounded-lg font-medium transition duration-200">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back
                            </button>
                            <button type="submit" id="submitBtn" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-4 rounded-lg font-medium transition duration-200 transform hover:scale-[1.02]">
                                <i class="fas fa-check mr-2"></i>
                                Submit Post
                            </button>
                            <button type="button" id="nextBtn" onclick="nextStep()" class="flex-1 hidden bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-4 rounded-lg font-medium transition duration-200 transform hover:scale-[1.02]">
                                Continue
                                <i class="fas fa-arrow-right ml-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Location Details (Hidden initially) -->
                    <div class="form-step hidden" id="step3">
                        <div class="mb-5">
                            <label class="block text-gray-700 font-medium mb-2">
                                <i class="fas fa-location-arrow text-indigo-500 mr-2"></i>
                                From Location <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="from_location" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Starting location..." />
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 font-medium mb-2">
                                <i class="fas fa-map-pin text-indigo-500 mr-2"></i>
                                To Location <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="to_location" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Destination location..." />
                        </div>

                        <div class="flex justify-between gap-4">
                            <button type="button" onclick="prevStep()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 px-4 rounded-lg font-medium transition duration-200">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back
                            </button>
                            <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-4 rounded-lg font-medium transition duration-200 transform hover:scale-[1.02]">
                                <i class="fas fa-check mr-2"></i>
                                Submit Post
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    let currentStep = 1;
    const totalSteps = 3;

    // Set minimum date for deadline field to today
    document.addEventListener('DOMContentLoaded', function() {
        // Format today's date as YYYY-MM-DD for the date input min attribute
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        const formattedToday = `${year}-${month}-${day}`;
        
        // Set the min attribute of the deadline field
        document.getElementById('deadline').setAttribute('min', formattedToday);
    });

    // Check if we need to show success message
    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['success'])): ?>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('multiStepForm').classList.add('hidden');
        document.getElementById('successMessage').classList.remove('hidden');
        
        // Countdown and redirect
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
    <?php endif; ?>

    function updateStepIndicators(step) {
        // Reset all indicators
        for (let i = 1; i <= totalSteps; i++) {
            const indicator = document.getElementById(`step${i}-indicator`);
            if (i < step) {
                // Completed steps
                indicator.classList.remove('bg-indigo-200', 'bg-indigo-600');
                indicator.classList.add('bg-green-500');
                indicator.innerHTML = '<i class="fas fa-check"></i>';
            } else if (i === step) {
                // Current step
                indicator.classList.remove('bg-indigo-200', 'bg-green-500');
                indicator.classList.add('bg-indigo-600');
                indicator.textContent = i;
            } else {
                // Future steps
                indicator.classList.remove('bg-indigo-600', 'bg-green-500');
                indicator.classList.add('bg-indigo-200');
                indicator.textContent = i;
            }
        }
    }

    function showStep(step) {
        document.querySelectorAll(".form-step").forEach((el) => el.classList.add("hidden"));
        document.getElementById(`step${step}`).classList.remove("hidden");
        currentStep = step;
        updateStepIndicators(step);
    }

    function nextStep() {
        // Validate mobile number if on step 1 before proceeding
        if (currentStep === 1) {
            const mobileInput = document.getElementById('mobileInput');
            if (!validateMobileNumber(mobileInput)) {
                return false; // Don't proceed if validation fails
            }
        }
        
        if (currentStep === 2) {
            const isChecked = document.getElementById("enableLocations").checked;
            if (isChecked) {
                showStep(3); // Show Step 3 if checkbox is checked
            } else {
                document.getElementById("multiStepForm").submit(); // Submit the form if unchecked
            }
        } else if (currentStep < totalSteps) {
            showStep(currentStep + 1);
        }
    }

    function prevStep() {
        if (currentStep > 1) {
            showStep(currentStep - 1);
        }
    }

    function toggleLocationStep() {
        const isChecked = document.getElementById("enableLocations").checked;
        document.getElementById("nextBtn").classList.toggle("hidden", !isChecked);
        document.getElementById("submitBtn").classList.toggle("hidden", isChecked);
    }

    // Handle form submission with validation
    document.getElementById('multiStepForm').addEventListener('submit', function(e) {
        const currentStepEl = document.getElementById(`step${currentStep}`);
        const requiredFields = currentStepEl.querySelectorAll('[required]');
        
        let isValid = true;
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('border-red-500');
            } else {
                field.classList.remove('border-red-500');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
        }
    });

    // Validate mobile number to ensure it contains only numbers
    function validateMobileNumber(input) {
        // Remove any non-digit characters
        const value = input.value;
        const numbers = value.replace(/\D/g, '');
        const errorElement = document.getElementById('mobileError');
        let isValid = true;
        let errorMessage = '';
        
        // Check if the input contained non-numeric characters
        if (value !== numbers) {
            errorMessage = 'Please enter only numbers in the mobile field';
            isValid = false;
        } else {
            // Validate number length - must be exactly 10 digits
            if (numbers.length !== 10) {
                errorMessage = 'Phone number must be exactly 10 digits';
                isValid = false;
            }
        }
        
        // Update the UI based on validation
        if (!isValid) {
            errorElement.textContent = errorMessage;
            errorElement.classList.remove('hidden');
            input.classList.add('border-red-500');
            input.classList.remove('border-green-500');
            
            // Shake the input field for better feedback
            input.classList.add('animate-shake');
            setTimeout(() => {
                input.classList.remove('animate-shake');
            }, 500);
        } else {
            errorElement.classList.add('hidden');
            input.classList.remove('border-red-500');
            input.classList.add('border-green-500');
        }
        
        // Update the input value to contain only numbers
        input.value = numbers;
        
        return isValid;
    }

    // Add to the head section
    document.addEventListener('DOMContentLoaded', function() {
        // Add the shake animation style
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                20%, 40%, 60%, 80% { transform: translateX(5px); }
            }
            .animate-shake {
                animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
            }
        `;
        document.head.appendChild(style);
    });

    // Update the Continue button to validate before proceeding
    document.querySelectorAll('button[onclick="nextStep()"]').forEach(button => {
        button.addEventListener('click', function(e) {
            // The validation now happens in the nextStep function
        });
    });
    
    // Update form submission logic without country code
    document.getElementById('multiStepForm').addEventListener('submit', function(e) {
        const mobileInput = document.getElementById('mobileInput');
        
        // Validate the mobile number before submission
        if (!validateMobileNumber(mobileInput)) {
            e.preventDefault();
            return false;
        }
    });

    // Ensure Step 1 is visible initially
    showStep(1);
    </script>
</body>
</html>