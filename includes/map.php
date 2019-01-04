
<!-- display the arma3 altis map. Need the tiled map on your server and works with leaflet 77

    TODO:
    -   display ingame coordinate grid
    -   display city names and other geolocations
    -   save and read occupied cities from the database and color them on the map
    -   add a map to the squad drop-down, let squads choose their spawn points by a map-click
-->

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.2.0/leaflet.css" integrity="sha256-LcmP8hlMTofQrGU6W2q3tUnDnDZ1QVraxfMkP060ekM=" crossorigin="anonymous" />
 <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.2.0/leaflet.js" integrity="sha256-kdEnCVOWosn3TNsGslxB8ffuKdrZoGQdIdPwh7W1CsE=" crossorigin="anonymous"></script>

<h1>Altis map</h1>
<i>This is a work in progress. Ultimatly, we want to display which side holds which terrain here.
    Also, the squads should be able to select their spawn points online on a map like this.
</i><br><br>
<div id='map' style="width: 60vw; height: 60vh;">
</div>

<script>
    var sat = L.tileLayer('http://localhost:3030/altis/{z}/{x}/{y}.png', {
        maxZoom: 6, 
        minZoom: 1, 
        attribution: 'Arma 3 ingame map, displayed with leaflet', 
        tms: true 
    });
    var map = L.map('map', { 
        layers: [sat] 
        }).setView([0, 0], 1);
    var baseLayers = { 
        "Topography": sat 
    };
    L.control.layers(baseLayers).addTo(map);
    var southWest = map.unproject([0, 16384], map.getMaxZoom()); // 16384 -> size of whole map 
    var northEast = map.unproject([16384, 0], map.getMaxZoom()); 
    map.setMaxBounds(new L.LatLngBounds(southWest, northEast));
    map.setView([ 0,  0], 2);
</script>