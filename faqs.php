<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs - ServiceSpire</title>
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
        
        .faq-item {
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 1rem;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        
        .faq-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
            border-color: var(--primary-light);
        }
        
        .faq-question {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: white;
            transition: all 0.3s ease;
        }
        
        .faq-question:hover {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
        }
        
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            background-color: rgba(249, 250, 251, 0.8);
        }
        
        .faq-answer-content {
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s ease 0.2s;
        }
        
        .faq-item.active .faq-answer {
            max-height: 500px;
        }
        
        .faq-item.active .faq-answer-content {
            opacity: 1;
            transform: translateY(0);
        }
        
        .faq-icon {
            transition: transform 0.3s ease;
        }
        
        .faq-item.active .faq-icon {
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
        
        .chevron-icon {
            transition: all 0.3s ease;
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
            <h1 class="text-4xl md:text-6xl font-bold mb-6">Frequently Asked <span class="gradient-text">Questions</span></h1>
            <p class="text-xl md:text-2xl text-gray-600 max-w-3xl mx-auto">
                Find answers to common questions about ServiceSpire and how we help users connect with skilled professionals.
            </p>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<div class="max-w-4xl mx-auto px-6 py-16">
    <div class="glass-card p-8">
        <div class="space-y-4">
            <!-- FAQ Item 1 -->
            <div class="faq-item animate-fadeInUp">
                <button class="faq-question w-full flex justify-between items-center p-6 text-left rounded-t-lg">
                    <span class="text-lg font-semibold">What is ServiceSpire?</span>
                    <svg class="faq-icon chevron-icon w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content p-6 text-gray-700">
                        ServiceSpire is a trusted platform that connects users with verified professionals for online and offline services across various domains.
                    </div>
                </div>
            </div>
            
            <!-- FAQ Item 2 -->
            <div class="faq-item animate-fadeInUp delay-100">
                <button class="faq-question w-full flex justify-between items-center p-6 text-left rounded-t-lg">
                    <span class="text-lg font-semibold">How does ServiceSpire work?</span>
                    <svg class="faq-icon chevron-icon w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content p-6 text-gray-700">
                        Users can browse through a range of services, select their preferred helper, schedule appointments, and receive assistance either online or in-person.
                    </div>
                </div>
            </div>
            
            <!-- FAQ Item 3 -->
            <div class="faq-item animate-fadeInUp delay-200">
                <button class="faq-question w-full flex justify-between items-center p-6 text-left rounded-t-lg">
                    <span class="text-lg font-semibold">Can I schedule an offline visit?</span>
                    <svg class="faq-icon chevron-icon w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content p-6 text-gray-700">
                        Yes! ServiceSpire allows scheduling in-person visits with professionals to ensure personalized assistance.
                    </div>
                </div>
            </div>
            
            <!-- FAQ Item 4 -->
            <div class="faq-item animate-fadeInUp">
                <button class="faq-question w-full flex justify-between items-center p-6 text-left rounded-t-lg">
                    <span class="text-lg font-semibold">What payment methods are accepted?</span>
                    <svg class="faq-icon chevron-icon w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content p-6 text-gray-700">
                        We accept multiple payment methods, including credit/debit cards, UPI, PayPal, and cash payments for offline services.
                    </div>
                </div>
            </div>
            
            <!-- FAQ Item 5 -->
            <div class="faq-item animate-fadeInUp delay-100">
                <button class="faq-question w-full flex justify-between items-center p-6 text-left rounded-t-lg">
                    <span class="text-lg font-semibold">Is there customer support available?</span>
                    <svg class="faq-icon chevron-icon w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content p-6 text-gray-700">
                        Yes! Our dedicated customer support team is available 24/7 via chat, email, and phone to assist you with any queries or concerns.
                    </div>
                </div>
            </div>
            
            <!-- FAQ Item 6 -->
            <div class="faq-item animate-fadeInUp delay-200">
                <button class="faq-question w-full flex justify-between items-center p-6 text-left rounded-t-lg">
                    <span class="text-lg font-semibold">Are the service providers verified?</span>
                    <svg class="faq-icon chevron-icon w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content p-6 text-gray-700">
                        Yes, all professionals on ServiceSpire undergo a strict verification process, including background checks, to ensure quality and trustworthiness.
                    </div>
                </div>
            </div>
            
            <!-- FAQ Item 7 -->
            <div class="faq-item animate-fadeInUp">
                <button class="faq-question w-full flex justify-between items-center p-6 text-left rounded-t-lg">
                    <span class="text-lg font-semibold">What if I am not satisfied with a service?</span>
                    <svg class="faq-icon chevron-icon w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content p-6 text-gray-700">
                        We offer a satisfaction guarantee. If you are not satisfied with a service, you can contact our support team for assistance or request a potential refund.
                    </div>
                </div>
            </div>
            
            <!-- FAQ Item 8 -->
            <div class="faq-item animate-fadeInUp delay-100">
                <button class="faq-question w-full flex justify-between items-center p-6 text-left rounded-t-lg">
                    <span class="text-lg font-semibold">How do I become a helper on ServiceSpire?</span>
                    <svg class="faq-icon chevron-icon w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content p-6 text-gray-700">
                        To become a helper, sign up on our platform, complete your profile, submit verification documents, and once approved, you can start offering services.
                    </div>
                </div>
            </div>
            
            <!-- FAQ Item 9 -->
            <div class="faq-item animate-fadeInUp delay-200">
                <button class="faq-question w-full flex justify-between items-center p-6 text-left rounded-t-lg">
                    <span class="text-lg font-semibold">Is there a mobile app for ServiceSpire?</span>
                    <svg class="faq-icon chevron-icon w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content p-6 text-gray-700">
                        Not currently! We are developing the application for future release.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const faqItems = document.querySelectorAll('.faq-item');
        
        // Animate FAQ items when they come into view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fadeInUp');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        
        faqItems.forEach((item, index) => {
            // Set initial state
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            
            // Add staggered delay
            if (index % 3 === 1) item.classList.add('delay-100');
            if (index % 3 === 2) item.classList.add('delay-200');
            
            // Observe each item
            observer.observe(item);
            
            // Add click handler
            const question = item.querySelector('.faq-question');
            const answer = item.querySelector('.faq-answer');
            const icon = item.querySelector('.faq-icon');
            
            question.addEventListener('click', () => {
                // Close all other FAQs
                faqItems.forEach(otherItem => {
                    if (otherItem !== item && otherItem.classList.contains('active')) {
                        otherItem.classList.remove('active');
                        otherItem.querySelector('.faq-answer').classList.remove('active');
                        otherItem.querySelector('.faq-answer-content').style.opacity = '0';
                        otherItem.querySelector('.faq-answer-content').style.transform = 'translateY(-10px)';
                    }
                });
                
                // Toggle current FAQ
                item.classList.toggle('active');
                
                if (item.classList.contains('active')) {
                    answer.classList.add('active');
                    setTimeout(() => {
                        item.querySelector('.faq-answer-content').style.opacity = '1';
                        item.querySelector('.faq-answer-content').style.transform = 'translateY(0)';
                    }, 50);
                } else {
                    item.querySelector('.faq-answer-content').style.opacity = '0';
                    item.querySelector('.faq-answer-content').style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        answer.classList.remove('active');
                    }, 300);
                }
            });
        });
    });
</script>
</body>
</html>