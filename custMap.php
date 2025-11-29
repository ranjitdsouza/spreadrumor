<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Add a snow effect to a map</title>
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
    </style>
</head>

<body>
    <div id="map"></div>
    <script>
        mapboxgl.accessToken = 'pk.eyJ1IjoicmFuaml0ZHNvdXphIiwiYSI6ImNtaWdzMXB0ZzAxNnMzZnIxeWh1dWEwaXcifQ.BgmVhDYzaRLB8LgXKNFqJQ';
        window.onload = () => {
            const map = (window.map = new mapboxgl.Map({
                container: 'map',
                zoom: 16.8,
                center: [24.951528, 60.169573],
                pitch: 74,
                bearing: 12.8,
                hash: true,
                style: 'mapbox://styles/mapbox/standard'
            }));

            map.on('style.load', () => {
                map.setConfigProperty('basemap', 'lightPreset', 'dusk');

                // use an expression to transition some properties between zoom levels 11 and 13, preventing visibility when zoomed out
                const zoomBasedReveal = (value) => {
                    return [
                        'interpolate',
                        ['linear'],
                        ['zoom'],
                        11,
                        0.0,
                        13,
                        value
                    ];
                };

                map.setSnow({
                    density: zoomBasedReveal(0.85),
                    intensity: 1.0,
                    'center-thinning': 0.1,
                    direction: [0, 50],
                    opacity: 1.0,
                    color: `#ffffff`,
                    'flake-size': 0.71,
                    vignette: zoomBasedReveal(0.3),
                    'vignette-color': `#ffffff`
                });
            });
        };
    </script>

</body>

</html>