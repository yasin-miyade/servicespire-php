<?php
// session_start();
require_once('../lib/function.php');

$db = new db_functions();
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $address = trim($_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $id_proof = $_POST['id_proof'];
    $id_proof_file = null;

    if (isset($_FILES['id_proof_file']) && $_FILES['id_proof_file']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $target_file = $target_dir . basename($_FILES["id_proof_file"]["name"]);
        
        if (move_uploaded_file($_FILES["id_proof_file"]["tmp_name"], $target_file)) {
            $id_proof_file = $target_file;
        } else {
            $id_proof_file = null;
        }
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif ($db->is_email_exists($email)) { 
        $error_message = "This email is already registered. Please use another email.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/', $password)) {
        $error_message = "Password must be at least 6 characters long and include one uppercase, one lowercase, one number, and one special character.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        $register_success = $db->register_user($first_name, $last_name, $email, $mobile, $gender, $dob, $address, $password, $id_proof, $id_proof_file);
    
        if ($register_success) {
            $_SESSION['success_message'] = "Registration successful!";
            header("Location: login.php");
            exit();
        } else {
            $error_message = "Error registering user.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Add Flatpickr for better date picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- Add Font Awesome for better icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: transparent;
            font-family: 'Inter', sans-serif;
        }
        input, select {
            transition: all 0.3s ease-in-out;
        }
        input:hover, select:hover {
            transform: scale(1.01);
            border-color: #a855f7 !important;
            box-shadow: 0 0 10px rgba(168, 85, 247, 0.3);
        }
        .file-drop-area {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
            padding: 25px;
            border: 2px dashed #a855f7;
            border-radius: 8px;
            transition: 0.3s;
            background-color: #faf5ff;
            margin-top: 8px;
        }
        .file-drop-area:hover {
            border-color: #8b5cf6;
            background-color: #f5f3ff;
        }
        .file-drop-area.is-active {
            border-color: #8b5cf6;
        }
        .file-input {
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 100%;
            cursor: pointer;
            opacity: 0;
        }
        .file-msg {
            font-weight: medium;
            color: #6b7280;
            text-align: center;
            width: 100%;
        }
        .file-preview {
            margin-top: 10px;
            display: none;
        }
        .file-preview.active {
            display: flex;
            align-items: center;
            padding: 8px;
            background: #f3f4f6;
            border-radius: 8px;
        }
        .file-preview-name {
            margin-left: 8px;
            font-size: 14px;
            color: #4b5563;
        }
        .file-remove {
            margin-left: auto;
            color: #ef4444;
            cursor: pointer;
        }
        /* Custom styling for flatpickr */
        .flatpickr-calendar {
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .flatpickr-day.selected {
            background: #a855f7 !important;
            border-color: #a855f7 !important;
        }
        /* Progress bar animation */
        @keyframes progress {
            0% { width: 0%; }
            100% { width: 100%; }
        }
        .animate-progress {
            animation: progress 3s linear forwards;
        }
    </style>
</head>
<body class="bg-gray-50 flex justify-center items-center min-h-screen p-4">
    <!-- Back Button -->
    <div class="absolute top-20 left-32">
        <a href="login.php" class="flex items-center text-gray-600 hover:text-blue-700 transition group">
            <div class="w-8 h-8 bg-white rounded-full shadow-md flex items-center justify-center mr-2 group-hover:bg-blue-50 transition">
                <i class="fas fa-arrow-left text-blue-600"></i>
            </div>
            <span class="font-medium">Back to Login</span>
        </a>
    </div>

    <div class="w-full max-w-2xl bg-white shadow-2xl p-8 rounded-2xl border border-gray-100 transition-all duration-300 hover:shadow-purple-100">
        <div class="flex items-center justify-center mb-8">
            <div class="bg-gradient-to-r from-purple-500 to-indigo-500 w-12 h-12 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-800 ml-3 bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent">Create an Account</h2>
        </div>
        
        <?php if ($error_message): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-md text-center mb-6 shadow-sm" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span class="font-medium"><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-md text-center mb-6 shadow-sm">
                <div class="flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="font-medium"><?php echo $_SESSION['success_message']; ?></span>
                </div>
                <div class="mt-3 text-gray-700">
                    <div class="text-sm">Redirecting to login page in <span id="countdown" class="font-bold">3</span> seconds...</div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2 overflow-hidden">
                        <div id="redirectProgressBar" class="bg-green-500 h-1.5 rounded-full w-0 animate-progress"></div>
                    </div>
                </div>
            </div>
            <script>
                // Set up countdown and progress bar
                let secondsLeft = 3;
                const countdownElement = document.getElementById('countdown');
                
                // Update every second
                const countdownInterval = setInterval(function() {
                    secondsLeft -= 1;
                    countdownElement.textContent = secondsLeft;
                    
                    if (secondsLeft <= 0) {
                        clearInterval(countdownInterval);
                    }
                }, 1000);
                
                // Redirect after 3 seconds
                setTimeout(function() {
                    window.location.href = "login.php";
                }, 3000);
            </script>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <!-- Progress Tracker -->
        <div class="mb-8">
            <!-- Progress bar -->
            <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4 overflow-hidden">
                <div id="progressBar" class="bg-gradient-to-r from-purple-500 to-indigo-500 h-2.5 rounded-full w-0 transition-all duration-500"></div>
            </div>
            
            <!-- Step indicators -->
            <div class="flex justify-between">
                <div class="flex flex-col items-center">
                    <div id="step1Indicator" class="w-12 h-12 flex items-center justify-center rounded-full bg-gradient-to-r from-purple-500 to-indigo-500 text-white font-bold mb-1 transition-all duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Personal Info</span>
                </div>
                <div class="flex flex-col items-center">
                    <div id="step2Indicator" class="w-12 h-12 flex items-center justify-center rounded-full bg-gray-200 text-gray-500 font-bold mb-1 transition-all duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">Contact Details</span>
                </div>
                <div class="flex flex-col items-center">
                    <div id="step3Indicator" class="w-12 h-12 flex items-center justify-center rounded-full bg-gray-200 text-gray-500 font-bold mb-1 transition-all duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">Account Setup</span>
                </div>
            </div>
        </div>

        <form id="signupForm" method="POST" action="signup.php" enctype="multipart/form-data" class="space-y-4">
            <!-- Step 1: Personal Information -->
            <div id="step1" class="form-step space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 mb-1 font-medium">First Name</label>
                        <input type="text" name="first_name" required 
                               value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1 font-medium">Last Name</label>
                        <input type="text" name="last_name" required 
                               value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 shadow-sm" />
                    </div>
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-1 font-medium">Date of Birth</label>
                    <div class="relative">
                        <input type="text" id="dob" name="dob" required 
                               value="<?php echo htmlspecialchars($_POST['dob'] ?? ''); ?>"
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 shadow-sm pl-10" 
                               placeholder="DD/MM/YYYY" />
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-1 font-medium">Gender</label>
                    <div class="relative">
                        <select name="gender" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 shadow-sm appearance-none pl-10" required>
                            <option value="">Select Gender</option>
                            <option value="male" <?php echo (($_POST['gender'] ?? '') === 'male') ? 'selected' : ''; ?>>Male</option>
                            <option value="female" <?php echo (($_POST['gender'] ?? '') === 'female') ? 'selected' : ''; ?>>Female</option>
                            <option value="other" <?php echo (($_POST['gender'] ?? '') === 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="pt-4 flex justify-end">
                    <button type="button" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-medium hover:shadow-lg transform hover:-translate-y-1 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50" onclick="nextStep()">
                        Next Step
                        <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>
            
            <!-- Step 2: Contact Information -->
            <div id="step2" class="form-step hidden space-y-4">
                <div>
                    <label class="block text-gray-700 mb-1 font-medium">Mobile Number</label>
                    <div class="relative">
                        <input type="tel" name="mobile" required 
                               value="<?php echo htmlspecialchars($_POST['mobile'] ?? ''); ?>"
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 shadow-sm pl-10"
                               maxlength="10"
                               pattern="\d{10}"
                               oninput="validateMobile(this)" />
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 mb-1 font-medium">Address</label>
                    <div class="relative">
                        <input type="text" name="address" required 
                               value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>"
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 shadow-sm pl-10" />
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-1 font-medium">ID Proof</label>
                    <div class="relative">
                        <select id="idProof" name="id_proof" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 shadow-sm appearance-none pl-10" 
                                onchange="toggleFileUpload()" required>
                            <option value="">Select ID Proof</option>
                            <option value="aadhaar" <?php echo (($_POST['id_proof'] ?? '') === 'aadhaar') ? 'selected' : ''; ?>>Aadhaar Card</option>
                            <option value="pan" <?php echo (($_POST['id_proof'] ?? '') === 'pan') ? 'selected' : ''; ?>>PAN Card</option>
                            <option value="voter" <?php echo (($_POST['id_proof'] ?? '') === 'voter') ? 'selected' : ''; ?>>Voter ID</option>
                        </select>
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                            </svg>
                        </div>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div id="fileUpload" class="mt-4 hidden">
                    <label class="block text-gray-700 mb-1 font-medium">Upload Document (Max 500KB)</label>
                    
                    <!-- Drag & Drop File Upload Area -->
                    <div class="file-drop-area">
                        <span class="file-msg flex items-center justify-center">
                            <svg class="h-6 w-6 text-purple-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            Upload or drag & drop your ID proof here
                        </span>
                        <input type="file" name="id_proof_file" class="file-input" accept="image/*,application/pdf" 
                               onchange="handleFileSelect(this)">
                    </div>
                    
                    <!-- File Preview -->
                    <div id="filePreview" class="file-preview">
                        <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span id="fileName" class="file-preview-name"></span>
                        <span class="file-remove" onclick="removeFile()">✕</span>
                    </div>
                </div>
                
                <div class="pt-4 flex justify-between">
                    <button type="button" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transform hover:-translate-y-1 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50" onclick="prevStep()">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Previous
                    </button>
                    <button type="button" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-medium hover:shadow-lg transform hover:-translate-y-1 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50" onclick="nextStep()">
                        Next Step
                        <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>
            
            <!-- Step 3: Account Information -->
            <div id="step3" class="form-step hidden space-y-4">
                <div>
                    <label class="block text-gray-700 mb-1 font-medium">Email</label>
                    <div class="relative">
                    <input type="email" name="email" required 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 shadow-sm pl-10" />
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-1 font-medium">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 shadow-sm pl-10" />
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button type="button" id="togglePassword" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg id="eyeIcon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Password must be at least 6 characters long and include uppercase, lowercase, number, and special character.</p>
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-1 font-medium">Confirm Password</label>
                    <div class="relative">
                        <input type="password" name="confirm_password" id="confirmPassword" required
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 shadow-sm pl-10" />
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="pt-4 flex justify-between">
                    <button type="button" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transform hover:-translate-y-1 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50" onclick="prevStep()">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Previous
                    </button>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-medium hover:shadow-lg transform hover:-translate-y-1 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50">
                        Create Account
                        <i class="fas fa-check-circle ml-2"></i>
                    </button>
                </div>
            </div>
        </form>
        
        <p class="text-center mt-6 text-gray-600">Already have an account? <a href="login.php" class="text-purple-600 hover:underline font-medium">Login here</a></p>
    </div>

    <script>
        // Initialize Flatpickr date picker
        flatpickr("#dob", {
            dateFormat: "Y-m-d",
            maxDate: new Date(),
            disableMobile: "true"
        });

        let currentStep = 1;
        const totalSteps = 3;
        
        function nextStep() {
            // Validate current step
            if (!validateStep(currentStep)) return;
            
            if (currentStep < totalSteps) {
                // Hide current step
                document.getElementById(`step${currentStep}`).classList.add('hidden');
                
                // Show next step
                currentStep++;
                document.getElementById(`step${currentStep}`).classList.remove('hidden');
                
                // Update progress bar
                updateProgressBar(currentStep);
            }
        }
        
        function prevStep() {
            if (currentStep > 1) {
                // Hide current step
                document.getElementById(`step${currentStep}`).classList.add('hidden');
                
                // Show previous step
                currentStep--;
                document.getElementById(`step${currentStep}`).classList.remove('hidden');
                
                // Update progress bar
                updateProgressBar(currentStep);
            }
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
                    indicator.classList.add('bg-gradient-to-r', 'from-purple-500', 'to-indigo-500', 'text-white');
                    indicator.classList.remove('bg-gray-200', 'text-gray-500');
                } else if (i === step) {
                    // Current step
                    indicator.classList.add('bg-gradient-to-r', 'from-purple-500', 'to-indigo-500', 'text-white');
                    indicator.classList.remove('bg-gray-200', 'text-gray-500');
                } else {
                    // Upcoming step
                    indicator.classList.add('bg-gray-200', 'text-gray-500');
                    indicator.classList.remove('bg-gradient-to-r', 'from-purple-500', 'to-indigo-500', 'text-white');
                }
            }
            
            // Update step labels
            const labels = document.querySelectorAll('.flex.flex-col.items-center span');
            labels.forEach((label, index) => {
                if (index + 1 < step) {
                    label.classList.add('text-gray-700');
                    label.classList.remove('text-gray-500');
                } else if (index + 1 === step) {
                    label.classList.add('text-gray-700');
                    label.classList.remove('text-gray-500');
                } else {
                    label.classList.add('text-gray-500');
                    label.classList.remove('text-gray-700');
                }
            });
        }
        
        function validateStep(step) {
            let valid = true;
            const inputs = document.querySelectorAll(`#step${step} input, #step${step} select`);
            
            inputs.forEach(input => {
                if (input.hasAttribute('required') && !input.value) {
                    input.classList.add('border-red-500');
                    valid = false;
                } else {
                    input.classList.remove('border-red-500');
                }
            });
            
            if (!valid) {
                alert("Please fill in all required fields.");
                return false;
            }
            
            // Additional validation for step 3 (password match)
            if (step === 3) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                
                if (password !== confirmPassword) {
                    alert("Passwords do not match!");
                    return false;
                }
                
                // Password strength validation
                const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/;
                if (!passwordRegex.test(password)) {
                    alert("Password must be at least 6 characters long and include one uppercase, one lowercase, one number, and one special character.");
                    return false;
                }
            }
            
            return true;
        }
        
        function validateMobile(input) {
            // Allow only numbers
            input.value = input.value.replace(/\D/g, '');
            
            // Check for required length
            if (input.value.length !== 10 && input.value.length > 0) {
                input.classList.add('border-red-500');
            } else {
                input.classList.remove('border-red-500');
            }
        }
        
        function toggleFileUpload() {
            const idProof = document.getElementById('idProof');
            const fileUpload = document.getElementById('fileUpload');
            
            if (idProof.value) {
                fileUpload.classList.remove('hidden');
            } else {
                fileUpload.classList.add('hidden');
            }
        }
        
        function handleFileSelect(input) {
            const filePreview = document.getElementById('filePreview');
            const fileName = document.getElementById('fileName');
            
            if (input.files && input.files[0]) {
                // Check file size (max 500KB)
                if (input.files[0].size > 500000) {
                    alert("File is too large! Maximum size is 500KB.");
                    input.value = '';
                    return;
                }
                
                fileName.textContent = input.files[0].name;
                filePreview.classList.add('active');
            }
        }
        
        function removeFile() {
            const fileInput = document.querySelector('input[type="file"]');
            const filePreview = document.getElementById('filePreview');
            
            fileInput.value = '';
            filePreview.classList.remove('active');
        }
        
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
            }
        });
        
        // Initialize file drop area
        const dropArea = document.querySelector('.file-drop-area');
        const fileInput = document.querySelector('.file-input');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            dropArea.classList.add('is-active');
        }
        
        function unhighlight() {
            dropArea.classList.remove('is-active');
        }
        
        dropArea.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            handleFileSelect(fileInput);
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateProgressBar(1);
        });
    </script>
</body>
</html>