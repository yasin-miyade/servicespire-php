<?php
include 'auth.php';
require_once('../lib/function.php');

$db = new db_functions();
$helpers = $db->getAllHelpers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Helpers</title>
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
                    <i class="fas fa-hands-helping text-indigo-600 text-2xl mr-3"></i>
                    <h1 class="text-2xl font-semibold text-gray-800">Manage Helpers</h1>
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
                            <h2 class="text-xl font-semibold text-gray-800">All Helpers</h2>
                            <p class="text-gray-500 text-sm mt-1">View and manage all registered helpers</p>
                        </div>
                        <button id="refreshBtn" class="btn bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center shadow-md">
                            <i class="fas fa-sync-alt mr-2"></i> Refresh
                        </button>
                    </div>
                    
                    <?php if (empty($helpers)): ?>
                        <div class="text-center py-12 bg-gray-50 rounded-lg">
                            <i class="fas fa-user-friends text-gray-400 text-6xl mb-4"></i>
                            <p class="text-gray-500 text-lg">No helpers found.</p>
                            <p class="text-gray-400 text-sm mt-2">New helpers will appear here when they register.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-container bg-white rounded-lg overflow-hidden">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mobile</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($helpers as $helper): ?>
                                        <tr>
                                            <td>
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center mr-3">
                                                        <?php echo strtoupper(substr($helper['first_name'], 0, 1)); ?>
                                                    </div>
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($helper['first_name'] . ' ' . $helper['last_name']); ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($helper['email']); ?></div>
                                            </td>
                                            <td class="text-sm text-gray-500"><?php echo htmlspecialchars($helper['mobile']); ?></td>
                                            <td class="text-sm text-gray-500">
                                                <div class="flex gap-2">
                                                    <button onclick="viewHelper(<?php echo htmlspecialchars(json_encode($helper)); ?>)" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition-colors">
                                                        <i class="fas fa-eye mr-1"></i> View
                                                    </button>
                                                    <button onclick="confirmDelete(<?php echo $helper['id']; ?>)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition-colors">
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

    <!-- Modal for Viewing Helper Details -->
    <div id="helperModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl mx-auto overflow-y-auto" style="max-height: 90vh;">
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800">Helper Details</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="p-6">
                <div class="flex flex-col md:flex-row gap-6">
                    <!-- Helper Avatar Section -->
                    <div class="flex flex-col items-center md:w-1/3">
                        <div class="w-32 h-32 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white text-4xl font-bold shadow-md mb-4">
                            <span id="helperInitials"></span>
                        </div>
                        <h4 class="text-xl font-semibold text-center" id="helperName"></h4>
                        <p class="text-gray-500 text-center" id="helperEmail"></p>
                    </div>
                    
                    <!-- Helper Details Section -->
                    <div class="md:w-2/3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-500">Password</p>
                                <p class="font-medium text-gray-800" id="helperPassword"></p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-500">Mobile</p>
                                <p class="font-medium text-gray-800" id="helperMobile"></p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-500">Gender</p>
                                <p class="font-medium text-gray-800" id="helperGender"></p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-500">Date of Birth</p>
                                <p class="font-medium text-gray-800" id="helperDOB"></p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg md:col-span-2">
                                <p class="text-sm text-gray-500">Address</p>
                                <p class="font-medium text-gray-800" id="helperAddress"></p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg md:col-span-2">
                                <p class="text-sm text-gray-500">ID Proof Type</p>
                                <p class="font-medium text-gray-800" id="helperIDProofType"></p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg md:col-span-2">
                                <p class="text-sm text-gray-500 mb-2">ID Proof Document</p>
                                <div id="idProofContainer" class="border rounded-lg p-2 bg-gray-50">
                                    <a id="idProofLink" href="#" target="_blank">
                                        <img id="helperIDProof" class="w-full rounded-lg object-contain max-h-60" alt="ID Proof" style="display: none;" />
                                    </a>
                                    <p id="noImageMessage" class="text-gray-500 text-center py-4 italic hidden">No ID proof image available</p>
                                </div>
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
        function viewHelper(helper) {
            document.getElementById('helperName').textContent = helper.first_name + ' ' + helper.last_name;
            document.getElementById('helperInitials').textContent = (helper.first_name.charAt(0) + helper.last_name.charAt(0)).toUpperCase();
            document.getElementById('helperEmail').textContent = helper.email;
            document.getElementById('helperPassword').textContent = helper.password;
            document.getElementById('helperMobile').textContent = helper.mobile;
            document.getElementById('helperGender').textContent = helper.gender;
            document.getElementById('helperDOB').textContent = helper.dob;
            document.getElementById('helperAddress').textContent = helper.address;
            document.getElementById('helperIDProofType').textContent = helper.id_proof;
            
            // Handle ID proof image
            const idProofImg = document.getElementById('helperIDProof');
            const idProofLink = document.getElementById('idProofLink');
            const noImageMessage = document.getElementById('noImageMessage');
            
            if (helper.id_proof_file && helper.id_proof_file.trim() !== '') {
                // Fix the path - remove duplicate "uploads/" if present
                let imagePath = '../helper/uploads/' + helper.id_proof_file;
                
                // Check if the path already contains "uploads/" in the filename and fix it
                if (helper.id_proof_file.startsWith('uploads/')) {
                    imagePath = '../helper/' + helper.id_proof_file;
                }
                
                console.log('Trying to load image from:', imagePath);
                
                idProofImg.src = imagePath;
                idProofLink.href = imagePath;
                idProofImg.style.display = 'block';
                noImageMessage.classList.add('hidden');
                
                idProofImg.onerror = function() {
                    console.error('Failed to load image:', imagePath);
                    this.style.display = 'none';
                    noImageMessage.textContent = 'Could not load image. Please check if the file exists at: ' + imagePath;
                    noImageMessage.classList.remove('hidden');
                };
            } else {
                idProofImg.style.display = 'none';
                noImageMessage.textContent = 'No ID proof image available';
                noImageMessage.classList.remove('hidden');
            }
            
            document.getElementById('helperModal').classList.remove('hidden');
        }
        
        function closeModal() {
            document.getElementById('helperModal').classList.add('hidden');
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
                    window.location.href = 'delete_helper.php?id=' + id;
                }
            });
        }
    </script>
</body>
</html>