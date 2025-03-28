<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions | ServiceSpire</title>
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
        
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .terms-section {
            border-radius: 16px;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 1.5rem;
        }
        
        .terms-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
            border-color: var(--primary-light);
        }
        
        .terms-header {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: white;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .terms-header:hover {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
        }
        
        .terms-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            background-color: rgba(249, 250, 251, 0.8);
        }
        
        .terms-content-inner {
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s ease 0.2s;
        }
        
        .terms-section.active .terms-content {
            max-height: 1000px;
        }
        
        .terms-section.active .terms-content-inner {
            opacity: 1;
            transform: translateY(0);
        }
        
        .terms-icon {
            transition: transform 0.3s ease;
        }
        
        .terms-section.active .terms-icon {
            transform: rotate(180deg);
        }
        
        .hero-section {
            background: linear-gradient(135deg, #f9fafb 0%, #f0f4ff 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(rgba(99, 102, 241, 0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            opacity: 0.6;
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
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease forwards;
        }
        
        .delay-100 {
            animation-delay: 0.1s;
        }
        
        .delay-200 {
            animation-delay: 0.2s;
        }
        
        .custom-list li {
            position: relative;
            padding-left: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .custom-list li:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0.5rem;
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 50%;
            background-color: var(--primary);
        }
        
        .btn-home {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3), 0 2px 4px -1px rgba(79, 70, 229, 0.2);
        }
        
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4), 0 4px 6px -2px rgba(79, 70, 229, 0.2);
        }
    </style>
</head>
<body class="antialiased">
<?php include 'header.php'; ?>

<!-- Hero Section -->
<section class="hero-section py-24 md:py-32">
    <div class="shape-blob one"></div>
    <div class="shape-blob two"></div>
    <div class="hero-pattern"></div>
    <div class="max-w-7xl mx-auto px-6 relative z-10">
        <div class="text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">Terms and <span class="gradient-text">Conditions</span></h1>
            <p class="text-xl md:text-2xl text-gray-600 max-w-3xl mx-auto">
                Please read these terms carefully before using our services.
            </p>
        </div>
    </div>
</section>

<!-- Terms Content -->
<div class="max-w-4xl mx-auto px-6 py-16">
    <div class="glass-card p-8">
        <div class="space-y-4">
            <!-- User Conduct Section -->
            <div class="terms-section animate-fadeInUp">
                <button class="terms-header w-full flex justify-between items-center p-6 text-left rounded-t-lg">
                    <span class="text-lg font-semibold">User Conduct</span>
                    <svg class="terms-icon w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="terms-content">
                    <div class="terms-content-inner p-6 text-gray-700">
                        <ul class="custom-list">
                            <li>Provide accurate information.</li>
                            <li>Respect and avoid offensive behavior.</li>
                            <li>No fraudulent or illegal activities.</li>
                            <li>No hacking, spamming, or security breaches.</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Privacy & Data Section -->
            <div class="terms-section animate-fadeInUp delay-100">
                <button class="terms-header w-full flex justify-between items-center p-6 text-left rounded-t-lg">
                    <span class="text-lg font-semibold">Privacy & Data</span>
                    <svg class="terms-icon w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="terms-content">
                    <div class="terms-content-inner p-6 text-gray-700">
                        <ul class="custom-list">
                            <li>Your data is securely stored.</li>
                            <li>Cookies improve user experience.</li>
                            <li>Update personal info in settings.</li>
                            <li>Transactions are encrypted.</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Prohibited Activities Section -->
            <div class="terms-section animate-fadeInUp delay-200">
                <button class="terms-header w-full flex justify-between items-center p-6 text-left rounded-t-lg">
                    <span class="text-lg font-semibold">Prohibited Activities</span>
                    <svg class="terms-icon w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="terms-content">
                    <div class="terms-content-inner p-6 text-gray-700">
                        <ul class="custom-list">
                            <li>No harassment or hate speech.</li>
                            <li>No malware, viruses, or hacking.</li>
                            <li>No spamming or phishing.</li>
                            <li>No unauthorized account access.</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Account Security Section -->
            <div class="terms-section animate-fadeInUp">
                <button class="terms-header w-full flex justify-between items-center p-6 text-left rounded-t-lg">
                    <span class="text-lg font-semibold">Account Security</span>
                    <svg class="terms-icon w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="terms-content">
                    <div class="terms-content-inner p-6 text-gray-700">
                        <ul class="custom-list">
                            <li>Use strong passwords.</li>
                            <li>Enable two-factor authentication.</li>
                            <li>Report suspicious activity.</li>
                            <li>Never share login credentials.</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Modifications & Termination Section -->
            <div class="terms-section animate-fadeInUp delay-100">
                <button class="terms-header w-full flex justify-between items-center p-6 text-left rounded-t-lg">
                    <span class="text-lg font-semibold">Modifications & Termination</span>
                    <svg class="terms-icon w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="terms-content">
                    <div class="terms-content-inner p-6 text-gray-700">
                        <ul class="custom-list">
                            <li>We may update these terms.</li>
                            <li>Feature modifications without notice.</li>
                            <li>Accounts violating rules may be banned.</li>
                            <li>Regional restrictions may apply.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const termsSections = document.querySelectorAll('.terms-section');
        
        // Animate sections when they come into view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fadeInUp');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        
        termsSections.forEach((section, index) => {
            // Set initial state
            section.style.opacity = '0';
            section.style.transform = 'translateY(20px)';
            
            // Add staggered delay
            if (index % 3 === 1) section.classList.add('delay-100');
            if (index % 3 === 2) section.classList.add('delay-200');
            
            // Observe each section
            observer.observe(section);
            
            // Add click handler
            const header = section.querySelector('.terms-header');
            const content = section.querySelector('.terms-content');
            const icon = section.querySelector('.terms-icon');
            
            header.addEventListener('click', () => {
                // Close all other sections first
                termsSections.forEach(otherSection => {
                    if (otherSection !== section && otherSection.classList.contains('active')) {
                        otherSection.classList.remove('active');
                        const otherContent = otherSection.querySelector('.terms-content');
                        const otherContentInner = otherSection.querySelector('.terms-content-inner');
                        
                        otherContentInner.style.opacity = '0';
                        otherContentInner.style.transform = 'translateY(-10px)';
                        setTimeout(() => {
                            otherContent.classList.remove('active');
                        }, 300);
                    }
                });
                
                // Toggle current section
                section.classList.toggle('active');
                
                if (section.classList.contains('active')) {
                    content.classList.add('active');
                    setTimeout(() => {
                        section.querySelector('.terms-content-inner').style.opacity = '1';
                        section.querySelector('.terms-content-inner').style.transform = 'translateY(0)';
                    }, 50);
                } else {
                    section.querySelector('.terms-content-inner').style.opacity = '0';
                    section.querySelector('.terms-content-inner').style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        content.classList.remove('active');
                    }, 300);
                }
            });
        });
    });
</script>
</body>
</html>