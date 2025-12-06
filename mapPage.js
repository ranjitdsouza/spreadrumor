// mapPage.js - Location Search with Autocomplete

mapboxgl.accessToken =
  "pk.eyJ1IjoicmFuaml0ZHNvdXphIiwiYSI6ImNtaWdzMXB0ZzAxNnMzZnIxeWh1dWEwaXcifQ.BgmVhDYzaRLB8LgXKNFqJQ";

let map;
let userMarker; // User's actual GPS location marker (blue, stays fixed)
let searchMarker; // Selected search location marker (red, for searched places)
let watchId;
let searchTimeout;
let userLocation = null; // Store user's actual location

// Initialize map
window.onload = () => {
  map = new mapboxgl.Map({
    container: "map",
    zoom: 14,
    center: [73.8567, 18.5204],
    pitch: 45,
    bearing: 0,
    hash: true,
    style: "mapbox://styles/ranjitdsouza/cmijtzilg00lr01qwf2ri04jb",
  });

  map.on("load", function () {
    console.log("Map loaded successfully");
    setTimeout(() => {
      getUserLocation();
    }, 1000);
  });

  setCurrentDate();
  setupSearchAutocomplete();
};

// Set current date
function setCurrentDate() {
  const now = new Date();
  const day = now.getDate();
  const month = now.toLocaleString("en-US", { month: "short" });

  let suffix = "th";
  if (day === 1 || day === 21 || day === 31) suffix = "st";
  else if (day === 2 || day === 22) suffix = "nd";
  else if (day === 3 || day === 23) suffix = "rd";

  document.getElementById(
    "dateTime"
  ).innerHTML = `<span class="on">on</span> ${day}${suffix} ${month}`;

  const dateInput = document.getElementById("dateInput");
  const today = now.toISOString().split("T")[0];
  dateInput.value = today;
}

// Get user's current location and LOCK it
function getUserLocation() {
  if (!navigator.geolocation) {
    showStatus("Geolocation is not supported", "error");
    return;
  }

  showStatus("Getting your location...", "loading");

  const options = {
    enableHighAccuracy: true,
    timeout: 10000,
    maximumAge: 0,
  };

  navigator.geolocation.getCurrentPosition(
    function (position) {
      const longitude = position.coords.longitude;
      const latitude = position.coords.latitude;
      const accuracy = position.coords.accuracy;

      console.log("User location found:", latitude, longitude);
      showStatus(`Location found (±${Math.round(accuracy)}m)`, "success");

      // Store user's actual location
      userLocation = { longitude, latitude };

      // Update location text
      updateLocationText(latitude, longitude);

      // Create FIXED user location marker (blue)
      createUserMarker(longitude, latitude);

      // Fly to user's location
      const offsetLatitude = -0.003;
      map.flyTo({
        center: [longitude, latitude + offsetLatitude],
        zoom: 16,
        pitch: 45,
        bearing: 0,
        essential: true,
        duration: 2000,
      });

      // Start tracking to keep user marker updated
      startTracking();
    },
    function (error) {
      let errorMessage = "";
      switch (error.code) {
        case error.PERMISSION_DENIED:
          errorMessage = "Location access denied";
          break;
        case error.POSITION_UNAVAILABLE:
          errorMessage = "Location unavailable";
          break;
        case error.TIMEOUT:
          errorMessage = "Location timeout";
          break;
        default:
          errorMessage = "Location error";
      }
      console.error("Geolocation error:", error);
      showStatus(errorMessage, "error");
    },
    options
  );
}

// Update location text with reverse geocoding
function updateLocationText(latitude, longitude) {
  const locationElement = document.getElementById("currentLocation");
  locationElement.textContent = "Locating...";

  fetch(
    `https://api.mapbox.com/geocoding/v5/mapbox.places/${longitude},${latitude}.json?types=neighborhood,locality,place,poi&access_token=${mapboxgl.accessToken}`
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.features && data.features.length > 0) {
        let locationName = "";
        const priorities = ["neighborhood", "locality", "place", "poi"];

        for (let priority of priorities) {
          const feature = data.features.find((f) =>
            f.place_type.includes(priority)
          );
          if (feature) {
            locationName = feature.text;
            const context = feature.context || [];
            const city = context.find((c) => c.id.startsWith("place"));
            if (city && city.text !== feature.text) {
              locationName += ", " + city.text;
            }
            break;
          }
        }

        if (!locationName && data.features[0]) {
          locationName = data.features[0].place_name
            .split(",")
            .slice(0, 2)
            .join(",");
        }

        locationElement.textContent = locationName || "Unknown Location";
      } else {
        locationElement.textContent = "Unknown Location";
      }
    })
    .catch((error) => {
      console.error("Geocoding error:", error);
      locationElement.textContent = "Location Error";
    });
}

// Create BLUE marker for user's actual GPS location (stays fixed)
function createUserMarker(longitude, latitude) {
  if (userMarker) {
    userMarker.remove();
  }

  const el = document.createElement("div");
  el.className = "user-marker"; // Blue pulsing marker

  userMarker = new mapboxgl.Marker({
    element: el,
    anchor: "center",
  })
    .setLngLat([longitude, latitude])
    .addTo(map);

  console.log("User marker created at:", longitude, latitude);
}

// Create RED marker for searched/selected location
function createSearchMarker(longitude, latitude) {
  if (searchMarker) {
    searchMarker.remove();
  }

  const el = document.createElement("div");
  el.className = "search-marker"; // Red marker for search results

  searchMarker = new mapboxgl.Marker({
    element: el,
    anchor: "center",
  })
    .setLngLat([longitude, latitude])
    .addTo(map);

  console.log("Search marker created at:", longitude, latitude);
}

// Setup search autocomplete
function setupSearchAutocomplete() {
  const searchInput = document.getElementById("newSearchCriteria");

  let suggestionsContainer = document.getElementById("searchSuggestions");
  if (!suggestionsContainer) {
    suggestionsContainer = document.createElement("div");
    suggestionsContainer.id = "searchSuggestions";
    suggestionsContainer.className = "search-suggestions";
    searchInput.parentElement.appendChild(suggestionsContainer);
  }

  searchInput.addEventListener("input", function (e) {
    const query = e.target.value.trim();
    clearTimeout(searchTimeout);

    if (query.length < 2) {
      hideSuggestions();
      return;
    }

    // Show suggestions box below the textbox
    suggestionsContainer.style.position = "absolute";
    suggestionsContainer.style.left = searchInput.offsetLeft + "px";
    suggestionsContainer.style.top =
      searchInput.offsetTop + searchInput.offsetHeight + "px";
    suggestionsContainer.style.width = searchInput.offsetWidth + "px";

    searchTimeout = setTimeout(() => {
      searchPlaces(query);
    }, 300);
  });

  // Hide suggestions when textbox loses focus (blur)
  searchInput.addEventListener("blur", function () {
    setTimeout(hideSuggestions, 120); // Delay to allow click on suggestion
  });

  // Show suggestions when textbox is focused and has value
  searchInput.addEventListener("focus", function () {
    if (
      searchInput.value.trim().length >= 2 &&
      suggestionsContainer.innerHTML.trim() !== ""
    ) {
      suggestionsContainer.style.display = "block";
    }
  });

  // Hide suggestions if clicking outside textbox or suggestion box
  document.addEventListener("mousedown", function (e) {
    if (e.target !== searchInput && !suggestionsContainer.contains(e.target)) {
      hideSuggestions();
    }
  });
}

// Search for places using Mapbox Geocoding API
function searchPlaces(query) {
  let proximity = "";
  if (userLocation) {
    proximity = `&proximity=${userLocation.longitude},${userLocation.latitude}`;
  }

  const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(
    query
  )}.json?access_token=${
    mapboxgl.accessToken
  }&limit=5&types=neighborhood,locality,place,poi,address${proximity}`;

  console.log("Searching:", query);

  fetch(url)
    .then((response) => response.json())
    .then((data) => {
      console.log("Results:", data.features);
      displaySuggestions(data.features);
    })
    .catch((error) => {
      console.error("Search error:", error);
    });
}

// Display search suggestions list
function displaySuggestions(features) {
  const suggestionsContainer = document.getElementById("searchSuggestions");

  if (!features || features.length === 0) {
    console.log("No results found");
    hideSuggestions();
    return;
  }

  console.log("Displaying", features.length, "suggestions");
  suggestionsContainer.innerHTML = "";

  features.forEach((feature) => {
    const item = document.createElement("div");
    item.className = "suggestion-item";

    const icon = document.createElement("span");
    icon.className = "suggestion-icon material-symbols-rounded";

    if (feature.place_type.includes("poi")) {
      icon.textContent = "location_on";
    } else if (feature.place_type.includes("address")) {
      icon.textContent = "home";
    } else {
      icon.textContent = "place";
    }

    const text = document.createElement("div");
    text.className = "suggestion-text";

    const name = document.createElement("div");
    name.className = "suggestion-name";
    name.textContent = feature.text;

    const address = document.createElement("div");
    address.className = "suggestion-address";
    address.textContent = feature.place_name.replace(feature.text + ", ", "");

    text.appendChild(name);
    text.appendChild(address);
    item.appendChild(icon);
    item.appendChild(text);

    item.addEventListener("click", () => {
      selectPlace(feature);
    });

    suggestionsContainer.appendChild(item);
  });

  suggestionsContainer.style.display = "block";
  console.log("Suggestions displayed");
}

// Select a place from suggestions
function selectPlace(feature) {
  const searchInput = document.getElementById("newSearchCriteria");
  const locationElement = document.getElementById("currentLocation");

  searchInput.value = feature.place_name;

  let displayName = feature.text;
  if (feature.context && feature.context.length > 0) {
    const city = feature.context.find((c) => c.id.startsWith("place"));
    if (city && city.text !== feature.text) {
      displayName += ", " + city.text;
    }
  }
  locationElement.textContent = displayName;

  const [longitude, latitude] = feature.center;

  // Create RED marker for the searched location
  createSearchMarker(longitude, latitude);

  // Fly to the searched location
  const offsetLatitude = -0.003;
  map.flyTo({
    center: [longitude, latitude + offsetLatitude],
    zoom: 16,
    pitch: 45,
    bearing: 0,
    essential: true,
    duration: 2000,
  });

  hideSuggestions();
  closeEditPanel();

  console.log(
    "Search location selected - User's blue marker still visible at original location"
  );
}

// Hide suggestions
function hideSuggestions() {
  const suggestionsContainer = document.getElementById("searchSuggestions");
  if (suggestionsContainer) {
    suggestionsContainer.style.display = "none";
  }
}

// Start tracking user location (updates blue marker)
function startTracking() {
  if (watchId) {
    navigator.geolocation.clearWatch(watchId);
  }

  const options = {
    enableHighAccuracy: true,
    timeout: 5000,
    maximumAge: 0,
  };

  watchId = navigator.geolocation.watchPosition(
    function (position) {
      const longitude = position.coords.longitude;
      const latitude = position.coords.latitude;

      // Update stored user location
      userLocation = { longitude, latitude };

      // Update blue user marker position
      if (userMarker) {
        userMarker.setLngLat([longitude, latitude]);
      }

      console.log("User location updated:", latitude, longitude);
    },
    function (error) {
      console.error("Tracking error:", error);
    },
    options
  );
}

// Show status message
function showStatus(message, type = "info") {
  const statusEl = document.getElementById("locationStatus");
  if (!statusEl) return;

  statusEl.textContent = message;
  statusEl.className = "location-status show " + type;

  if (type === "success") {
    setTimeout(() => {
      statusEl.classList.remove("show");
    }, 3000);
  }
}

// Clear search bar
function clearSearchBar() {
  document.getElementById("newSearchCriteria").value = "";
  hideSuggestions();
}

// Submit edited location
function submitEditLocation() {
  const searchValue = document.getElementById("newSearchCriteria").value.trim();

  if (searchValue) {
    searchPlaces(searchValue);
  }
}

// Open edit panel
function openEditPanel() {
  const editSearch = document.getElementById("editSearch");
  const currentSearch = document.getElementById("currentSearch");

  if (editSearch) {
    editSearch.style.display = "block";
  }
  if (currentSearch) {
    currentSearch.style.display = "none";
  }
}

// Close edit panel
function closeEditPanel() {
  const editSearch = document.getElementById("editSearch");
  const currentSearch = document.getElementById("currentSearch");

  if (editSearch) {
    editSearch.style.display = "none";
  }
  if (currentSearch) {
    currentSearch.style.display = "flex";
  }

  clearSearchBar();
}

// Return to user's current GPS location
function returnToMyLocation() {
  if (userLocation) {
    const offsetLatitude = -0.003;
    map.flyTo({
      center: [userLocation.longitude, userLocation.latitude + offsetLatitude],
      zoom: 16,
      pitch: 45,
      bearing: 0,
      essential: true,
      duration: 2000,
    });
  }
}

// Stop tracking when page unloads
window.addEventListener("beforeunload", function () {
  if (watchId) {
    navigator.geolocation.clearWatch(watchId);
  }
});
