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
$updated = false;
$isUpdating = false;

// HANDLE POST SUBMISSION
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize input
    $post_id = $_POST['post_id'];
    $work = htmlspecialchars($_POST['work']);
    $city = htmlspecialchars($_POST['city']);
    $deadline = htmlspecialchars($_POST['deadline']);
    $reward = htmlspecialchars($_POST['reward']);
    $message = htmlspecialchars($_POST['message']);
    
    // Check if we should clear locations
    if (isset($_POST['clear_locations']) && $_POST['clear_locations'] == '1') {
        $from_location = '';
        $to_location = '';
    } else {
        $from_location = isset($_POST['from_location']) ? htmlspecialchars($_POST['from_location']) : '';
        $to_location = isset($_POST['to_location']) ? htmlspecialchars($_POST['to_location']) : '';
    }

    // Update the database
    $updated = $db->updateUserWorkPost($post_id, $email, $work, $city, $deadline, $reward, $message, $from_location, $to_location);
    $isUpdating = true;
} 
// HANDLE GET REQUEST (INITIAL FORM LOAD)
else {
    // Check if post ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        die("Invalid request.");
    }

    $post_id = $_GET['id'];
    $post = $db->getUserWorkPostById($post_id, $email);

    if (!$post) {
        die("Post not found or you don't have permission to edit this post.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Work Post</title>
    <link href="../assets/css/style.css" rel="stylesheet">
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
                <h2 class="text-2xl font-bold text-white text-center">Edit Work Post</h2>
                <p class="text-indigo-100 text-center mt-1">Update your work request details</p>
            </div>

            <?php if ($isUpdating): ?>
                <!-- Success Message -->
                <div id="successMessage" class="<?php echo $updated ? '' : 'hidden'; ?> p-8 text-center">
                    <div class="bg-green-100 border border-green-300 text-green-700 px-6 py-8 rounded-lg mb-6">
                        <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
                        <h3 class="text-xl font-bold mb-2">Post Updated Successfully!</h3>
                        <p class="mb-2">Your work post has been updated.</p>
                        <p class="text-sm text-gray-600">Redirecting to homepage in <span id="countdown">3</span> seconds...</p>
                    </div>
                    
                    <a href="index.php" class="inline-block bg-indigo-600 text-white font-semibold py-2 px-6 rounded-lg hover:bg-indigo-700 transition-all">
                        <i class="fas fa-arrow-left mr-2"></i> Go to My Work Posts
                    </a>
                </div>

                <!-- Error Message -->
                <div id="errorMessage" class="<?php echo $updated ? 'hidden' : ''; ?> p-8 text-center">
                    <div class="bg-red-100 border border-red-300 text-red-700 px-6 py-8 rounded-lg mb-6">
                        <i class="fas fa-exclamation-circle text-red-500 text-5xl mb-4"></i>
                        <h3 class="text-xl font-bold mb-2">Update Failed</h3>
                        <p class="mb-2">Something went wrong. Please try again.</p>
                    </div>
                    
                    <a href="index.php" class="inline-block bg-indigo-600 text-white font-semibold py-2 px-6 rounded-lg hover:bg-indigo-700 transition-all">
                        <i class="fas fa-arrow-left mr-2"></i> Return to My Work Posts
                    </a>
                </div>
            <?php else: ?>
                <!-- Step Indicators -->
                <div class="flex justify-between px-6 pt-5 pb-3">
                    <div class="flex flex-col items-center">
                        <div id="step1-indicator" class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold mb-2">
                            1
                        </div>
                        <span class="text-sm text-gray-600">Basic Info</span>
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
                    <form id="multiStepForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                        <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post['id']); ?>">
                        <input type="hidden" name="clear_locations" id="clear_locations" value="0">

                        <!-- Step 1: Basic Info -->
                        <div class="form-step" id="step1">
                            <div class="mb-5">
                                <label class="block text-gray-700 font-medium mb-2">Work Title</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                                        <i class="fas fa-briefcase"></i>
                                    </span>
                                    <input type="text" name="work" value="<?php echo htmlspecialchars($post['work']); ?>" required class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Enter work title" />
                                </div>
                            </div>

                            <div class="mb-5">
                                <label class="block text-gray-700 font-medium mb-2">City</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </span>
                                    <input type="text" name="city" value="<?php echo htmlspecialchars($post['city']); ?>" required class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Enter city" />
                                </div>
                            </div>

                            <div class="flex justify-between gap-4">
                                <a href="index.php" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 px-4 rounded-lg font-medium transition duration-200 text-center">
                                    <i class="fas fa-times mr-2"></i>
                                    Cancel
                                </a>
                                <button type="button" onclick="nextStep()" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-4 rounded-lg font-medium transition duration-200 transform hover:scale-[1.02]">
                                    Continue to Details
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Work Details -->
                        <div class="form-step hidden" id="step2">
                            <div class="mb-5">
                                <label class="block text-gray-700 font-medium mb-2">Deadline</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                                        <i class="fas fa-calendar-alt"></i>
                                    </span>
                                    <input type="date" name="deadline" value="<?php echo htmlspecialchars($post['deadline']); ?>" min="<?php echo date('Y-m-d'); ?>" required class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                </div>
                            </div>

                            <div class="mb-5">
                                <label class="block text-gray-700 font-medium mb-2">Reward/Budget</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                                        <i class="fas fa-rupee-sign"></i>
                                    </span>
                                    <input type="text" name="reward" value="<?php echo htmlspecialchars($post['reward']); ?>" required class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Enter reward amount" />
                                </div>
                            </div>

                            <div class="mb-5">
                                <label class="block text-gray-700 font-medium mb-2">Work Description</label>
                                <div class="relative">
                                    <textarea name="message" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 min-h-32" placeholder="Describe the work requirements in detail..."><?php echo htmlspecialchars($post['message']); ?></textarea>
                                </div>
                            </div>

                            <!-- Checkbox to enable location fields -->
                            <div class="flex items-center gap-3 p-3 border border-indigo-100 bg-indigo-50 rounded-lg mb-6">
                                <input type="checkbox" id="enableLocations" class="w-5 h-5 text-indigo-600 focus:ring-indigo-500 rounded" 
                                       onchange="toggleLocationStep()" 
                                       <?php echo (!empty($post['from_location']) || !empty($post['to_location'])) ? 'checked' : ''; ?>>
                                <label for="enableLocations" class="text-gray-800 font-medium">
                                    <i class="fas fa-map-marked-alt text-indigo-500 mr-2"></i>
                                    This work involves transportation (from one location to another)
                                </label>
                            </div>

                            <div class="flex justify-between gap-3">
                                <a href="index.php" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 px-4 rounded-lg font-medium transition duration-200 text-center">
                                    <i class="fas fa-times mr-2"></i>
                                    Cancel
                                </a>
                                <button type="button" onclick="prevStep()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 px-4 rounded-lg font-medium transition duration-200">
                                    <i class="fas fa-arrow-left mr-2"></i>
                                    Back
                                </button>
                                <button type="submit" id="submitBtn" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-4 rounded-lg font-medium transition duration-200 transform hover:scale-[1.02]">
                                    <i class="fas fa-check mr-2"></i>
                                    Update Post
                                </button>
                                <button type="button" id="nextBtn" onclick="nextStep()" class="flex-1 <?php echo (!empty($post['from_location']) || !empty($post['to_location'])) ? '' : 'hidden'; ?> bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-4 rounded-lg font-medium transition duration-200 transform hover:scale-[1.02]">
                                    Continue
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Location Details (Hidden initially) -->
                        <div class="form-step hidden" id="step3">
                            <div class="p-3 mb-5 border border-indigo-100 bg-indigo-50 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-indigo-800 font-medium">Location Details</h3>
                                    <button type="button" id="removeLocationBtn" onclick="removeLocations()" class="text-red-600 hover:text-red-800 text-sm font-medium flex items-center">
                                        <i class="fas fa-trash-alt mr-1"></i> Remove Locations
                                    </button>
                                </div>
                                <p class="text-sm text-gray-600">Add pickup and delivery locations for transportation work</p>
                            </div>
                            
                            <div class="mb-5">
                                <label class="block text-gray-700 font-medium mb-2">
                                    <i class="fas fa-location-arrow text-indigo-500 mr-2"></i>
                                    From Location
                                </label>
                                <input type="text" name="from_location" id="from_location" value="<?php echo isset($post['from_location']) ? htmlspecialchars($post['from_location']) : ''; ?>" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Starting location..." />
                            </div>

                            <div class="mb-6">
                                <label class="block text-gray-700 font-medium mb-2">
                                    <i class="fas fa-map-pin text-indigo-500 mr-2"></i>
                                    To Location
                                </label>
                                <input type="text" name="to_location" id="to_location" value="<?php echo isset($post['to_location']) ? htmlspecialchars($post['to_location']) : ''; ?>" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Destination location..." />
                            </div>

                            <div class="flex justify-between gap-3">
                                <a href="index.php" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 px-4 rounded-lg font-medium transition duration-200 text-center">
                                    <i class="fas fa-times mr-2"></i>
                                    Cancel
                                </a>
                                <button type="button" onclick="prevStep()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 px-4 rounded-lg font-medium transition duration-200">
                                    <i class="fas fa-arrow-left mr-2"></i>
                                    Back
                                </button>
                                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-4 rounded-lg font-medium transition duration-200 transform hover:scale-[1.02]">
                                    <i class="fas fa-check mr-2"></i>
                                    Update Post
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    let currentStep = 1;
    const totalSteps = 3;

    // Check if we need to show success message and handle redirect
    <?php if ($isUpdating && $updated): ?>
    document.addEventListener('DOMContentLoaded', function() {
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
        
        // If unchecking the box, set the hidden input to clear locations on submit
        if (!isChecked) {
            document.getElementById("clear_locations").value = "1";
        } else {
            document.getElementById("clear_locations").value = "0";
        }
    }
    
    function removeLocations() {
        // Clear location inputs
        document.getElementById("from_location").value = "";
        document.getElementById("to_location").value = "";
        
        // Set the hidden input to clear locations on submit
        document.getElementById("clear_locations").value = "1";
        
        // Show confirmation message
        const confirmMsg = document.createElement("div");
        confirmMsg.className = "p-2 mb-4 text-sm text-green-700 bg-green-100 rounded-lg flex items-center";
        confirmMsg.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Locations will be removed when you update the post';
        
        const parent = document.getElementById("removeLocationBtn").parentNode.parentNode;
        parent.appendChild(confirmMsg);
        
        // Disable the remove button
        document.getElementById("removeLocationBtn").disabled = true;
        document.getElementById("removeLocationBtn").className = "text-gray-400 text-sm font-medium flex items-center cursor-not-allowed";
    }

    // Handle form submission with validation
    document.getElementById('multiStepForm')?.addEventListener('submit', function(e) {
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

    // Ensure Step 1 is visible initially
    if (document.getElementById('step1')) {
        showStep(1);
    }
    </script>
</body>
</html>