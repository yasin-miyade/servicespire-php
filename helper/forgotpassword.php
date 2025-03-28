<?php
require_once('../lib/function.php');

$db = new db_functions();
$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        $user = $db->get_helper_by_email($email);
        
        if (!$user) {
            $error_message = "No helper found with this email.";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "New password and confirm password do not match.";
        } elseif (!preg_match('/^(?=.[a-z])(?=.[A-Z])(?=.\d)(?=.[\W_]).{6,}$/', $new_password)) {
            $error_message = "New password must be at least 6 characters, including uppercase, lowercase, number, and special character.";
        } else {
            // Update password
            $db->update_helper_password($email, $new_password);
            $success_message = "Password successfully updated. <a href='login.php' class='text-blue-500'>Login now</a>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold text-center mb-4">Reset Password</h2>
        <p class="text-gray-600 text-center mb-6">Enter your email and new password to reset your password.</p>

        <?php if ($error_message): ?>
            <div class="text-red-500 text-center mb-4"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="text-green-500 text-center mb-4"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-600 font-medium mb-2">Email</label>
                <input type="email" name="email" required 
                       class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none transition">
            </div>
            <div class="mb-4">
                <label class="block text-gray-600 font-medium mb-2">New Password</label>
                <input type="password" name="new_password" required 
                       class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none transition">
            </div>
            <div class="mb-4">
                <label class="block text-gray-600 font-medium mb-2">Confirm Password</label>
                <input type="password" name="confirm_password" required 
                       class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none transition">
            </div>
            <button type="submit" 
                    class="w-full p-3 bg-blue-500 text-white font-bold rounded-lg hover:bg-blue-600 transition">
                Reset Password
            </button>
        </form>
        <div class="text-center mt-4">
            <a href="login.php" class="text-blue-500">Back to Login</a>
        </div>
    </div>
</body>
</html>