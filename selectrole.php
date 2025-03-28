<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Your Role</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen flex items-center justify-center font-[Inter]">
    <!-- Decorative elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-100 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-100 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
        <div class="absolute -top-40 -left-20 w-72 h-72 bg-green-100 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
    </div>

    <!-- Back Button -->
    <div class="absolute top-20 left-32">
        <a href="index.php" class="flex items-center text-gray-600 hover:text-blue-700 transition group">
            <div class="w-8 h-8 bg-white rounded-full shadow-md flex items-center justify-center mr-2 group-hover:bg-blue-50 transition">
                <i class="fas fa-arrow-left text-blue-600"></i>
            </div>
            <span class="font-medium">Back to Home</span>
        </a>
    </div>

    <!-- Card Container -->
    <div class="relative w-full max-w-md mx-4">
        <!-- Glass card effect -->
        <div class="relative bg-white/90 backdrop-blur-lg rounded-3xl shadow-xl border border-white/60 p-10 overflow-hidden">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="flex justify-center mb-6">
                    <div class="relative">
                        <div class="w-16 h-16 flex items-center justify-center bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                            </svg>
                        </div>
                        <div class="absolute -right-2 -bottom-2 w-6 h-6 bg-green-400 rounded-full border-2 border-white flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
                <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Select Your Role</h1>
                <p class="mt-3 text-slate-500 text-sm">Choose how you want to proceed with our platform</p>
            </div>

            <!-- Role Selection Buttons -->
            <div class="space-y-4">
                <!-- User Button -->
                <a href="user/login.php" class="group block w-full transition-all duration-300">
                    <div class="bg-white hover:bg-gradient-to-r hover:from-blue-50 hover:to-blue-100 border border-slate-200 hover:border-blue-200 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all duration-300 flex items-center">
                        <div class="bg-blue-100 group-hover:bg-blue-500 rounded-xl p-3 mr-4 transition-colors duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 group-hover:text-white transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-slate-700 group-hover:text-blue-700 transition-colors duration-300">Continue as User</h3>
                            <p class="text-xs text-slate-500 mt-1">Access services and get the help you need</p>
                        </div>
                        <div class="text-slate-300 group-hover:text-blue-500 transition-colors duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Helper Button -->
                <a href="helper/login.php" class="group block w-full transition-all duration-300">
                    <div class="bg-white hover:bg-gradient-to-r hover:from-green-50 hover:to-green-100 border border-slate-200 hover:border-green-200 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all duration-300 flex items-center">
                        <div class="bg-green-100 group-hover:bg-green-500 rounded-xl p-3 mr-4 transition-colors duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 group-hover:text-white transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-slate-700 group-hover:text-green-700 transition-colors duration-300">Continue as Helper</h3>
                            <p class="text-xs text-slate-500 mt-1">Provide assistance and support to users</p>
                        </div>
                        <div class="text-slate-300 group-hover:text-green-500 transition-colors duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <p class="text-xs text-slate-400">Need help? <a href="contact_us.php" class="text-blue-500 hover:text-blue-700 font-medium">Contact support</a></p>
            </div>
        </div>
    </div>
</body>
</html>