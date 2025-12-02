
function openEditPanel() {
    // const editPanel = document.getElementById('editSearchPanel');
    // editPanel.style.display = 'block';
    console.log('Edit panel opened');
    document.getElementById('currentSearch').style.display = 'none';
    document.getElementById('editSearch').style.display = 'block';
}
openEditPanel();


mapboxgl.accessToken = 'pk.eyJ1IjoicmFuaml0ZHNvdXphIiwiYSI6ImNtaWdzMXB0ZzAxNnMzZnIxeWh1dWEwaXcifQ.BgmVhDYzaRLB8LgXKNFqJQ';

let map;
let userMarker;
let watchId;

window.onload = () => {
    // Initialize map with default location
    map = new mapboxgl.Map({
container: 'map',
zoom: 12,
center: [24.951528, 60.169573],
pitch: 14,
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




