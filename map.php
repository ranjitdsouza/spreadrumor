<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Location Map</title>
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css" rel="stylesheet" />
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .nav {
            background: linear-gradient(to top, rgba(223, 223, 223, 0.12), rgba(0, 0, 0, 0.57));

            color: white;
            padding: 14px 10px;
            text-align: left;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .nav .appName {
            font-size: 1.5em;
            font-weight: 700;
            font-style: italic;
            color: white;
        }

        .nav .explore-btn {
            float: right;

        }

        .nav .explore-btn button {
            background-color: transparent;
            border: 2px solid #c03d3dff;
            color: white;
            padding: 8px 22px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 0.8em;
            letter-spacing: 1px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
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

        .bottom-box {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 57%;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.4), rgba(223, 223, 223, 0.12));
            backdrop-filter: blur(3px);
        }

        .search {
            margin-left: 10px;
            margin-right: 10px;
            background-color: #00000059;
            padding: 15px 12px;
            color: white;
            border-radius: 12px;
            font-size: 0.8em;
        }

        .search .current-location {
            font-weight: 500;
            font-size: 1.1em;
        }

        .search .date-time {
            font-weight: 400;
            font-size: 1em;
        }


        .search-range {
            margin-top: -12px;
            margin-left: 10px;
            margin-right: 10px;
            padding: 1px 3px;
            color: black;
            border-radius: 12px;
        }

        .search-range h2 {
            font-weight: 700;
            font-size: 1.7em;
        }

        /* Hide Mapbox logo and attribution */
        .mapboxgl-ctrl-logo,
        .mapboxgl-ctrl-attrib,
        .mapboxgl-ctrl-compass,
        .mapboxgl-ctrl-zoom-in,
        .mapboxgl-ctrl-zoom-out,
        .mapboxgl-ctrl-geolocate {
            display: none !important;
        }

        /* Permission dialog styles */
        .permission-dialog {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            max-width: 350px;
            width: 90%;
            text-align: center;
            display: none;
        }

        .permission-dialog h3 {
            margin: 0 0 15px 0;
            color: #333;
        }

        .permission-dialog p {
            margin: 0 0 20px 0;
            color: #666;
            line-height: 1.5;
            font-size: 14px;
        }

        .permission-btn {
            background: #49a565;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            margin: 5px;
            width: 90%;
            max-width: 250px;
        }

        .permission-btn:hover {
            background: #21c23c;
        }

        .permission-btn.secondary {
            background: #6c757d;
        }

        .permission-btn.secondary:hover {
            background: #545b62;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }

        .instructions {
            font-size: 12px;
            color: #888;
            margin-top: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .location-status {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(26, 26, 26, 0.9);
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            z-index: 100;
            display: none;
        }

        .retry-btn {
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="nav">
        <span class="appName">Spread Rumor</span>
        <span class="explore-btn">
            <button>Explore</button>
        </span>
    </div>
    <div id="map"></div>
    <div class="location-status" id="locationStatus"></div>
    <div class="bottom-box">
        <div class="search">
            <label class="current-location">Getting your location...</label>
            on
            <label class="date-time">12th Dec, 2025</label>
        </div>
        <div class="search-range">
            <h2>Near Me</h2>
        </div>

    </div>

    <!-- Permission Request Dialog -->
    <div class="overlay" id="overlay"></div>
    <div class="permission-dialog" id="permissionDialog">
        <h3>Location Access Required</h3>
        <p>This app needs access to your location to show you on the map and provide location-based services.</p>
        <div class="instructions">
            <strong>For Mobile Users:</strong> Make sure location services are enabled and try again.
        </div>
        <button class="permission-btn" id="requestPermission">Allow Location Access</button>
        <button class="permission-btn secondary" id="skipLocation">Skip for Now</button>
    </div>

    <script>
        // Replace with your Mapbox access token
        mapboxgl.accessToken = 'pk.eyJ1IjoicmFuaml0ZHNvdXphIiwiYSI6ImNtaWdzMXB0ZzAxNnMzZnIxeWh1dWEwaXcifQ.BgmVhDYzaRLB8LgXKNFqJQ';

        let map;
        let userMarker;
        let geolocateControl;
        let isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        // Initialize map
        function initializeMap() {
            // Create map with default location first
            createMap([73.8567, 18.5204]);

            // Then check location permission
            setTimeout(() => {
                checkLocationPermission();
            }, 1000);
        }

        // Check location permission status
        async function checkLocationPermission() {
            if (!navigator.geolocation) {
                showPermissionDialog('Geolocation is not supported by this browser.');
                return;
            }

            showLocationStatus('Checking location permissions...');

            try {
                // Check if permission is already granted using Permissions API
                if (navigator.permissions && navigator.permissions.query) {
                    const permissionStatus = await navigator.permissions.query({ name: 'geolocation' });

                    if (permissionStatus.state === 'granted') {
                        // Permission already granted - get location directly without showing dialog
                        showLocationStatus('Location permission granted', 'success');
                        getCurrentLocation();
                        return;
                    } else if (permissionStatus.state === 'denied') {
                        // Permission denied - show dialog
                        showPermissionDialog('Location access was previously denied. Please enable it in your browser settings.');
                        return;
                    }
                    // If prompt state, continue to normal flow
                }

                // For mobile devices or when permission state is 'prompt'
                if (isMobile) {
                    showPermissionDialog('Tap "Allow Location Access" to enable location services.');
                } else {
                    // For desktop, try to get location directly (will trigger prompt if needed)
                    getCurrentLocation();
                }
            } catch (error) {
                console.log('Permission check failed, using fallback:', error);
                // Fallback for browsers that don't support Permissions API
                if (isMobile) {
                    showPermissionDialog('Tap "Allow Location Access" to enable location services.');
                } else {
                    getCurrentLocation();
                }
            }
        }

        // Show permission request dialog
        function showPermissionDialog(message = null) {
            const dialog = document.getElementById('permissionDialog');
            const overlay = document.getElementById('overlay');
            const requestBtn = document.getElementById('requestPermission');
            const skipBtn = document.getElementById('skipLocation');

            if (message) {
                const mainParagraph = dialog.querySelector('p');
                if (mainParagraph) {
                    mainParagraph.textContent = message;
                }
            }

            dialog.style.display = 'block';
            overlay.style.display = 'block';

            // Request permission when button is clicked
            requestBtn.onclick = function () {
                hidePermissionDialog();
                getCurrentLocationWithRetry();
            };

            // Skip location when button is clicked
            skipBtn.onclick = function () {
                hidePermissionDialog();
                updateLocationText(18.5204, 73.8567, 'Pune, India');
                showLocationStatus('Using default location', 'info');
            };
        }

        // Hide permission dialog
        function hidePermissionDialog() {
            document.getElementById('permissionDialog').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }

        // Show location status
        function showLocationStatus(message, type = 'info') {
            const statusElement = document.getElementById('locationStatus');
            statusElement.textContent = message;
            statusElement.style.display = 'block';

            if (type === 'error') {
                statusElement.style.background = 'rgba(255, 107, 107, 0.9)';
                statusElement.style.color = 'white';

                // Add retry button for errors
                if (!statusElement.querySelector('.retry-btn')) {
                    const retryBtn = document.createElement('button');
                    retryBtn.className = 'retry-btn';
                    retryBtn.textContent = 'Retry';
                    retryBtn.onclick = getCurrentLocationWithRetry;
                    statusElement.appendChild(retryBtn);
                }
            } else {
                statusElement.style.background = 'rgba(255, 255, 255, 0.9)';
                statusElement.style.color = '#333';

                // Remove retry button if exists
                const retryBtn = statusElement.querySelector('.retry-btn');
                if (retryBtn) {
                    retryBtn.remove();
                }
            }

            // Auto-hide success messages after 3 seconds
            if (type === 'success') {
                setTimeout(() => {
                    statusElement.style.display = 'none';
                }, 3000);
            }
        }

        // Get current location with retry logic
        function getCurrentLocationWithRetry() {
            showLocationStatus('Getting your location...');

            const options = {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            };

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
                    const accuracy = position.coords.accuracy;

                    showLocationStatus(`Location found (accuracy: ${Math.round(accuracy)}m)`, 'success');

                    // Update location text
                    updateLocationText(latitude, longitude);

                    // Update map with user's location
                    updateMapLocation([longitude, latitude]);

                    // Trigger the geolocate control if available
                    if (geolocateControl) {
                        setTimeout(() => {
                            geolocateControl.trigger();
                        }, 1000);
                    }
                },
                function (error) {
                    let errorMessage = 'Unable to get your location. ';

                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = 'Location access denied. Please enable location permissions in your browser settings.';
                            showPermissionDialog(errorMessage);
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = 'Location information unavailable. Please check your device location settings.';
                            showLocationStatus(errorMessage, 'error');
                            break;
                        case error.TIMEOUT:
                            errorMessage = 'Location request timed out. Please try again.';
                            showLocationStatus(errorMessage, 'error');
                            break;
                        default:
                            errorMessage = 'Failed to get location. Please try again.';
                            showLocationStatus(errorMessage, 'error');
                    }

                    console.error('Geolocation error:', error);
                },
                options
            );
        }

        // Get user's current location
        function getCurrentLocation() {
            showLocationStatus('Getting your location...');

            const options = {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
            };

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;

                    showLocationStatus('Location found!', 'success');

                    // Update location text
                    updateLocationText(latitude, longitude);

                    // Update map with user's location
                    updateMapLocation([longitude, latitude]);
                },
                function (error) {
                    // If permission is denied on desktop, show dialog
                    if (error.code === error.PERMISSION_DENIED) {
                        showPermissionDialog('Location access was denied. Please allow location access.');
                    } else {
                        // For other errors, show default location
                        showLocationStatus('Using default location', 'info');
                        updateLocationText(18.5204, 73.8567, 'Pune, India');
                    }
                },
                options
            );
        }

        // Update location text with reverse geocoding
        function updateLocationText(latitude, longitude, fallbackText = 'Your Location') {
            const locationElement = document.querySelector('.current-location');

            // Simple reverse geocoding using Mapbox
            fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${longitude},${latitude}.json?access_token=${mapboxgl.accessToken}`)
                .then(response => response.json())
                .then(data => {
                    if (data.features && data.features.length > 0) {
                        // Get the most relevant place name
                        const place = data.features[0].place_name;
                        locationElement.textContent = place.split(',')[0]; // Get first part (usually locality)
                    } else {
                        locationElement.textContent = fallbackText;
                    }
                })
                .catch(() => {
                    locationElement.textContent = fallbackText;
                });
        }

        // Create map with specified center
        function createMap(center) {
            map = new mapboxgl.Map({
                container: 'map',
                style: 'mapbox://styles/mapbox/streets-v12',
                center: center,
                zoom: 13,
                attributionControl: false // Disable attribution control
            });

            // Add navigation controls
            map.addControl(new mapboxgl.NavigationControl());

            // Add geolocate control
            geolocateControl = new mapboxgl.GeolocateControl({
                positionOptions: {
                    enableHighAccuracy: true
                },
                trackUserLocation: true,
                showUserLocation: true,
                showAccuracyCircle: true
            });

            map.addControl(geolocateControl);

            // Add user marker for default location
            addDefaultMarker(center);

            // Listen for geolocate events
            geolocateControl.on('geolocate', function (e) {
                const longitude = e.coords.longitude;
                const latitude = e.coords.latitude;
                updateLocationText(latitude, longitude);
                showLocationStatus('Location updated!', 'success');

                // Update marker position when user location is found
                updateMapLocation([longitude, latitude]);
            });

            // Handle geolocate errors
            geolocateControl.on('error', function (e) {
                console.error('Geolocate control error:', e);
                showLocationStatus('Location error: ' + (e.message || 'Unknown error'), 'error');
            });
        }

        // Add default marker for initial location
        function addDefaultMarker(coordinates) {
            const el = document.createElement('div');
            el.className = 'user-marker';

            userMarker = new mapboxgl.Marker({
                element: el,
                anchor: 'center'
            })
                .setLngLat(coordinates)
                .addTo(map);
        }

        // Update map with user's location
        function updateMapLocation(coordinates) {
            // Remove existing user marker if it exists
            if (userMarker) {
                userMarker.remove();
            }

            // Create new marker element
            const el = document.createElement('div');
            el.className = 'user-marker';

            // Add marker at correct coordinates
            userMarker = new mapboxgl.Marker({
                element: el,
                anchor: 'center'
            })
                .setLngLat(coordinates)
                .addTo(map);

            // Calculate offset to position user marker higher on screen (above bottom box)
            const offsetY = 0.002;

            // Fly to location with offset to position marker higher on screen
            map.flyTo({
                center: [coordinates[0], coordinates[1] - offsetY],
                zoom: 15,
                essential: true
            });
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function () {
            initializeMap();
        });
    </script>
</body>

</html>