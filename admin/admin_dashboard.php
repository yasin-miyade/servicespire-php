<?php
include 'auth.php';
require_once('../lib/function.php');

$db = new db_functions();
$total_users = $db->getTotalUsers();
$total_helpers = $db->getTotalHelpers();
$total_messages = $db->getTotalContactMessages(); // Add this line
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f3f4f6;
        }
        .card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .chart-container {
            width: 100%;
            height: 250px;
        }
    </style>
    <?php include 'sidebar_styles.php'; ?>
</head>
<body class="min-h-screen bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center">
                <div class="flex items-center">
                    <i class="fas fa-tachometer-alt text-indigo-600 text-2xl mr-3"></i>
                    <h1 class="text-2xl font-semibold text-gray-800">Dashboard Overview</h1>
                </div>
                
                <div class="flex items-center">
                    <span class="mr-3 text-gray-700">Admin</span>
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 flex items-center justify-center text-white shadow-md">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </header>
            
            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- Users Card -->
                    <div class="card p-6 flex items-center">
                        <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center mr-4">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Total Users</h3>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $total_users; ?></p>
                        </div>
                    </div>
                    
                    <!-- Helpers Card -->
                    <div class="card p-6 flex items-center">
                        <div class="w-12 h-12 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center mr-4">
                            <i class="fas fa-hands-helping text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Total Helpers</h3>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $total_helpers; ?></p>
                        </div>
                    </div>
                    
                    <!-- Messages Card -->
                    <div class="card p-6 flex items-center">
                        <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center mr-4">
                            <i class="fas fa-envelope text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Messages</h3>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $total_messages; ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Chart Section -->
                <div class="card p-6 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">User & Helper Statistics</h3>
                        <button class="text-indigo-600 hover:text-indigo-800">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="chart-container">
                        <canvas id="userChart"></canvas>
                    </div>
                </div>
                
                <!-- Recent Activity Section -->
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Activity</h3>
                    <div class="space-y-4">
                        <?php if (empty($recent_activities)): ?>
                            <div class="text-center py-6">
                                <p class="text-gray-500">No recent activities found.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <div class="w-10 h-10 rounded-full <?php echo $activity['bg']; ?> <?php echo $activity['text']; ?> flex items-center justify-center mr-3">
                                        <i class="<?php echo $activity['icon']; ?>"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">
                                            New <?php echo $activity['type']; ?> registered: <?php echo htmlspecialchars($activity['name']); ?>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?php 
                                                if ($activity['time'] !== 'Recently') {
                                                    $time_diff = time() - strtotime($activity['time']);
                                                    if ($time_diff < 60) {
                                                        echo "Just now";
                                                    } elseif ($time_diff < 3600) {
                                                        echo floor($time_diff / 60) . " minutes ago";
                                                    } elseif ($time_diff < 86400) {
                                                        echo floor($time_diff / 3600) . " hours ago";
                                                    } else {
                                                        echo floor($time_diff / 86400) . " days ago";
                                                    }
                                                } else {
                                                    echo "Recently";
                                                }
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Chart Data
        const ctx = document.getElementById('userChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Users', 'Helpers'],
                datasets: [{
                    label: 'Total Count',
                    data: [<?php echo $total_users; ?>, <?php echo $total_helpers; ?>],
                    backgroundColor: ['#6b46c1', '#ff9800'],
                    borderColor: ['#4a2883', '#e65100'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Prevents chart from becoming too big
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false // Hides legend to keep UI clean
                    }
                }
            }
        });
    </script>

</body>
</html>
