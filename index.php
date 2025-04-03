<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ServiceSpire | Premium Service Connection Platform</title>
    <meta name="description" content="ServiceSpire connects users needing help with verified helpers. Post service requests or offer your assistance - all in one secure platform.">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6A00D4;
            --primary-light: #8E2DE2;
            --secondary: #00C9FF;
            --dark: #1E1E2C;
            --light: #F9F9FF;
            --primary-indigo: #4f46e5;
            --primary-indigo-dark: #4338ca;
            --primary-indigo-light: #818cf8;
            --secondary-pink: #f43f5e;
            --neutral-dark: #1f2937;
            --neutral: #6b7280;
            --neutral-light: #f3f4f6;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            color: var(--dark);
            background-color: #FAFAFF;
        }
        
        h1, h2, h3, h4 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
        }
        
        .gradient-text {
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-light) 100%);
            -webkit-backdrop-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        }
        
        .hero-gradient {
            background: linear-gradient(135deg, #f9f9ff 0%, #f0e9ff 100%);
        }
        
        .card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border-radius: 16px;
            overflow: hidden;
            background: white;
        }
        
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(106, 0, 212, 0.15);
        }
        
        .btn-primary {
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(106, 0, 212, 0.3);
        }
        
        .btn-primary::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: all 0.6s ease;
        }
        
        .btn-primary:hover::after {
            left: 100%;
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            margin-bottom: 1.5rem;
            font-size: 2rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }
        
        .section-header {
            position: relative;
            margin-bottom: 3rem;
        }
        
        .section-header::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            border-radius: 2px;
        }
        
        .service-tag {
            display: inline-block;
            padding: 4px 12px;
            background: rgba(106, 0, 212, 0.1);
            border-radius: 20px;
            color: var(--primary);
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 1rem;
        }
        
        .animated-bg {
            animation: gradientShift 8s ease infinite;
            background-size: 200% 200%;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .step-number {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: white;
            color: var(--primary);
            font-weight: bold;
            font-size: 1.5rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            margin-right: 1.5rem;
            flex-shrink: 0;
        }
        
        .hero-image {
            transition: all 0.3s ease;
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
            height: auto;
            object-fit: contain;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .hero-image:hover {
            transform: scale(1.02);
        }
        
        .hero-container {
            min-height: calc(100vh - 80px);
            display: flex;
            align-items: center;
            padding-top: 1rem;
            padding-bottom: 2rem;
        }
        
        /* Header specific styles */
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
            background: linear-gradient(135deg, var(--primary-indigo), var(--primary-indigo-dark));
            transition: all 0.3s ease;
        }

        .gradient-btn:hover {
            background: linear-gradient(135deg, var(--primary-indigo-dark), var(--primary-indigo));
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .header-glass {
            background-color: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        @media (max-width: 500px) {
            .mt-responsive {
                margin-top: 6rem;
            }
        }
    </style>
</head>

<body class="antialiased">
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

    <!-- Hero Section -->
    <section class="hero-gradient hero-container">
        <div class="container mx-auto px-6">
            <div class="flex flex-col lg:ml-10 lg:flex-row items-center mt-responsive">
                <div class="w-full lg:w-1/2 lg:pr-10 mb-8 lg:mb-0">
                    <div class="mb-4">
                        <span class="text-purple-700 font-semibold text-lg tracking-wider uppercase">Community-Powered Services</span>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-bold leading-tight mb-4">
                        Connect with <span class="gradient-text">Trusted Helpers</span> for All Your Needs
                    </h1>
                    <p class="text-lg text-gray-600 mb-6 max-w-lg">
                        ServiceSpire bridges the gap between those needing assistance and skilled helpers ready to provide it. Post your request or offer your expertise - all in one secure platform.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="user/login.php" class="btn-primary font-medium py-3 px-6 rounded-full text-base">
                            Request Help <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                        <a href="helper/login.php" class="border-2 border-purple-600 text-purple-600 font-medium py-3 px-6 rounded-full transition duration-300 hover:bg-purple-50 text-base">
                            Become a Helper
                        </a>
                    </div>
                </div>
                
                <div class="w-full mt-32 lg:w-1/2 flex justify-center">
                    <img src="assets/images/Web1.jpg" alt="ServiceSpire platform in action" class="hero-image">
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-16 bg-gray-50" id="how-it-works">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12 section-header">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">How <span class="gradient-text">ServiceSpire</span> Works</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Whether you need help or want to offer assistance, our platform makes the process simple and secure
                </p>
            </div>
            
            <!-- For Users -->
            <section class="py-20">
                <div class="flex flex-col md:flex-row justify-center items-center gap-10 px-10">
                    <!-- User Card -->
                    <div class="max-w-md w-full p-8 bg-white shadow-lg rounded-lg border border-gray-300 transition-transform transform hover:-translate-y-2 hover:shadow-2xl">
                        <div class="flex items-center gap-4">
                            <i class="fa-solid fa-user text-5xl text-blue-600"></i>
                            <h3 class="text-2xl font-semibold">For Users</h3>
                        </div>
                        <p class="text-gray-600 mt-4">
                            Need reliable services? Sign up as a user and find skilled professionals instantly.
                        </p>
                        <ul class="mt-4 space-y-2 text-gray-700">
                            <li>✅ Book trusted service providers</li>
                            <li>✅ Pay securely & get assistance</li>
                            <li>✅ Track service status in real-time</li>
                        </ul>
                        <a href="user/login.php" class="mt-5 inline-block bg-blue-600 text-white py-2 px-5 rounded-lg hover:bg-blue-800">Join as User</a>
                    </div>

                    <!-- Helper Card -->
                    <div class="max-w-md w-full p-8 bg-white shadow-lg rounded-lg border border-gray-300 transition-transform transform hover:-translate-y-2 hover:shadow-2xl">
                        <div class="flex items-center gap-4">
                            <i class="fa-solid fa-handshake text-5xl text-green-600"></i>
                            <h3 class="text-2xl font-semibold">For Helpers</h3>
                        </div>
                        <p class="text-gray-600 mt-4">
                            Want to earn by helping others? Register as a helper and offer your services.
                        </p>
                        <ul class="mt-4 space-y-2 text-gray-700">
                            <li>✅ Get paid for your skills</li>
                            <li>✅ Connect with real clients</li>
                            <li>✅ Flexible work schedule</li>
                        </ul>
                        <a href="helper/login.php" class="mt-5 inline-block bg-green-600 text-white py-2 px-5 rounded-lg hover:bg-green-800">Join as Helper</a>
                    </div>
                </div>
            </section>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12 section-header">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">Why Choose <span class="gradient-text">ServiceSpire</span></h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Our platform is designed for safety, convenience, and community connection
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="card p-6">
                    <div class="feature-icon bg-purple-100 text-purple-600">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-2">Secure Platform</h3>
                    <p class="text-gray-600">
                        All helpers are verified and our payment system protects both parties. Your safety is our priority.
                    </p>
                </div>
                
                <div class="card p-6">
                    <div class="feature-icon bg-green-100 text-green-600">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-2">Quick Matching</h3>
                    <p class="text-gray-600">
                        Get connected with helpers in your area within hours, not days. Fast response times guaranteed.
                    </p>
                </div>
                
                <div class="card p-6">
                    <div class="feature-icon bg-blue-100 text-blue-600">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-2">Fair Pricing</h3>
                    <p class="text-gray-600">
                        Helpers set competitive rates and you agree on price before service begins. No surprises.
                    </p>
                </div>
                
                <div class="card p-6">
                    <div class="feature-icon bg-yellow-100 text-yellow-600">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-2">Ratings & Reviews</h3>
                    <p class="text-gray-600">
                        Our community rating system helps you choose the best helper for your needs.
                    </p>
                </div>
                
                <div class="card p-6">
                    <div class="feature-icon bg-red-100 text-red-600">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-2">24/7 Support</h3>
                    <p class="text-gray-600">
                        Our team is always available to help resolve any issues or answer questions.
                    </p>
                </div>
                
                <div class="card p-6">
                    <div class="feature-icon bg-indigo-100 text-indigo-600">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-2">Community Focus</h3>
                    <p class="text-gray-600">
                        Build relationships with helpers in your neighborhood. Many users find their "go-to" helper.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Service Categories Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12 section-header">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">Popular <span class="gradient-text">Service Categories</span></h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    These are some of the most requested services on our platform
                </p>
            </div>
            
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="card p-4 group">
                    <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mb-3 text-purple-600 transition-all duration-300 group-hover:bg-purple-600 group-hover:text-white">
                        <i class="fas fa-tools text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-1">Home Repairs</h3>
                    <p class="text-gray-600 text-sm">
                        Plumbing, electrical, furniture assembly, and general handyman services
                    </p>
                    <span class="service-tag group-hover:bg-purple-600 group-hover:text-white transition-all">Most Popular</span>
                </div>
                
                <div class="card p-4 group">
                    <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mb-3 text-blue-600 transition-all duration-300 group-hover:bg-blue-600 group-hover:text-white">
                        <i class="fas fa-laptop-code text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-1">Tech Support</h3>
                    <p class="text-gray-600 text-sm">
                        Computer help, smart home setup, software installation, and troubleshooting
                    </p>
                    <span class="service-tag group-hover:bg-blue-600 group-hover:text-white transition-all">Fast Response</span>
                </div>
                
                <div class="card p-4 group">
                    <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mb-3 text-green-600 transition-all duration-300 group-hover:bg-green-600 group-hover:text-white">
                        <i class="fas fa-car text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-1">Transportation</h3>
                    <p class="text-gray-600 text-sm">
                        Rides to appointments, grocery delivery, and moving assistance
                    </p>
                    <span class="service-tag group-hover:bg-green-600 group-hover:text-white transition-all">Available Now</span>
                </div>
                
                <div class="card p-4 group">
                    <div class="w-14 h-14 bg-yellow-100 rounded-xl flex items-center justify-center mb-3 text-yellow-600 transition-all duration-300 group-hover:bg-yellow-600 group-hover:text-white">
                        <i class="fas fa-book text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-1">Tutoring</h3>
                    <p class="text-gray-600 text-sm">
                        Academic subjects, language learning, music lessons, and test prep
                    </p>
                    <span class="service-tag group-hover:bg-yellow-600 group-hover:text-white transition-all">Online Available</span>
                </div>
                
                <div class="card p-4 group">
                    <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center mb-3 text-red-600 transition-all duration-300 group-hover:bg-red-600 group-hover:text-white">
                        <i class="fas fa-heart text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-1">Personal Care</h3>
                    <p class="text-gray-600 text-sm">
                        Senior assistance, pet care, meal prep, and organization help
                    </p>
                    <span class="service-tag group-hover:bg-red-600 group-hover:text-white transition-all">Compassionate</span>
                </div>
                
                <div class="card p-4 group">
                    <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center mb-3 text-indigo-600 transition-all duration-300 group-hover:bg-indigo-600 group-hover:text-white">
                        <i class="fas fa-paint-brush text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-1">Creative Services</h3>
                    <p class="text-gray-600 text-sm">
                        Photography, graphic design, writing, and artistic projects
                    </p>
                    <span class="service-tag group-hover:bg-indigo-600 group-hover:text-white transition-all">Skilled Pros</span>
                </div>
                
                <div class="card p-4 group">
                    <div class="w-14 h-14 bg-pink-100 rounded-xl flex items-center justify-center mb-3 text-pink-600 transition-all duration-300 group-hover:bg-pink-600 group-hover:text-white">
                        <i class="fas fa-briefcase text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-1">Professional Help</h3>
                    <p class="text-gray-600 text-sm">
                        Resume writing, career coaching, business consulting, and legal advice
                    </p>
                    <span class="service-tag group-hover:bg-pink-600 group-hover:text-white transition-all">Expert Advice</span>
                </div>
                
                <div class="card p-4 group">
                    <div class="w-14 h-14 bg-teal-100 rounded-xl flex items-center justify-center mb-3 text-teal-600 transition-all duration-300 group-hover:bg-teal-600 group-hover:text-white">
                        <i class="fas fa-calendar-check text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-1">Event Help</h3>
                    <p class="text-gray-600 text-sm">
                        Party setup, catering assistance, photography, and event planning
                    </p>
                    <span class="service-tag group-hover:bg-teal-600 group-hover:text-white transition-all">Stress-Free</span>
                </div>
            </div>
            
            <div class="text-center mt-10">
                <a href="services.php" class="btn-primary inline-block font-medium py-3 px-6 rounded-full text-base">
                    Browse All Categories
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 gradient-bg text-white animated-bg">
        <div class="container mx-auto px-6">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">Ready to Get Started?</h2>
                <p class="text-lg mb-6 opacity-90">
                    Join thousands of community members helping each other through ServiceSpire
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-3">
                    <a href="user/login.php" class="bg-white text-purple-700 hover:bg-gray-100 font-medium py-3 px-6 rounded-full transition duration-300 transform hover:scale-105 shadow-lg text-base">
                        I Need Help
                    </a>
                    <a href="helper/login.php" class="border-2 border-white text-white hover:bg-white hover:text-purple-700 font-medium py-3 px-6 rounded-full transition duration-300 text-base">
                        I Want to Help
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">ServiceSpire</h3>
                    <p class="text-gray-400">
                        Connecting communities through trusted services and helping hands.
                    </p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-400 hover:text-white transition">Home</a></li>
                        <li><a href="about.php" class="text-gray-400 hover:text-white transition">About Us</a></li>
                        <li><a href="services.php" class="text-gray-400 hover:text-white transition">Services</a></li>
                        <li><a href="contact_us.php" class="text-gray-400 hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Terms of Service</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Cookie Policy</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Connect With Us</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2023 ServiceSpire. All rights reserved.</p>
            </div>
        </div>
    </footer>

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
            const currentPath = window.location.pathname;
            document.querySelectorAll(".nav-link").forEach(link => {
                if (
                    (currentPath.endsWith('/servicespire1/') || 
                     currentPath.endsWith('/servicespire1/index.php') ||
                     currentPath === '/servicespire1') && 
                    link.getAttribute("href") === "index.php"
                ) {
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

            // Simple animations for cards
            const animateOnScroll = () => {
                const cards = document.querySelectorAll('.card');
                cards.forEach(card => {
                    const cardPosition = card.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.3;
                    
                    if(cardPosition < screenPosition) {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }
                });
            };
            
            // Set initial state
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.6s ease';
            });
            
            // Run on load and scroll
            animateOnScroll();
            window.addEventListener('scroll', animateOnScroll);
            
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });
        });
    </script>
</body>
</html>