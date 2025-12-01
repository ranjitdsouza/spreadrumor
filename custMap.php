<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>User Location Map</title>
    <meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap"
        rel="stylesheet">
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Open Sans', sans-serif !important;
        }

        .nav {

            padding: 14px 10px;
            text-align: left;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .nav .appName {
            font-size: 1.5em;
            font-weight: 900;
            font-style: italic;
            color: #5e51a5ff;
        }

        .nav .explore-btn {
            float: right;
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




        .bottom-box {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 0;
            width: 100%;
            height: 50%;
            backdrop-filter: blur(6.40px);
            border-radius: 32px 32px 0 0;
            /* border: 1px solid #bd2424ff; */
            background: rgba(235, 235, 235, 0.60);
            box-shadow: 0 -2px 12.6px 0 rgba(144, 144, 158, 0.50);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 8px 0 8px 0;
            overflow: hidden;
        }

        .search {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            width: 83%;
            height: 28px;
            padding: 8px 24px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.10);
            box-shadow: -4px -4px 8px 0 #FFF, 4px 4px 19px 0 rgba(255, 42, 0, 0.20);
            color: #606060;
            /* border: 1px solid orange; */
            font-family: "Open Sans";
            /* font-size: 14px; */
            /* font-weight: 600; */
            margin-bottom: 16px;

        }

        .current-location {
            font-size: 14px;
            /* background-color: #3d3333ff; */
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
            text-align: left;
            font-weight: 600;
            color: #606060;
            flex: 1;
            max-width: 72%;

        }


        .current-location {
            position: relative;
        }

        .on {
            font-size: 14px;
            font-weight: 400;
            color: #808080;
            margin-right: 4px;
        }

        .date-time {
            /* background-color: #3d3333ff; */
            font-size: 14px;
            text-align: right;
            font-weight: 600;
            color: #606060;
            flex-shrink: 0;

        }

        .results {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            width: 358px;
            background-color: transparent;
            padding: 0 8px;
        }

        .results .aval-event {
            font-size: 16px;
            font-weight: 700;
            color: black;
            background-color: transparent;
        }

        .results .result-count {
            font-size: 14px;
            font-weight: 500;
            color: #606060;
            background-color: transparent;
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


        /* Edit Search */
        .editSearch {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: 84%;
            height: 192px;
            padding: 24px;
            border-radius: 24px;
            background: rgba(228, 228, 228, 0.97);
            box-shadow: -4px -4px 8px 0 #FFF, 4px 4px 19px 0 rgba(255, 42, 0, 0.20);
            color: #606060;
            /* border: 1px solid orange; */
            font-family: "Open Sans";
            /* font-size: 14px; */
            /* font-weight: 600; */
            margin-bottom: 8px;
        }

        .editPanel {
            width: 100%;
            height: 100%;
            border: 1px solid #44b9fdff;
            /* border-radius: 16px; */
        }

        .editPanel #dateLabel {
            font-size: 14px;
            font-weight: 600;
            color: #606060;
            left: 0;
            float: left;
            text-align: left;
            margin-top: 7px;
        }

        .editPanel input[type="date"] {

            right: 0;
            float: right;
            border: 1px solid #ccc;
            padding: 8px;
            font-size: 14px;
            height: 17px;
            background-color: white;
            border: none;
            border-radius: 8px;
            text-align: center;
            font-family: 'Open Sans', sans-serif;
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            display: none;
        }
    </style>
</head>

<body onload="getCurrentLocation()">
    <div class="nav">
        <span class="appName">Spread Rumor</span>

    </div>
    <div id="map"></div>

    <div class="bottom-box" id="searchBox">
        <!-- Height adjustable bar -->
        <span
            style="background-color: #B9B9B9; width: 126px; height: 4px; border-radius: 4px; margin-bottom: 8px;"></span>

        <div class="search">
            <span class="current-location" id="currentLocation">Swargate, Pune, Maharashtra, India</span>

            <span class="date-time" id="dateTime">
                <span class="on" style="">on</span>
                12th Dec</span>
        </div>


        <div class="bottom-box" id="editSearch" style="display: none;">
            <!-- Height adjustable bar -->
            <span
                style="background-color: #B9B9B9; width: 126px; height: 4px; border-radius: 4px; margin-bottom: 8px;"></span>

            <!-- Search area -->
            <div class="editSearch" id="search" onclick="enableLocationChange()">
                <div class="editPanel">
                    <label id="dateLabel">Date</label>
                    <input type="date" id="dateInput" name="dateInput" value="">
                </div>
            </div>

            <!-- 
        <img src="search.jpeg" alt="Drag Handle"
            style="margin-top: -63px; margin-bottom: 4px; width: 100%; z-index: 1000; !important;  pointer-events: none; opacity: 0.2;"> -->


            <div class="results">
                <span class="aval-event">
                    Available Events
                </span>
                <span class="result-count">
                    10 Results
                </span>
            </div>
        </div>


        <!-- Status Message -->
        <div class="location-status" id="locationStatus"></div>

        <script>

            function openEditPanel() {
                document.getElementById('editSearch').style.display = 'block';
                document.getElementById('search').style.display = 'none';
            }


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

                        // Calculate offset to position marker above bottom box
                        // Bottom box is 50% height, so we offset upward to show marker in visible area
                        const offsetLatitude = -0.003; // Negative moves map down, marker appears higher

                        // Fly to user's location with offset
                        map.flyTo({
                            center: [longitude, latitude + offsetLatitude],
                            zoom: 16,
                            pitch: 45,
                            bearing: 0,
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

            // REPLACING DEFAULT MARKER WITH CUSTOM MARKER
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