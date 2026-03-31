@extends('layouts.app')
@section('title', 'Map - Pangasinan')

@section('content')

<!-- for the map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>


<style>
/* MAP CONTAINER */
#map-container {
    position: relative;
    width: 100%;
    height: 100vh;
}

/* MAP */
#map {
    width: 100%;
    height: 100%;
}
/* TOGGLE BUTTON */
#toggleBtn {
    position: absolute;
    top: 20px;
    left: 20px;
    z-index: 1000;
    padding: 10px 15px;
    background: #0b5e2c;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 5px;
}
/* LEGEND */
#legend {
    position: absolute;
    top: 20px;
    right: 20px;
    z-index: 1000;
    background: rgba(255,255,255,0);
    backdrop-filter: blur(1px);
    padding: 12px 15px;
    border-radius: 8px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.2);
    font-size: 14px;
}

.legend-item {
    display: flex;
    align-items: center;
    margin-bottom: 6px;
}

.legend-color {
    width: 18px;
    height: 18px;
    margin-right: 8px;
    border-radius: 3px;
    border: 1px solid #333;
}

/* TOGGLE CONTAINER */
#map-toggle {
    position: absolute;
    top: 20px;
    left: 20px;
    z-index: 1000;
    background: rgba(255,255,255,0.9);
    backdrop-filter: blur(6px);
    padding: 8px 12px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
}

/* SWITCH */
.switch {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 20px;
}

.switch input {
    display: none;
}

/* SLIDER */
.slider {
    position: absolute;
    cursor: pointer;
    background-color: #ccc;
    border-radius: 20px;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    transition: 0.3s;
}

.slider:before {
    content: "";
    position: absolute;
    height: 14px;
    width: 14px;
    left: 3px;
    bottom: 3px;
    background: white;
    border-radius: 50%;
    transition: 0.3s;
}

input:checked + .slider {
    background-color: #0b5e2c;
}

input:checked + .slider:before {
    transform: translateX(20px);
}

#miniMap {
    position: absolute;
    bottom: 100px;
    right: 20px;
    width: 250px;
    height: 180px;
    z-index: 1000;
    border-radius: 10px;
    overflow: hidden;
    border: 2px solid white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    display: none;
}
</style>

<div id="map-container">

    <div id="map-toggle">
    <span>🗺 Map</span>

    <label class="switch">
        <input type="checkbox" id="toggleSwitch">
        <span class="slider"></span>
    </label>

    <span>🛰 Satellite</span>
</div>

    <!-- MAP -->
    <div id="map"></div>

    <!-- LEGEND -->
    <div id="legend">
        <strong>Legend</strong>

        <!-- <div class="legend-item">
            <div class="legend-color" style="background: blue;"></div>
            Canals
        </div>

        <div class="legend-item">
            <div class="legend-color" style="background: yellow;"></div>
            Dam
        </div> -->

        <div class="legend-item">
            <div class="legend-color" style="background: green;"></div>
            Irrigated
        </div>

        <div class="legend-item">
            <div class="legend-color" style="background: red;"></div>
            Not Irrigated
        </div>

          <div class="legend-item">
            <div class="legend-color" style="background: yellow;"></div>
            Potential Irrigable Area
        </div>

        <div class="legend-item">
            <div class="legend-color" style="background: white;"></div>
            Service Boundary
        </div>

         <div class="legend-item">
            <div class="legend-color" style="background: blue;"></div>
            Land Boundary
        </div>
   </div>
<div id="miniMap"></div>
</div>

<!-- the map -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
let map = L.map('map').setView([15.8949, 120.2863], 9);


let normalLayer = L.tileLayer(
    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    { maxZoom: 19 }
).addTo(map);

let satelliteLayer = L.tileLayer(
    'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
    { maxZoom: 19 }
);


let labelLayer = L.tileLayer(
    'https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}',
    { maxZoom: 19 }
);

let isSatellite = false;

const toggle = document.getElementById("toggleSwitch");

toggle.addEventListener("change", () => {

    if (toggle.checked) {
        map.removeLayer(normalLayer);
        map.addLayer(satelliteLayer);
        map.addLayer(labelLayer);
    } else {
        map.removeLayer(satelliteLayer);
        map.removeLayer(labelLayer);
        map.addLayer(normalLayer);
    }

});

// highlight of pangasinan
fetch('/maps/PANGASINAN.geojson')
.then(res => res.json())
.then(data => {

    let geoLayer = L.geoJSON(data, {

        style: function(feature) {
            return {
                color: "white",
                weight: 1,
                fillColor: feature.properties.irrigated ? "green" : "#242525",
                fillOpacity: 0.6
            };
        },

        onEachFeature: function(feature, layer) {

            let name = feature.properties.MUNICIPALI || "Unknown";

layer.on('click', function() {

    layer.setStyle({ fillColor: "red" });

    showMiniMap(feature, layer);

});

            layer.bindPopup("<b>" + name + "</b>");
        }

    }).addTo(map);

});

let miniMap = L.map('miniMap', {
    attributionControl: false,
    zoomControl: false
});

let miniLayer = L.tileLayer(
    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
);
miniLayer.addTo(miniMap);

let miniGeoLayer;

function showMiniMap(feature, layer) {

    document.getElementById("miniMap").style.display = "block";

    if (miniGeoLayer) {
        miniMap.removeLayer(miniGeoLayer);
    }

    miniGeoLayer = L.geoJSON(feature, {
        style: {
            color: "red",
            weight: 2,

            fillOpacity: 0.5
        }
    }).addTo(miniMap);

    miniMap.fitBounds(miniGeoLayer.getBounds());
}
</script>

@endsection
