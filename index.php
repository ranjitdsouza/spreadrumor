<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>User Location Map</title>
    <meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no">
    <!-- Material Icons -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=search" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">


    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" rel="stylesheet" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap"
        rel="stylesheet">

    <!-- Mapbox -->
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.js"></script>

    <!-- UI Style - SPREAD -->
    <link rel="stylesheet" href="style.css">


</head>
<!-- getCurrentLocation -->

<body onload="openEditPanel();">
    <div class="nav">
        <span class="appName">
            <img src="res/logo.svg" alt="Logo" class="logo">
        </span>
    </div>

    <!-- ============================MAP============================= -->

    <div id="map"></div>

    <!-- ============================MAP============================= -->

    <div class="bottom-box" id="searchBox">
        <span class="adjBar"></span>

        <div class="search" id="currentSearch" onclick="openEditPanel()">
            <span class="current-location" id="currentLocation">Finding location...</span>
            <span class="date-time" id="dateTime"><span class="on">on</span> 12th Dec</span>
        </div>

        <div class="editSearch" id="editSearch">
            <div class="editPanel">
                <div class="dateSection">
                    <label id="dateLabel" for="dateInput">Date</label>
                    <input type="date" id="dateInput" name="dateInput">
                </div>

                <label class="locationLabel" for="newSearchCriteria">Location</label>
                <br><br>
                <div class="newSearchCriteria">
                    <div class="searchIcon">
                        <span class="material-symbols-rounded">search</span>
                    </div>
                    <input type="text" class="inputContainer" id="newSearchCriteria"
                        placeholder="Search for a place...">
                </div>

                <div class="cancelBtn">
                    <button onclick="closeEditPanel()">Cancel</button>
                </div>

                <div class="submitBox">
                    <button type="submit" class="submitBtn" onclick="submitEditLocation()">Scan</button>
                </div>
            </div>
        </div>

        <!-- <div class="results">
            <span class="aval-event">Available Events</span>
            <span class="result-count">10 Results</span>
        </div> -->
    </div>

    <div class="location-status" id="locationStatus"></div>



    <!-- Status Message -->
    <div class="location-status" id="locationStatus"></div>
    <script src="mapPage.js"></script>




</body>

</html>