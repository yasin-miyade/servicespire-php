<?php
require_once('before_index.php'); // Include the buffer handler
// session_start();
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

// Get a count of all posts for this user regardless of status
$all_posts_query = "SELECT COUNT(*) AS total FROM work_posts WHERE email = ? AND deleted = 0";
$stmt1 = $db->connect()->prepare($all_posts_query);
$stmt1->bind_param("s", $email);
$stmt1->execute();
$result1 = $stmt1->get_result()->fetch_assoc();
$debug['total_posts'] = $result1['total'];

// Main query to fetch posts - show all posts regardless of deadline
$query = "SELECT * FROM work_posts 
          WHERE email = ? 
          AND deleted = 0
          AND assigned_helper_email IS NULL
          AND (
              status = 'open' 
              OR status IS NULL 
              OR (status != 'completed' AND status != 'cancelled')
          )
          ORDER BY created_at DESC";

$stmt = $db->connect()->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$work_posts = $stmt->get_result();

// Add debug output
$debug_count = $work_posts->num_rows;
error_log("Found {$debug_count} available posts for email: {$email}");

// Output debug info as HTML comment
echo "<!-- DEBUG INFO: Posts found: $debug_count, Total posts: {$result1['total']}, User email: $email -->";

// Handle post deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $post_id = $_POST['delete_id'];
    
    // Get post details before deletion
    $get_post_query = "SELECT reward FROM work_posts WHERE id = ? AND email = ?";
    $stmt = $db->connect()->prepare($get_post_query);
    $stmt->bind_param("is", $post_id, $email);
    $stmt->execute();
    $post_result = $stmt->get_result()->fetch_assoc();
    
    if ($post_result) {
        $reward_amount = $post_result['reward'];
        
        // Start transaction
        $db->connect()->begin_transaction();
        
        try {
            // First process the refund
            $refund_query = "UPDATE users SET wallet_balance = wallet_balance + ? WHERE email = ?";
            $refund_stmt = $db->connect()->prepare($refund_query);
            $refund_stmt->bind_param("ds", $reward_amount, $email);
            
            if ($refund_stmt->execute()) {
                // After successful refund, delete the post
                if ($db->deleteWorkPost($post_id)) {
                    $db->connect()->commit();
                    $_SESSION['success'] = "₹" . $reward_amount . " refunded to your wallet and post deleted successfully!";
                } else {
                    throw new Exception("Post refunded but deletion failed");
                }
            } else {
                throw new Exception("Failed to process refund");
            }
        } catch (Exception $e) {
            $db->connect()->rollback();
            $_SESSION['error'] = $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Post not found or unauthorized!";
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
        
        /* Expired post notification styles */
        .expired-post-indicator {
            position: absolute;
            top: 0;
            right: 0;
            padding: 0.5rem 1rem;
            background-color: rgba(239, 68, 68, 0.85);
            color: white;
            font-weight: bold;
            border-bottom-left-radius: 0.5rem;
            z-index: 10;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 0.8; }
            50% { opacity: 1; }
            100% { opacity: 0.8; }
        }
        
        .expired-post {
            border-left-color: #ef4444 !important;
        }
    </style>
    
    <!-- Pre-load the sidebar state before any DOM rendering -->
    <script>
        // Add tab coordination code at the start
        const tabId = Date.now().toString();
        localStorage.setItem(`tab_${tabId}`, 'active');
        
        // Handle tab lifecycle
        window.addEventListener('beforeunload', () => {
            localStorage.removeItem(`tab_${tabId}`);
        });
        
        // Apply sidebar state immediately
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (sidebarCollapsed) {
            document.documentElement.classList.add('sidebar-collapsed');
        }
        
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
                        <?php while ($post = $work_posts->fetch_assoc()) : 
                            // Check if post deadline has expired
                            $is_expired = false;
                            if(!empty($post['deadline'])) {
                                $deadline_date = strtotime($post['deadline']);
                                $current_date = strtotime(date('Y-m-d'));
                                $is_expired = ($current_date > $deadline_date);
                            }
                            
                            // Add 'expired-post' class if post is expired
                            $post_class = $is_expired ? 'expired-post' : '';
                        ?>
                        <!-- Post Container with Hover Effect -->
                        <div class="relative 
                            <?php 
                            $isEmergency = stripos($post['work'], 'emergency') !== false;
                            $is_expired = !empty($post['deadline']) && strtotime($post['deadline']) < strtotime(date('Y-m-d'));
                            
                            // Set background and border colors - expired posts get gray styling
                            if ($is_expired) {
                                echo 'bg-gray-50 border-gray-400';
                            } elseif ($isEmergency) {
                                echo 'bg-red-50 border-red-500';
                            } else {
                                echo 'bg-white border-indigo-500';
                            }
                            ?> 
                            shadow-lg border-l-8 rounded-xl overflow-hidden transform hover:-translate-y-2 hover:shadow-xl transition-all">
                            
                            <!-- Add expired status badge -->
                            <?php if ($is_expired): ?>
                                <div class="absolute top-4 right-4 px-3 py-1 bg-gray-200 text-gray-700 text-xs font-semibold rounded-full flex items-center gap-1">
                                    <i class="ph ph-clock-counter-clockwise"></i> Expired Post
                                </div>
                            <?php endif; ?>
                            
                            <div class="p-8 <?php echo $is_expired ? 'border-t-4 border-gray-200' : ''; ?>">
                                <!-- Post Title with expired styling -->
                                <h3 class="text-2xl font-semibold <?php echo $is_expired ? 'text-gray-600' : ($isEmergency ? 'text-red-700' : 'text-indigo-700'); ?> mb-6 flex items-center gap-2">
                                    <?php if ($is_expired): ?>
                                        <i class="ph ph-clock-counter-clockwise text-gray-500"></i>
                                    <?php elseif ($isEmergency): ?>
                                        <i class="ph ph-warning-circle text-red-600"></i>
                                    <?php else: ?>
                                        <i class="ph ph-briefcase text-indigo-600"></i>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($post['work']); ?>
                                </h3>

                                <!-- Post Details -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 <?php echo $is_expired ? 'text-gray-400' : 'text-gray-800'; ?>">
                                    <div class="flex items-center gap-3">
                                        <i class="ph ph-user <?php echo $is_expired ? 'text-gray-400' : 'text-indigo-600'; ?>"></i>
                                        <strong class="<?php echo $is_expired ? 'text-gray-400' : ''; ?>">Name:</strong> 
                                        <?php echo htmlspecialchars($post['name']); ?>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="ph ph-phone <?php echo $is_expired ? 'text-gray-400' : 'text-indigo-600'; ?>"></i>
                                        <strong class="<?php echo $is_expired ? 'text-gray-400' : ''; ?>">Mobile:</strong> 
                                        <?php echo htmlspecialchars($post['mobile']); ?>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="ph ph-map-pin <?php echo $is_expired ? 'text-gray-400' : 'text-indigo-600'; ?>"></i>
                                        <strong class="<?php echo $is_expired ? 'text-gray-400' : ''; ?>">City:</strong> 
                                        <?php echo htmlspecialchars($post['city']); ?>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="ph ph-gear <?php echo $is_expired ? 'text-gray-400' : 'text-indigo-600'; ?>"></i>
                                        <strong class="<?php echo $is_expired ? 'text-gray-400' : ''; ?>">Work:</strong> 
                                        <?php echo htmlspecialchars($post['work']); ?>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="ph ph-calendar <?php echo $is_expired ? 'text-gray-400' : 'text-indigo-600'; ?>"></i>
                                        <strong class="<?php echo $is_expired ? 'text-gray-400' : ''; ?>">Deadline:</strong> 
                                        <span class="<?php echo $is_expired ? 'text-gray-400' : ''; ?>">
                                            <?php echo htmlspecialchars($post['deadline']); ?>
                                            <?php if($is_expired): ?>
                                                <span class="text-gray-600 ml-2">(Expired)</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="ph ph-currency-circle-dollar <?php echo $is_expired ? 'text-gray-400' : 'text-indigo-600'; ?>"></i>
                                        <strong class="<?php echo $is_expired ? 'text-gray-400' : ''; ?>">Work Reward:</strong>
                                        <span class="<?php echo $is_expired ? 'text-gray-400 font-bold' : 'text-green-600 font-bold'; ?>">
                                            <?php echo htmlspecialchars($post['reward']); ?>
                                        </span>
                                    </div>
                                    <div class="col-span-2 flex items-start gap-3">
                                        <i class="ph ph-chat-circle-dots <?php echo $is_expired ? 'text-gray-400' : 'text-indigo-600'; ?>"></i>
                                        <strong class="<?php echo $is_expired ? 'text-gray-400' : ''; ?>">Message:</strong>
                                        <span class="<?php echo $is_expired ? 'text-gray-400' : 'text-gray-600'; ?> italic">
                                            <?php echo nl2br(htmlspecialchars($post['message'])); ?>
                                        </span>
                                    </div>
                                    
                                    <?php if (!empty($post['from_location']) && !empty($post['to_location'])): ?>
                                    <div class="flex items-center gap-3">
                                        <i class="ph ph-map-trifold <?php echo $is_expired ? 'text-gray-400' : 'text-blue-600'; ?>"></i>
                                        <strong class="<?php echo $is_expired ? 'text-gray-400' : ''; ?>">From Location:</strong> 
                                        <?php echo htmlspecialchars($post['from_location']); ?>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="ph ph-map-trifold <?php echo $is_expired ? 'text-gray-400' : 'text-red-600'; ?>"></i>
                                        <strong class="<?php echo $is_expired ? 'text-gray-400' : ''; ?>">To Location:</strong> 
                                        <?php echo htmlspecialchars($post['to_location']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Post Footer with gray styling for expired posts -->
                                <div class="mt-8 pt-6 <?php echo $is_expired ? 'border-t border-gray-200' : 'border-gray-100'; ?> flex flex-col sm:flex-row justify-between items-center gap-4">
                                    <span class="<?php echo $is_expired ? 'text-gray-400' : 'text-gray-700'; ?> text-sm flex items-center gap-2">
                                        <i class="ph ph-clock <?php echo $is_expired ? 'text-gray-400' : 'text-indigo-600'; ?>"></i> 
                                        Posted on: <?php echo date("d M Y", strtotime($post['created_at'])); ?>
                                    </span>
                                    <div class="flex items-center gap-3">
                                        <a href="edit_post.php?id=<?php echo $post['id']; ?>"
                                            class="btn <?php echo $is_expired ? 'bg-gray-400 hover:bg-gray-500' : 'bg-indigo-600 hover:bg-indigo-700'; ?> text-white font-medium py-2 px-5 rounded-lg transition-all hover:-translate-y-1 flex items-center gap-2 shadow-sm">
                                            <i class="ph ph-pencil-simple"></i> Edit
                                        </a>
                                        <button type="button" 
                                            data-post-id="<?php echo $post['id']; ?>" 
                                            class="delete-btn btn <?php echo $is_expired ? 'bg-gray-400 hover:bg-gray-500' : 'bg-red-500 hover:bg-red-700'; ?> text-white font-medium py-2 px-5 rounded-lg transition-all hover:-translate-y-1 flex items-center gap-2 shadow-sm">
                                            <i class="ph ph-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else : ?>
                    <p class="text-center text-gray-600 text-lg">No work posts available.</p>
                    <?php endif; ?>
                    
                    <!-- Delete Confirmation Modal -->
                    <div id="deleteModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
                        <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
                        <div class="modal-container bg-white w-11/12 md:max-w-md mx-auto rounded-lg shadow-lg z-50 overflow-y-auto transform scale-95 opacity-0 transition-all duration-300">
                            <div class="modal-content py-6 px-8">
                                <div class="flex items-center justify-center mb-6">
                                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100">
                                        <i class="ph ph-warning text-red-600 text-3xl"></i>
                                    </div>
                                </div>
                                <h3 class="text-xl font-bold text-center text-gray-800 mb-4">Confirm Deletion</h3>
                                <p class="text-center text-gray-600 mb-8">Are you sure you want to delete this post? This action cannot be undone.</p>
                                <div class="flex justify-center gap-4">
                                    <button id="cancelDelete" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition-all">
                                        Cancel
                                    </button>
                                    <a href="refund_confirm.php?post_id=" id="confirmRefundLink" 
                                       class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-all inline-block">
                                        Proceed to Refund
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
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
        
        // Setup delete confirmation modal
        setupDeleteModal();
    });
    
    // Set up navigation links to maintain sidebar state
    function setupNavigationLinks() {
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            if (!link.getAttribute('href').includes('logout.php')) {
                const originalHref = link.getAttribute('href');
                
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (window.isNavigating) return;
                    
                    // Clear any existing refresh timers
                    localStorage.setItem('lastNavigationTime', Date.now().toString());
                    window.location.href = originalHref;
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
        if (document.visibilityState === 'visible') {
            // Mark this tab as active
            localStorage.setItem(`tab_${tabId}`, 'active');
            window.isNavigating = false;
        } else if (document.visibilityState === 'hidden') {
            // Remove active status when tab is hidden
            localStorage.removeItem(`tab_${tabId}`);
        }
    });

    function setupDeleteModal() {
        const deleteButtons = document.querySelectorAll('.delete-btn');
        const deleteModal = document.getElementById('deleteModal');
        const cancelDelete = document.getElementById('cancelDelete');
        const confirmRefundLink = document.getElementById('confirmRefundLink');
        const modalOverlay = document.querySelector('.modal-overlay');
        const modalContainer = document.querySelector('.modal-container');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const postId = this.getAttribute('data-post-id');
                confirmRefundLink.href = `refund_confirm.php?post_id=${postId}`;
                deleteModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                
                setTimeout(() => {
                    modalContainer.classList.add('scale-100', 'opacity-100');
                    modalContainer.classList.remove('scale-95', 'opacity-0');
                }, 10);
            });
        });
        
        function closeModal() {
            // Animate modal closing
            modalContainer.classList.remove('scale-100', 'opacity-100');
            modalContainer.classList.add('scale-95', 'opacity-0');
            
            // Hide modal after animation completes
            setTimeout(() => {
                deleteModal.classList.add('hidden');
                document.body.style.overflow = ''; // Restore scrolling
            }, 300);
        }
        
        cancelDelete.addEventListener('click', closeModal);
        modalOverlay.addEventListener('click', closeModal);
    }
    </script>
    
    <style>
    /* Modal animations are handled with Tailwind classes in the modal markup */
    .scale-100 {
        transform: scale(1);
    }
    .opacity-100 {
        opacity: 1;
    }
    </style>
</body>
</html>