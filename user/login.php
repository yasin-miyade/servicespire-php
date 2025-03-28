<?php
session_start();
require_once('../lib/function.php'); // Include database connection

$flag = 0; // Status flag for displaying messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $db = new db_functions();
    $user = $db->get_user_by_email($email); // Fetch user from DB by email

    if ($user) {
        if ($password === $user['password']) { // Direct comparison (since passwords are stored in plain text)
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $flag = 1; // Success flag for delayed redirect
        } else {
            $flag = 2; // Incorrect password
        }
    } else {
        $flag = 3; // User not found
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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-blue-50 to-indigo-50">
    <!-- Decorative elements -->
    <div class="fixed top-0 left-0 w-full bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>
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
                <h1 class="text-2xl font-bold text-white">Welcome Back</h1>
                <p class="text-blue-100 text-sm mt-1">Please sign in to your account</p>
            </div>
            
            <div class="p-8">
                <?php if ($flag == 1) { ?>
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
                <?php } elseif ($flag == 2) { ?>
                    <div class="text-red-700 bg-red-50 border border-red-200 px-4 py-3 rounded-lg mb-6 flex items-center transform transition-all duration-500 ease-in-out">
                        <i class="fas fa-exclamation-circle mr-3 text-lg"></i>
                        <span class="font-medium">Invalid password. Please try again.</span>
                    </div>
                <?php } elseif ($flag == 3) { ?>
                    <div class="text-red-700 bg-red-50 border border-red-200 px-4 py-3 rounded-lg mb-6 flex items-center transform transition-all duration-500 ease-in-out">
                        <i class="fas fa-exclamation-circle mr-3 text-lg"></i>
                        <span class="font-medium">User not found. Please check your email.</span>
                    </div>
                <?php } ?>
                
                <form action="" method="POST" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input type="email" id="email" name="email" required 
                                placeholder="yourname@example.com" 
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block transition-all duration-300 outline-none">
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
                            <input type="password" id="password" name="password" required 
                                placeholder="Enter your password" 
                                class="w-full pl-10 pr-10 py-3 border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block transition-all duration-300 outline-none">
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
                            Sign in
                        </button>
                    </div>
                </form>
                
                <!-- Social Login Options -->
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">Or continue with</span>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-3 gap-3">
                        <div>
                            <a href="#" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-all duration-300">
                                <i class="fab fa-google"></i>
                            </a>
                        </div>
                        <div>
                            <a href="#" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-all duration-300">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        </div>
                        <div>
                            <a href="#" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-all duration-300">
                                <i class="fab fa-apple"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-6 border-t border-gray-200 pt-6">
                    <p class="text-gray-600">Don't have an account? <a href="signup.php" class="text-indigo-600 hover:text-indigo-800 font-medium transition-colors duration-300">Create one</a></p>
                </div>
            </div>
        </div>
        
        <div class="mt-6 text-center text-gray-500 text-sm">
            <p>&copy; 2025 Your Company. All rights reserved.</p>
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