<?php
// Place this at the very top of the file - no whitespace before the opening PHP tag
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// Note: No ob_end_flush() here to prevent output before redirects
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ServiceSpire Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.1/css/all.min.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <!-- Add improved sidebar transition styles -->
    <style>
        /* Improved sidebar transitions */
        .sidebar-collapsed #sidebar {
            width: 5rem !important;
            transition: width 0.3s ease-in-out;
        }
        .sidebar-collapsed #main-content {
            margin-left: 5rem !important;
            transition: margin-left 0.3s ease-in-out;
        }
        .sidebar-collapsed .sidebar-text {
            display: none !important;
        }
        .sidebar-collapsed #logo-text {
            transform: scale(0);
            width: 0;
            transition: transform 0.3s ease, width 0.3s ease;
        }
        
        /* Prevent layout shifts during navigation */
        #sidebar, #main-content {
            transition: all 0.3s ease-in-out;
        }
        
        /* Hide scrollbar during page transition */
        body.navigating {
            overflow: hidden;
        }
    </style>
    
    <!-- Pre-load the sidebar state before any DOM rendering -->
    <script>
        // Apply sidebar state immediately
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (sidebarCollapsed) {
            document.documentElement.classList.add('sidebar-collapsed');
        }
        
        // Set up page transition variable to prevent unwanted behavior
        window.isNavigating = false;
    </script>
</head>
<body class="bg-gradient-to-br from-purple-50 to-purple-100 font-inter">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="w-64 bg-white shadow-lg p-5 fixed top-0 left-0 h-screen z-10 transition-all duration-300">
            <div class="flex justify-between items-center mb-6">
                <h1 id="logo-text" class="text-2xl font-bold text-blue-500 transition-all"><img
                src="../assets/images/logo1.jpg" alt="" class="w-40 "></h1>
                <button onclick="toggleSidebar()" class="text-black text-2xl ml-2 focus:outline-none">☰</button>
            </div>
            <nav>
                <ul>
                    <?php 
                    $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
                    $menuItems = [
                        "dashboard" => ["Dashboard", "fa-house"],
                        "pending" => ["Pending Requests", "fa-clock"],
                        // "ongoing" => ["Ongoing Requests", "fa-spinner"],
                        "completed" => ["Completed Tasks", "fa-check-circle"],
                        "notification" => ["Alerts & Reminders", "fa-bell"],
                        "profile" => ["Helper Profile", "fa-user"],
                        "feedback" => ["User Feedback", "fa-comment"],
                        "support" => ["Support Helpdesk", "fa-headset"],
                    ];
                    
                    foreach ($menuItems as $key => $value) {
                        $activeClass = ($page === $key) ? "bg-indigo-600 text-white" : "bg-gray-100 text-gray-600 hover:bg-gray-200";
                        $link = ($key === "home") ? "logout.php" : "?page=$key";
                        echo "<li class='mb-2'>
                                <a href='$link' class='nav-link flex items-center p-2.5 min-h-[50px] rounded-lg $activeClass'>
                                    <i class='fa-solid {$value[1]} fa-lg pr-1 text-center'></i>
                                    <span class='sidebar-text'>{$value[0]}</span>
                                </a>
                              </li>";
                    }
                    ?>

                    <li class="mb-2">
                        <a href="logout.php" class="nav-link flex items-center p-3.5 rounded-lg min-h-[50px] bg-red-500 text-white hover:bg-red-600">
                            <i class="fa-solid fa-sign-out-alt fa-lg mr-1"></i>
                            <span class="sidebar-text">Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div id="main-content" class="flex-1 p-6 ml-64 transition-all duration-300">
            <!-- <h2 class="text-3xl font-semibold mb-4">Welcome, <?php echo htmlspecialchars($helper_name); ?>!</h2> -->
            <!-- <p class="text-gray-600 mb-6">Email: <?php echo htmlspecialchars($helper_email); ?></p> -->
            <?php 
                $file = "$page.php";
                
                if (file_exists($file)) {
                    include $file;
                } else {
                    echo "<h2 class='text-3xl font-semibold'>Page Not Found</h2>";
                }
            ?>
        </div>
    </div>
    
    <script>
    // DOM ready handler
    document.addEventListener('DOMContentLoaded', function() {
        setupNavigationLinks();
        
        // Ensure any CSS transitions from page load are completed
        setTimeout(function() {
            document.body.classList.add('transitions-ready');
        }, 100);
    });
    
    // Set up navigation links to maintain sidebar state
    function setupNavigationLinks() {
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            if (!link.getAttribute('href').includes('logout.php')) {
                const originalHref = link.getAttribute('href');
                
                link.addEventListener('click', function(e) {
                    // Prevent the default action
                    e.preventDefault();
                    
                    // Avoid multiple clicks
                    if (window.isNavigating) return;
                    window.isNavigating = true;
                    
                    // Add a navigating class to the body to prevent scrolling during transition
                    document.body.classList.add('navigating');
                    
                    // Navigate smoothly after a tiny delay to allow CSS transitions
                    setTimeout(function() {
                        window.location.href = originalHref;
                    }, 10);
                });
            }
        });
    }

    function toggleSidebar() {
        // Prevent toggling during navigation
        if (window.isNavigating) return;
        
        let sidebar = document.getElementById("sidebar");
        let mainContent = document.getElementById("main-content");
        let texts = document.querySelectorAll(".sidebar-text");
        let logoText = document.getElementById("logo-text");
        
        // Check if sidebar is currently expanded
        const isExpanded = sidebar.classList.contains("w-64");
        
        if (isExpanded) {
            // Collapse sidebar
            document.documentElement.classList.add('sidebar-collapsed');
            sidebar.classList.remove("w-64");
            sidebar.classList.add("w-20");
            mainContent.classList.remove("ml-64");
            mainContent.classList.add("ml-20");
            texts.forEach(text => text.classList.add("hidden"));
            logoText.classList.add("hidden");
            
            // Save collapsed state
            localStorage.setItem('sidebarCollapsed', 'true');
        } else {
            // Expand sidebar
            document.documentElement.classList.remove('sidebar-collapsed');
            sidebar.classList.add("w-64");
            sidebar.classList.remove("w-20");
            mainContent.classList.add("ml-64");
            mainContent.classList.remove("ml-20");
            texts.forEach(text => text.classList.remove("hidden"));
            logoText.classList.remove("hidden");
            
            // Save expanded state
            localStorage.setItem('sidebarCollapsed', 'false');
        }
    }

    // Track page visibility to help with navigation transitions
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'hidden') {
            // Page is being navigated away from
            window.isNavigating = true;
        }
    });
    </script>
</body>
</html>
<?php
// Place this at the very end to ensure all content is sent properly
ob_end_flush();
?>