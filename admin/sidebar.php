<div class="sidebar w-64 text-white py-6 flex flex-col justify-between">
    <div>
        <div class="px-6 mb-8 flex items-center justify-center flex-col">
            <h2 class="text-2xl font-bold">Admin Dashboard</h2>
            <div class="w-16 h-1 bg-purple-300 rounded-full mt-2"></div>
        </div>
        
        <nav class="px-4">
            <a href="admin_dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'text-white sidebar-active' : 'text-gray-300 hover:text-white'; ?>">
                <i class="fas fa-tachometer-alt text-lg"></i>
                <span>Dashboard</span>
            </a>
            <a href="manage_users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'text-white sidebar-active' : 'text-gray-300 hover:text-white'; ?>">
                <i class="fas fa-users text-lg"></i>
                <span>Users</span>
            </a>
            <a href="manage_helpers.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_helpers.php' ? 'text-white sidebar-active' : 'text-gray-300 hover:text-white'; ?>">
                <i class="fas fa-hands-helping text-lg"></i>
                <span>Helpers</span>
            </a>
            <a href="contact_us_data.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact_us_data.php' ? 'text-white sidebar-active' : 'text-gray-300 hover:text-white'; ?>">
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