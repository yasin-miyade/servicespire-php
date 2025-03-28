<?php
require_once('../lib/function.php');
$db = new db_functions();

// Fetch users and helpers
$users = $db->getUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="min-h-screen flex">
    <aside class="w-72 bg-gradient-to-b from-purple-900 to-purple-700 text-white p-6 flex flex-col shadow-lg">
        <div class="flex items-center space-x-3 mb-6">
            <img src="https://i.imgur.com/6VBx3io.png" alt="Admin" class="w-12 h-12 rounded-full border-2 border-white">
            <div>
                <h2 class="text-lg font-semibold">Admin Panel</h2>
                <p class="text-sm text-purple-300">Administrator</p>
            </div>
        </div>

        <nav class="flex-1">
            <ul class="space-y-2">
                <li><a href="#" onclick="showSection('usersSection')" class="flex items-center p-3 rounded-lg hover:bg-purple-800 transition duration-300">Users Management</a></li>
                <li class="mt-auto"><a href="logout.php" class="flex items-center p-3 bg-red-600 rounded-lg hover:bg-red-700 transition duration-300">Logout</a></li>
            </ul>
        </nav>
    </aside>

    <main class="flex-1 p-8">
        <h1 class="text-3xl font-bold text-purple-800 mb-6">Admin Dashboard</h1>

        <!-- Users Section -->
        <div id="usersSection" class="bg-white p-6 rounded-lg shadow-lg mb-6">
            <h2 class="text-xl font-semibold mb-4">Users</h2>
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-purple-100">
                        <th class="p-3 border">Name</th>
                        <th class="p-3 border">Email</th>
                        <th class="p-3 border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $row): ?>
                        <tr class="hover:bg-gray-100 transition">
                            <td class="p-3 border"><?= $row['first_name'] . " " . $row['last_name'] ?></td>
                            <td class="p-3 border"><?= $row['email'] ?></td>
                            <td class="p-3 border text-center">
                                <button class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition" onclick="viewUser(<?= $row['id'] ?>)">View</button>
                                <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition" onclick="deleteUser(<?= $row['id'] ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- User Details Modal -->
<div id="userModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h2 class="text-xl font-bold mb-4">User Details</h2>
        <p><strong>Name:</strong> <span id="userName"></span></p>
        <p><strong>Email:</strong> <span id="userEmail"></span></p>
        <p><strong>Mobile:</strong> <span id="userMobile"></span></p>
        <p><strong>Gender:</strong> <span id="userGender"></span></p>
        <p><strong>Date of Birth:</strong> <span id="userDOB"></span></p>
        <p><strong>Address:</strong> <span id="userAddress"></span></p>
        <p><strong>Password:</strong> <span id="userPassword"></span></p>
        <p><strong>ID Proof:</strong></p>
        <img id="userIdProof" class="mt-2 w-32 h-32 rounded-lg border" alt="ID Proof">

        <button onclick="closeModal()" class="mt-4 bg-red-500 text-white px-4 py-2 rounded">Close</button>
    </div>
</div>

<script>
function showSection(sectionId) {
    document.getElementById('usersSection').classList.add('hidden');
    document.getElementById(sectionId).classList.remove('hidden');
}

function deleteUser(userId) {
    if (confirm("Are you sure you want to delete this user?")) {
        window.location.href = `delete_user.php?id=${userId}`;
    }
}

function viewUser(userId) {
    fetch(`get_user.php?id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert("User not found.");
                return;
            }

            document.getElementById('userName').innerText = `${data.first_name} ${data.last_name}`;
            document.getElementById('userEmail').innerText = data.email;
            document.getElementById('userMobile').innerText = data.mobile ?? 'N/A';
            document.getElementById('userGender').innerText = data.gender ?? 'N/A';
            document.getElementById('userDOB').innerText = data.dob ?? 'N/A';
            document.getElementById('userAddress').innerText = data.address ?? 'N/A';
            document.getElementById('userPassword').innerText = data.password ?? 'N/A';

            let imageElement = document.getElementById('userIdProof');
            if (data.id_proof_file) {
                imageElement.src = data.id_proof_file;
                imageElement.classList.remove('hidden');
            } else {
                imageElement.classList.add('hidden');
            }

            document.getElementById('userModal').classList.remove('hidden');
        })
        .catch(error => {
            alert("Error fetching data.");
            console.error(error);
        });
}

function closeModal() {
    document.getElementById('userModal').classList.add('hidden');
}
</script>

</body>
</html>
