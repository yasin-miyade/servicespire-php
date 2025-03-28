<?php
session_start();
// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit();
}

// Check if helper email and post ID are provided
if (!isset($_GET['helper_email']) || !isset($_GET['post_id'])) {
    header("Location: index.php?page=pending");
    exit();
}

require_once("../lib/function.php");
$db = new db_functions();
$conn = $db->connect();

$helper_email = $_GET['helper_email'];
$post_id = $_GET['post_id'];
$from_location = $_GET['from'] ?? '';
$to_location = $_GET['to'] ?? '';

// Verify that the logged-in user is the owner of the work post
$user_email = $_SESSION['email'];
$verify_query = "SELECT * FROM work_posts WHERE id = ? AND email = ? AND assigned_helper_email = ?";
$verify_stmt = $conn->prepare($verify_query);
$verify_stmt->bind_param("iss", $post_id, $user_email, $helper_email);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows == 0) {
    // Not authorized to view this helper's location
    header("Location: index.php?page=pending");
    exit();
}

// Get work post details
$post = $verify_result->fetch_assoc();

// Get helper details
$helper_query = "SELECT first_name, last_name, mobile FROM helper_sign_up WHERE email = ?";
$helper_stmt = $conn->prepare($helper_query);
$helper_stmt->bind_param("s", $helper_email);
$helper_stmt->execute();
$helper_info = $helper_stmt->get_result()->fetch_assoc();

// Try to include header from different possible paths
$header_included = false;
$possible_paths = [
    "../include/header.php",
    "../includes/header.php",
    "header.php"
];

foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        include($path);
        $header_included = true;
        break;
    }
}

// If no header file is found, create a basic HTML header
if (!$header_included) {
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Track Helper Location - ServiceSpire</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/fill/style.css" />
    </head>
    <body class="bg-gray-50">';
}
?>

<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <!-- Page Header/Title -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Tracking Helper Location</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Tracking location for work: <span class="font-semibold"><?php echo htmlspecialchars($post['work']); ?></span>
                </p>
            </div>
            <a href="index.php?page=pending" class="btn inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                <i class="ph ph-arrow-left mr-2"></i> Back to Pending
            </a>
        </div>
        
        <!-- Helper Info Card -->
        <div class="bg-white shadow rounded-lg mb-6 p-4">
            <div class="flex items-center">
                <div class="bg-indigo-500 rounded-full w-12 h-12 flex items-center justify-center">
                    <span class="text-lg font-medium text-white">
                        <?php echo substr($helper_info['first_name'], 0, 1) . substr($helper_info['last_name'], 0, 1); ?>
                    </span>
                </div>
                <div class="ml-4">
                    <h2 class="text-xl font-semibold text-gray-800">
                        <?php echo htmlspecialchars($helper_info['first_name'].' '.$helper_info['last_name']); ?>
                    </h2>
                    <div class="flex items-center mt-1">
                        <i class="ph ph-phone text-indigo-600 mr-2"></i>
                        <a href="tel:<?php echo htmlspecialchars($helper_info['mobile']); ?>" class="text-indigo-600 hover:underline">
                            <?php echo htmlspecialchars($helper_info['mobile']); ?>
                        </a>
                        <span class="mx-2 text-gray-400">|</span>
                        <button id="btn-call" class="text-sm px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full hover:bg-indigo-200 transition-colors">
                            <i class="ph ph-phone mr-1"></i> Call Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Map and Location Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Map Container -->
            <div class="lg:col-span-2 bg-white shadow rounded-lg overflow-hidden">
                <div class="h-[500px]" id="map"></div>
            </div>
            
            <!-- Location Details -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Location Details</h3>
                
                <div class="space-y-4">
                    <!-- Current Location -->
                    <div>
                        <div class="flex items-center text-sm font-medium text-gray-500 mb-2">
                            <i class="ph ph-map-pin text-red-500 mr-2"></i> Current Location
                        </div>
                        <div class="px-4 py-3 bg-gray-50 rounded-lg" id="current-location">
                            <div class="animate-pulse flex space-x-4">
                                <div class="flex-1 space-y-2 py-1">
                                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- From Location -->
                    <div>
                        <div class="flex items-center text-sm font-medium text-gray-500 mb-2">
                            <i class="ph ph-map-trifold text-blue-500 mr-2"></i> From
                        </div>
                        <div class="px-4 py-3 bg-blue-50 rounded-lg">
                            <p class="text-sm text-gray-700"><?php echo htmlspecialchars($from_location); ?></p>
                        </div>
                    </div>
                    
                    <!-- To Location -->
                    <div>
                        <div class="flex items-center text-sm font-medium text-gray-500 mb-2">
                            <i class="ph ph-flag-checkered text-green-500 mr-2"></i> To
                        </div>
                        <div class="px-4 py-3 bg-green-50 rounded-lg">
                            <p class="text-sm text-gray-700"><?php echo htmlspecialchars($to_location); ?></p>
                        </div>
                    </div>
                    
                    <!-- Last Updated -->
                    <div class="pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-500 flex items-center">
                            <i class="ph ph-clock mr-1"></i> 
                            Last updated: <span id="last-updated" class="ml-1">Just now</span>
                        </p>
                    </div>
                </div>
                
                <div class="mt-6">
                    <a href="https://www.google.com/maps/dir/?api=1&origin=<?php echo urlencode($from_location); ?>&destination=<?php echo urlencode($to_location); ?>" 
                       target="_blank"
                       class="w-full btn inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        <i class="ph ph-map-trifold mr-2"></i> Open in Google Maps
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Google Maps API -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBNS5Bak8GWm_JzbUZYPv4q4JigFIQIgTg&callback=initMap" async defer></script>

<script>
    let map;
    let helperMarker;
    let fromMarker;
    let toMarker;
    let updateInterval;
    let directionsService;
    let directionsRenderer;
    
    // Helper data
    const helperEmail = "<?php echo $helper_email; ?>";
    const postId = <?php echo $post_id; ?>;
    const fromLocation = "<?php echo addslashes($from_location); ?>";
    const toLocation = "<?php echo addslashes($to_location); ?>";
    
    // Phone call functionality
    document.getElementById('btn-call').addEventListener('click', function() {
        window.location.href = 'tel:<?php echo htmlspecialchars($helper_info['mobile']); ?>';
    });
    
    function initMap() {
        // Initialize map centered on Malaysia (will be updated when helper location is fetched)
        map = new google.maps.Map(document.getElementById("map"), {
            center: { lat: 3.139, lng: 101.6869 }, // Default to Kuala Lumpur
            zoom: 14,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            mapTypeControl: false,
            fullscreenControl: true,
            streetViewControl: true,
        });
        
        // Initialize directions service
        directionsService = new google.maps.DirectionsService();
        directionsRenderer = new google.maps.DirectionsRenderer({
            suppressMarkers: true,
            polylineOptions: {
                strokeColor: '#4F46E5',
                strokeWeight: 5,
                strokeOpacity: 0.7
            }
        });
        directionsRenderer.setMap(map);
        
        // Create info windows
        const fromInfoWindow = new google.maps.InfoWindow({
            content: `<div class="p-2"><strong>Pickup Location</strong><p>${fromLocation}</p></div>`
        });
        
        const toInfoWindow = new google.maps.InfoWindow({
            content: `<div class="p-2"><strong>Destination</strong><p>${toLocation}</p></div>`
        });
        
        // Helper marker with custom icon or fallback to default
        helperMarker = new google.maps.Marker({
            map: map,
            icon: {
                url: '../assets/imgs/helper-marker.png',
                scaledSize: new google.maps.Size(40, 40),
                // Add error handling for the icon
                onerror: function() {
                    this.onerror = null;
                    this.src = 'https://maps.google.com/mapfiles/ms/icons/red-dot.png';
                }
            },
            title: "Helper Current Location"
        });
        
        // If the custom icon fails to load, use a default Google marker
        helperMarker.addListener('click', function() {
            if (!this.icon || typeof this.icon === 'string') {
                this.setIcon({
                    url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
                    scaledSize: new google.maps.Size(32, 32)
                });
            }
        });
        
        // Geocode the from and to addresses
        const geocoder = new google.maps.Geocoder();
        
        // Geocode From location
        if (fromLocation) {
            geocoder.geocode({ address: fromLocation }, (results, status) => {
                if (status === "OK" && results[0]) {
                    fromMarker = new google.maps.Marker({
                        position: results[0].geometry.location,
                        map: map,
                        icon: {
                            url: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                            scaledSize: new google.maps.Size(32, 32)
                        },
                        title: "From: " + fromLocation
                    });
                    
                    fromMarker.addListener("click", () => {
                        fromInfoWindow.open(map, fromMarker);
                    });
                    
                    // Update map bounds to include this marker
                    updateMapBounds();
                }
            });
        }
        
        // Geocode To location
        if (toLocation) {
            geocoder.geocode({ address: toLocation }, (results, status) => {
                if (status === "OK" && results[0]) {
                    toMarker = new google.maps.Marker({
                        position: results[0].geometry.location,
                        map: map,
                        icon: {
                            url: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png',
                            scaledSize: new google.maps.Size(32, 32)
                        },
                        title: "To: " + toLocation
                    });
                    
                    toMarker.addListener("click", () => {
                        toInfoWindow.open(map, toMarker);
                    });
                    
                    // Update map bounds to include this marker
                    updateMapBounds();
                }
            });
        }
        
        // Get initial helper location
        fetchHelperLocation();
        
        // Set interval to update helper location every 10 seconds
        updateInterval = setInterval(fetchHelperLocation, 10000);
    }
    
    function updateMapBounds() {
        // Create a bounds object
        const bounds = new google.maps.LatLngBounds();
        
        // Add markers to bounds
        if (helperMarker && helperMarker.getPosition()) bounds.extend(helperMarker.getPosition());
        if (fromMarker && fromMarker.getPosition()) bounds.extend(fromMarker.getPosition());
        if (toMarker && toMarker.getPosition()) bounds.extend(toMarker.getPosition());
        
        // If we have at least one marker, fit the map to the bounds
        if (!bounds.isEmpty()) {
            map.fitBounds(bounds);
            
            // Don't zoom in too far
            const listener = google.maps.event.addListener(map, "idle", function() {
                if (map.getZoom() > 16) map.setZoom(16);
                google.maps.event.removeListener(listener);
            });
        }
    }
    
    function fetchHelperLocation() {
        fetch('../ajax/get_helper_location.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `helper_email=${encodeURIComponent(helperEmail)}&post_id=${postId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.lat && data.lng) {
                updateHelperLocation(data.lat, data.lng, data.address);
                updateLastUpdated();
                
                // Show alert if this is simulated data
                if (data.is_simulated) {
                    document.getElementById('current-location').innerHTML = 
                        `<p class="text-sm text-gray-700">${data.address} <span class="text-xs text-yellow-600">(Demo location)</span></p>`;
                }
            } else {
                document.getElementById('current-location').innerHTML = 
                    `<p class="text-sm text-gray-700">Location information not available yet.</p>`;
            }
        })
        .catch(error => {
            console.error('Error fetching helper location:', error);
        });
    }
    
    function updateHelperLocation(lat, lng, address) {
        const helperPosition = new google.maps.LatLng(lat, lng);
        
        helperMarker.setPosition(helperPosition);
        
        // Center map on helper location
        map.setCenter(helperPosition);
        
        // Update the location text
        document.getElementById('current-location').innerHTML = 
            `<p class="text-sm text-gray-700">${address || 'Current location'}</p>`;
            
        // Calculate route from helper location to destination if both markers exist
        if (toMarker) {
            calculateAndDisplayRoute(helperPosition, toMarker.getPosition());
        }
        
        // Update the map bounds
        updateMapBounds();
    }
    
    function calculateAndDisplayRoute(origin, destination) {
        directionsService.route(
            {
                origin: origin,
                destination: destination,
                travelMode: google.maps.TravelMode.DRIVING,
            },
            (response, status) => {
                if (status === "OK") {
                    directionsRenderer.setDirections(response);
                    
                    // Get route details
                    const route = response.routes[0].legs[0];
                    const distance = route.distance.text;
                    const duration = route.duration.text;
                    
                    // Update the location text with ETA
                    const currentLocationElement = document.getElementById('current-location');
                    const currentText = currentLocationElement.innerText;
                    currentLocationElement.innerHTML = 
                        `<p class="text-sm text-gray-700">${currentText}</p>
                         <div class="mt-1 text-xs text-gray-600">
                            <span class="font-medium">ETA: ${duration}</span> (${distance} away)
                         </div>`;
                }
            }
        );
    }
    
    function updateLastUpdated() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString();
        document.getElementById('last-updated').textContent = timeStr;
    }
    
    // Clean up interval when page is unloaded
    window.addEventListener('beforeunload', function() {
        if (updateInterval) {
            clearInterval(updateInterval);
        }
    });
</script>

<?php 
// Try to include footer from different possible paths
$footer_included = false;
$possible_footer_paths = [
    "../include/footer.php",
    "../includes/footer.php",
    "footer.php"
];

foreach ($possible_footer_paths as $path) {
    if (file_exists($path)) {
        include($path);
        $footer_included = true;
        break;
    }
}

// If no footer file is found, add a simple closing HTML structure
if (!$footer_included) {
    echo '</div></body></html>';
}
?>
