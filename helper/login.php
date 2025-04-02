<?php
session_start();
require_once('../lib/function.php');

$db = new db_functions();
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_message = "Both fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/', $password)) {
        $error_message = "Invalid password format.";
    } else {
        // Debugging: Print the email and password
        error_log("Attempting to login with email: $email and password: $password");

        $user = $db->login_user($email, $password);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . " " . $user['last_name'];
            // Set a flag for delayed redirect instead of immediately redirecting
            $login_success = true;
        } else {
            $error_message = "Invalid email or password.";
            // Debugging: Log the error
            error_log("Login failed for email: $email");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Secure Portal</title>
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-blue-50 to-indigo-50">
    <!-- Decorative elements -->
    <div class="fixed top-0 left-0 w-full  bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>
    <div class="fixed top-0 right-0 w-1/4 h-screen bg-gradient-to-b from-blue-400 to-indigo-600 opacity-10 rounded-l-full transform translate-x-1/2"></div>
    <div class="fixed bottom-0 left-0 w-1/3 h-screen bg-gradient-to-t from-purple-400 to-indigo-600 opacity-10 rounded-r-full transform translate-y-1/2"></div>

    <!-- Back Button -->
    <div class="absolute top-20 left-32">
        <a href="../selectrole.php" class="flex items-center text-gray-600 hover:text-blue-700 transition group">
            <div class="w-8 h-8 bg-white rounded-full shadow-md flex items-center justify-center mr-2 group-hover:bg-blue-50 transition">
                <i class="fas fa-arrow-left text-blue-600"></i>
            </div>
            <span class="font-medium">Back to Select Role</span>
        </a>
    </div>

    <div class="w-full max-w-md space-y-8 relative">
        <!-- Company Logo Placeholder -->
        <div class="flex justify-center">
            <div class="w-20 h-20 rounded-full flex items-center justify-center bg-gradient-to-r from-blue-600 to-indigo-600 shadow-lg">
                <i class="fas fa-shield-alt text-white text-3xl"></i>
            </div>
        </div>
        
        <div class="bg-white shadow-2xl rounded-2xl overflow-hidden border border-gray-100">
            <!-- Header Section -->
            <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 py-6 px-8 text-center">
                <h1 class="text-2xl font-bold text-white">Welcome Back!</h1>
                <p class="text-blue-100 text-sm mt-1">Log in to your account to access exclusive content.</p>
            </div>
            
            <div class="p-8">
                <?php if (isset($login_success) && $login_success): ?>
                    <div id="successMessage" class="text-green-700 bg-green-50 border border-green-200 px-4 py-3 rounded-lg mb-6 flex items-center justify-center transform transition-all duration-500 ease-in-out">
                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-green-700 mr-3"></div>
                        <span class="font-medium">Login successful. Redirecting...</span>
                    </div>
                    <script>
                        // Add 3-second delay before redirecting
                        setTimeout(function() {
                            window.location.href = 'index.php';
                        }, 3000);
                    </script>
                <?php elseif ($error_message): ?>
                    <div class="text-red-700 bg-red-50 border border-red-200 px-4 py-3 rounded-lg mb-6 flex items-center transform transition-all duration-500 ease-in-out">
                        <i class="fas fa-exclamation-circle mr-3 text-lg"></i>
                        <span class="font-medium"><?php echo htmlspecialchars($error_message); ?></span>
                    </div> 
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input id="email" name="email" type="email" 
                                placeholder="Enter your email"
                                value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block transition-all duration-300 outline-none" 
                                required
                                pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"
                                title="Enter a valid email address">
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <a href="forgotpassword.php" class="text-xs text-indigo-600 hover:text-indigo-800 transition-colors duration-300">Forgot password?</a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input id="password" name="password" type="password" 
                                placeholder="Enter your password" 
                                class="w-full pl-10 pr-10 py-3 border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block transition-all duration-300 outline-none"
                                required
                                minlength="6"
                                title="Password must be at least 6 characters">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i id="togglePassword" class="fas fa-eye text-gray-400 cursor-pointer hover:text-gray-600 transition-colors duration-300"></i>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input id="remember_me" name="remember_me" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-700">
                            Remember me
                        </label>
                    </div>
                    
                    <div>
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:from-blue-700 hover:via-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:-translate-y-0.5">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Log In
                        </button>
                    </div>
                </form>
                
                
                <!-- Register Now Option -->
                <div class="text-center mt-6 border-t border-gray-200 pt-6">
                    <p class="text-gray-600">
                        Don't have an account? 
                        <a href="signup.php" 
                           class="text-indigo-600 hover:text-indigo-800 font-medium transition-colors duration-300">
                            Register Now
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Password Toggle JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            togglePassword.addEventListener('click', function() {
                // Toggle the password visibility
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle the eye icon
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html>