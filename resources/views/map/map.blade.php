@extends('layouts.app')
@section('title', 'Map - Pangasinan')

@section('content')

<!-- for the map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>


<style>
html, body {
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100%;
    overflow-x: hidden;
}

#map-container {
    position: relative;
    width: 100%;
    height: 100vh; /* Use 100vh to fill the entire screen height */
    overflow: hidden; /* This prevents the "scroll to the right" issue */
    display: flex;
    flex-direction: column;
}

.municipality-label {
    font-size: 13px;
    font-weight: 600;
    color: #222;

    padding: 2px 6px;
    border-radius: 4px;
    pointer-events: none;
}

/* MAP */
#map {
    flex-grow: 1;
    width: 100%;
    height: 100%;
}

.province-label {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    color: #ffffff;
    font-weight: 900;
    text-shadow: 2px 2px 4px #000;
    font-size: 28px; /* Larger than municipality labels */
    pointer-events: none;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.municipality-label-base {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    color: #ffffff;
    font-weight: 600;
    text-shadow: 1px 1px 3px #000;
    font-size: 10px; /* Small and clean */
    pointer-events: none;
    text-transform: uppercase;
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

/* LAYER CONTROLS CONTAINER */
#layer-controls {
    position: absolute;
    top: 70px;
    left: 20px;
    z-index: 1000;
    padding: 15px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    min-width: 220px;
    border: 1px solid rgba(0,0,0,0.1);
}

/* INDIVIDUAL ITEM WRAPPERS */
#layer-controls {
    position: absolute;
    top: 75px;
    left: 20px;
    z-index: 1000;
    width: auto;
    min-width: 160px;
    padding: 8px 10px;

    /* 2. Make it transparent */
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(8px);

    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.layer-check {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 4px 6px;
    margin-bottom: 4px;
    border-radius: 6px;
    cursor: pointer;
}

.layer-check:last-child {
    margin-bottom: 0;
}

.legend-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}

.layer-check input {
    margin-right: 10px;
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: #0b5e2c;
}

.layer-check span {
    font-size: 11px;
    font-weight: 500;
    color: #1a1a1a;
    white-space: nowrap;
}

.layer-check input[type="checkbox"] {
    width: 14px;
    height: 14px;
    margin: 0;
}

#map-status {
    position: absolute;
    left: 20px;
    bottom: 20px;
    z-index: 1000;
    max-width: 360px;
    background: rgba(255,255,255,0.94);
    padding: 10px 12px;
    border-radius: 10px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.18);
    font-size: 13px;
    line-height: 1.4;
}

#map-status.error {
    border-left: 4px solid #c62828;
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
.view-btn {
    background: none;
    border: none;
    padding: 0;
    color: #ebeef2;
    cursor: pointer;
    font-size: 14px;
    margin-top: 20%;
    text-decoration: underline;
    text-shadow:
        -1px -1px 0 black,
         1px -1px 0 black,
        -1px  1px 0 black,
         1px  1px 0 black;
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
    left: 20px;
    width: 250px;
    height: 180px;
    z-index: 1000;
    border-radius: 10px;
    overflow: hidden;
    border: 2px solid white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    display: none;
}

.upload-status {
    margin-bottom: 10px;
    padding: 10px;
    border-radius: 6px;
    display: none;
    font-weight: bold;
}

.upload-success {
    background: #4caf50;
    color: white;
    animation: fadeIn 0.5s ease;
}

.upload-error {
    background: #f44336;
    color: white;
    animation: fadeIn 0.5s ease;
}

.upload-loading {
    background: #2196f3;
    color: white;
}

/* Sidebar Container */
#admin-sidebar {
    position: absolute;
    top: 0;
    right: 0;
    width: 340px;
    height: 100%;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(12px);
    z-index: 2000;
    box-shadow: -10px 0 30px rgba(0,0,0,0.1);
    transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
    border-left: 1px solid rgba(0,0,0,0.05);
}

.sidebar-closed {
    transform: translateX(115%); /* Hide it completely including shadows */
}

/* Header Refinement */
.sidebar-header {
    background: #181818;
    color: white;
    padding: 24px 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Sidebar Content Layout */
.sidebar-content {
    padding: 25px 20px;
    overflow-y: auto;
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.panel-header h4 {
    margin: 0;
    color: #333;
    font-size: 15px;
    font-weight: 700;
}

.files-link {
    text-decoration: none;
    color: #0b5e2c;
    font-size: 12px;
    font-weight: bold;
    background: rgba(11, 94, 44, 0.1);
    padding: 5px 12px;
    border-radius: 20px;
}

/* Form Styling */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: #666;
    text-transform: uppercase;
    margin-bottom: 8px;
    letter-spacing: 0.5px;
}

/* Styled Select */
select[name="category"] {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #f9f9f9;
    font-size: 14px;
    color: #333;
    outline: none;
    transition: border-color 0.3s;
}

select[name="category"]:focus {
    border-color: #0b5e2c;
}

/* File Upload Boxes - The "Big Fix" */
.upload-box {
    border: 2px dashed #cbd5e0;
    padding: 20px 15px;
    text-align: center;
    border-radius: 12px;
    background: #fdfdfd;
    transition: all 0.2s ease;
    cursor: pointer;
}

.upload-box:hover {
    border-color: #0b5e2c;
    background: rgba(11, 94, 44, 0.03);
}

.upload-box i {
    font-size: 20px;
    color: #0b5e2c;
    margin-bottom: 8px;
    display: block;
}

.upload-box strong {
    display: block;
    font-size: 13px;
    color: #2d3748;
}

.upload-box span {
    font-size: 11px;
    color: #718096;
}

/* Submit Button */
.submit-btn {
    width: 100%;
    padding: 14px;
    background: #0b5e2c;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    box-shadow: 0 4px 6px rgba(11, 94, 44, 0.2);
    transition: 0.3s;
    margin-top: 10px;
}

.submit-btn:hover {
    background: #084a22;
    transform: translateY(-1px);
}

/* Floating Admin Trigger Button */
#admin-toggle-btn {
    position: fixed; /* Changed from absolute to fixed */
    bottom: 30px;
    right: 30px;
    z-index: 1500; /* Higher than the map, lower than the sidebar */
    background: #0b5e2c;
    color: white;
    border: none;
    padding: 14px 24px;
    border-radius: 50px; /* Pill shape */
    font-weight: bold;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

#admin-toggle-btn:hover {
    background: #084a22;
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(0,0,0,0.4);
}

/* Hide the floating button when the sidebar is open (optional) */
#admin-sidebar:not(.sidebar-closed) ~ #admin-toggle-btn {
    opacity: 0;
    pointer-events: none;
}



.loader {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 3px solid white;
    border-top: 3px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-right: 8px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}

.leaflet-interactive {
    filter: drop-shadow(3px 4px 4px rgba(0,0,0,0.5));
    transition: all 0.2s ease;
}

/* when hovered = raised */
.leaflet-interactive:hover {
    transform: translateY(-3px) scale(1.02);
}
</style>
<meta name="csrf-token" content="{{ csrf_token() }}">
<form id="uploadForm" enctype="multipart/form-data">
<meta name="csrf-token" content="{{ csrf_token() }}">

<div id="uploadStatus" class="upload-status" style="display:none;"></div>

<form id="uploadForm">
    @csrf

    <select name="category" required>
        <option value="">Select Category</option>
        <option value="irrigated">Irrigated Area</option>
        <option value="land_boundary">Land Boundary</option>
        <option value="potential">Potential Area</option>
    </select>

    <label>Upload Files:</label>
    <input type="file" id="fileInput" multiple>


    <label>Upload Folder:</label>
    <input type="file" id="folderInput" webkitdirectory directory multiple>


    <button type="submit">Upload</button>
</form>
<meta name="csrf-token" content="{{ csrf_token() }}">

<a href="map/files">files</a>

<div id="map-container">
<div id="infoPanel" class="info-panel">
    <div class="info-header">
        <h2 id="infoTitle">Municipality</h2>
        <button onclick="closePanel()">✖</button>
    </div>

    <div id="infoContent" class="info-content">
<canvas id="landChart" class="chart-small"></canvas>
        <!-- LEGEND -->
        <div id="legendContainer"></div>

        <!-- DATA -->
            <div id="extraData"></div>
          <button class="view-btn" onclick="openDetail()" style="">View Full Details</button>
    </div>


</div>
<!-- DETAIL POPUP -->
<div id="detailPanel" class="detail-panel">
    <div class="detail-header">
        <span id="municipalityName">Details</span>
        <button onclick="closeDetail()">✖</button>
    </div>

    <div id="detailContent" class="detail-content"></div>
</div>
    <div id="map-toggle">
    <span>🗺 Map</span>

    <label class="switch">
        <input type="checkbox" id="toggleSwitch">
        <span class="slider"></span>
    </label>

    <span>🛰 Satellite</span>
</div>

<div id="map-status" style="padding: 5px; font-size: 12px; font-weight: bold;"></div>

    <div id="layer-controls">
    <!-- Irrigated Area -->
    <label class="layer-check">
        <div class="legend-indicator" style="background-color: #1b5e20;"></div>
        <input type="checkbox" id="toggleIrrigated" {{ empty($overlayGroups['Irrigated Area']['files']) ? 'disabled' : '' }}>
        <span>Irrigated Area</span>
    </label>

    <!-- Land Boundary -->
    <label class="layer-check">
        <div class="legend-indicator" style="background-color: #0d47a1;"></div>
        <input type="checkbox" id="toggleLandBoundary" {{ empty($overlayGroups['Pangasinan Land Boundary']['files']) ? 'disabled' : '' }}>
        <span>Land Boundary</span>
    </label>

    <!-- Potential Irrigable Area -->
    <label class="layer-check">
        <div class="legend-indicator" style="background-color: #fbc02d;"></div>
        <input type="checkbox" id="togglePotential" {{ empty($overlayGroups['Potential Irrigable Area']['files']) ? 'disabled' : '' }}>
        <span>Potential Irrigable Area</span>
    </label>
</div>

    <!-- MAP -->
    <div id="map"></div>


<div id="miniMap"></div>

<!-- Floating Admin Trigger Button -->
<button id="admin-toggle-btn" title="Open Admin Panel">
    <i class="fas fa-cog"></i> Upload
</button>

<!-- Side Admin Panel -->
<div id="admin-sidebar" class="sidebar-closed">
    <div class="sidebar-header">
        <h3><i class="fas fa-tools"></i> Admin Panel</h3>
        <button id="close-sidebar">&times;</button>
    </div>

    <div class="sidebar-content">
        <div class="panel-section">
            <div class="panel-header">
                <h4>Data Management</h4>
                <a href="{{ url('map/files') }}" class="files-link">Manage Files</a>
            </div>

            <form id="uploadForm">
                @csrf
                <div class="form-group">
                    <label>Layer Category</label>
                    <select name="category" required>
                        <option value="">-- Choose Category --</option>
                        <option value="Irrigated Area">Irrigated Area</option>
                        <option value="Pangasinan Land Boundary">Pangasinan Land Boundary</option>
                        <option value="Potential Irrigable Area">Potential Irrigable Area</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Upload Source</label>
                    <div class="upload-stack">
                        <div class="upload-box" onclick="document.getElementById('fileInput').click()">
                            <i class="fas fa-file-upload"></i>
                            <strong>Select Files</strong>
                            <span>.shp, .kml, .json, .kmz</span>
                            <input type="file" id="fileInput" multiple style="display:none;">
                        </div>

                        <div class="upload-box" onclick="document.getElementById('folderInput').click()">
                            <i class="fas fa-folder-open"></i>
                            <strong>Upload Folder</strong>
                            <span>Select map directory</span>
                            <input type="file" id="folderInput" webkitdirectory directory multiple style="display:none;">
                        </div>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-cloud-upload-alt"></i> Upload Data
                </button>
            </form>
            <div id="uploadStatus" class="upload-status"></div>
        </div>
    </div>
</div>

</div>

<div id="uploadStatus" class="upload-status" style="display:none;"></div>


<!-- the map -->
 <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@tmcw/togeojson@5.8.1/dist/togeojson.umd.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/shpjs@6.2.0/dist/shp.min.js" crossorigin="anonymous"></script>
<script>
const overlayGroups = JSON.parse('{!! json_encode($overlayGroups) !!}');
const appBaseUrl = "{{ rtrim(request()->getBaseUrl(), '/') }}";

function buildAppUrl(path) {
    if (!path) {
        return null;
    }

    if (/^https?:\/\//i.test(path)) {
        return path;
    }

    const normalizedPath = String(path).replace(/^\/+/, '');
    return appBaseUrl ? `${appBaseUrl}/${normalizedPath}` : `/${normalizedPath}`;
}

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

const statusBox = document.getElementById('map-status');
const toggle = document.getElementById('toggleSwitch');

const overlayToggles = {
    'Irrigated Area': document.getElementById('toggleIrrigated'),
    'Pangasinan Land Boundary': document.getElementById('toggleLandBoundary'),
    'Potential Irrigable Area': document.getElementById('togglePotential')
};

const overlayStyles = {
    'Irrigated Area': {
        color: '#1b5e20',
        weight: 2,
        fillColor: '#43a047',
        fillOpacity: 0.9
    },
    'Pangasinan Land Boundary': {
        color: '#0d47a1', // Dark blue border
        weight: 2,
        fillColor: '#2196f3', // Vibrant blue fill
        fillOpacity: 0.1    // Adjusted for visibility
    },
    'Potential Irrigable Area': {
        color: '#fbc02d',
        weight: 2,
        fillColor: '#ffeb3b',
        fillOpacity: 0.7
    }
};

let geoLayer;
let selectedBaseLayer;
let miniGeoLayer;
let provinceLabelLayer = null;
let municipalityLabels = [];
const overlayLayers = {};

toggle.addEventListener('change', () => {
    // Get the container that holds your checkboxes
    const layerControls = document.getElementById('layer-controls');

    if (toggle.checked) {
        map.removeLayer(normalLayer);
        map.addLayer(satelliteLayer);
        map.addLayer(labelLayer);

        // Add the class to turn text white
        layerControls.classList.add('satellite-active');
    } else {
        map.removeLayer(satelliteLayer);
        map.removeLayer(labelLayer);
        map.addLayer(normalLayer);

        // Remove the class to go back to dark text
        layerControls.classList.remove('satellite-active');
    }
});

function updateStatus(message, isError = false) {
    statusBox.innerHTML = message;
    statusBox.classList.toggle('error', isError);
}

function getFeatureName(feature, fallback = 'Unknown') {
    const properties = feature?.properties || {};

    return properties.ADM3_EN
        || properties.name
        || properties.Name
        || properties.MUNICIPALI
        || properties.MUNICIPAL
        || properties.title
        || fallback;
}

function getBaseStyle(feature) {
    return {
        color: '#ffffff',
        weight: 1,
        fillColor: '#9e9e9e', // ✅ visible gray
        fillOpacity: 0.5
    };
}

function setSelectedBaseLayer(layer) {
    if (selectedBaseLayer && geoLayer) {
        geoLayer.resetStyle(selectedBaseLayer);
    }

    selectedBaseLayer = layer;
    // selectedBaseLayer.setStyle({
    //     color: '#ffd400',
    //     weight: 2,
    //     fillColor: '#ff5a36',
    //     fillOpacity: 0.8
    // });
}

function updateProvinceLabelVisibility() {
    // Check if ANY checkbox is checked
    const anyChecked = Object.values(overlayToggles).some(checkbox => checkbox && checkbox.checked);

    if (anyChecked) {
        // HIDE PROVINCE
        if (provinceLabelLayer && map.hasLayer(provinceLabelLayer)) {
            map.removeLayer(provinceLabelLayer);
        }
        // HIDE ALL MUNICIPALITIES
        municipalityLabels.forEach(label => {
            if (map.hasLayer(label)) map.removeLayer(label);
        });
    } else {
        // SHOW PROVINCE
        if (provinceLabelLayer && !map.hasLayer(provinceLabelLayer)) {
            provinceLabelLayer.addTo(map);
        }
        // SHOW ALL MUNICIPALITIES
        municipalityLabels.forEach(label => {
            if (!map.hasLayer(label)) label.addTo(map);
        });
    }
}

async function loadBaseMap() {
    const response = await fetch(buildAppUrl('maps/PANGASINAN.geojson'));

    if (!response.ok) {
        throw new Error('Unable to load the base Pangasinan boundary.');
    }

    const data = await response.json();
    const labeledNames = new Set(); // To prevent duplicate labels for islands/multipolygons

    geoLayer = L.geoJSON(data, {
        style: getBaseStyle,
        onEachFeature: function(feature, layer) {
            const name = getFeatureName(feature);

            // 1. Create Municipality Labels (Only once per unique name)
            if (name && !labeledNames.has(name)) {
                const bounds = layer.getBounds();
                if (bounds.isValid()) {
                    const center = bounds.getCenter();
                    const labelMarker = L.marker(center, {
                        opacity: 0,
                        interactive: false
                    });

                    labelMarker.bindTooltip(name, {
                        permanent: true,
                        direction: 'center',
                        className: 'municipality-label-base',
                        offset: [0, 0]
                    });

                    municipalityLabels.push(labelMarker);
                    labeledNames.add(name);
                }
            }

            layer.on('click', function() {
                setSelectedBaseLayer(layer);
                showMiniMap(feature);
            });

            const bounds = layer.getBounds();

// get size of polygon
const size = bounds.getNorthEast().distanceTo(bounds.getSouthWest());


if (size < 5000) return;

const center = bounds.getCenter();

L.marker(center, {
    icon: L.divIcon({
        className: 'municipality-label',
        html: name
    })
}).addTo(map);
        }
    }).addTo(map);

    const bounds = geoLayer.getBounds();
    if (bounds.isValid()) {
        map.fitBounds(bounds, { padding: [20, 20] });
    }

}

function normalizeGeoJson(data) {
    if (Array.isArray(data)) {
        return {
            type: 'FeatureCollection',
            features: data.flatMap(item => item?.features || [])
        };
    }

    if (data && typeof data === 'object' && !data.type) {
        return {
            type: 'FeatureCollection',
            features: Object.values(data).flatMap(item => item?.features || [])
        };
    }

    return data;
}

async function convertStoredFileToGeoJson(fileUrl) {
    const safeUrl = encodeURI(buildAppUrl(fileUrl));
    const lowerFileUrl = fileUrl.toLowerCase();

    if (lowerFileUrl.endsWith('.geojson') || lowerFileUrl.endsWith('.json')) {
        const response = await fetch(safeUrl);
        if (!response.ok) {
            throw new Error('The GeoJSON file could not be fetched.');
        }

        return await response.json();
    }

    if (lowerFileUrl.endsWith('.kml')) {
        const response = await fetch(safeUrl);
        if (!response.ok) {
            throw new Error('The KML file could not be fetched.');
        }

        const kmlText = await response.text();
        const kmlDocument = new DOMParser().parseFromString(kmlText, 'text/xml');
        return toGeoJSON.kml(kmlDocument);
    }

    if (lowerFileUrl.endsWith('.kmz')) {
        const response = await fetch(safeUrl);
        if (!response.ok) {
            throw new Error('The KMZ file could not be fetched.');
        }

        const arrayBuffer = await response.arrayBuffer();
        const zip = await JSZip.loadAsync(arrayBuffer);
        const kmlEntryName = Object.keys(zip.files).find(name => name.toLowerCase() === 'doc.kml')
            || Object.keys(zip.files).find(name => name.toLowerCase().endsWith('.kml'));

        if (!kmlEntryName) {
            throw new Error('No KML document was found inside the KMZ file.');
        }

        const kmlText = await zip.files[kmlEntryName].async('text');
        const kmlDocument = new DOMParser().parseFromString(kmlText, 'text/xml');
        return toGeoJSON.kml(kmlDocument);
    }

    if (lowerFileUrl.endsWith('.shp')) {
        return await shp(safeUrl);
    }

    throw new Error('Unsupported map file type.');
}

function styleOverlayFeature(categoryKey, feature) {
    const baseStyle = overlayStyles[categoryKey];
    const geometryType = feature?.geometry?.type || '';

    if (categoryKey === 'Pangasinan Land Boundary') {
        return {
            color: baseStyle.color,
            weight: baseStyle.weight,
            fillColor: baseStyle.fillColor,
            fillOpacity: 0.3
        };
    }

    if (geometryType.includes('Line')) {
        return {
            color: baseStyle.color,
            weight: baseStyle.weight + 1,
            fillOpacity: 0
        };
    }

    return baseStyle;
}

function createOverlayLayer(categoryKey, geoJson, fileName) {
    return L.geoJSON(normalizeGeoJson(geoJson), {
        style: feature => styleOverlayFeature(categoryKey, feature),

       onEachFeature: function(feature, layer) {

    layer.feature.properties._category = categoryKey;

    const name = getFeatureName(feature, fileName);

    layer.bindPopup('<b>' + name + '</b><br>' + overlayGroups[categoryKey].label);

    //If it's a Land Boundary, add the permanent floating label
            if (categoryKey === 'Pangasinan Land Boundary') {
                layer.bindTooltip(name, {
                    permanent: true,
                    direction: 'center',
                    className: 'municipality-label'
                }).openTooltip();
            }


    layer.on('click', function(e) {

        // 🔥 highlight selected overlay
        // layer.setStyle({
        //     color: '#ff0000',
        //     weight: 3,
        //     fillColor: '#ff5722',
        //     fillOpacity: 0.9
        // });

        // reset others
//         Object.values(overlayLayers).forEach(group => {
//             group.eachLayer(l => {
//                 if (l !== layer) {
//                     l.setStyle(styleOverlayFeature(categoryKey, l.feature));
//                 }
//             });
//         });
// // 🔥 bring overlays to front
// Object.values(overlayLayers).forEach(group => {
//     group.eachLayer(layer => layer.bringToFront());
// });

// // 🔥 keep Pangasinan layer BELOW (but visible)
// if (geoLayer) {
//     geoLayer.eachLayer(layer => layer.bringToBack());
// }
        // 🔥 zoom to clicked overlay
        map.fitBounds(layer.getBounds());

        // 🔥 send ONLY this overlay to mini map
        showMiniMap(layer.toGeoJSON());
    });
}
    });
}

// async function showOverlayCategory(categoryKey) {
//     const config = overlayGroups[categoryKey];

//     if (!config || !config.files.length) {
//         updateStatus('No files found for ' + (config?.label || categoryKey) + '.', true);
//         return;
//     }

//     if (!overlayLayers[categoryKey]) {
//         const layers = [];

//         for (let index = 0; index < config.files.length; index++) {
//             const file = config.files[index];

//             updateStatus(`Loading ${config.label} (${index + 1}/${config.files.length})...`);

//             try {
//                 const geoJson = await convertStoredFileToGeoJson(file.url);
//                 layers.push(createOverlayLayer(categoryKey, geoJson, file.name));
//             } catch (error) {
//                 console.error('Failed to load file:', file.name, error);
//             }
//         }

//         if (!layers.length) {
//             updateStatus('No valid polygons could be loaded for ' + config.label + '.', true);
//             return;
//         }

//         overlayLayers[categoryKey] = L.featureGroup(layers);
//     }

//     if (!map.hasLayer(overlayLayers[categoryKey])) {
//         overlayLayers[categoryKey].addTo(map);
//     }

async function showOverlayCategory(categoryKey) {
    const config = overlayGroups[categoryKey];

    if (!config || !config.files.length) {
        updateStatus('No files found for ' + (config?.label || categoryKey) + '.', true);
        return;
    }

    if (!overlayLayers[categoryKey]) {
        const layers = [];

        for (let index = 0; index < config.files.length; index++) {
            const file = config.files[index];

            updateStatus(`Loading ${config.label} (${index + 1}/${config.files.length})...`);

            try {
                const geoJson = await convertStoredFileToGeoJson(file.url);
                layers.push(createOverlayLayer(categoryKey, geoJson, file.name));
            } catch (error) {
                console.error('Failed to load file:', file.name, error);
            }
        }

        if (!layers.length) {
            updateStatus('No valid polygons could be loaded for ' + config.label + '.', true);
            return;
        }

        overlayLayers[categoryKey] = L.featureGroup(layers);
    }

    if (!map.hasLayer(overlayLayers[categoryKey])) {
        overlayLayers[categoryKey].addTo(map);
    }

    // bring layers to front based on priority
    const categoriesSorted = Object.keys(overlayLayers).sort((a, b) => overlayPriority[a] - overlayPriority[b]);
    categoriesSorted.forEach(cat => {
        if (map.hasLayer(overlayLayers[cat])) {
            overlayLayers[cat].eachLayer(layer => layer.bringToFront());
        }
    });

    const bounds = overlayLayers[categoryKey].getBounds();
    if (bounds.isValid()) {
        map.fitBounds(bounds, { padding: [25, 25] });
    }

    updateStatus(config.label + ' is now highlighted on the map.');
}

//     const bounds = overlayLayers[categoryKey].getBounds();
//     if (bounds.isValid()) {
//         map.fitBounds(bounds, { padding: [25, 25] });
//     }

//     updateStatus(config.label + ' is now highlighted on the map.');
// }

function hideOverlayCategory(categoryKey) {
    if (overlayLayers[categoryKey] && map.hasLayer(overlayLayers[categoryKey])) {
        map.removeLayer(overlayLayers[categoryKey]);
    }

    const config = overlayGroups[categoryKey];
    updateStatus((config?.label || 'Selected layer') + ' has been hidden.');
}



let miniMap = L.map('miniMap', {
    attributionControl: false,
    zoomControl: false
});

let miniLayer = L.tileLayer(
    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
);
miniLayer.addTo(miniMap);
function showMiniMap(feature) {
    document.getElementById('miniMap').style.display = 'block';

    if (miniGeoLayer) {
        miniMap.removeLayer(miniGeoLayer);
    }

    miniGeoLayer = L.geoJSON(feature, {
        style: function(f) {
            const category = f.properties?._category;

            if (category && overlayStyles[category]) {
                return overlayStyles[category]; // same color
            }

            return {
                color: '#ff0000',
                weight: 2,
                fillOpacity: 0.6
            };
        }
    }).addTo(miniMap);

    miniMap.fitBounds(miniGeoLayer.getBounds(), { padding: [10,10] });
}



(async function initializeMap() {
    try {
        // 1. Create the high-priority layer (Pane) for the Province Label
        // This ensures "PANGASINAN" stays above all other map layers
        map.createPane('provincePane');
        map.getPane('provincePane').style.zIndex = 650;
        map.getPane('provincePane').style.pointerEvents = 'none';

        // 2. Load the map data
        await loadBaseMap();

    } catch (error) {
        console.error(error);
        updateStatus(error.message, true);
    }
})();


const form = document.getElementById('uploadForm');
const statusBoxUpload = document.getElementById('uploadStatus');

form.addEventListener('submit', async function (e) {
    e.preventDefault();

    const fileInput = document.getElementById('fileInput');
    const folderInput = document.getElementById('folderInput');

    const files = fileInput.files;
    const folderFiles = folderInput.files;

    const formData = new FormData();

    const category = document.querySelector('select[name="category"]').value;
    formData.append('category', category);

    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    // 🚨 VALIDATION
    if (files.length === 0 && folderFiles.length === 0) {
        alert('Please select files OR a folder');
        return;
    }

    if (files.length > 0 && folderFiles.length > 0) {
        alert('Please select only one: files OR folder');
        return;
    }

    // 📄 FILE MODE
    if (files.length > 0) {
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
            formData.append('paths[]', files[i].name);
        }
    }

    // 📁 FOLDER MODE
    if (folderFiles.length > 0) {
        for (let i = 0; i < folderFiles.length; i++) {
            formData.append('files[]', folderFiles[i]);
            formData.append('paths[]', folderFiles[i].webkitRelativePath);
        }
    }

    // UI
    statusBoxUpload.style.display = 'block';
    statusBoxUpload.className = 'upload-status upload-loading';
    statusBoxUpload.innerHTML = 'Uploading...';

    try {
        const response = await fetch("{{ route('map.upload') }}", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        console.log(result);

        if (response.ok && result.files.length > 0) {
            statusBoxUpload.className = 'upload-status upload-success';
            statusBoxUpload.innerHTML = `✅ Uploaded ${result.files.length} file(s)!`;

            form.reset(); // keep form visible

        } else {
            throw new Error(result.message || 'Upload failed');
        }

    } catch (error) {
        statusBoxUpload.className = 'upload-status upload-error';
        statusBoxUpload.innerHTML = '❌ ' + error.message;
    }
});
const overlayPriority = {
    irrigated: 3,       // highest
    land_boundary: 1,   // middle
    potential: 2        // lowest
};

</script>

@endsection
