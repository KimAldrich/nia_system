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

#layer-controls {
    position: absolute;
    top: 70px;
    left: 20px;
    z-index: 1000;
    background: rgba(255,255,255,0.94);
    backdrop-filter: blur(6px);
    padding: 12px 14px;
    border-radius: 12px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.18);
    min-width: 230px;
}

.layer-check {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    font-size: 13px;
    cursor: pointer;
}

.layer-check:last-child {
    margin-bottom: 0;
}

.layer-check input {
    accent-color: #0b5e2c;
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

    <div id="layer-controls">
        <label class="layer-check">
            <input type="checkbox" id="toggleIrrigated" {{ empty($overlayGroups['irrigated']['files']) ? 'disabled' : '' }}>
            <span>Irrigated Area</span>
        </label>
        <label class="layer-check">
            <input type="checkbox" id="toggleLandBoundary" {{ empty($overlayGroups['land_boundary']['files']) ? 'disabled' : '' }}>
            <span>Land Boundary</span>
        </label>
        <label class="layer-check">
            <input type="checkbox" id="togglePotential" {{ empty($overlayGroups['potential']['files']) ? 'disabled' : '' }}>
            <span>Potential Irrigable Area</span>
        </label>
    </div>

    <!-- MAP -->
    <div id="map"></div>

<div id="map-status">Tick a layer to load the uploaded polygons from <code>storage/app/public/maps</code>.</div>
<div id="miniMap"></div>
</div>

<!-- the map -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tmcw/togeojson@5.8.1/dist/togeojson.umd.min.js"></script>
<script src="https://unpkg.com/shpjs@latest/dist/shp.min.js"></script>

<script>
const overlayGroups = @json($overlayGroups);
const appBaseUrl = @json(rtrim(request()->getBaseUrl(), '/'));

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

const toggle = document.getElementById('toggleSwitch');
const statusBox = document.getElementById('map-status');
const overlayToggles = {
    irrigated: document.getElementById('toggleIrrigated'),
    land_boundary: document.getElementById('toggleLandBoundary'),
    potential: document.getElementById('togglePotential')
};

const overlayStyles = {
    irrigated: {
        color: '#1b5e20',
        weight: 2,
        fillColor: '#43a047',
        fillOpacity: 0.45
    },
    land_boundary: {
        color: '#0d47a1',
        weight: 3,
        fillColor: '#64b5f6',
        fillOpacity: 0.08,
        opacity: 1
    },
    potential: {
        color: '#ff8f00',
        weight: 3,
        fillColor: '#ffeb3b',
        fillOpacity: 0.55,
        opacity: 1
    }
};

let geoLayer;
let selectedBaseLayer;
let miniGeoLayer;
const overlayLayers = {};

toggle.addEventListener('change', () => {
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

function updateStatus(message, isError = false) {
    statusBox.innerHTML = message;
    statusBox.classList.toggle('error', isError);
}

function getFeatureName(feature, fallback = 'Unknown') {
    const properties = feature?.properties || {};

    return properties.name
        || properties.Name
        || properties.MUNICIPALI
        || properties.MUNICIPAL
        || properties.title
        || fallback;
}

function getBaseStyle(feature) {
    return {
        color: 'white',
        weight: 1,
        fillColor: feature.properties?.irrigated ? 'green' : '#242525',
        fillOpacity: 0.6
    };
}

function setSelectedBaseLayer(layer) {
    if (selectedBaseLayer && geoLayer) {
        geoLayer.resetStyle(selectedBaseLayer);
    }

    selectedBaseLayer = layer;
    selectedBaseLayer.setStyle({
        color: '#ffd400',
        weight: 2,
        fillColor: '#ff5a36',
        fillOpacity: 0.8
    });
}

async function loadBaseMap() {
    const response = await fetch(buildAppUrl('maps/PANGASINAN.geojson'));

    if (!response.ok) {
        throw new Error('Unable to load the base Pangasinan boundary.');
    }

    const data = await response.json();

    geoLayer = L.geoJSON(data, {
        style: getBaseStyle,
        onEachFeature: function(feature, layer) {
            const name = getFeatureName(feature);

            layer.on('click', function() {
                setSelectedBaseLayer(layer);
                showMiniMap(feature);
            });

            layer.bindPopup('<b>' + name + '</b>');
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

    if (categoryKey === 'land_boundary') {
        return {
            color: baseStyle.color,
            weight: baseStyle.weight,
            opacity: 1,
            fillColor: baseStyle.fillColor,
            fillOpacity: geometryType.includes('Polygon') ? 0.04 : 0
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
            const name = getFeatureName(feature, fileName);
            layer.bindPopup('<b>' + name + '</b><br>' + overlayGroups[categoryKey].label);
            layer.on('click', function() {
                showMiniMap(feature);
            });
        }
    });
}

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

    overlayLayers[categoryKey].eachLayer(layer => {
        if (typeof layer.bringToFront === 'function') {
            layer.bringToFront();
        }
    });

    const bounds = overlayLayers[categoryKey].getBounds();
    if (bounds.isValid()) {
        map.fitBounds(bounds, { padding: [25, 25] });
    }

    updateStatus(config.label + ' is now highlighted on the map.');
}

function hideOverlayCategory(categoryKey) {
    if (overlayLayers[categoryKey] && map.hasLayer(overlayLayers[categoryKey])) {
        map.removeLayer(overlayLayers[categoryKey]);
    }

    const config = overlayGroups[categoryKey];
    updateStatus((config?.label || 'Selected layer') + ' has been hidden.');
}

Object.entries(overlayToggles).forEach(([categoryKey, checkbox]) => {
    if (!checkbox) {
        return;
    }

    checkbox.addEventListener('change', async function() {
        if (this.checked) {
            await showOverlayCategory(categoryKey);
        } else {
            hideOverlayCategory(categoryKey);
        }
    });
});

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
        style: {
            color: 'red',
            weight: 2,
            fillOpacity: 0.5
        }
    }).addTo(miniMap);

    miniMap.fitBounds(miniGeoLayer.getBounds());
}

(async function initializeMap() {
    try {
        await loadBaseMap();
    } catch (error) {
        console.error(error);
        updateStatus(error.message, true);
    }
})();
</script>

@endsection