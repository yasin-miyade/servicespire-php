<?php
// session_start();
require_once('../lib/function.php');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['email'])) {
    // Redirect to login page if not logged in
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$db = new db_functions();

// Fetch user data from database
$user = $db->get_user_by_id($user_id);

// Handle profile update
$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $mobile = $_POST['mobile'];
    $address = $_POST['address'];
    $bio = $_POST['bio'] ?? '';
    
    // Handle profile photo upload
    $profile_photo_path = $user['profile_photo'] ?? 'assets/images/default.png';
    
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
    
    // Update user profile
    if ($db->update_user_profile($user_id, $first_name, $last_name, $dob, $gender, $mobile, $address, $bio, $profile_photo_path)) {
        $message = "Profile updated successfully!";
        $messageType = "success";
        
        // Refresh user data after update
        $user = $db->get_user_by_id($user_id);
    } else {
        $message = "Failed to update profile.";
        $messageType = "error";
    }
}

// Handle account deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_account'])) {
    if ($db->delete_user_account($user_id)) {
        // Clear session and redirect to login
        session_destroy();
        header('Location: login.php?account_deleted=1');
        exit();
    } else {
        $message = "Failed to delete account.";
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ServiceSpire Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col">        

        <!-- Main Content -->
        <div class="container mx-auto px-4 py-8 flex-grow">
            <div class="max-w-5xl mx-auto bg-white rounded-xl shadow-md overflow-hidden">
                <!-- Page Header -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b">
                    <h1 class="text-2xl font-bold text-gray-800">Account Settings</h1>
                </div>

                <div class="flex flex-col md:flex-row">
                    <!-- Sidebar -->
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
                        
                        <!-- Profile Section -->
                        <div id="profile" class="tab-content">
                            <div class="flex justify-between items-center mb-6">
                                <h2 class="text-xl font-bold text-gray-800">Personal Information</h2>
                                <button id="editButton" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-sm transition-colors flex items-center">
                                    <i class="fas fa-edit mr-2"></i> Edit Profile
                                </button>
                            </div>

                            <form method="POST" action="" enctype="multipart/form-data" id="profileForm">
                                <div class="flex flex-col items-center mb-8">
                                    <div class="relative" id="profilePhotoContainer">
                                        <div class="w-32 h-32 rounded-full border-4 border-white shadow-lg overflow-hidden bg-gray-100">
                                            <img id="profilePhoto" src="<?php echo !empty($user['profile_photo']) ? $user['profile_photo'] : 'assets/images/default.png'; ?>" class="w-full h-full object-cover" alt="Profile Photo" />
                                        </div>
                                        <div id="photoEditOverlay" class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity hidden cursor-pointer">
                                            <i class="fas fa-camera text-white text-xl"></i>
                                        </div>
                                        <input type="file" id="photoInput" name="profile_photo" class="hidden" accept="image/*">
                                    </div>
                                    <p class="text-sm text-gray-500 mt-2 hidden" id="photoHint">Click to change photo</p>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg border mb-6">
                                    <h3 class="font-medium text-gray-700 mb-3">Profile Information</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" class="border border-gray-300 rounded-md p-2 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400 focus:outline-none transition-colors" readonly>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                            <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" class="border border-gray-300 rounded-md p-2 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400 focus:outline-none transition-colors" readonly>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                            <input type="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" class="border border-gray-300 rounded-md p-2 w-full bg-gray-100" readonly disabled>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                            <input type="text" name="mobile" value="<?php echo htmlspecialchars($user['mobile'] ?? ''); ?>" class="border border-gray-300 rounded-md p-2 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400 focus:outline-none transition-colors" readonly>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                                            <select name="gender" class="border border-gray-300 rounded-md p-2 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400 focus:outline-none transition-colors bg-white" disabled>
                                                <option value="male" <?php echo (isset($user['gender']) && $user['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                                                <option value="female" <?php echo (isset($user['gender']) && $user['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                                                <option value="other" <?php echo (isset($user['gender']) && $user['gender'] == 'other') ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                                            <input type="date" name="dob" value="<?php echo htmlspecialchars($user['dob'] ?? ''); ?>" class="border border-gray-300 rounded-md p-2 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400 focus:outline-none transition-colors" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg border mb-6">
                                    <h3 class="font-medium text-gray-700 mb-3">Additional Information</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                            <input type="text" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" class="border border-gray-300 rounded-md p-2 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400 focus:outline-none transition-colors" readonly>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                                            <textarea name="bio" rows="3" class="border border-gray-300 rounded-md p-2 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400 focus:outline-none transition-colors" readonly><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
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
                                    <li>You will need to create a new account to use ServiceSpire again</li>
                                </ul>
                            </div>
                            
                            <form method="POST" action="" onsubmit="return confirm('Are you absolutely sure you want to delete your account? This action cannot be undone.');">
                                <button type="submit" name="delete_account" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-md shadow-sm transition-colors flex items-center">
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
        const editButton = document.getElementById("editButton");
        const inputs = document.querySelectorAll("input[type=text], input[type=email], input[type=date], textarea, select");
        const profilePhotoContainer = document.getElementById("profilePhotoContainer");
        const photoInput = document.getElementById("photoInput");
        const profilePhoto = document.getElementById("profilePhoto");
        const tabLinks = document.querySelectorAll(".tab-link");
        const tabContents = document.querySelectorAll(".tab-content");
        const updateButtonContainer = document.getElementById("updateButtonContainer");
        const cancelButton = document.getElementById("cancelButton");
        const photoHint = document.getElementById("photoHint");
        const photoEditOverlay = document.getElementById("photoEditOverlay");

        let isEditing = false;

        editButton.addEventListener("click", () => {
            isEditing = !isEditing;
            toggleEditMode();
        });

        cancelButton.addEventListener("click", () => {
            isEditing = false;
            toggleEditMode();
            // Reset form to original values
            document.getElementById("profileForm").reset();
            // Reset profile photo preview
            profilePhoto.src = '<?php echo !empty($user['profile_photo']) ? $user['profile_photo'] : 'assets/images/default.png'; ?>';
        });

        function toggleEditMode() {
            editButton.innerHTML = isEditing ? '<i class="fas fa-spinner fa-spin mr-2"></i> Editing...' : '<i class="fas fa-edit mr-2"></i> Edit Profile';
            editButton.style.display = isEditing ? "none" : "block";
            updateButtonContainer.classList.toggle("hidden", !isEditing);
            photoHint.classList.toggle("hidden", !isEditing);
            photoEditOverlay.classList.toggle("hidden", !isEditing);
            
            inputs.forEach(input => {
                if (!input.disabled) { // Skip email field which is permanently disabled
                    input.readOnly = !isEditing;
                    if (isEditing) {
                        input.classList.add("bg-white");
                    } else {
                        input.classList.remove("bg-white");
                    }
                }
            });
            
            document.querySelector("select").disabled = !isEditing;
        }

        profilePhotoContainer.addEventListener("click", () => {
            if (isEditing) {
                photoInput.click();
            }
        });

        photoInput.addEventListener("change", (event) => {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    profilePhoto.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        tabLinks.forEach(link => {
            link.addEventListener("click", (e) => {
                const target = e.target.closest('.tab-link').dataset.tab;
                
                // If we're in edit mode and trying to leave profile tab, confirm first
                if (isEditing && target !== "profile") {
                    if (!confirm("You have unsaved changes. Are you sure you want to leave this page?")) {
                        return;
                    } else {
                        isEditing = false;
                        toggleEditMode();
                    }
                }
                
                tabContents.forEach(content => content.classList.add("hidden"));
                document.getElementById(target).classList.remove("hidden");

                tabLinks.forEach(link => {
                    link.classList.remove("bg-blue-100", "text-blue-700", "font-medium");
                    link.classList.add("text-gray-700", "hover:bg-gray-100");
                });
                
                const clickedTab = e.target.closest('.tab-link');
                clickedTab.classList.add("bg-blue-100", "text-blue-700", "font-medium");
                clickedTab.classList.remove("text-gray-700", "hover:bg-gray-100");
            });
        });
    </script>
</body>
</html>