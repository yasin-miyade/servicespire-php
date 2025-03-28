<?php


ob_start();
// session_start();
require_once('../lib/function.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Use user_id from session for the helper profile
$helper_id = $_SESSION['user_id'];
$db = new db_functions();

// Fetch helper data from database
$helper_data = $db->get_helper_id($helper_id);

// Handle profile update
$message = '';
$messageType = '';

// Handle profile update
// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $mobile = $_POST['mobile'];
    $address = $_POST['address'];
    $bio = $_POST['bio'] ?? '';
    
    // Handle profile photo upload
    $profile_photo_path = $helper_data['profile_photo'] ?? 'assets/images/default.png';
    
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['size'] > 0) {
        $target_dir = "uploads/profile_photos/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES["profile_photo"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid("profile_", true) . "." . $file_extension;
        $profile_photo_path = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $profile_photo_path)) {
            // File uploaded successfully
        } else {
            $message = "Error uploading profile photo.";
            $messageType = "error";
        }
    }

    // Direct database update - using your existing db_functions class
    // Use the update_helper method if it exists, or do a direct SQL update
    $update_result = $db->update_helper_profile(
        $helper_id, 
        $first_name, 
        $last_name, 
        $mobile, 
        $gender, 
        $dob, 
        $address, 
        $bio, 
        $profile_photo_path
    );

    if ($update_result) {
        $message = "Profile updated successfully!";
        $messageType = "success";
        
        // Refresh helper data after update
        $helper_data = $db->get_helper_id($helper_id);
    } else {
        $message = "Failed to update profile.";
        $messageType = "error";
    }
}

// Handle account deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $delete_result = $db->delete_helper_account($helper_id);
    
    if ($delete_result) {
        // Clear session and redirect to login
        session_destroy();
        header("Location: login.php?account_deleted=true");
        exit();
    } else {
        $error_message = "Failed to delete account!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo bin2hex(random_bytes(32)); ?>">
    <title>Profile Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.1/css/all.min.css">
</head>
<body class="bg-gray-50 font-inter">
    <div class="min-h-screen flex flex-col">        
        <!-- Main Content -->
        <div class="container mx-auto px-4 py-8 flex-grow">
            <div class="max-w-5xl mx-auto bg-white rounded-xl shadow-md overflow-hidden">
                <!-- Page Header -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4">
                    <h1 class="text-2xl font-bold text-white">Account Settings</h1>
                </div>

                <div class="flex flex-col md:flex-row">
                    <!-- Mini Sidebar -->
                    <div class="w-full md:w-1/4 bg-gray-50 md:border-r">
                        <nav class="p-4">
                            <ul class="space-y-1">
                                <li>
                                    <button class="tab-link w-full text-left px-4 py-2 rounded-lg bg-blue-100 text-blue-700 font-medium flex items-center" data-tab="profile">
                                        <i class="fas fa-user-circle mr-2"></i> Profile
                                    </button>
                                </li>
                                <li>
                                    <button class="tab-link w-full text-left px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 flex items-center" data-tab="delete-account">
                                        <i class="fas fa-trash-alt mr-2 text-red-500"></i> Delete Account
                                    </button>
                                </li>
                                <li class="mt-8">
                                    <a href="index.php" class="w-full text-left px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 flex items-center">
                                        <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>

                    <!-- Content Area -->
                    <div class="w-full md:w-3/4 p-6">
                        <?php if (!empty($message)): ?>
                        <div class="<?php echo $messageType === 'success' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'; ?> px-4 py-3 mb-4 border rounded-md flex items-center">
                            <i class="<?php echo $messageType === 'success' ? 'fas fa-check-circle text-green-500' : 'fas fa-exclamation-circle text-red-500'; ?> mr-2"></i>
                            <?php echo $message; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error_message)): ?>
                        <div id="error-message" class="bg-red-50 text-red-700 border-red-200 px-4 py-3 mb-4 border rounded-md flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                            <?php echo $error_message; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Profile Section -->
                        <div id="profile" class="tab-content">
                            <div class="flex flex-col md:flex-row items-center mb-6">
                                <div class="relative mb-4 md:mb-0">
                                    <div class="w-24 h-24 rounded-full border-4 border-white shadow-lg overflow-hidden bg-gray-100">
                                        <img id="profilePhoto" src="<?php echo !empty($helper_data['profile_photo']) ? $helper_data['profile_photo'] : 'assets/images/default.png'; ?>" class="w-full h-full object-cover" alt="Profile Photo">
                                    </div>
                                    <div id="photoEditOverlay" class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity hidden cursor-pointer">
                                        <i class="fas fa-camera text-white text-xl"></i>
                                    </div>
                                </div>
                                <div class="md:ml-6 text-center md:text-left">
                                    <h3 class="text-2xl font-bold text-gray-800"><?php echo !empty($helper_data) ? htmlspecialchars($helper_data['first_name'] . ' ' . $helper_data['last_name']) : 'User Name'; ?></h3>
                                    <p class="text-gray-500"><?php echo !empty($helper_data) ? htmlspecialchars($helper_data['email']) : 'email@example.com'; ?></p>
                                </div>
                                <div class="ml-auto mt-4 md:mt-0">
                                    <button id="editButton" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-sm transition-colors flex items-center">
                                        <i class="fas fa-edit mr-2"></i> Edit Profile
                                    </button>
                                </div>
                            </div>

                            <form method="POST" action="" enctype="multipart/form-data" id="profileForm">
                                <input type="file" id="photoInput" name="profile_photo" class="hidden" accept="image/*">
                                
                                <div class="bg-gray-50 p-4 rounded-lg border mb-6">
                                    <h3 class="font-medium text-gray-700 mb-3">Personal Information</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                            <input type="text" name="first_name" value="<?php echo !empty($helper_data) ? htmlspecialchars($helper_data['first_name'] ?? '') : ''; ?>" class="border border-gray-300 rounded-md p-2 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400 focus:outline-none transition-colors bg-gray-50" readonly>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                            <input type="text" name="last_name" value="<?php echo !empty($helper_data) ? htmlspecialchars($helper_data['last_name'] ?? '') : ''; ?>" class="border border-gray-300 rounded-md p-2 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400 focus:outline-none transition-colors bg-gray-50" readonly>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                            <input type="email" value="<?php echo !empty($helper_data) ? htmlspecialchars($helper_data['email'] ?? '') : ''; ?>" class="border border-gray-300 rounded-md p-2 w-full bg-gray-100" readonly disabled>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                            <input type="text" name="mobile" value="<?php echo !empty($helper_data) ? htmlspecialchars($helper_data['mobile'] ?? '') : ''; ?>" class="border border-gray-300 rounded-md p-2 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400 focus:outline-none transition-colors bg-gray-50" readonly>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                                            <select name="gender" class="border border-gray-300 rounded-md p-2 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400 focus:outline-none transition-colors bg-gray-50" disabled>
                                                <option value="male" <?php echo (!empty($helper_data) && ($helper_data['gender'] ?? '') == 'male') ? 'selected' : ''; ?>>Male</option>
                                                <option value="female" <?php echo (!empty($helper_data) && ($helper_data['gender'] ?? '') == 'female') ? 'selected' : ''; ?>>Female</option>
                                                <option value="other" <?php echo (!empty($helper_data) && ($helper_data['gender'] ?? '') == 'other') ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                                            <input type="date" name="dob" value="<?php echo !empty($helper_data) ? htmlspecialchars($helper_data['dob'] ?? '') : ''; ?>" class="border border-gray-300 rounded-md p-2 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400 focus:outline-none transition-colors bg-gray-50" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg border mb-6">
                                    <h3 class="font-medium text-gray-700 mb-3">Additional Information</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                            <input type="text" name="address" value="<?php echo !empty($helper_data) ? htmlspecialchars($helper_data['address'] ?? '') : ''; ?>" class="border border-gray-300 rounded-md p-2 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400 focus:outline-none transition-colors bg-gray-50" readonly>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                                            <textarea name="bio" rows="3" class="border border-gray-300 rounded-md p-2 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400 focus:outline-none transition-colors bg-gray-50" readonly><?php echo !empty($helper_data) ? htmlspecialchars($helper_data['bio'] ?? '') : ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="hidden" id="updateButtonContainer">
                                    <div class="flex space-x-4">
                                        <button type="submit" name="update_profile" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md shadow-sm transition-colors flex items-center">
                                            <i class="fas fa-save mr-2"></i> Save Changes
                                        </button>
                                        <button type="button" id="cancelButton" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md shadow-sm transition-colors flex items-center">
                                            <i class="fas fa-times mr-2"></i> Cancel
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Delete Account Section -->
                        <div id="delete-account" class="tab-content hidden">
                            <h2 class="text-xl font-bold text-red-600 mb-6 flex items-center">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Delete Account
                            </h2>
                            
                            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-red-700">
                                            Warning: This action cannot be undone. All your data will be permanently deleted.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <p class="text-gray-700 mb-6">Please confirm that you want to delete your account. You will lose all your data and won't be able to recover it.</p>
                            
                            <div class="bg-gray-50 p-4 rounded-lg border mb-6">
                                <h3 class="font-medium text-gray-700 mb-3">What happens when you delete your account:</h3>
                                <ul class="space-y-2 ml-6 list-disc text-gray-600">
                                    <li>Your profile information will be permanently deleted</li>
                                    <li>You will lose access to all your services and data</li>
                                    <li>Any active subscriptions will be canceled</li>
                                    <li>You will need to create a new account to use our services again</li>
                                </ul>
                            </div>
                            
                            <form method="POST" action="" id="deleteAccountForm">
                                <button type="submit" name="delete_account" id="deleteAccountBtn" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-md shadow-sm transition-colors flex items-center">
                                    <i class="fas fa-trash-alt mr-2"></i> Delete My Account
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variable to track editing mode
let isEditing = false;

// DOM References
const editButton = document.getElementById('editButton');
const cancelButton = document.getElementById('cancelButton');
const updateButtonContainer = document.getElementById('updateButtonContainer');
const photoEditOverlay = document.getElementById('photoEditOverlay');
const photoInput = document.getElementById('photoInput');
const profilePhoto = document.getElementById('profilePhoto');
const profileForm = document.getElementById('profileForm');
const deleteAccountBtn = document.getElementById('deleteAccountBtn');

// Tab functionality
document.querySelectorAll('.tab-link').forEach(tab => {
    tab.addEventListener('click', () => {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        
        // Remove active class from all tabs
        document.querySelectorAll('.tab-link').forEach(t => {
            t.classList.remove('bg-blue-100', 'text-blue-700');
            t.classList.add('text-gray-700', 'hover:bg-gray-100');
        });
        
        // Show selected tab content
        const tabId = tab.getAttribute('data-tab');
        document.getElementById(tabId).classList.remove('hidden');
        
        // Add active class to selected tab
        tab.classList.add('bg-blue-100', 'text-blue-700');
        tab.classList.remove('text-gray-700', 'hover:bg-gray-100');
    });
});

// Toggle edit mode
function toggleEditMode() {
    const inputs = document.querySelectorAll('#profileForm input:not([disabled]), #profileForm select, #profileForm textarea');
    inputs.forEach(input => {
        input.readOnly = !isEditing;
        input.disabled = !isEditing;
        
        if (isEditing) {
            input.classList.remove('bg-gray-50');
            input.classList.add('bg-white');
        } else {
            input.classList.add('bg-gray-50');
            input.classList.remove('bg-white');
        }
    });
    
    // Toggle photo edit overlay
    photoEditOverlay.classList.toggle('hidden', !isEditing);
    
    // Toggle update button container
    updateButtonContainer.classList.toggle('hidden', !isEditing);
    
    // Toggle edit button text
    if (isEditing) {
        editButton.classList.add('hidden');
    } else {
        editButton.classList.remove('hidden');
    }
}

// Edit button click handler
editButton.addEventListener('click', () => {
    isEditing = true;
    toggleEditMode();
});

// Cancel button click handler
cancelButton.addEventListener('click', () => {
    isEditing = false;
    toggleEditMode();
    
    // Reset form by reloading the page
    location.reload();
});

// Photo edit overlay click handler
photoEditOverlay.addEventListener('click', () => {
    photoInput.click();
});

// Photo input change handler
photoInput.addEventListener('change', (e) => {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            profilePhoto.src = e.target.result;
        };
        reader.readAsDataURL(e.target.files[0]);
    }
});

// Auto-hide success message after 3 seconds
const successMessage = document.getElementById('success-message');
if (successMessage) {
    setTimeout(() => {
        successMessage.classList.add('hidden');
    }, 3000);
}

// Confirm account deletion
const deleteAccountForm = document.getElementById('deleteAccountForm');
if (deleteAccountForm) {
    deleteAccountForm.addEventListener('submit', (e) => {
        if (!confirm("Are you sure you want to delete your account? This action cannot be undone.")) {
            e.preventDefault();
        }
    });
}
    </script>
</body>
</html>