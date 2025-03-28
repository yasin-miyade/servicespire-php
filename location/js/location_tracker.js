function initHelperTracking(username) {
    let map, helperMarker;

    function initMap(latitude, longitude) {
        // Initialize map
        map = L.map('map').setView([latitude, longitude], 15);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Create marker
        helperMarker = L.marker([latitude, longitude]).addTo(map);
    }

    function updateLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;

                    // Update marker on map
                    helperMarker.setLatLng([latitude, longitude]);
                    map.setView([latitude, longitude], 15);

                    // Send location to server
                    fetch('backend/update_location.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            username: username,
                            latitude: latitude,
                            longitude: longitude
                        })
                    });
                },
                (error) => {
                    console.error('Error getting location:', error);
                }
            );
        }
    }

    // Get initial location and set up map
    navigator.geolocation.getCurrentPosition(
        (position) => {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;
            initMap(latitude, longitude);

            // Update location every 10 seconds
            setInterval(updateLocation, 10000);
        },
        (error) => {
            console.error('Error getting initial location:', error);
        }
    );
}

function initSeekerTracking(username) {
    let map, helperMarker;

    function initMap(latitude, longitude) {
        // Initialize map
        map = L.map('map').setView([latitude, longitude], 15);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Create marker
        helperMarker = L.marker([latitude, longitude]).addTo(map);
    }

    function fetchHelperLocation() {
        fetch(`backend/get_location.php?username=helper1`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const latitude = parseFloat(data.latitude);
                    const longitude = parseFloat(data.longitude);

                    // Update marker position
                    helperMarker.setLatLng([latitude, longitude]);
                    map.setView([latitude, longitude], 15);
                }
            })
            .catch(error => {
                console.error('Error fetching helper location:', error);
            });
    }

    // Get initial location and set up map
    navigator.geolocation.getCurrentPosition(
        (position) => {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;
            initMap(latitude, longitude);

            // Fetch helper location every 10 seconds
            setInterval(fetchHelperLocation, 10000);
        },
        (error) => {
            console.error('Error getting initial location:', error);
        }
    );
}