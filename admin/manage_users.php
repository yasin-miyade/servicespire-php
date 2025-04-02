<?php
include 'auth.php';
require_once('../lib/function.php');

$db = new db_functions();
$users = $db->getAllUsers(); // Fetch all users from the database
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f3f4f6;
        }
        .card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            border-collapse: separate;
            border-spacing: 0;
        }
        th {
            padding: 1rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
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
                    <i class="fas fa-users text-indigo-600 text-2xl mr-3"></i>
                    <h1 class="text-2xl font-semibold text-gray-800">Manage Users</h1>
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
                            <h2 class="text-xl font-semibold text-gray-800">All Users</h2>
                            <p class="text-gray-500 text-sm mt-1">View and manage all registered users</p>
                        </div>
                        <button id="refreshBtn" class="btn bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center shadow-md">
                            <i class="fas fa-sync-alt mr-2"></i> Refresh
                        </button>
                    </div>
                    
                    <?php if (empty($users)): ?>
                        <div class="text-center py-12 bg-gray-50 rounded-lg">
                            <i class="fas fa-users text-gray-400 text-6xl mb-4"></i>
                            <p class="text-gray-500 text-lg">No users found.</p>
                            <p class="text-gray-400 text-sm mt-2">New users will appear here when they register.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-container bg-white rounded-lg overflow-hidden">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center mr-3">
                                                        <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                                                    </div>
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></div>
                                            </td>
                                            <td class="text-sm text-gray-500">
                                                <div class="flex gap-2">
                                                    <button onclick="viewUser(<?= htmlspecialchars(json_encode($user)) ?>)" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition-colors">
                                                        <i class="fas fa-eye mr-1"></i> View
                                                    </button>
                                                    <button onclick="confirmDelete(<?= $user['id'] ?>)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition-colors">
                                                        <i class="fas fa-trash-alt mr-1"></i> Delete
                                                    </button>
                                                </div>
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
    </div>

    <!-- User Detail Modal -->
    <div id="userModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl mx-auto overflow-y-auto" style="max-height: 90vh;">
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800">User Details</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="p-6">
                <div class="flex flex-col md:flex-row gap-6">
                    <!-- User Avatar Section -->
                    <div class="flex flex-col items-center md:w-1/3">
                        <div class="w-32 h-32 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white text-4xl font-bold shadow-md mb-4">
                            <span id="modalUserInitials"></span>
                        </div>
                        <h4 class="text-xl font-semibold text-center" id="modalUserName"></h4>
                        <p class="text-gray-500 text-center" id="modalUserEmail"></p>
                    </div>
                    
                    <!-- User Details Section -->
                    <div class="md:w-2/3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-500">Password</p>
                                <p class="font-medium text-gray-800" id="modalUserPassword"></p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-500">Mobile</p>
                                <p class="font-medium text-gray-800" id="modalUserMobile"></p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-500">Gender</p>
                                <p class="font-medium text-gray-800" id="modalUserGender"></p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-500">Date of Birth</p>
                                <p class="font-medium text-gray-800" id="modalUserDOB"></p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg md:col-span-2">
                                <p class="text-sm text-gray-500">Address</p>
                                <p class="font-medium text-gray-800" id="modalUserAddress"></p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg md:col-span-2">
                                <p class="text-sm text-gray-500">ID Proof Type</p>
                                <p class="font-medium text-gray-800" id="modalUserIDProof"></p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg md:col-span-2">
                                <p class="text-sm text-gray-500 mb-2">ID Proof Document</p>
                                <img id="modalUserIDProofFile" class="w-full rounded-lg object-contain max-h-60 border border-gray-200" alt="ID Proof" style="display: none;" />
                                <p id="noImageMessage" class="text-gray-500 text-center py-4 italic">No ID proof image available</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-center p-6 border-t border-gray-200">
                <button onclick="closeModal()" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all shadow-md flex items-center justify-center">
                    <i class="fas fa-times-circle mr-2"></i> Close
                </button>
            </div>
        </div>
    </div>

    <script>
        function viewUser(user) {
            document.getElementById('modalUserName').textContent = user.first_name + " " + user.last_name;
            document.getElementById('modalUserInitials').textContent = (user.first_name.charAt(0) + user.last_name.charAt(0)).toUpperCase();
            document.getElementById('modalUserEmail').textContent = user.email;
            document.getElementById('modalUserPassword').textContent = user.password;
            document.getElementById('modalUserMobile').textContent = user.mobile;
            document.getElementById('modalUserGender').textContent = user.gender;
            document.getElementById('modalUserDOB').textContent = user.dob;
            document.getElementById('modalUserAddress').textContent = user.address;
            document.getElementById('modalUserIDProof').textContent = user.id_proof;
            const idProofFileElement = document.getElementById('modalUserIDProofFile');
            const noImageMessage = document.getElementById('noImageMessage');

            // Assuming the ID proof is a file path
            const idProofFilePath = '../uploads/' + user.id_proof_file;
            if (user.id_proof_file) {
                idProofFileElement.src = idProofFilePath;
                idProofFileElement.style.display = "block"; // Show the image
                noImageMessage.style.display = "none"; // Hide the message
                
                // Handle image loading error
                idProofFileElement.onerror = function() {
                    this.style.display = "none";
                    noImageMessage.style.display = "block";
                    noImageMessage.textContent = "Could not load image";
                };
            } else {
                idProofFileElement.style.display = "none"; // Hide if no file
                noImageMessage.style.display = "block"; // Show the message
            }
            document.getElementById('userModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('userModal').classList.add('hidden');
        }

        function confirmDelete(id) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'delete_user.php?id=' + id;
                }
            });
        }
    </script>
</body>
</html>