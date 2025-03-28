<?php
session_start();
require_once("../lib/function.php");

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    die("Unauthorized access. Please log in first.");
}

$email = $_SESSION['email'];

// Initialize database connection
$db = new db_functions();

// Add more comprehensive debugging to check the SQL and database structure
$debug = [];

// Check if status column exists
$status_check = $db->connect()->query("SHOW COLUMNS FROM work_posts LIKE 'status'");
$status_exists = $status_check && $status_check->num_rows > 0;
$debug['status_column_exists'] = $status_exists ? 'Yes' : 'No';

// Get a count of all posts for this user regardless of status
$all_posts_query = "SELECT COUNT(*) AS total FROM work_posts WHERE email = ?";
$stmt1 = $db->connect()->prepare($all_posts_query);
$stmt1->bind_param("s", $email);
$stmt1->execute();
$result1 = $stmt1->get_result()->fetch_assoc();
$debug['total_posts'] = $result1['total'];

// Fix the query to properly handle status filtering
// Use a more flexible query that works even if some posts don't have a status value
if ($status_exists) {
    $query = "SELECT * FROM work_posts WHERE email = ? AND (status IS NULL OR status = 'open' OR status = '') ORDER BY created_at DESC";
} else {
    // Fallback query if status column doesn't exist
    $query = "SELECT * FROM work_posts WHERE email = ? AND (assigned_helper_email IS NULL) ORDER BY created_at DESC";
}

$stmt = $db->connect()->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$work_posts = $stmt->get_result();
$debug_count = $work_posts->num_rows;

// Output debug info as HTML comment
echo "<!-- DEBUG INFO: Open posts found: $debug_count, Total posts: {$result1['total']}, Status column exists: $status_exists -->";

// Handle post deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $post_id = $_POST['delete_id'];
    if ($db->deleteWorkPost($post_id)) {
        $_SESSION['success'] = "Post deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete post!";
    }
    header("Location: index.php");
    exit();
}

// Set default page
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.1/css/all.min.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <!-- Add inline styles to prevent flash of unstyled content -->
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
        <div id="sidebar"
            class="w-64 bg-white shadow-lg p-5 fixed top-0 left-0 h-screen z-10 transition-all duration-300">
            <div class="flex justify-between items-center mb-6">
                <h1 id="logo-text" class="text-2xl font-bold text-blue-500 transition-all"><img
                        src="../assets/images/logo1.jpg" alt="" class="w-40 "></h1>
              <button onclick="toggleSidebar()" class="text-black text-2xl mr-2 focus:outline-none">☰</button>
            </div>
            <nav>
                <ul>
                    <?php 
                    $menuItems = [
                        "dashboard" => ["Dashboard", "fa-house"],
                        "post_form" => ["Posts", "fa-briefcase"],
                        "pending" => ["Pending Work", "fa-hourglass-half"],
                        "completed" => ["Completed Work", "fa-check-circle"],
                        "notifaction" => ["Notifications", "fa-bell"],
                        "profile" => ["User Profile", "fa-user"]
                    ];
                    
                    foreach ($menuItems as $key => $value) {
                        $activeClass = ($page === $key) ? "bg-indigo-600 text-white" : "bg-gray-100 text-gray-600 hover:bg-gray-200";
                        echo "<li class='mb-2'>
                                <a href='?page=$key' class='nav-link flex items-center p-2.5 min-h-[50px] rounded-lg $activeClass'>
                                    <i class='fa-solid {$value[1]} fa-lg pr-1 text-center'></i>
                                    <span class='sidebar-text'>{$value[0]}</span>
                                </a>
                              </li>";
                    }
                    ?>

                    <li class="mb-2">
                        <a href="logout.php"
                            class="nav-link flex items-center p-3.5 rounded-lg min-h-[50px] bg-red-500 text-white hover:bg-red-600">
                            <i class="fa-solid fa-sign-out-alt fa-lg mr-1"></i>
                            <span class="sidebar-text">Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <div id="main-content" class="flex-1 p-6 ml-64 transition-all duration-300">
            <?php 
            if ($page === 'dashboard') {
                // Display work posts dashboard content
            ?>
                <div class="max-w-6xl mx-auto">
                    <!-- Header -->
                    <header class="text-center mb-10 scale-in">
                        <h2 class="text-3xl md:text-4xl font-bold text-indigo-800 mb-3">My Open Work Posts</h2>
                        <p class="text-gray-600 max-w-xl mx-auto">Manage and view all your available work posts that haven't been accepted or completed yet.</p>
                        <div class="mt-6">
                            <a href="?page=post_form" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-indigo-700 transition-all btn">
                                <i class="ph ph-plus-circle"></i> Add New Post
                            </a>
                        </div>
                    </header>

                    <!-- Work Posts Section -->
                    <?php if ($work_posts->num_rows > 0) : ?>
                    <div class="grid grid-cols-1 gap-8">
                        <?php while ($post = $work_posts->fetch_assoc()) : ?>
                        <!-- Post Container with Hover Effect -->
                        <div
                            class="relative bg-white shadow-lg border-l-8 border-indigo-500 rounded-xl overflow-hidden transform hover:-translate-y-2 hover:shadow-xl transition-all ">
                            <div class="p-8">
                                <!-- Post Title -->
                                <h3 class="text-2xl font-semibold text-indigo-700 mb-6 flex items-center gap-2">
                                    <i class="ph ph-briefcase text-indigo-600"></i> Work Details
                                </h3>

                                <!-- Post Details -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-gray-800">
                                    <div class="flex items-center gap-3">
                                        <i class="ph ph-user text-indigo-600"></i>
                                        <strong>Name:</strong> <?php echo htmlspecialchars($post['name']); ?>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="ph ph-phone text-indigo-600"></i>
                                        <strong>Mobile:</strong> <?php echo htmlspecialchars($post['mobile']); ?>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="ph ph-map-pin text-indigo-600"></i>
                                        <strong>City:</strong> <?php echo htmlspecialchars($post['city']); ?>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="ph ph-gear text-indigo-600"></i>
                                        <strong>Work:</strong> <?php echo htmlspecialchars($post['work']); ?>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="ph ph-calendar text-indigo-600"></i>
                                        <strong>Deadline:</strong> <?php echo htmlspecialchars($post['deadline']); ?>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="ph ph-currency-circle-dollar text-indigo-600"></i>
                                        <strong>Work Reward:</strong>
                                        <span
                                            class="text-green-600 font-bold"><?php echo htmlspecialchars($post['reward']); ?></span>
                                    </div>
                                    <div class="col-span-2 flex items-start gap-3">
                                        <i class="ph ph-chat-circle-dots text-indigo-600"></i>
                                        <strong>Message:</strong>
                                        <span
                                            class="italic text-gray-600">"<?php echo nl2br(htmlspecialchars($post['message'])); ?>"</span>
                                    </div>
                                    <?php if (!empty($post['from_location']) && !empty($post['to_location'])): ?>
                                    <div class="flex items-center gap-3">
                                        <i class="ph ph-map-trifold text-blue-600"></i>
                                        <strong>From Location:</strong> <?php echo htmlspecialchars($post['from_location']); ?>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="ph ph-map-trifold text-red-600"></i>
                                        <strong>To Location:</strong> <?php echo htmlspecialchars($post['to_location']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Post Footer -->
                                <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                                    <span class="text-gray-700 text-sm flex items-center gap-2">
                                        <i class="ph ph-clock text-indigo-600"></i> Posted on:
                                        <?php echo date("d M Y", strtotime($post['created_at'])); ?>
                                    </span>
                                    <div class="flex items-center gap-3">
                                        <a href="edit_post.php?id=<?php echo $post['id']; ?>"
                                            class="btn bg-indigo-600 text-white font-medium py-2 px-5 rounded-lg hover:bg-indigo-700 transition-all hover:-translate-y-1 flex items-center gap-2 shadow-sm">
                                            <i class="ph ph-pencil-simple"></i> Edit
                                        </a>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this post? This action cannot be undone.');">
                                            <input type="hidden" name="delete_id" value="<?php echo $post['id']; ?>">
                                            <button type="submit"
                                                class="btn bg-red-500 text-white font-medium py-2 px-4 rounded-lg hover:bg-red-700 transition-all hover:-translate-y-1 flex items-center gap-2 shadow-sm">
                                                <i class="ph ph-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else : ?>
                    <p class="text-center text-gray-600 text-lg">No work posts available.</p>
                    <?php endif; ?>
                </div>
            <?php 
            } else {
                // Load the appropriate page based on the parameter
                $file = "$page.php";
                if (file_exists($file)) {
                    include $file;
                } else {
                    echo "<h2 class='text-3xl font-semibold'>Page Not Found</h2>";
                }
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
            logoText.classList.add("scale-0");
            logoText.classList.add("w-0");
            
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
            logoText.classList.remove("scale-0");
            logoText.classList.remove("w-0");
            
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