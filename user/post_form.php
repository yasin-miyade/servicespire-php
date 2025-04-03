<?php
// No whitespace or output before this line
ob_start();
session_start(); // Uncomment this line

// Include required files
require_once('../lib/function.php');

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email']; // Get logged-in user's email

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_form'])) {
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
    
    // Debug log
    error_log("Attempting to create post: " . print_r($postData, true));
    
    try {
        $result = $db->insertWorkPost(
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
        
        if ($result) {
            error_log("Post created with ID: " . $result);
            $_SESSION['last_post_id'] = $result;
            $_SESSION['success'] = "Work posted successfully!";
            unset($_SESSION['post_data']);
            unset($_SESSION['payment_success']); // Clear payment flag
            
            header("Location: index.php?refresh=" . time());
            exit();
        } else {
            throw new Exception("Failed to insert post");
        }
    } catch (Exception $e) {
        error_log("Error creating post: " . $e->getMessage());
        $_SESSION['error'] = "Failed to create work post. Please try again.";
        header("Location: post_form.php");
        exit();
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
        .suggestions-container {
            position: relative;
            width: 100%;
        }
        .suggestions-list {
            position: absolute;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin-top: 0.25rem;
            display: none;
        }
        .suggestion-item {
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .suggestion-item:hover {
            background-color: #f7fafc;
        }
        .suggestion-item.active {
            background-color: #ebf4ff;
        }
        .emergency-item {
            color: #dc2626;
            font-weight: 600;
        }
        .emergency-item::before {
            content: "🚨 ";
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
                    <h3 class="text-xl font-bold mb-2" id="successTitle">Work Posted Successfully!</h3>
                    <p class="mb-2" id="successText">Your work request has been submitted.</p>
                    <p class="text-sm text-gray-600">Redirecting to homepage in <span id="countdown">3</span> seconds...</p>
                </div>

                <!-- Form -->
                <form id="multiStepForm" method="POST" class="<?php echo ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['success'])) ? 'hidden' : ''; ?>">
                    <input type="hidden" name="submit_form" value="1">
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
                            <div class="suggestions-container">
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                                        <i class="fas fa-briefcase"></i>
                                    </span>
                                    <input type="text" id="workTitle" name="work" required class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Enter work title" autocomplete="off" />
                                </div>
                                <div id="suggestionsList" class="suggestions-list"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Start typing to see suggestions (e.g. "Medicine delivery", "Emergency plumber")</p>
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
    
    // Comprehensive work suggestions including emergency services
    const workSuggestions = [
        // Emergency Services (highlighted in red)
        {text: "Emergency medicine delivery (urgent)", emergency: true},
        {text: "Emergency plumber needed (water leak)", emergency: true},
        {text: "Emergency electrician (power outage)", emergency: true},
        {text: "Emergency locksmith (locked out)", emergency: true},
        {text: "Emergency car towing service", emergency: true},
        {text: "Emergency pet care/veterinary help", emergency: true},
        {text: "Emergency home repair (storm damage)", emergency: true},
        {text: "Emergency babysitting needed", emergency: true},
        {text: "Emergency document delivery (urgent)", emergency: true},
        
        // Academic Help
        {text: "Assignment writing help (college)", emergency: false},
        {text: "Thesis writing assistance", emergency: false},
        {text: "Research paper writing", emergency: false},
        {text: "Essay writing service", emergency: false},
        {text: "Homework help (school subjects)", emergency: false},
        {text: "Online tutoring (math/science)", emergency: false},
        {text: "Project report writing", emergency: false},
        
        // Delivery Services
        {text: "Medicine delivery from pharmacy", emergency: false},
        {text: "Grocery delivery service", emergency: false},
        {text: "Food delivery from restaurant", emergency: false},
        {text: "Document delivery between offices", emergency: false},
        {text: "Parcel delivery within city", emergency: false},
        {text: "Gift delivery service", emergency: false},
        {text: "Flower delivery for special occasion", emergency: false},
        
        // Home Services
        {text: "Plumbing repair for leaky faucet", emergency: false},
        {text: "Electrical wiring installation", emergency: false},
        {text: "Home cleaning service", emergency: false},
        {text: "Carpentry work for furniture", emergency: false},
        {text: "Painting house interior", emergency: false},
        {text: "AC repair and service", emergency: false},
        {text: "Appliance repair (refrigerator, washing machine)", emergency: false},
        {text: "Moving and packing assistance", emergency: false},
        {text: "Gardening and landscaping", emergency: false},
        {text: "Roof repair and maintenance", emergency: false},
        
        // Other Essential Services
        {text: "Car mechanic service", emergency: false},
        {text: "Computer repair and maintenance", emergency: false},
        {text: "Event planning and management", emergency: false},
        {text: "Photography for special occasions", emergency: false},
        {text: "Catering service for events", emergency: false},
        {text: "Personal fitness training", emergency: false},
        {text: "Interior design consultation", emergency: false},
        {text: "Tax preparation and filing", emergency: false},
        {text: "Legal document preparation", emergency: false},
        {text: "Translation services", emergency: false}
    ];

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
        
        // Initialize work title suggestions
        const workTitleInput = document.getElementById('workTitle');
        const suggestionsList = document.getElementById('suggestionsList');
        
        workTitleInput.addEventListener('input', function() {
            const input = this.value.toLowerCase();
            suggestionsList.innerHTML = '';
            
            if (input.length > 1) {
                const filtered = workSuggestions.filter(item => 
                    item.text.toLowerCase().includes(input)
                );
                
                if (filtered.length > 0) {
                    filtered.forEach(item => {
                        const div = document.createElement('div');
                        div.className = item.emergency ? 'suggestion-item emergency-item' : 'suggestion-item';
                        div.textContent = item.text;
                        div.addEventListener('click', function() {
                            workTitleInput.value = this.textContent;
                            suggestionsList.style.display = 'none';
                        });
                        suggestionsList.appendChild(div);
                    });
                    suggestionsList.style.display = 'block';
                } else {
                    suggestionsList.style.display = 'none';
                }
            } else {
                suggestionsList.style.display = 'none';
            }
        });
        
        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target !== workTitleInput) {
                suggestionsList.style.display = 'none';
            }
        });
        
        // Keyboard navigation for suggestions
        workTitleInput.addEventListener('keydown', function(e) {
            const items = suggestionsList.querySelectorAll('.suggestion-item');
            let activeItem = suggestionsList.querySelector('.suggestion-item.active');
            
            if (items.length > 0) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (!activeItem) {
                        items[0].classList.add('active');
                    } else {
                        activeItem.classList.remove('active');
                        const next = activeItem.nextElementSibling;
                        if (next) {
                            next.classList.add('active');
                        } else {
                            items[0].classList.add('active');
                        }
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (!activeItem) {
                        items[items.length - 1].classList.add('active');
                    } else {
                        activeItem.classList.remove('active');
                        const prev = activeItem.previousElementSibling;
                        if (prev) {
                            prev.classList.add('active');
                        } else {
                            items[items.length - 1].classList.add('active');
                        }
                    }
                } else if (e.key === 'Enter' && activeItem) {
                    e.preventDefault();
                    workTitleInput.value = activeItem.textContent;
                    suggestionsList.style.display = 'none';
                }
            }
        });
    });

    // Check if we need to show success message
    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['success'])): ?>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('multiStepForm').classList.add('hidden');
        document.getElementById('successMessage').classList.remove('hidden');
        
        // Check if this is after payment success
        <?php if (isset($_SESSION['payment_success_msg']) && $_SESSION['payment_success_msg'] === true): ?>
        document.getElementById('successTitle').textContent = "Payment Successful!";
        document.getElementById('successText').textContent = "Your work post has been published successfully.";
        // Remove the flag
        <?php unset($_SESSION['payment_success_msg']); ?>
        <?php endif; ?>
        
        // Countdown and redirect
        let count = 3;
        const countdownElement = document.getElementById('countdown');
        
        const countdownInterval = setInterval(function() {
            count--;
            countdownElement.textContent = count;
            
            if (count <= 0) {
                clearInterval(countdownInterval);
                // Redirect to index page with refresh parameter to ensure cache is bypassed
                window.location.href = 'index.php?refresh=' + new Date().getTime() + 
                    '<?php echo isset($_SESSION["last_post_id"]) ? "&highlight=" . $_SESSION["last_post_id"] : ""; ?>';
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
                return; // Don't proceed if validation fails
            }
            showStep(2); // Proceed to step 2 after successful validation
        } else if (currentStep === 2) {
            const isChecked = document.getElementById("enableLocations").checked;
            if (isChecked) {
                showStep(3); // Show Step 3 if checkbox is checked
            } else {
                document.getElementById("multiStepForm").submit(); // Submit the form if unchecked
            }
        } else if (currentStep < totalSteps) {
            showStep(currentStep + 1);
        } else {
            // All steps completed, add hidden input and submit the form
            const form = document.getElementById('multiStepForm');
            const submitInput = document.createElement('input');
            submitInput.type = 'hidden';
            submitInput.name = 'submit_form';
            submitInput.value = '1';
            form.appendChild(submitInput);
            form.submit(); // Submit the form to trigger PHP redirection
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

    // Ensure Step 1 is visible initially
    showStep(1);
    </script>
</body>
</html>