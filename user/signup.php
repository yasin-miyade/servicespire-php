<?php
    session_start(); // Start session
    require_once('../lib/function.php'); // Include database functions

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $dob = $_POST['dob'];
        $gender = $_POST['gender'];
        $mobile = $_POST['mobile'];
        $address = $_POST['address'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $id_proof_type = $_POST['id_proof_type'];

        // Convert date from dd/mm/yyyy to yyyy-mm-dd for database storage
        $dob_parts = explode('/', $dob);
        if (count($dob_parts) === 3) {
            $dob_formatted = $dob_parts[2] . '-' . $dob_parts[1] . '-' . $dob_parts[0];
            
            // Check if user is 18 or older
            $dob_date = new DateTime($dob_formatted);
            $today = new DateTime();
            $age = $dob_date->diff($today)->y;
            
            if ($age < 18) {
                $_SESSION['error'] = "Error: You must be 18 years or older to register.";
                header("Location: signup.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Error: Invalid date format.";
            header("Location: signup.php");
            exit();
        }
        
        // Check if passwords match
        if ($password !== $confirm_password) {
            $_SESSION['error'] = "Error: Passwords do not match.";
            header("Location: signup.php");
            exit();
        }

        // Check if password meets security requirements
        $db = new db_functions();
        if (!$db->validatePassword($password)) {
            $_SESSION['error'] = "Error: Password must be at least 8 characters long and include uppercase, lowercase, numbers, and special characters.";
            header("Location: signup.php");
            exit();
        }

        // Check if email already exists
        $existing_user = $db->get_user_by_email($email);
        if ($existing_user) {
            $_SESSION['error'] = "Error: Email already in use. Please use a different email address.";
            header("Location: signup.php");
            exit();
        }

        // Handle file upload
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Create directory if not exists
        }

        $file_extension = pathinfo($_FILES["id_proof"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid("id_proof_", true) . "." . $file_extension; // Generate unique filename
        $id_proof_path = $target_dir . $new_filename;

        if (!move_uploaded_file($_FILES["id_proof"]["tmp_name"], $id_proof_path)) {
            $_SESSION['error'] = "Error uploading file.";
            header("Location: signup.php");
            exit();
        }

        // Insert data into the database
        if ($db->save_sign_up_data($first_name, $last_name, $dob_formatted, $gender, $mobile, $address, $email, $password, $id_proof_path)) {
            $_SESSION['success'] = "Registration successful! Please log in.";
            // Don't redirect immediately - let the JavaScript handle the 3-second delay
        } else {
            $_SESSION['error'] = "Registration failed!";
            header("Location: signup.php");
            exit();
        }
    }
    ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="../assets/css/style.css" rel="stylesheet">
    <!-- Add Flatpickr for better date picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        // Track the current step
        let currentStep = 1;
        
        function goToStep(step) {
            // Hide all steps
            document.querySelectorAll('.form-step').forEach(el => {
                el.classList.add('hidden');
            });
            
            // Show the requested step
            document.getElementById(`step${step}`).classList.remove('hidden');
            
            // Update progress indicator
            updateProgressBar(step);
            
            // Update current step
            currentStep = step;
        }
        
        function nextStep() {
            // Form validation logic for each step
            if (currentStep === 1) {
                // Validate first step fields
                const firstName = document.querySelector('[name="first_name"]').value;
                const lastName = document.querySelector('[name="last_name"]').value;
                const dob = document.querySelector('[name="dob"]').value;
                const gender = document.querySelector('[name="gender"]').value;
                
                if (!firstName || !lastName || !dob || !gender) {
                    showToast('Please fill out all fields in this step');
                    return;
                }
                
                // Check if user is 18 or older
                if (!isEighteenOrOlder(dob)) {
                    showToast('You must be 18 years or older to register');
                    return;
                }
            } else if (currentStep === 2) {
                // Validate second step fields
                const mobile = document.querySelector('[name="mobile"]').value;
                const address = document.querySelector('[name="address"]').value;
                const idProofType = document.querySelector('[name="id_proof_type"]:checked');
                const idProof = document.querySelector('[name="id_proof"]').value;
                
                if (!mobile || !address || !idProofType) {
                    showToast('Please fill out all fields in this step');
                    return;
                }
                
                // Validate mobile number for exactly 10 digits
                if (!/^\d{10}$/.test(mobile)) {
                    showToast('Mobile number must be exactly 10 digits');
                    return;
                }
                
                if (!idProof) {
                    showToast('Please upload your ID proof document');
                    return;
                }
            }
            
            // Move to next step
            goToStep(currentStep + 1);
        }
        
        function prevStep() {
            goToStep(currentStep - 1);
        }
        
        function updateProgressBar(step) {
            // Update progress bar
            const percent = ((step - 1) / 2) * 100;
            document.getElementById('progressBar').style.width = `${percent}%`;
            
            // Update step indicators
            for (let i = 1; i <= 3; i++) {
                const indicator = document.getElementById(`step${i}Indicator`);
                if (i < step) {
                    // Completed step
                    indicator.classList.remove('bg-gray-200', 'text-gray-700');
                    indicator.classList.add('bg-blue-600', 'text-white');
                } else if (i === step) {
                    // Current step
                    indicator.classList.remove('bg-gray-200', 'text-gray-700');
                    indicator.classList.add('bg-blue-500', 'text-white');
                } else {
                    // Future step
                    indicator.classList.remove('bg-blue-600', 'bg-blue-500', 'text-white');
                    indicator.classList.add('bg-gray-200', 'text-gray-700');
                }
            }
        }
        
        function validatePassword() {
            let password = document.getElementById("password").value;
            let confirmPassword = document.getElementById("confirm_password").value;
            let errorText = document.getElementById("password_error");
            
            // Password requirements
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[^A-Za-z0-9]/.test(password);
            const isLongEnough = password.length >= 8;
            
            // Check all password requirements
            if (!isLongEnough || !hasUppercase || !hasLowercase || !hasNumber || !hasSpecial) {
                let message = "Password must:";
                if (!isLongEnough) message += "<br>- Be at least 8 characters long";
                if (!hasUppercase) message += "<br>- Include an uppercase letter";
                if (!hasLowercase) message += "<br>- Include a lowercase letter";
                if (!hasNumber) message += "<br>- Include a number";
                if (!hasSpecial) message += "<br>- Include a special character";
                
                errorText.innerHTML = message;
                errorText.classList.remove("hidden");
                return false;
            }
            
            // Check if passwords match
            if (password !== confirmPassword) {
                errorText.innerHTML = "Passwords do not match.";
                errorText.classList.remove("hidden");
                return false;
            } else {
                errorText.classList.add("hidden");
                return true;
            }
        }
        
        function isEighteenOrOlder(dobString) {
            // Parse the date of birth
            const dob = new Date(dobString);
            
            // Calculate the difference in years
            const today = new Date();
            let age = today.getFullYear() - dob.getFullYear();
            
            // Check if birthday has occurred this year
            const monthDiff = today.getMonth() - dob.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                age--;
            }
            
            return age >= 18;
        }
        
        function toggleIdProofUpload() {
            const idProofUploadSection = document.getElementById('id_proof_upload_section');
            const idProofSelected = document.querySelector('input[name="id_proof_type"]:checked');
            
            if (idProofSelected) {
                idProofUploadSection.classList.remove('hidden');
            } else {
                idProofUploadSection.classList.add('hidden');
            }
        }
        
        // Preview uploaded file
        function previewFile() {
            const preview = document.getElementById('file-preview');
            const previewContainer = document.getElementById('preview-container');
            const fileInput = document.getElementById('id_proof');
            const file = fileInput.files[0];
            const reader = new FileReader();
            
            if (file) {
                reader.onloadend = function() {
                    if (file.type.includes('image')) {
                        // If it's an image, display the image
                        preview.src = reader.result;
                        preview.classList.remove('hidden');
                        document.getElementById('file-name').textContent = file.name;
                        previewContainer.classList.remove('hidden');
                    } else if (file.type === 'application/pdf') {
                        // If it's a PDF, show PDF icon or text
                        preview.classList.add('hidden');
                        document.getElementById('file-name').textContent = file.name + ' (PDF)';
                        previewContainer.classList.remove('hidden');
                    }
                }
                reader.readAsDataURL(file);
            }
        }
        
        // Toggle password visibility
        function togglePasswordVisibility(inputId) {
            const passwordInput = document.getElementById(inputId);
            const icon = document.getElementById(inputId + '_icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Show toast notification
        function showToast(message) {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            
            toastMessage.textContent = message;
            toast.classList.remove('hidden');
            toast.classList.add('flex');
            
            // Hide toast after 3 seconds
            setTimeout(() => {
                toast.classList.add('hidden');
                toast.classList.remove('flex');
            }, 3000);
        }
        
        // Initialize date picker and other components when document is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize flatpickr date picker
            flatpickr("#dob", {
                dateFormat: "d/m/Y", // Format as dd/mm/yyyy
                maxDate: new Date(new Date().setFullYear(new Date().getFullYear() - 18)), // Set max date to 18 years ago
                disableMobile: true,
                allowInput: true,
                static: true
            });
            
            // Setup ID proof type selection
            const idProofTypeInputs = document.querySelectorAll('input[name="id_proof_type"]');
            idProofTypeInputs.forEach(input => {
                input.addEventListener('change', toggleIdProofUpload);
            });
            
            // Hide ID proof upload section initially
            document.getElementById('id_proof_upload_section').classList.add('hidden');
            
            // Add file change listener for preview
            document.getElementById('id_proof').addEventListener('change', previewFile);
            
            // Check for success message and set redirect timer
            const successMessage = document.querySelector('.success-message');
            if (successMessage && !successMessage.classList.contains('hidden')) {
                // If there's a success message displayed, redirect after 3 seconds
                setTimeout(function() {
                    window.location.href = "login.php";
                }, 3000);
            }

            // Add event listener for password validation
            document.getElementById('password').addEventListener('input', function() {
                const password = this.value;
                const requirements = {
                    length: password.length >= 8,
                    uppercase: /[A-Z]/.test(password),
                    lowercase: /[a-z]/.test(password),
                    number: /[0-9]/.test(password),
                    special: /[^A-Za-z0-9]/.test(password)
                };
                
                // Update requirement indicators
                updatePasswordRequirementStatus('length', requirements.length);
                updatePasswordRequirementStatus('uppercase', requirements.uppercase);
                updatePasswordRequirementStatus('lowercase', requirements.lowercase);
                updatePasswordRequirementStatus('number', requirements.number);
                updatePasswordRequirementStatus('special', requirements.special);
                
                validatePassword();
            });
            
            document.getElementById('confirm_password').addEventListener('input', validatePassword);
        });
        
        // Update the visual status of password requirements
        function updatePasswordRequirementStatus(requirement, isMet) {
            const indicator = document.getElementById(`req-${requirement}`);
            if (!indicator) return;
            
            if (isMet) {
                indicator.classList.remove('text-gray-500');
                indicator.classList.add('text-green-500');
                indicator.querySelector('i').className = 'fas fa-check-circle mr-1';
            } else {
                indicator.classList.remove('text-green-500');
                indicator.classList.add('text-gray-500');
                indicator.querySelector('i').className = 'fas fa-circle mr-1';
            }
        }
    </script>
</head>
<body class="bg-gradient-to-b from-blue-50 to-gray-100 flex items-center justify-center min-h-screen">
    <!-- Back Button -->
    <div class="absolute top-20 left-32">
        <a href="login.php" class="flex items-center text-gray-600 hover:text-blue-700 transition group">
            <div class="w-8 h-8 bg-white rounded-full shadow-md flex items-center justify-center mr-2 group-hover:bg-blue-50 transition">
                <i class="fas fa-arrow-left text-blue-600"></i>
            </div>
            <span class="font-medium">Back to Login</span>
        </a>
    </div>
    
    <!-- Toast Notification -->
    <div id="toast" class="fixed top-4 right-4 flex items-center p-4 mb-4 text-gray-500 bg-white rounded-lg shadow hidden z-50 max-w-xs" role="alert">
        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-100 rounded-lg">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <div id="toast-message" class="ml-3 text-sm font-normal"></div>
        <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg p-1.5 hover:bg-gray-100 inline-flex h-8 w-8" onclick="document.getElementById('toast').classList.add('hidden')">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-2xl border border-gray-200">
        <div class="flex justify-center mb-6">
            <!-- You can add your logo here -->
            <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mb-2 shadow-md">
                <i class="fas fa-user-plus text-2xl text-white"></i>
            </div>
        </div>
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-2">Create an Account</h2>
        <p class="text-gray-500 text-center mb-6">Fill in your details to get started</p>
        
        <!-- Messages -->
        <div class="message">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-message text-green-700 bg-green-100 border border-green-500 px-4 py-3 rounded-md text-center mb-6 flex items-center">
                    <div class="mr-3 flex-shrink-0">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div class="flex-1 text-left">
                        <p class="font-medium"><?php echo $_SESSION['success']; ?></p>
                        <div class="mt-1 text-sm text-gray-700">
                            <div>Redirecting to login page in <span id="countdown">3</span> seconds...</div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                                <div id="redirectProgressBar" class="bg-green-600 h-1.5 rounded-full w-0 transition-all duration-300"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    // Set up countdown and progress bar
                    let secondsLeft = 3;
                    const countdownElement = document.getElementById('countdown');
                    const progressBar = document.getElementById('redirectProgressBar');
                    
                    // Update every second
                    const countdownInterval = setInterval(function() {
                        secondsLeft -= 1;
                        countdownElement.textContent = secondsLeft;
                        progressBar.style.width = ((3 - secondsLeft) / 3 * 100) + '%';
                        
                        if (secondsLeft <= 0) {
                            clearInterval(countdownInterval);
                        }
                    }, 1000);
                    
                    // Redirect after 3 seconds
                    setTimeout(function() {
                        window.location.href = "login.php";
                    }, 3000);
                </script>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="text-red-700 bg-red-50 border border-red-300 px-4 py-3 rounded-md mb-6 flex items-center">
                    <div class="mr-3 flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium"><?php echo $_SESSION['error']; ?></p>
                    </div>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
        </div>
        
        <!-- Progress Tracker -->
        <div class="mb-8">
            <!-- Progress bar -->
            <div class="w-full bg-gray-200 rounded-full h-2.5 mb-6">
                <div id="progressBar" class="bg-blue-600 h-2.5 rounded-full w-0 transition-all duration-300"></div>
            </div>
            
            <!-- Step indicators -->
            <div class="flex justify-between">
                <div class="flex flex-col items-center">
                    <div id="step1Indicator" class="w-12 h-12 flex items-center justify-center rounded-full bg-blue-500 text-white font-bold mb-1 transition-all duration-300 shadow-md">
                        <i class="fas fa-user"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Personal Info</span>
                </div>
                <div class="flex flex-col items-center">
                    <div id="step2Indicator" class="w-12 h-12 flex items-center justify-center rounded-full bg-gray-200 text-gray-700 font-bold mb-1 transition-all duration-300 shadow-sm">
                        <i class="fas fa-address-card"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Contact Details</span>
                </div>
                <div class="flex flex-col items-center">
                    <div id="step3Indicator" class="w-12 h-12 flex items-center justify-center rounded-full bg-gray-200 text-gray-700 font-bold mb-1 transition-all duration-300 shadow-sm">
                        <i class="fas fa-lock"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Account Setup</span>
                </div>
            </div>
        </div>
        
        <form id="signupForm" action="signup.php" method="POST" enctype="multipart/form-data" onsubmit="return validatePassword()">
            <!-- Step 1: Personal Information -->
            <div id="step1" class="form-step">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">First Name</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input type="text" name="first_name" class="w-full border border-gray-300 p-3 pl-10 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Last Name</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input type="text" name="last_name" class="w-full border border-gray-300 p-3 pl-10 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <label class="block text-gray-700 font-medium mb-2">Date of Birth (18+ only)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400"></i>
                        </div>
                        <input type="text" id="dob" name="dob" class="w-full border border-gray-300 p-3 pl-10 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="DD/MM/YYYY" required>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">You must be 18 years or older to register</p>
                </div>
                
                <div class="mt-4">
                    <label class="block text-gray-700 font-medium mb-2">Gender</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-venus-mars text-gray-400"></i>
                        </div>
                        <select name="gender" class="w-full border border-gray-300 p-3 pl-10 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button type="button" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-md transition flex items-center justify-center" onclick="nextStep()">
                        Next 
                        <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>
            
            <!-- Step 2: Contact Information -->
            <div id="step2" class="form-step hidden">
                <div class="mt-4">
                    <label class="block text-gray-700 font-medium mb-2">Mobile Number</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-mobile-alt text-gray-400"></i>
                        </div>
                        <input type="tel" name="mobile" pattern="\d{10}" title="Please enter exactly 10 digits" class="w-full border border-gray-300 p-3 pl-10 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">Enter a 10-digit mobile number without any prefixes</p>
                </div>

                <div class="mt-4">
                    <label class="block text-gray-700 font-medium mb-2">Address</label>
                    <div class="relative">
                        <div class="absolute top-3 left-3 flex items-start pointer-events-none">
                            <i class="fas fa-home text-gray-400"></i>
                        </div>
                        <textarea name="address" class="w-full border border-gray-300 p-3 pl-10 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="3" required></textarea>
                    </div>
                </div>
                
                <div class="mt-4">
                    <label class="block text-gray-700 font-medium mb-2">ID Proof Type</label>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-white border border-gray-300 rounded-lg p-4 hover:border-blue-500 cursor-pointer group transition">
                            <input type="radio" id="aadhar" name="id_proof_type" value="aadhar" class="sr-only">
                            <label for="aadhar" class="cursor-pointer flex flex-col items-center">
                                <i class="fas fa-id-card text-2xl text-gray-500 group-hover:text-blue-600 mb-2"></i>
                                <span class="text-sm text-gray-700 font-medium">Aadhar Card</span>
                            </label>
                        </div>
                        <div class="bg-white border border-gray-300 rounded-lg p-4 hover:border-blue-500 cursor-pointer group transition">
                            <input type="radio" id="pan" name="id_proof_type" value="pan" class="sr-only">
                            <label for="pan" class="cursor-pointer flex flex-col items-center">
                                <i class="fas fa-credit-card text-2xl text-gray-500 group-hover:text-blue-600 mb-2"></i>
                                <span class="text-sm text-gray-700 font-medium">PAN Card</span>
                            </label>
                        </div>
                        <div class="bg-white border border-gray-300 rounded-lg p-4 hover:border-blue-500 cursor-pointer group transition">
                            <input type="radio" id="voter" name="id_proof_type" value="voter" class="sr-only">
                            <label for="voter" class="cursor-pointer flex flex-col items-center">
                                <i class="fas fa-vote-yea text-2xl text-gray-500 group-hover:text-blue-600 mb-2"></i>
                                <span class="text-sm text-gray-700 font-medium">Voter ID</span>
                            </label>
                        </div>
                    </div>
                    <script>
                        // Add click handler to ID proof type boxes
                        document.querySelectorAll('[name="id_proof_type"]').forEach(radio => {
                            radio.addEventListener('change', function() {
                                // Remove active class from all boxes
                                document.querySelectorAll('[name="id_proof_type"]').forEach(r => {
                                    r.closest('div').classList.remove('border-blue-500', 'bg-blue-50');
                                    r.closest('div').querySelector('i').classList.remove('text-blue-600');
                                });
                                
                                // Add active class to selected box
                                if (this.checked) {
                                    this.closest('div').classList.add('border-blue-500', 'bg-blue-50');
                                    this.closest('div').querySelector('i').classList.add('text-blue-600');
                                }
                                
                                // Show upload section
                                toggleIdProofUpload();
                            });
                        });
                    </script>
                </div>
                
                <div id="id_proof_upload_section" class="mt-4 hidden">
                    <label class="block text-gray-700 font-medium mb-2">Upload ID Proof</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg bg-gray-50 hover:bg-blue-50 hover:border-blue-400 transition">
                        <div class="space-y-1 text-center">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400"></i>
                            <div class="flex text-sm text-gray-600">
                                <label for="id_proof" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                    <span>Upload a file</span>
                                    <input id="id_proof" name="id_proof" type="file" class="sr-only" accept="image/*,.pdf">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, PDF up to 5MB</p>
                        </div>
                    </div>
                    
                    <!-- File Preview Section -->
                    <div id="preview-container" class="mt-4 hidden">
                        <div class="p-4 border border-green-200 rounded-lg bg-green-50">
                            <div class="flex items-center">
                                <div class="mr-4 bg-white p-2 rounded-md shadow-sm">
                                    <img id="file-preview" class="h-16 w-auto object-contain" src="#" alt="ID Proof Preview">
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">File Uploaded</h4>
                                    <p id="file-name" class="text-sm text-gray-500"></p>
                                </div>
                                <div>
                                    <button type="button" onclick="document.getElementById('id_proof').click()" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                                        <i class="fas fa-sync-alt mr-1"></i> Change
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-between">
                <button type="button" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium transition flex items-center" onclick="prevStep()">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Previous
                    </button>
                    <button type="button" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-md transition flex items-center" onclick="nextStep()">
                        Next
                        <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>
            
            <!-- Step 3: Account Information -->
            <div id="step3" class="form-step hidden">
                <div class="mt-4">
                    <label class="block text-gray-700 font-medium mb-2">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" name="email" class="w-full border border-gray-300 p-3 pl-10 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-gray-700 font-medium mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" id="password" class="w-full border border-gray-300 p-3 pl-10 pr-10 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required minlength="8">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none" onclick="togglePasswordVisibility('password')">
                                <i id="password_icon" class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Password requirements -->
                    <div class="mt-2 bg-blue-50 p-3 rounded-lg border border-blue-100">
                        <p class="text-sm font-medium text-gray-700 mb-2">Password must contain:</p>
                        <ul class="text-xs space-y-1">
                            <li id="req-length" class="text-gray-500"><i class="fas fa-circle mr-1"></i>At least 8 characters</li>
                            <li id="req-uppercase" class="text-gray-500"><i class="fas fa-circle mr-1"></i>At least one uppercase letter</li>
                            <li id="req-lowercase" class="text-gray-500"><i class="fas fa-circle mr-1"></i>At least one lowercase letter</li>
                            <li id="req-number" class="text-gray-500"><i class="fas fa-circle mr-1"></i>At least one number</li>
                            <li id="req-special" class="text-gray-500"><i class="fas fa-circle mr-1"></i>At least one special character</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-gray-700 font-medium mb-2">Confirm Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="confirm_password" id="confirm_password" class="w-full border border-gray-300 p-3 pl-10 pr-10 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none" onclick="togglePasswordVisibility('confirm_password')">
                                <i id="confirm_password_icon" class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <p id="password_error" class="text-red-500 text-sm mt-1 hidden">Passwords do not match.</p>
                </div>
                
                <div class="mt-6">
                    <div class="flex items-center">
                        <input id="terms" name="terms" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" required>
                        <label for="terms" class="ml-2 block text-sm text-gray-700">
                            I agree to the <a href="#" class="text-blue-600 hover:underline">Terms of Service</a> and <a href="#" class="text-blue-600 hover:underline">Privacy Policy</a>
                        </label>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-between">
                    <button type="button" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium transition flex items-center" onclick="prevStep()">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Previous
                    </button>
                    <button type="submit" class="px-8 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 shadow-md transition flex items-center">
                        <i class="fas fa-user-plus mr-2"></i>
                        Create Account
                    </button>
                </div>
            </div>
        </form>
        
        <div class="mt-6 text-center text-gray-600 text-sm">
            Already have an account? <a href="login.php" class="text-blue-600 font-medium hover:underline">Sign in</a>
        </div>
    </div>
    
</body>
</html>