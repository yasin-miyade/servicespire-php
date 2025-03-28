<?php
// Footer Component for ServiceSpire
?>

<footer class="bg-gradient-to-r from-gray-900 to-black text-white py-12">
    <div class="container mx-auto px-4 ">
        <!-- Top Section with Logo and Main Content -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
            <!-- Brand Section -->
            <div class="md:pr-8">
                <img src="assets/images/logo-white.svg" alt="ServiceSpire Logo" class=" size-60 -mt-20" />
                <p class="text-gray-300 text-sm leading-relaxed -mt-20">Connecting users for seamless offline assistance. Your trusted platform for community service exchange.</p>
                
                <!-- Social Media Icons for Desktop (Moved from bottom) -->
                <div class="hidden md:flex mt-6 space-x-4">
                    <a href="#" class="bg-gray-800 hover:bg-blue-600 text-white p-2 rounded-full transition-colors duration-300">
                        <i class="fab fa-facebook-f text-sm"></i>
                    </a>
                    <a href="#" class="bg-gray-800 hover:bg-blue-600 text-white p-2 rounded-full transition-colors duration-300">
                        <i class="fab fa-twitter text-sm"></i>
                    </a>
                    <a href="https://www.instagram.com/servicespire2025?igsh=MXB5YTBjcGUxYTQ0Ng==" class="bg-gray-800 hover:bg-blue-600 text-white p-2 rounded-full transition-colors duration-300">
                        <i class="fab fa-instagram text-sm"></i>
                    </a>
                </div>
            </div>

            <!-- Services Links -->
            <div>
                <h3 class="text-lg font-bold text-white border-b border-blue-500 pb-2 mb-3 inline-block">Services</h3>
                <ul class="mt-4 space-y-3 text-gray-300">
                    <li><a href="#" class="hover:text-blue-400 transition-colors duration-300 flex items-center">
                        <span class="text-blue-500 mr-2">&rsaquo;</span> Request Help
                    </a></li>
                    <li><a href="#" class="hover:text-blue-400 transition-colors duration-300 flex items-center">
                        <span class="text-blue-500 mr-2">&rsaquo;</span> Offer Help
                    </a></li>
                    <li><a href="#" class="hover:text-blue-400 transition-colors duration-300 flex items-center">
                        <span class="text-blue-500 mr-2">&rsaquo;</span> Find Assistance
                    </a></li>
                    <li><a href="#" class="hover:text-blue-400 transition-colors duration-300 flex items-center">
                        <span class="text-blue-500 mr-2">&rsaquo;</span> Safety Tips
                    </a></li>
                </ul>
            </div>

            <!-- Useful Links -->
            <div>
                <h3 class="text-lg font-bold text-white border-b border-blue-500 pb-2 mb-3 inline-block">Useful Links</h3>
                <ul class="mt-4 space-y-3 text-gray-300">
                    <li><a href="about.php" class="hover:text-blue-400 transition-colors duration-300 flex items-center">
                        <span class="text-blue-500 mr-2">&rsaquo;</span> About Us
                    </a></li>
                    <li><a href="contact_us.php" class="hover:text-blue-400 transition-colors duration-300 flex items-center">
                        <span class="text-blue-500 mr-2">&rsaquo;</span> Contact
                    </a></li>
                    <li><a href="faqs.php" class="hover:text-blue-400 transition-colors duration-300 flex items-center">
                        <span class="text-blue-500 mr-2">&rsaquo;</span> FAQs
                    </a></li>
                    <li><a href="termcondition.php" class="hover:text-blue-400 transition-colors duration-300 flex items-center">
                        <span class="text-blue-500 mr-2">&rsaquo;</span> Terms & Conditions
                    </a></li>
                </ul>
            </div>

            <!-- Contact Section -->
            <div>
                <h3 class="text-lg font-bold text-white border-b border-blue-500 pb-2 mb-3 inline-block">Contact Us</h3>
                <ul class="mt-4 space-y-3 text-gray-300">
                    <li class="flex items-start">
                        <i class="far fa-envelope text-blue-500 mt-1 mr-3"></i>
                        <span>Email: <a href="mailto:servicespire@gmail.com" class="hover:text-blue-400 transition-colors duration-300">servicespire@gmail.com</a></span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-phone-alt text-blue-500 mt-1 mr-3"></i>
                        <span>Phone: <a href="tel:+1234567890" class="hover:text-blue-400 transition-colors duration-300">+1 234 567 890</a></span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-map-marker-alt text-blue-500 mt-1 mr-3"></i>
                        <span>Address: 123 Service Street, City, Country</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Social Media Icons (Mobile Only) -->
        <div class="md:hidden mt-8 flex justify-center space-x-4">
            <a href="#" class="bg-gray-800 hover:bg-blue-600 text-white p-2 rounded-full transition-colors duration-300">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="bg-gray-800 hover:bg-blue-600 text-white p-2 rounded-full transition-colors duration-300">
                <i class="fab fa-twitter"></i>
            </a>
            <a href="https://www.instagram.com/servicespire2025?igsh=MXB5YTBjcGUxYTQ0Ng==" class="bg-gray-800 hover:bg-blue-600 text-white p-2 rounded-full transition-colors duration-300">
                <i class="fab fa-instagram"></i>
            </a>
        </div>

        <!-- Divider -->
        <div class="border-t border-gray-800 my-8"></div>

        <!-- Copyright -->
        <div class="text-center text-gray-400 text-sm">
            <p>&copy; <?php echo date("Y"); ?> ServiceSpire. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- FontAwesome for Icons -->
<script src="https://kit.fontawesome.com/YOUR_KIT_CODE.js" crossorigin="anonymous"></script>