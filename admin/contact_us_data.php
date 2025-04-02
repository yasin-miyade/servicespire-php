<?php
session_start();
require_once('../lib/function.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$db = new db_functions();
$con = $db->connect();

// Fetch all contact form submissions
$query = "SELECT * FROM contact_data ORDER BY date DESC, time DESC";
$result = $con->query($query);
$contacts = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $contacts[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form Submissions | Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom right, #eef2f3, #ffffff);
        }
        
        .sidebar {
            background: linear-gradient(to bottom, #4338ca, #5b21b6);
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 4px solid white;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin-bottom: 8px;
        }
        
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .table-container {
            overflow-x: auto;
            scrollbar-width: thin;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .table-container::-webkit-scrollbar {
            height: 8px;
        }
        
        .table-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .table-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        
        .table-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        .message-cell {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .message-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 1rem;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            animation: modalFadeIn 0.3s ease-out;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .btn {
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        table {
            border-collapse: separate;
            border-spacing: 0;
        }
        
        table th {
            background-color: #f9fafb;
            position: sticky;
            top: 0;
            z-index: 10;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 16px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        table td {
            padding: 16px;
            border-bottom: 1px solid #f3f4f6;
        }
        
        tbody tr {
            transition: all 0.2s ease;
        }
        
        tbody tr:hover {
            background-color: #f9fafb;
        }
        
        .view-btn {
            padding: 6px 12px;
            border-radius: 6px;
            background-color: #4f46e5;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .view-btn:hover {
            background-color: #4338ca;
        }
    </style>
</head>
<body class="bg-gray-100 flex h-screen">
    <!-- Sidebar -->
    <div class="sidebar w-64 text-white py-6 flex flex-col justify-between">
        <div>
            <div class="px-6 mb-8 flex items-center justify-center flex-col">
                <h2 class="text-2xl font-bold">Admin Dashboard</h2>
                <div class="w-16 h-1 bg-purple-300 rounded-full mt-2"></div>
            </div>
            
            <nav class="px-4">
                <a href="admin_dashboard.php" class="nav-link text-gray-300 hover:text-white">
                    <i class="fas fa-tachometer-alt text-lg"></i>
                    <span>Dashboard</span>
                </a>
                <a href="manage_users.php" class="nav-link text-gray-300 hover:text-white">
                    <i class="fas fa-users text-lg"></i>
                    <span>Users</span>
                </a>
                <a href="manage_helpers.php" class="nav-link text-gray-300 hover:text-white">
                    <i class="fas fa-hands-helping text-lg"></i>
                    <span>Helpers</span>
                </a>
                <a href="contact_us_data.php" class="nav-link text-white sidebar-active">
                    <i class="fas fa-envelope text-lg"></i>
                    <span>Contact Messages</span>
                </a>
            </nav>
        </div>
        
        <div class="px-6 mb-6">
            <a href="logout.php" class="flex items-center text-gray-300 hover:text-white transition-colors p-2 rounded-lg hover:bg-red-500">
                <i class="fas fa-sign-out-alt mr-3"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Header -->
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center">
            <div class="flex items-center">
                <i class="fas fa-envelope text-indigo-600 text-2xl mr-3"></i>
                <h1 class="text-2xl font-semibold text-gray-800">Contact Messages</h1>
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
            <div class="card p-6 mb-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">All Contact Messages</h2>
                        <p class="text-gray-500 text-sm mt-1">View and manage all contact form submissions</p>
                    </div>
                    <button id="refreshBtn" class="btn bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center shadow-md">
                        <i class="fas fa-sync-alt mr-2"></i> Refresh
                    </button>
                </div>
                
                <?php if (empty($contacts)): ?>
                    <div class="text-center py-12 bg-gray-50 rounded-lg">
                        <i class="fas fa-inbox text-gray-400 text-6xl mb-4"></i>
                        <p class="text-gray-500 text-lg">No contact form submissions found.</p>
                        <p class="text-gray-400 text-sm mt-2">New messages will appear here when users submit the contact form.</p>
                    </div>
                <?php else: ?>
                    <div class="table-container bg-white rounded-lg overflow-hidden">
                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">S.No</th>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $serial = 1;
                                foreach ($contacts as $contact): 
                                ?>
                                    <tr>
                                        <td class="text-sm text-gray-500"><?php echo $serial++; ?></td>
                                        <td>
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center mr-3">
                                                    <?php echo strtoupper(substr($contact['username'], 0, 1)); ?>
                                                </div>
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($contact['username']); ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($contact['email']); ?></div>
                                        </td>
                                        <td class="text-sm text-gray-500"><?php echo htmlspecialchars($contact['phone']); ?></td>
                                        <td>
                                            <div class="text-sm text-gray-900 message-cell" data-message="<?php echo htmlspecialchars($contact['message']); ?>">
                                                <?php echo htmlspecialchars($contact['message']); ?>
                                            </div>
                                        </td>
                                        <td class="text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <i class="far fa-calendar-alt mr-2 text-indigo-500"></i>
                                                <div>
                                                    <?php echo htmlspecialchars(date('M d, Y', strtotime($contact['date']))); ?>
                                                    <div class="text-xs text-gray-400">
                                                        <?php echo htmlspecialchars(date('h:i A', strtotime($contact['time']))); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="view-message view-btn" data-id="<?php echo isset($contact['id']) ? $contact['id'] : '0'; ?>">
                                                <i class="fas fa-eye mr-1"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Message Modal -->
    <div id="messageModal" class="message-modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6 border-b pb-4">
                <h3 class="text-xl font-semibold text-gray-800">Contact Message Details</h3>
                <button id="closeModal" class="text-gray-500 hover:text-gray-700 text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <p class="text-sm text-gray-500 mb-1">From:</p>
                    <p id="modalName" class="font-medium text-gray-800"></p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500 mb-1">Email:</p>
                    <p id="modalEmail" class="font-medium text-gray-800"></p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500 mb-1">Phone:</p>
                    <p id="modalPhone" class="font-medium text-gray-800"></p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500 mb-1">Date & Time:</p>
                    <p id="modalDateTime" class="font-medium text-gray-800"></p>
                </div>
            </div>
            
            <div>
                <p class="text-sm text-gray-500 mb-2">Message:</p>
                <div id="modalMessage" class="p-4 bg-gray-50 rounded-lg text-gray-800 whitespace-pre-wrap border border-gray-200 text-sm leading-relaxed"></div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Refresh button functionality
            document.getElementById('refreshBtn').addEventListener('click', function() {
                this.classList.add('animate-spin');
                setTimeout(() => {
                    location.reload();
                }, 500);
            });
            
            // View message functionality
            const viewButtons = document.querySelectorAll('.view-message');
            const modal = document.getElementById('messageModal');
            const closeModal = document.getElementById('closeModal');
            
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const name = row.querySelector('td:nth-child(2) .text-gray-900').textContent;
                    const email = row.querySelector('td:nth-child(3) div').textContent;
                    const phone = row.querySelector('td:nth-child(4)').textContent;
                    const message = row.querySelector('.message-cell').getAttribute('data-message');
                    const dateTime = row.querySelector('td:nth-child(6) div').textContent.trim();
                    
                    document.getElementById('modalName').textContent = name;
                    document.getElementById('modalEmail').textContent = email;
                    document.getElementById('modalPhone').textContent = phone;
                    document.getElementById('modalMessage').textContent = message;
                    document.getElementById('modalDateTime').textContent = dateTime;
                    
                    modal.style.display = 'flex';
                    setTimeout(() => {
                        document.querySelector('.modal-content').style.opacity = 1;
                    }, 10);
                });
            });
            
            closeModal.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>