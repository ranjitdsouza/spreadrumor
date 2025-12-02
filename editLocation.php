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
            margin-bottom: 8px;

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
            width: 83%;
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


        /* Edit Container */
        .editSearch {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: 83%;
            height: 192px;
            padding: 24px;
            border-radius: 24px;
            background: rgba(228, 228, 228, 0.15);
            box-shadow: -4px -4px 8px 0 #FFF, 4px 4px 19px 0 rgba(255, 42, 0, 0.20);
            color: #606060;
            /* border: 1px solid orange; */
            font-family: "Open Sans";
            /* font-size: 14px; */
            /* font-weight: 600; */

            margin: auto;
            margin-top: -5px;
            display: none;
        }

        @keyframes slideDownExpand {
            from {

                opacity: 0;
                max-height: 0;
                margin-top: 61px;
            }

            to {

                opacity: 1;
                max-height: 300px;
            }
        }

        .editSearch {
            animation: slideDownExpand 0.4s ease-out forwards;
            transform-origin: top;
        }

        .editSearch.hide {
            animation: slideDownCollapse 0.3s ease-in forwards;
        }

        @keyframes slideDownCollapse {
            from {
                transform: translateY(0);
                opacity: 1;
                max-height: 300px;
            }

            to {
                transform: translateY(-100%);
                opacity: 0;
                max-height: 0;
            }
        }

        .editPanel {
            width: 100%;
            height: 100%;
            padding: 0;
        }

        .editPanel #dateLabel {
            font-size: 14px;
            font-weight: 600;
            color: #606060;
            left: 0;
            float: left;
            text-align: left;
            margin-top: 8px;
        }

        .dateSection {
            position: relative;
            width: 100%;
            height: 40px;

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

        .newSearchCriteria {
            display: flex;
            flex-direction: row;
            justify-content: flex-start;
            width: 75%;
            height: 36px;
            margin-top: 8px;
            padding: 4px 8px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.99);
            color: #606060;
            font-family: "Open Sans";
            font-size: 14px;
            font-weight: 600;

        }

        .searchIcon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            margin-right: 8px;
            background-color: transparent;
            margin-top: 5px;
        }


        .inputContainer {
            margin-left: 4px;
            width: 100%;
            right: 0 !important;
            float: right;
            height: 30px;
            background-color: transparent;
            border: none;
            padding-top: 3px;
            font-size: 16px;
        }

        .inputContainer:focus {
            outline: none;
        }

        .materail-symbols-rounded span {
            font-size: 20px;
            color: #606060;
            left: 0;
            text-align: center;
        }

        .cancelBtn {
            margin-top: ;
            right: 0;
            float: right;
            margin-top: -40px;
            height: 36px;
            margin-left: 94px;
            background-color: transparent;
        }

        .cancelBtn button {
            width: 100%;
            height: 100%;
            border-radius: 8px;
            border: none;
            color: #DA4949;
            background-color: transparent;
            font-size: 16px;
            font-weight: 400;
            cursor: pointer;
        }

        .submitBox {
            width: 100%;
            margin-top: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .submitBtn {
            width: 50%;
            height: 40px;
            border-radius: 20px;
            border: none;
            outline: 3px solid #F6D9D5;
            color: black;
            background: linear-gradient(180deg, #f0f0f0 0%, #DBDBDB 100%);
            box-shadow: rgb(204, 219, 232) 3px 3px 6px 0px inset, rgba(255, 255, 255, 0.5) -3px -3px 6px 1px inset;
            font-size: 16px;
            font-weight: 400;
            cursor: pointer;
        }
    </style>
</head>
<!-- getCurrentLocation -->

<body onload="openEditPanel();">
    <div class="nav">
        <span class="appName">Spread Rumor</span>

    </div>
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

        <!-- Search area -->
        <div class="editSearch" id="editSearch">
            <div class="editPanel">
                <div class="dateSection" style="border-bottom: none">
                    <label id="dateLabel">Date</label>
                    <input type="date" id="dateInput" name="dateInput" value="">
                </div>
                <br>
                <label margin>Location</label>
                <div>
                    <div class="newSearchCriteria">
                        <div class="searchIcon">
                            <span class="material-symbols-rounded">
                                search
                            </span>
                        </div>
                        <input type="text" class="inputContainer" id="newSearchCriteria" name="newSearchCriteria"
                            placeholder="Type here...">

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