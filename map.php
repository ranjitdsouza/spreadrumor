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
            height: 50%;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.4), rgba(223, 223, 223, 0.12));
            backdrop-filter: blur(3px);
            pointer-events: none;
        }

        .search {
            margin-left: 10px;
            margin-right: 10px;
            background-color: #00000059;
            padding: 15px 12px;
            color: white;
            border-radius: 12px;
            font-size: 0.8em;
            pointer-events: auto;
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
            color: white;
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

        .location-status {
            position: absolute;
            top: 70px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(26, 26, 26, 0.95);
            color: white;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            z-index: 100;
            display: none;
            max-width: 90%;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .retry-btn {
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            margin-top: 8px;
            font-weight: 500;
        }

        .loading-spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
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

    <div class="bottom-box">
        <div class="search">
            <label class="current-location">Getting your location...</label>
            on
            <label class="date-time" id="dateTime">12th Dec, 2025</label>
        </div>
        <div class="search-range">
            <h2>Near Me</h2>
        </div>
    </div>

    <script>
        // Replace with your Mapbox access token
        mapboxgl.accessToken = 'pk.eyJ1IjoicmFuaml0ZHNvdXphIiwiYSI6ImNtaWdzMXB0ZzAxNnMzZnIxeWh1dWEwaXcifQ.BgmVhDYzaRLB8LgXKNFqJQ';

        let map;
        let userMarker;
        let geolocateControl;
        let isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        // Set current date
        function setCurrentDate() {
            const now = new Date();
            const options = { day: 'numeric', month: 'short', year: 'numeric' };
            const dateStr = now.toLocaleDateString('en-US', options);
            document.getElementById('dateTime').textContent = dateStr;
        }

        // Initialize map
        function initializeMap() {
            createMap([73.8567, 18.5204]);
            setTimeout(() => {
                checkLocationPermission();
            }, 1000);
        }

        // Check location permission status
        async function checkLocationPermission() {
            if (!navigator.geolocation) {
                showLocationStatus('Geolocation is not supported by this browser.', 'error');
                return;
            }

            showLocationStatus('Checking location permissions...', 'loading');

            try {
                if (navigator.permissions && navigator.permissions.query) {
                    const permissionStatus = await navigator.permissions.query({ name: 'geolocation' });

                    if (permissionStatus.state === 'granted') {
                        showLocationStatus('Location permission granted', 'success');
                        getCurrentLocation();
                        return;
                    } else if (permissionStatus.state === 'denied') {
                        showLocationStatus('Location access was previously denied. Please enable it in your browser settings.', 'error');
                        return;
                    }
                }

                if (isMobile) {
                    showLocationStatus('Tap "Allow Location Access" to enable location services.', 'info');
                } else {
                    getCurrentLocation();
                }
            } catch (error) {
                console.log('Permission check failed, using fallback:', error);
                if (isMobile) {
                    showLocationStatus('Tap "Allow Location Access" to enable location services.', 'info');
                } else {
                    getCurrentLocation();
                }
            }
        }

        // Show location status
        function showLocationStatus(message, type = 'info') {
            const statusElement = document.getElementById('locationStatus');

            // Add loading spinner for loading state
            if (type === 'loading') {
                statusElement.innerHTML = '<span class="loading-spinner"></span>' + message;
            } else {
                statusElement.textContent = message;
            }

            statusElement.style.display = 'block';

            if (type === 'error') {
                statusElement.style.background = 'rgba(255, 107, 107, 0.95)';
                statusElement.style.color = 'white';

                if (!statusElement.querySelector('.retry-btn')) {
                    const retryBtn = document.createElement('button');
                    retryBtn.className = 'retry-btn';
                    retryBtn.textContent = 'Retry';
                    retryBtn.onclick = getCurrentLocationWithRetry;
                    statusElement.appendChild(document.createElement('br'));
                    statusElement.appendChild(retryBtn);
                }
            } else if (type === 'success') {
                statusElement.style.background = 'rgba(76, 175, 80, 0.95)';
                statusElement.style.color = 'white';
                const retryBtn = statusElement.querySelector('.retry-btn');
                if (retryBtn) retryBtn.remove();
            } else if (type === 'loading') {
                statusElement.style.background = 'rgba(33, 150, 243, 0.95)';
                statusElement.style.color = 'white';
            } else {
                statusElement.style.background = 'rgba(26, 26, 26, 0.95)';
                statusElement.style.color = 'white';
                const retryBtn = statusElement.querySelector('.retry-btn');
                if (retryBtn) retryBtn.remove();
            }

            if (type === 'success') {
                setTimeout(() => {
                    statusElement.style.display = 'none';
                }, 3000);
            }
        }

        // Get current location with retry logic
        function getCurrentLocationWithRetry() {
            showLocationStatus('Getting your location...', 'loading');

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

                    showLocationStatus(`Location found (±${Math.round(accuracy)}m)`, 'success');
                    updateLocationText(latitude, longitude);
                    updateMapLocation([longitude, latitude]);

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
                            errorMessage = 'Location access denied. Please enable location permissions.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = 'Location information unavailable. Check your device settings.';
                            break;
                        case error.TIMEOUT:
                            errorMessage = 'Location request timed out. Please try again.';
                            break;
                        default:
                            errorMessage = 'Failed to get location. Please try again.';
                    }

                    showLocationStatus(errorMessage, 'error');
                    console.error('Geolocation error:', error);
                },
                options
            );
        }

        // Get user's current location
        function getCurrentLocation() {
            showLocationStatus('Getting your location...', 'loading');

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
                    updateLocationText(latitude, longitude);
                    updateMapLocation([longitude, latitude]);
                },
                function (error) {
                    if (error.code === error.PERMISSION_DENIED) {
                        showLocationStatus('Location access denied. Please allow location access.', 'error');
                    } else {
                        showLocationStatus('Using default location (Pune)', 'info');
                        updateLocationText(18.5204, 73.8567, 'Pune, India');
                    }
                },
                options
            );
        }

        // Update location text with reverse geocoding
        function updateLocationText(latitude, longitude, fallbackText = 'Your Location') {
            const locationElement = document.querySelector('.current-location');

            fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${longitude},${latitude}.json?access_token=${mapboxgl.accessToken}`)
                .then(response => response.json())
                .then(data => {
                    if (data.features && data.features.length > 0) {
                        const place = data.features[0].place_name;
                        locationElement.textContent = place.split(',')[0];
                    } else {
                        locationElement.textContent = fallbackText;
                    }
                })
                .catch(() => {
                    locationElement.textContent = fallbackText;
                });
        }

        // Create map with specified center - FIXED VERSION
        function createMap(center) {
            try {
                // Your custom style URL
                const customStyleURL = 'mapbox://styles/ranjitdsouza/cmijtzilg00lr01qwf2ri04jb';

                console.log('Initializing map with custom style...');

                map = new mapboxgl.Map({
                    container: 'map',
                    style: 'mapbox://styles/standard/style-v11', // Start with standard style
                    center: [center[0], center[1]],
                    zoom: 13,
                    pitch: 60,
                    bearing: 12.8,
                    attributionControl: false,
                    failIfMajorPerformanceCaveat: false
                });

                // Handle when map is ready (use 'load' instead of 'style.load')
                map.on('load', function () {
                    console.log('Map loaded successfully!');
                    setupMapControls(center);
                });

                // Handle style data event (fires when style is loaded)
                map.on('styledata', function () {
                    console.log('Style data loaded');
                });

                // Handle errors
                map.on('error', function (e) {
                    console.error('Map error:', e.error);

                    // If custom style fails, switch to fallback
                    if (e.error && e.error.status === 404) {
                        console.log('Custom style not found, using fallback...');
                        switchToFallbackStyle(center);
                    }
                });

            } catch (error) {
                console.error('Map initialization error:', error);
                showLocationStatus('Map initialization failed. Trying fallback...', 'error');
                switchToFallbackStyle(center);
            }
        }

        // Switch to fallback style if custom style fails
        function switchToFallbackStyle(center) {
            if (map) {
                map.remove();
            }

            console.log('Loading fallback style...');

            map = new mapboxgl.Map({
                container: 'map',
                style: 'mapbox://styles/ranjitdsouza/cmijtzilg00lr01qwf2ri04jb', // Fallback to dark theme
                center: center,
                zoom: 13,
                pitch: 60,
                bearing: 12.8,
                attributionControl: false
            });

            map.on('load', function () {
                console.log('Fallback map loaded successfully!');
                setupMapControls(center);
            });
        }

        // Setup map controls (extracted to separate function)
        function setupMapControls(center) {
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

            // Add default marker
            addDefaultMarker(center);

            // Listen for geolocate events
            geolocateControl.on('geolocate', function (e) {
                const longitude = e.coords.longitude;
                const latitude = e.coords.latitude;
                updateLocationText(latitude, longitude);
                showLocationStatus('Location updated!', 'success');
                updateMapLocation([longitude, latitude]);
            });

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
            if (userMarker) {
                userMarker.remove();
            }

            const el = document.createElement('div');
            el.className = 'user-marker';

            userMarker = new mapboxgl.Marker({
                element: el,
                anchor: 'center'
            })
                .setLngLat(coordinates)
                .addTo(map);

            const offsetY = 0.002;

            map.flyTo({
                center: [coordinates[0], coordinates[1] - offsetY],
                zoom: 15,
                pitch: 60,
                essential: true
            });
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function () {
            setCurrentDate();
            initializeMap();
        });
    </script>
</body>

</html>