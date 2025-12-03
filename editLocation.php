<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>User Location Map</title>
    <meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no">
    <!-- Material Icons -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=search" />


    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap"
        rel="stylesheet">

    <!-- Mapbox -->
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.js"></script>
    <link rel="stylesheet" href="style.css">

</head>
<!-- getCurrentLocation -->

<body onload="openEditPanel();">
    <div class="nav">
        <span class="appName">Spread Rumor</span>
    </div>

    <!-- Contains Map -->
    <div id="map"></div>

    <div class="bottom-box" id="searchBox">
        <!-- Height adjustable bar -->
        <span
            style="background-color: #B9B9B9; width: 126px; height: 4px; border-radius: 4px; margin-bottom: 8px;"></span>

        <div class="search" id="currentSearch" onclick="openEditPanel()" hidden>
            <span class="current-location" id="currentLocation">Swargate, Pune</span>

            <span class="date-time" id="dateTime">
                <span class="on" style="">on</span>
                12th Dec</span>
        </div>

        <!-- Height adjustable bar -->
        <!-- <img src="edit.jpeg" alt="Drag Handle"
            style="margin-top: -5px; margin-bottom: 4px; width: 100%; z-index: 1 !important;  pointer-events: none; opacity: 0.5;"> -->


        <!-- Search area -->
        <!-- style="z-index: 1 !impportant; margin-top: -243px;"  -->
        <div class="editSearch" id="editSearch">
            <div class="editPanel">
                <div class="dateSection" style="border-bottom: 1px solid #262626ff; padding-bottom: 4px;">
                    <label id="dateLabel">Date</label>
                    <input type="date" id="dateInput" name="dateInput" value="">
                </div>
                <!-- <div style="height: 1px;"></div> -->
                <label id="locationLabel">Location</label>
                <div style="margin-top: 18px;">
                    <div class="newSearchCriteria">
                        <div class="searchIcon">
                            <span class="material-symbols-rounded">
                                search
                            </span>
                        </div>
                        <input type="text" class="inputContainer" id="newSearchCriteria" name="newSearchCriteria">

                    </div>
                    <div class="cancelBtn">
                        <button>
                            Cancel
                        </button>
                    </div>
                </div>

                <div class="submitBox">
                    <button type="submit" class="submitBtn" onclick="submitEditLocation()">Scan</button>
                </div>

            </div>

        </div>



        <div class="results">
            <span class="aval-event">
                Available Events
            </span>
            <span class="result-count">
                10 Results
            </span>
        </div>
    </div>


    </div>
    <!-- Search area -->




    </div>
    <!-- 
    <img src="edit.jpeg" alt="Edit Location"
        style="margin-top: 372px; width: 100%; z-index: 1 !important;  pointer-events: none; opacity: 0.3;"> -->



    <!-- Status Message -->
    <div class="location-status" id="locationStatus"></div>
    <script src="mapPage.js"></script>

</body>

</html>