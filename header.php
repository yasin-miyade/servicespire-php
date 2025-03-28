<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsive Header</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.js"></script>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: #818cf8;
            --secondary: #f43f5e;
            --neutral-dark: #1f2937;
            --neutral: #6b7280;
            --neutral-light: #f3f4f6;
        }
        
        body {
            background-color: #f9fafb;
        }

        .nav-link {
            position: relative;
            color: var(--neutral-dark);
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background-color: var(--primary);
            transition: width 0.3s ease, left 0.3s ease;
        }
        
        .nav-link:hover::after {
            width: 100%;
            left: 0;
        }
        
        .nav-link:hover {
            color: var(--primary);
        }

        .gradient-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            transition: all 0.3s ease;
        }

        .gradient-btn:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .header-glass {
            background-color: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
    </style>
</head>
<body>
    <header id="header" class="fixed top-4 w-full max-w-6xl left-1/2 transform -translate-x-1/2 transition-all duration-300 z-50 bg-transparent mt-2 sm:mt-2 md:mt-2 lg:mt-2">
        <nav class="px-6 py-4 rounded-xl">
            <div class="flex justify-between items-center mx-auto max-w-screen-xl">
                <!-- Logo -->
                <a href="index.php" class="flex items-center">
                    <h3 class="text-indigo-800 md:font-bold text-3xl mr-12">
                        <img src="assets/images/logo1.jpg" alt="Logo" class="w-44">
                    </h3>
                </a>
                
                <!-- Desktop Navigation -->
                <div class="hidden lg:flex lg:items-center lg:space-x-8 text-lg">
                    <a href="index.php" class="nav-link block py-2 px-3 transition-colors duration-200">Home</a>
                    <a href="about.php" class="nav-link block py-2 px-3 transition-colors duration-200">About Us</a>
                    <a href="contact_us.php" class="nav-link block py-2 px-3 transition-colors duration-200">Contact Us</a>
                    <a href="services.php" class="nav-link block py-2 px-3 transition-colors duration-200">Services</a>
                </div>

                <!-- CTA Button -->
                <div class="hidden lg:block">
                    <a href="selectrole.php" class="text-white gradient-btn focus:ring-4 focus:ring-indigo-300 font-medium rounded-lg text-sm px-6 py-3 focus:outline-none transition-all duration-200 transform hover:scale-105 shadow-md">
                        Get Started
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-button" class="lg:hidden p-2 text-gray-500 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile Navigation Menu -->
            <div id="mobile-menu" class="hidden w-full mt-4 rounded-lg bg-white shadow-lg p-4 lg:hidden">
                <ul class="flex flex-col space-y-4 text-center">
                    <li><a href="index.php" class="nav-link block py-2 px-3">Home</a></li>
                    <li><a href="about.php" class="nav-link block py-2 px-3">About Us</a></li>
                    <li><a href="contact_us.php" class="nav-link block py-2 px-3">Contact Us</a></li>
                    <li><a href="services.php" class="nav-link block py-2 px-3">Services</a></li>
                    <li>
                        <a href="selectrole.php" class="text-white gradient-btn block rounded-lg text-sm px-6 py-3 shadow-md">
                            Get Started
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- JavaScript for Mobile Menu Toggle -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const header = document.getElementById("header");
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            // Add background and shadow on scroll
            window.addEventListener("scroll", function () {
                if (window.scrollY > 50) {
                    header.classList.add("header-glass", "shadow-lg", "rounded-xl");
                    header.classList.remove("bg-transparent");
                } else {
                    header.classList.remove("header-glass", "shadow-lg", "rounded-xl");
                    header.classList.add("bg-transparent");
                }
            });

            // Highlight the active page
            const currentPath = window.location.pathname.split("/").pop();
            document.querySelectorAll(".nav-link").forEach(link => {
                if (link.getAttribute("href").endsWith(currentPath)) {
                    link.classList.add("border-b-2", "border-indigo-600", "text-indigo-700", "font-semibold");
                }
            });

            // Mobile menu toggle functionality
            mobileMenuButton.addEventListener('click', function () {
                mobileMenu.classList.toggle("hidden");
            });

            // Close menu when clicking outside
            document.addEventListener("click", function (event) {
                if (!mobileMenu.contains(event.target) && !mobileMenuButton.contains(event.target)) {
                    mobileMenu.classList.add("hidden");
                }
            });
        });
    </script>
</body>
</html>
