<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>User Location Map</title>
    <meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no">
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        #map {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 100%;
        }

        .user-marker {
            background: #1c52bdff;
            border: 3px solid white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            position: relative;
            cursor: pointer;
        }

        .user-marker::after {
            content: '';
            position: absolute;
            top: -8px;
            left: -8px;
            right: -8px;
            bottom: -8px;
            background: #4291e694;
            border: 2px solid #5083e1ff;
            border-radius: 70%;
            animation: pulse-ring 2s infinite;
            opacity: 0;
            z-index: -1;
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(0.8);
                opacity: 0.8;
            }

            80%,
            100% {
                transform: scale(1.9);
                opacity: 0;
            }
        }

        .location-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: white;
            border: none;
            border-radius: 8px;
            padding: 12px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            z-index: 10;
            transition: all 0.3s ease;
        }

        .location-btn:hover {
            background: #f0f0f0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .location-btn:active {
            transform: scale(0.95);
        }

        .location-btn svg {
            width: 24px;
            height: 24px;
            fill: #1c52bdff;
        }

        .location-status {
            position: absolute;
            top: 80px;
            right: 20px;
            background: rgba(26, 26, 26, 0.9);
            color: white;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            z-index: 10;
            display: none;
            max-width: 250px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .location-status.show {
            display: block;
        }

        .location-status.success {
            background: rgba(76, 175, 80, 0.95);
        }

        .location-status.error {
            background: rgba(244, 67, 54, 0.95);
        }

        .location-status.loading {
            background: rgba(33, 150, 243, 0.95);
        }
    </style>
</head>

<body>
    <div id="map"></div>

    <!-- Custom Location Button -->
    <button class="location-btn" id="locationBtn" title="Get my location">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path
                d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3A8.994 8.994 0 0 0 13 3.06V1h-2v2.06A8.994 8.994 0 0 0 3.06 11H1v2h2.06A8.994 8.994 0 0 0 11 20.94V23h2v-2.06A8.994 8.994 0 0 0 20.94 13H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z" />
        </svg>
    </button>

    <!-- Status Message -->
    <div class="location-status" id="locationStatus"></div>

    <script>
        mapboxgl.accessToken = 'pk.eyJ1IjoicmFuaml0ZHNvdXphIiwiYSI6ImNtaWdzMXB0ZzAxNnMzZnIxeWh1dWEwaXcifQ.BgmVhDYzaRLB8LgXKNFqJQ';


        let map;
        let userMarker;
        let watchId;

        window.onload = () => {
            // Initialize map with default location
            map = new mapboxgl.Map({
                container: 'map',
                zoom: 16.8,
                center: [24.951528, 60.169573],
                pitch: 74,
                bearing: 12.8,
                hash: true,
                style: 'mapbox://styles/ranjitdsouza/cmijtzilg00lr01qwf2ri04jb'
            });

            // Wait for map to load
            map.on('load', function () {
                console.log('Map loaded successfully');

                // Automatically get user location on load
                setTimeout(() => {
                    getUserLocation();
                }, 1000);
            });

            // Add click event to location button
            document.getElementById('locationBtn').addEventListener('click', getUserLocation);
        };

        // Show status message
        function showStatus(message, type = 'info') {
            const statusEl = document.getElementById('locationStatus');
            statusEl.textContent = message;
            statusEl.className = 'location-status show ' + type;

            // Auto-hide success messages
            if (type === 'success') {
                setTimeout(() => {
                    statusEl.classList.remove('show');
                }, 3000);
            }
        }

        // Get user's current location
        function getUserLocation() {
            if (!navigator.geolocation) {
                showStatus('Geolocation is not supported by your browser', 'error');
                return;
            }

            showStatus('Getting your location...', 'loading');

            const options = {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            };

            navigator.geolocation.getCurrentPosition(
                // Success callback
                function (position) {
                    const longitude = position.coords.longitude;
                    const latitude = position.coords.latitude;
                    const accuracy = position.coords.accuracy;

                    console.log('Location found:', latitude, longitude);
                    showStatus(`Location found (±${Math.round(accuracy)}m)`, 'success');

                    // Update or create marker
                    updateUserMarker(longitude, latitude);

                    // Fly to user's location
                    map.flyTo({
                        center: [longitude, latitude],
                        zoom: 16,
                        pitch: 45, // Match the reduced pitch
                        bearing: 0, // Keep north orientation
                        essential: true,
                        duration: 2000
                    });

                    // Optional: Start tracking user location
                    startTracking();
                },
                // Error callback
                function (error) {
                    let errorMessage = '';

                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = 'Location access denied. Please enable location permissions.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = 'Location information unavailable.';
                            break;
                        case error.TIMEOUT:
                            errorMessage = 'Location request timed out.';
                            break;
                        default:
                            errorMessage = 'An unknown error occurred.';
                    }

                    console.error('Geolocation error:', error);
                    showStatus(errorMessage, 'error');
                },
                options
            );
        }

        // Update or create user marker
        function updateUserMarker(longitude, latitude) {
            // Remove existing marker if it exists
            if (userMarker) {
                userMarker.remove();
            }

            // Create custom marker element
            const el = document.createElement('div');
            el.className = 'user-marker';

            // Create new marker
            userMarker = new mapboxgl.Marker({
                element: el,
                anchor: 'center'
            })
                .setLngLat([longitude, latitude])
                .addTo(map);

            console.log('Marker updated at:', longitude, latitude);
        }

        // Start tracking user location (optional - updates marker as user moves)
        function startTracking() {
            // Stop existing tracking if any
            if (watchId) {
                navigator.geolocation.clearWatch(watchId);
            }

            const options = {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            };

            // Watch position for continuous updates
            watchId = navigator.geolocation.watchPosition(
                function (position) {
                    const longitude = position.coords.longitude;
                    const latitude = position.coords.latitude;

                    // Update marker position smoothly
                    if (userMarker) {
                        userMarker.setLngLat([longitude, latitude]);
                    }

                    console.log('Location updated:', latitude, longitude);
                },
                function (error) {
                    console.error('Tracking error:', error);
                },
                options
            );
        }

        // Stop tracking when page unloads
        window.addEventListener('beforeunload', function () {
            if (watchId) {
                navigator.geolocation.clearWatch(watchId);
            }
        });
    </script>

</body>

</html>