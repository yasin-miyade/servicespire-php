<?php
session_start();
require_once('lib/function.php');

$db = new db_functions();
$flag = $_SESSION['flag'] ?? 0; // Retrieve flag value from session
unset($_SESSION['flag']); // Clear flag after use

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $var_username = $_POST['username'] ?? '';
    $var_email = $_POST['email'] ?? '';
    $var_phone = $_POST['phone'] ?? ''; 
    $var_message = $_POST['message'] ?? '';

    if (!empty($var_username) && !empty($var_email) && !empty($var_message)) {
        if ($db->save_contact_data($var_username, $var_email, $var_phone, $var_message)) {
            $_SESSION['flag'] = 1; // Success
        } else {
            $_SESSION['flag'] = 2; // Error
        }
    } else {
        $_SESSION['flag'] = 3; // Validation error
    }
    
    header("Location: contact_us.php"); // Redirect to avoid resubmission on refresh
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | ServiceSpire</title>
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --secondary: #10b981;
            --accent: #f59e0b;
            --dark: #111827;
            --light: #f9fafb;
        }
        
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background-color: #f8fafc;
            scroll-behavior: smooth;
        }
        
        .gradient-text {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
            border-color: rgba(99, 102, 241, 0.3);
        }
        
        .contact-icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(99, 102, 241, 0.05) 100%);
            transition: all 0.4s ease;
        }
        
        .contact-card:hover .contact-icon {
            transform: rotate(5deg) scale(1.1);
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(99, 102, 241, 0.1) 100%);
        }
        
        .input-field {
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            background: rgba(249, 250, 251, 0.7);
        }
        
        .input-field:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
            background: white;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.6s ease;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        
        .animate-float {
            animation: float 4s ease-in-out infinite;
        }
        
        .hero-pattern {
            background-image: radial-gradient(rgba(99, 102, 241, 0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            opacity: 0.6;
        }
        
        .section-title {
            position: relative;
            display: inline-block;
            margin-bottom: 2rem;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -10px;
            width: 60px;
            height: 4px;
            background: linear-gradient(to right, var(--primary-dark), var(--primary));
            border-radius: 4px;
        }
        
        .shape-blob {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 30% 50% 20% 40%;
            opacity: 0.1;
            z-index: 0;
        }
        
        .shape-blob.one {
            background: var(--primary);
            left: -200px;
            top: -150px;
            transform: rotate(-45deg);
            animation: float 15s linear infinite;
        }
        
        .shape-blob.two {
            background: var(--secondary);
            right: -100px;
            bottom: -150px;
            transform: rotate(45deg);
            animation: float 18s linear infinite reverse;
        }
    </style>
</head>
<body class="antialiased">
<?php include 'header.php'; ?>

<!-- Hero Section -->
<section class="relative py-24 md:py-32 overflow-hidden">
    <div class="shape-blob one"></div>
    <div class="shape-blob two"></div>
    <div class="hero-pattern absolute inset-0"></div>
    <div class="max-w-7xl mx-auto px-6 relative z-10">
        <div class="text-center">
            <h1 class="text-5xl md:text-6xl font-bold mb-6">Contact <span class="gradient-text">ServiceSpire</span></h1>
            <p class="text-xl md:text-2xl text-gray-600 max-w-3xl mx-auto">
                We're here to answer your questions and help you succeed. Reach out to us today.
            </p>
        </div>
    </div>
</section>

<!-- Main Content -->
<div class="max-w-6xl mx-auto px-6 py-16 relative z-10">
    <div class="flex flex-col lg:flex-row gap-12">
        <!-- Contact Info Section -->
        <div class="lg:w-5/12">
            <div class="glass-card p-10 sticky top-24">
                <h2 class="text-3xl font-bold text-indigo-700 section-title">How Can We Help?</h2>
                <p class="text-gray-600 text-lg mb-10">
                    Our team of experts is ready to provide you with exceptional service. Connect with us using any of these channels.
                </p>
                
                <div class="space-y-8">
                    <!-- Phone Contact -->
                    <div class="contact-card glass-card p-8 flex items-start">
                        <div class="contact-icon mr-6 animate-float">
                            <i class="fas fa-phone-alt text-indigo-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Phone Support</h3>
                            <p class="text-gray-600 mt-2">+91 9604364376</p>
                            <p class="text-sm text-gray-500 mt-3">Available 24/7 for urgent inquiries</p>
                        </div>
                    </div>
                    
                    <!-- Email Contact -->
                    <div class="contact-card glass-card p-8 flex items-start">
                        <div class="contact-icon mr-6 animate-float" style="animation-delay: 0.5s;">
                            <i class="fas fa-envelope text-indigo-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Email Us</h3>
                            <p class="text-gray-600 mt-2">servicespire@gmail.com</p>
                            <p class="text-sm text-gray-500 mt-3">We'll respond within 24 hours</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contact Form Section -->
        <div class="lg:w-7/12">
            <div class="glass-card overflow-hidden">
                <div class="gradient-bg px-10 py-8">
                    <h2 class="text-2xl font-bold text-white">Send Us A Message</h2>
                    <p class="text-indigo-100 mt-2">We'd love to hear from you</p>
                </div>
                
                <div class="p-10">
                    <!-- Status Messages -->
                    <?php if ($flag == 1): ?>
                        <div class="mb-8 rounded-xl bg-green-50 p-5 border-l-4 border-green-500">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-base font-medium text-green-800">Your message has been sent successfully!</p>
                                    <p class="mt-1 text-sm text-green-700">We'll get back to you as soon as possible.</p>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($flag == 2): ?>
                        <div class="mb-8 rounded-xl bg-red-50 p-5 border-l-4 border-red-500">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-base font-medium text-red-800">Something went wrong.</p>
                                    <p class="mt-1 text-sm text-red-700">Please try again or contact us directly.</p>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($flag == 3): ?>
                        <div class="mb-8 rounded-xl bg-amber-50 p-5 border-l-4 border-amber-500">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-base font-medium text-amber-800">Please fill out all required fields.</p>
                                    <p class="mt-1 text-sm text-amber-700">Fields marked with * are required.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Contact Form -->
                    <form action="contact_us.php" method="POST" class="space-y-6">
                        <div class="space-y-2">
                            <label for="username" class="text-sm font-medium text-gray-700">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" id="username" name="username" required 
                                   class="input-field w-full px-5 py-3 rounded-xl text-gray-700 focus:outline-none">
                        </div>
                        
                        <div class="space-y-2">
                            <label for="email" class="text-sm font-medium text-gray-700">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" required 
                                   class="input-field w-full px-5 py-3 rounded-xl text-gray-700 focus:outline-none">
                        </div>
                        
                        <div class="space-y-2">
                            <label for="phone" class="text-sm font-medium text-gray-700">Phone Number</label>
                            <input type="text" id="phone" name="phone" 
                                   class="input-field w-full px-5 py-3 rounded-xl text-gray-700 focus:outline-none">
                            <p class="text-xs text-gray-500 mt-2">Optional, but helpful for urgent matters</p>
                        </div>
                        
                        <div class="space-y-2">
                            <label for="message" class="text-sm font-medium text-gray-700">Your Message <span class="text-red-500">*</span></label>
                            <textarea id="message" name="message" rows="5" required 
                                      class="input-field w-full px-5 py-3 rounded-xl text-gray-700 focus:outline-none resize-none"></textarea>
                        </div>
                        
                        <div class="pt-4">
                            <button type="submit" 
                                    class="btn-primary w-full text-white py-4 rounded-xl text-lg font-semibold">
                                Send Message
                                <i class="fas fa-paper-plane ml-2"></i>
                            </button>
                            <p class="text-xs text-center text-gray-500 mt-3">We'll get back to you within 24 hours</p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
    // Enhanced form interactions
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.input-field');
        
        inputs.forEach(input => {
            // Add floating label effect
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('is-focused');
            });
            
            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.parentElement.classList.remove('is-focused');
                }
            });
            
            // Check for existing content (e.g., on page refresh)
            if (input.value !== '') {
                input.parentElement.classList.add('is-focused');
            }
        });
        
        // Basic form validation
        const form = document.querySelector('form');
        form.addEventListener('submit', function(event) {
            let valid = true;
            const requiredInputs = form.querySelectorAll('[required]');
            
            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    valid = false;
                    input.classList.add('border-red-500');
                } else {
                    input.classList.remove('border-red-500');
                }
            });
            
            // Email validation
            const emailInput = form.querySelector('[type="email"]');
            if (emailInput && emailInput.value) {
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(emailInput.value)) {
                    valid = false;
                    emailInput.classList.add('border-red-500');
                }
            }
            
            if (!valid) {
                event.preventDefault();
            }
        });
    });
</script>
</body>
</html>