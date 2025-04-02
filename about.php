<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - ServiceSpire</title>
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
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
            color: var(--dark);
            background-color: #f8fafc;
            scroll-behavior: smooth;
        }
        
        h1, h2, h3, h4 {
            font-family: 'Manrope', sans-serif;
            font-weight: 800;
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
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            position: relative;
        }
        
        .glass-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, rgba(99, 102, 241, 0) 70%);
            z-index: -1;
        }
        
        .glass-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(79, 70, 229, 0.15);
            border-color: rgba(99, 102, 241, 0.5);
        }
        
        .section-title {
            position: relative;
            display: inline-block;
            margin-bottom: 2.5rem;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -12px;
            width: 60px;
            height: 5px;
            background: linear-gradient(to right, var(--primary-dark), var(--primary));
            border-radius: 4px;
        }
        
        .team-card {
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(226, 232, 240, 0.8);
            position: relative;
        }
        
        .team-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(to right, var(--primary-dark), var(--primary));
        }
        
        .team-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 30px 60px -12px rgba(79, 70, 229, 0.2);
        }
        
        .team-image {
            border: none;
            box-shadow: 0 0 0 4px white, 0 0 0 8px var(--primary-light);
            transition: all 0.5s ease;
        }
        
        .team-card:hover .team-image {
            transform: scale(1.08);
            box-shadow: 0 0 0 4px white, 0 0 0 8px var(--primary-light), 0 15px 30px -5px rgba(79, 70, 229, 0.4);
        }
        
        .hero-section {
            background: linear-gradient(135deg, #f9fafb 0%, #f0f4ff 100%);
            position: relative;
            overflow: hidden;
            min-height: 80vh;
            display: flex;
            align-items: center;
        }
        
        .hero-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(rgba(99, 102, 241, 0.15) 1px, transparent 1px),
                radial-gradient(rgba(99, 102, 241, 0.1) 1px, transparent 1px);
            background-size: 40px 40px, 80px 80px;
            background-position: 0 0, 40px 40px;
            opacity: 0.6;
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(99, 102, 241, 0.05) 100%);
            margin-bottom: 2rem;
            transition: all 0.5s ease;
        }
        
        .feature-card:hover .feature-icon {
            transform: rotate(5deg) scale(1.1);
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(99, 102, 241, 0.1) 100%);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: white;
            font-weight: 700;
            transition: all 0.4s ease;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
            position: relative;
            overflow: hidden;
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
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        .animate-fadeInUp {
            animation: fadeInUp 1s ease forwards;
        }
        
        .delay-100 {
            animation-delay: 0.1s;
        }
        
        .delay-200 {
            animation-delay: 0.2s;
        }
        
        .delay-300 {
            animation-delay: 0.3s;
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
    <!-- Header -->
    <?php include 'header.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="shape-blob one"></div>
        <div class="shape-blob two"></div>
        <div class="hero-pattern"></div>
        <div class="max-w-7xl mx-auto px-6 w-full relative z-10">
            <div class="text-center mb-12">
                <h1 class="text-5xl md:text-7xl font-bold mb-8 animate-fadeInUp">
                    <span class="gradient-text">ServiceSpire</span> Story
                </h1>
                <p class="text-xl md:text-2xl text-gray-600 max-w-4xl mx-auto animate-fadeInUp delay-100 leading-relaxed">
                    Where community meets purpose through meaningful connections and shared support.
                </p>
                <div class="mt-12 animate-fadeInUp delay-200">
                    <a href="#our-mission" class="btn-primary inline-block py-4 px-10 rounded-full text-lg font-bold">
                        Explore Our Journey
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <div class="max-w-6xl mx-auto py-16 px-6 -mt-20 relative z-20">
        <!-- Vision & Mission Section -->
        <div id="our-mission" class="glass-card p-12 mb-20 animate-fadeInUp delay-200">
            <div class="flex flex-col lg:flex-row gap-12">
                <div class="lg:w-1/2">
                    <h2 class="text-4xl font-bold text-indigo-700 section-title">Our Vision</h2>
                    <div class="text-gray-700 leading-relaxed space-y-6 text-lg">
                        <p>
                            At Servicespire, we envision a world where no one has to face challenges alone. We believe in the power of community and the importance of lending a helping hand.
                        </p>
                        <p>
                            Our vision is to create a network of support that fosters compassion, collaboration, and connection among individuals, ensuring that everyone has access to the assistance they need.
                        </p>
                    </div>
                </div>
                <div class="lg:w-1/2">
                    <h2 class="text-4xl font-bold text-indigo-700 section-title">Our Mission</h2>
                    <div class="text-gray-700 leading-relaxed space-y-6 text-lg">
                        <p>
                            Our mission is to simplify the process of seeking and providing help. We aim to empower users to easily request services while enabling helpers to offer their skills and time.
                        </p>
                        <p>
                            By facilitating secure transactions and promoting trust, we strive to create a seamless experience that benefits both users and helpers in our communities.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Success Story Section -->
        <div class="glass-card p-12 mb-20 animate-fadeInUp delay-300">
            <h2 class="text-4xl font-bold text-indigo-700 section-title text-center">Our Impact</h2>
            <div class="text-gray-700 leading-relaxed text-lg max-w-4xl mx-auto text-center">
                <p class="mb-8">
                    Since our launch, Servicespire has transformed how communities connect and support each other. Our platform has become a beacon of trust and reliability in the sharing economy.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">
                <div class="feature-card glass-card p-8 text-center hover:transform hover:scale-105 transition-all duration-500">
                    <div class="feature-icon mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Trust Built</h3>
                    <p class="text-gray-600">Verified profiles and secure transactions create a safe environment for meaningful connections.</p>
                </div>
                
                <div class="feature-card glass-card p-8 text-center hover:transform hover:scale-105 transition-all duration-500">
                    <div class="feature-icon mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Community Grown</h3>
                    <p class="text-gray-600">Thousands of neighbors helping neighbors creates stronger, more resilient communities.</p>
                </div>
                
                <div class="feature-card glass-card p-8 text-center hover:transform hover:scale-105 transition-all duration-500">
                    <div class="feature-icon mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Lives Touched</h3>
                    <p class="text-gray-600">From essential services to daily needs, we've made real differences in people's lives.</p>
                </div>
            </div>
        </div>
        
        <!-- Executive Team Section -->
        <div class="glass-card p-12">
            <h2 class="text-4xl font-bold text-indigo-700 text-center mb-16 section-title mx-auto">The Minds Behind ServiceSpire</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Team Member Card -->
                <div class="team-card p-8 text-center">
                    <div class="relative mb-8">
                        <img src="assets/images/suraj.jpg" class="w-40 h-40 mx-auto rounded-full team-image object-cover">
                    </div>
                    <h3 class="text-2xl font-bold text-indigo-700">Suraj</h3>
                    <p class="text-gray-500 mt-2">Backend Developer</p>
                    <div class="mt-6 pt-6 border-t border-gray-100 flex justify-center space-x-4">
                        <a href="#" class="text-gray-400 hover:text-indigo-600 transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"></path></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-indigo-600 transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"></path></svg>
                        </a>
                    </div>
                </div>
                
                <div class="team-card p-8 text-center">
                    <div class="relative mb-8">
                        <img src="assets/images/omkar.jpg" class="w-40 h-40 mx-auto rounded-full team-image object-cover">
                    </div>
                    <h3 class="text-2xl font-bold text-indigo-700">Omkar</h3>
                    <p class="text-gray-500 mt-2">Backend Developer</p>
                    <div class="mt-6 pt-6 border-t border-gray-100 flex justify-center space-x-4">
                        <a href="#" class="text-gray-400 hover:text-indigo-600 transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"></path></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-indigo-600 transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"></path></svg>
                        </a>
                    </div>
                </div>
                
                <div class="team-card p-8 text-center">
                    <div class="relative mb-8">
                        <img src="assets/images/yasin.jpg" class="w-40 h-40 mx-auto rounded-full team-image object-cover">
                    </div>
                    <h3 class="text-2xl font-bold text-indigo-700">Yasin</h3>
                    <p class="text-gray-500 mt-2">Frontend Developer</p>
                    <div class="mt-6 pt-6 border-t border-gray-100 flex justify-center space-x-4">
                        <a href="#" class="text-gray-400 hover:text-indigo-600 transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"></path></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-indigo-600 transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"></path></svg>
                        </a>
                    </div>
                </div>
                
                <div class="team-card p-8 text-center">
                    <div class="relative mb-8">
                        <img src="assets/images/Arya.jpg" class="w-40 h-40 mx-auto rounded-full team-image object-cover">
                    </div>
                    <h3 class="text-2xl font-bold text-indigo-700">Arya</h3>
                    <p class="text-gray-500 mt-2">Product Lead</p>
                    <div class="mt-6 pt-6 border-t border-gray-100 flex justify-center space-x-4">
                        <a href="#" class="text-gray-400 hover:text-indigo-600 transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"></path></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-indigo-600 transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- CTA Section -->
    <div class="gradient-bg py-24 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-white to-transparent opacity-20"></div>
        </div>
        <div class="max-w-4xl mx-auto px-6 text-center relative z-10">
            <h2 class="text-4xl md:text-5xl font-bold text-white mb-8">Ready to Experience Community Support?</h2>
            <p class="text-indigo-100 text-xl mb-10 leading-relaxed">
                Join thousands who are already making a difference in their communities every day.
            </p>
            <a href="selectrole.php" class="btn-primary inline-block py-5 px-12 rounded-full text-xl font-bold">
                Get Started Now
            </a>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include 'footer.php'; ?>
    
    <script>
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fadeInUp');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.addEventListener('DOMContentLoaded', function() {
            const animatedElements = document.querySelectorAll('.glass-card, .team-card, .feature-card');
            
            animatedElements.forEach((el, index) => {
                // Set initial state
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                
                // Add delay based on index
                el.style.transitionDelay = ${index * 0.1}s;
                
                // Observe each element
                observer.observe(el);
            });
        });

        
    </script>
</body>
</html>