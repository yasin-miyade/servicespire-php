<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services | ServiceSpire</title>
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
            background-color: #f8fafc;
            scroll-behavior: smooth;
        }
        
        .gradient-text {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .service-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(226, 232, 240, 0.8);
            z-index: 1;
        }
        
        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary-dark), var(--primary));
        }
        
        .service-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 30px 60px -12px rgba(79, 70, 229, 0.2);
        }
        
        .service-card:hover .service-icon {
            transform: translateY(-10px) rotate(10deg) scale(1.1);
            box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.2);
        }
        
        .service-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 2;
        }
        
        .service-icon::after {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            border-radius: 25px;
            z-index: -1;
            opacity: 0;
            transition: all 0.5s ease;
        }
        
        .service-card:hover .service-icon::after {
            opacity: 0.2;
            top: -8px;
            left: -8px;
            right: -8px;
            bottom: -8px;
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
            background-image: 
                radial-gradient(rgba(99, 102, 241, 0.15) 1px, transparent 1px),
                radial-gradient(rgba(99, 102, 241, 0.1) 1px, transparent 1px);
            background-size: 40px 40px, 80px 80px;
            background-position: 0 0, 40px 40px;
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
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fadeInUp {
            animation: fadeInUp 0.8s ease forwards;
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
            <h1 class="text-4xl md:text-6xl font-bold mb-6">Our <span class="gradient-text">Services</span></h1>
            <p class="text-xl md:text-2xl text-gray-600 max-w-3xl mx-auto">
                Empowering you with seamless service solutions—efficient, reliable, and tailored to your needs.
            </p>
        </div>
    </div>
</section>

<!-- Services Section -->
<div class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php
        $services = [
            [
                "icon" => "fas fa-user-plus",
                "color" => "text-blue-600",
                "bg" => "bg-blue-100",
                "glow" => "bg-blue-500",
                "title" => "User Submissions",
                "description" => "Users can easily submit requests through our intuitive posting form, ensuring all needs are clear and concise for helpers."
            ],
            [
                "icon" => "fas fa-users",
                "color" => "text-green-600",
                "bg" => "bg-green-100",
                "glow" => "bg-green-500",
                "title" => "Qualified Helpers",
                "description" => "Connect with vetted professionals who can provide the assistance you need, ensuring help is just a click away."
            ],
            [
                "icon" => "fas fa-map-marked-alt",
                "color" => "text-purple-600",
                "bg" => "bg-purple-100",
                "glow" => "bg-purple-500",
                "title" => "Live Location Tracking",
                "description" => "Track your helper's location in real-time, ensuring timely arrivals and seamless assistance."
            ],
            [
                "icon" => "fas fa-credit-card",
                "color" => "text-red-600",
                "bg" => "bg-red-100",
                "glow" => "bg-red-500",
                "title" => "Secure Payment Integration",
                "description" => "Easily pay for services using our secure payment gateways with transaction encryption for your protection."
            ],
            [
                "icon" => "fas fa-life-ring",
                "color" => "text-yellow-600",
                "bg" => "bg-yellow-100",
                "glow" => "bg-yellow-500",
                "title" => "Emergency Assistance",
                "description" => "In times of urgency, our platform provides instant access to helpers who are ready to assist you in critical situations."
            ],
            [
                "icon" => "fas fa-calendar-alt",
                "color" => "text-teal-600",
                "bg" => "bg-teal-100",
                "glow" => "bg-teal-500",
                "title" => "Scheduled Services",
                "description" => "Plan ahead with scheduled services to ensure that help is available at the time you need it most."
            ]
        ];
        
        foreach ($services as $index => $service) {
            $delayClass = 'delay-' . (($index % 3) * 100);
            echo '<div class="service-card p-8 text-center animate-fadeInUp ' . $delayClass . '">';
            echo '<div class="service-icon ' . $service['bg'] . ' ' . $service['color'] . '" style="--glow-color: ' . $service['glow'] . '">';
            echo '<i class="' . $service['icon'] . ' text-3xl"></i>';
            echo '</div>';
            echo '<h3 class="text-xl font-bold text-gray-800 mb-4">' . $service['title'] . '</h3>';
            echo '<p class="text-gray-600">' . $service['description'] . '</p>';
            echo '</div>';
        }
        ?>
    </div>
</div>

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
        const serviceCards = document.querySelectorAll('.service-card');
        
        serviceCards.forEach((card, index) => {
            // Set initial state
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            
            // Observe each card
            observer.observe(card);
            
            // Add glow effect on hover
            const icon = card.querySelector('.service-icon');
            icon.style.setProperty('--glow-color', icon.getAttribute('data-glow'));
            
            icon.addEventListener('mouseenter', function() {
                this.style.boxShadow = 0 0 20px 5px var(--glow-color);
            });
            
            icon.addEventListener('mouseleave', function() {
                this.style.boxShadow = '0 10px 20px rgba(0, 0, 0, 0.1)';
            });
        });
    });
</script>
</body>
</html>