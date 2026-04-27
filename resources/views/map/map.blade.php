@extends('layouts.app')
@section('title', 'Map - Pangasinan')

@section('content')

<!-- for the map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>


<style>
.content {
    padding: 0 !important;
    margin: 0 !important;
    height: 100vh !important;
    width: 100% !important;
    max-width: none !important;
    overflow: hidden !important;
}
#map-container {
    position: relative;
    width: 100%;
    flex: 1 1 auto;
    max-width: 100%;
    height: 100vh;
    overflow: hidden;
    transition: margin-right 0.3s ease;
}
.map-loader {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 2000; /* Must be higher than Leaflet layers */
    background: rgba(255, 255, 255, 0.9);
    padding: 20px 40px;
    border-radius: 50px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    font-family: sans-serif;
    font-weight: bold;
    color: #333;
}

.spinner {
    width: 25px;
    height: 25px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #2e7d32; /* PSU Green */
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
/* Custom class to apply when in satellite mode */
#map-toggle.satellite-active {
    color: white !important;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.8); /* Optional: adds a glow to make it pop */
}

/* Ensure the transition is smooth */
#map-toggle {
    transition: color 0.3s ease;
}
.municipality-label {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    color: #ffffff; /* White text for visibility on blue */
    font-weight: bold;
    text-shadow: 1px 1px 2px #000, -1px -1px 2px #000; /* Outline to make it readable */
    font-size: 12px;
    pointer-events: none; /* Let clicks pass through to the map */

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
    left: 50px;
    z-index: 1000;
    background: rgba(255, 255, 255, 0);
    backdrop-filter: blur(6px);
    padding: 8px 12px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
}

#resetMapBtn {
    background: none;
    color: #ebeef2;
    border: none;
    padding: 0;
    cursor: pointer;
    font-size: 13px;
    font-weight: inherit;
    transition: all 0.3s ease;
    text-shadow: -1px -1px 0 black, 1px -1px 0 black, -1px 1px 0 black, 1px 1px 0 black;
}

#resetMapBtn:hover {
    text-shadow: -1px -1px 0 black, 1px -1px 0 black, -1px 1px 0 black, 1px 1px 0 black, 0 0 8px rgba(255,255,255,0.5);
    transform: scale(1.08);
}

#resetMapBtn:active {
    transform: scale(0.95);
}

#layer-controls {
    position: absolute;
    top: 70px;
    left: 20px;
    z-index: 1000;

    padding: 12px 14px;
    border-radius: 12px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.18);
    min-width: 200px;
}
#layer-controls,
#map-toggle,
#legend,
#infoPanel,
#miniMap,
#admin-toggle-btn {
    z-index: 9999 !important;
}
.layer-check:has(#toggleIrrigated) { --glow-color: #43a047; } /* Green */
.layer-check:has(#toggleLandBoundary) { --glow-color: #2196f3; } /* Blue */
.layer-check:has(#togglePotential) { --glow-color: #ffeb3b; } /* Yellow */

/* 2. Style the text and glow when Satellite is active */
#layer-controls.satellite-active .layer-check span {
    color: white !important;
    transition: all 0.3s ease;
}

/* 3. Apply the dynamic glow to the checkbox text and box */
#layer-controls.satellite-active .layer-check input[type="checkbox"]:checked + span,
#layer-controls.satellite-active .layer-check:has(input:checked) span {
    text-shadow: 0 0 10px var(--glow-color), 0 0 20px var(--glow-color);
    color: var(--glow-color) !important;
    font-weight: bold;
}

/* 4. Optional: Glow the actual checkbox itself */
#layer-controls.satellite-active input[type="checkbox"]:checked {
    box-shadow: 0 0 15px var(--glow-color);
    outline: none;
}
#map {
    position: relative;
    z-index: 1;
}
.leaflet-pane {
    z-index: 1 !important;
}
.leaflet-top,
.leaflet-bottom {
    z-index: 999 !important;
}
.layer-check {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
    font-size: 13px;
    cursor: pointer;

    padding: 6px 10px;
    border-radius: 8px;

    width: 100%;              /* allow full width */
    box-sizing: border-box;   /* prevent overflow */
}
#admin-sidebar {
    position: fixed; /* 🔥 CHANGE FROM absolute */
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
    bottom: 80px;
    left: 20px;
    width: 200px;
    height: 150px;
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

/* INFO PANEL */
.info-panel { position: fixed; /* 🔥 CHANGE from absolute */ top: 0; right: -400px; width: 250px; height: 100vh; background: #81717187; box-shadow: -4px 0 10px rgba(150, 133, 133, 0.53); z-index: 10000 !important; /* 🔥 STRONGER than everything */ transition: right 0.3s ease; display: flex; flex-direction: column; }

.info-panel.active {
    right: 0;
}

.info-header {
    padding: 15px;
    background: #2e7d32;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.info-header h2 {
    margin: 0;
    font-size: 18px;
}

.info-header button {
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
}

.data-item {
    margin-bottom: 10px;
    padding: 10px;
    background: #f5f5f5;
    border-radius: 6px;
}
/* CHART */
.chart-small {
    width: 200px !important;
    height: 200px !important;
    margin: 0 auto 10px auto;
    display: block;
    color: #cccccc00;
}
#infoContent {
    padding: 25px;
    text-align: center;
}
#legendContainer {
    margin-top: 5px; /* reduce gap */
    text-align: left;
}

#legendContainer,
#legendContainer .legend-item,
#infoTitle,
.detail-content,
.info-content{
    color: white;
    text-shadow:
        0 0 2px #020202,
        0 0 4px #000000,
        1px 1px 2px #000000;
}

.legend-item {
    display: flex;
    align-items: center;
    margin-bottom: 4px; /* tighter spacing */
    font-size: 13px;
}

.legend-color {
    width: 12px;
    height: 12px;
    margin-right: 6px; /* closer text */
}
/* DATA TABLE */
.data-table {
    margin-top: 10px;
    border-radius: 6px;
    overflow: hidden;
    font-size: 12px;
    border: 1px solid #ccc;
}

/* HEADER */
.data-header {
    display: grid;
    grid-template-columns: 1fr 1fr 1.5fr;
    background: #455a64;
    color: white;
    font-weight: bold;
    padding: 8px;
}

/* ROWS */
.data-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1.5fr;
    padding: 8px;
    border-top: 1px solid #eee;
    background: #fafafa;
}

/* ALTERNATE ROW COLOR */
.data-row:nth-child(even) {
    background: #f1f1f1;
}

/* TEXT STYLE */
.data-row div,
.data-header div {
    padding: 2px 5px;
}
/* FLOATING PANEL */
.detail-panel {
    position: fixed;
    top: 80px;
    left: 60%;
    transform: translateX(-50%) scale(0.9);

    width: 350px;
    max-height: 400px;

    background: rgba(50, 60, 70, 0.95);
    color: #fff;

    border-radius: 6px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.4);

    opacity: 0;
    visibility: hidden;
    transition: 0.25s;
    z-index: 3000;

    display: flex;
    flex-direction: column;
}

/* SHOW */
.detail-panel.active {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) scale(1);
}

/* HEADER */
.detail-header {
    padding: 10px;
    background: rgba(0,0,0,0.3);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.detail-header button {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
}

/* CONTENT */
.detail-content {
    padding: 10px;
    overflow-y: auto;
    font-size: 12px;
}

/* TABLE STYLE */
.detail-table {
    width: 100%;
}

.detail-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1.5fr;
    margin-bottom: 6px;
}

.detail-header-row {
    font-weight: bold;
    border-bottom: 1px solid #aaa;
    margin-bottom: 6px;
}
#admin-sidebar:not(.sidebar-closed) ~ #admin-toggle-btn {
    opacity: 0;
    pointer-events: none;
}
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
.sidebar-content {
    padding: 25px 20px;
    overflow-y: auto;
}
/* .sidebar-header {
    background: #181818;
    color: white;
    padding: 24px 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
} */
.sidebar-closed {
    transform: translateX(115%); /* Hide it completely including shadows */
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
    opacity: 1 !important;
    visibility: visible !important;
}

#admin-toggle-btn:hover {
    background: #084a22;
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(0,0,0,0.4);
}
.layer-check {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    font-size: 13px;
    cursor: pointer;
    color: black; /* keep text normal */
}

/* remove default checkbox */
.layer-check {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    font-size: 13px;
    cursor: pointer;
    color: white;
    text-shadow:
        -1px -1px 0 black,
         1px -1px 0 black,
        -1px  1px 0 black,
         1px  1px 0 black;
}

/* remove default checkbox */
.layer-check input {
    appearance: none;
    width: 16px;
    height: 16px;
    border-radius: 3px;
    border: 2px solid transparent;
    position: relative;
    cursor: pointer;
}

/* ✔ check icon */
.layer-check input:checked::after {
    content: "✔";
    position: absolute;
    top: -2px;
    left: 2px;
    font-size: 12px;
    color: white;
}

/* 🎨 ALWAYS COLORED (even unchecked) */
#toggleIrrigated { background-color: #81c784; }
#toggleIrrigated:checked { background-color: #2e7d32; }

#toggleLandBoundary { background-color: #64b5f6; }
#toggleLandBoundary:checked { background-color: #1565c0; }

#togglePotential { background-color: #fff176; }
#togglePotential:checked { background-color: #fbc02d; }
.province-label {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    color: rgb(255, 255, 255);
    font-weight: 900;
    text-shadow: 1px 1px 2px #000, -1px -1px 2px #000; /* Outline to make it readable */
    font-size: 32px;
    letter-spacing: 3px;
}
</style>

<div id="map-container">
    <div id="map-loader" class="map-loader" style="display: none;">
        <div class="spinner"></div>
        <span id="loader-text">Loading Layer...</span>
    </div>
<div id="infoPanel" class="info-panel">
    <div class="info-header">
        <h2 id="infoTitle">Municipality</h2>
        <button onclick="closeAllPanels()">✖</button>
    </div>

    <div id="infoContent" class="info-content">
<canvas id="landChart" class="chart-small"></canvas>
        <!-- LEGEND -->
        <div id="legendContainer"></div>

        <!-- DATA -->
            <div id="extraData"></div>
          <!-- <button class="view-btn" onclick="openDetail()" style="">View Full Details</button> -->
    </div>


</div>
<!-- DETAIL POPUP -->
<div id="detailPanel" class="detail-panel">
    <div class="detail-header">
        <span id="municipalityName">Details</span>
        <button onclick="closeAllPanels()">✖</button>
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

    <button id="resetMapBtn" title="Reset to default position">🔄 Reset</button>
</div>

    <div id="layer-controls">
        <label class="layer-check">
            <input type="checkbox" id="toggleIrrigated" {{ empty($overlayGroups['irrigated']['files']) ? 'disabled' : '' }}>
            <span>Irrigated Area</span>
        </label>
        <label class="layer-check" >
            <input type="checkbox" id="toggleLandBoundary" {{ empty($overlayGroups['land_boundary']['files']) ? 'disabled' : '' }}>
            <span>Land Boundary</span>
        </label>
        <label class="layer-check" >
            <input type="checkbox" id="togglePotential" {{ empty($overlayGroups['potential']['files']) ? 'disabled' : '' }}>
            <span>Potential Irrigable Area</span>
        </label>
    </div>

    <!-- MAP -->
    <div id="map"></div>

<div id="map-status">Tick a layer to load the uploaded polygons from <code>storage/app/public/maps</code>.</div>
<div id="miniMap"></div>

@if(auth()->check() && auth()->user()->role === 'admin')
<button id="admin-toggle-btn" title="Open Admin Panel">
    <i class="fas fa-cog"></i> Upload
</button>

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
                    <label>Destination Folder</label>
                    <select name="target_folder" id="targetFolderSelect">
                        <option value="">Category root</option>
                    </select>
                    <small id="targetFolderHint">Choose the municipality or folder where the files should be added.</small>
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
                    <div id="uploadSelectionInfo" class="upload-selection-info">No files selected.</div>
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
@endif

<!-- the map -->
 <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@tmcw/togeojson@5.8.1/dist/togeojson.umd.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/shpjs@6.2.0/dist/shp.min.js" crossorigin="anonymous"></script>
<script>
function toTitleCase(str) {
    if (!str) return '';
    return str.toLowerCase().split(' ').map(word => {
        return word.charAt(0).toUpperCase() + word.slice(1);
    }).join(' ');
}

const overlayGroups = JSON.parse('{!! json_encode($overlayGroups) !!}');
const uploadTargets = JSON.parse('{!! json_encode($uploadTargets ?? []) !!}');
const appBaseUrl = "{{ rtrim(request()->getBaseUrl(), '/') }}";
let landChart = null;
let selectedMunicipality = null;
let activeSliceIndex = null;
let municipalityMarkers = [];
let provinceLabelLayer = null;
let irrigatedStats = {};

fetch('/irrigated-chart-data')
    .then(res => res.json())
    .then(data => {
        irrigatedStats = data;
    });

function buildAppUrl(path) {
    if (!path) {
        return null;
    }

    if (/^https?:\/\//i.test(path)) {
        return path;
    }

    const normalizedPath = String(path).replace(/^\/+/, '');
    const baseUrl = appBaseUrl || window.location.origin;
    return new URL(normalizedPath, `${baseUrl.replace(/\/+$/, '')}/`).toString();
}

// Dagupan City Center
const DEFAULT_CENTER = [16.0433, 120.3333];
const DEFAULT_ZOOM = 10;
let map = L.map('map').setView(DEFAULT_CENTER, DEFAULT_ZOOM);

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
// function closeDetail(){
//     document.getElementById('detailPanel').classList.add('deactive');
// };
const overlayStyles = {
    irrigated: {
        color: '#1b5e20',
        weight: 2,
        fillColor: '#43a047',
        fillOpacity: 0.9
    },
    land_boundary: {
        color: '#0d47a1', // Dark blue border
        weight: 2,
        fillColor: '#2196f3', // Vibrant blue fill
        fillOpacity: 0.5    // Adjusted for visibility
    },
    potential: {
        color: '#fbc02d',
        weight: 2,
        fillColor: '#ffeb3b',
        fillOpacity: 0.7
    }
};

// Reset map to default position
document.getElementById('resetMapBtn').addEventListener('click', function() {
    map.flyTo(DEFAULT_CENTER, DEFAULT_ZOOM, {
        duration: 1.5,
        easeLinearity: 0.25
    });
});

let geoLayer;
let selectedBaseLayer;
let miniGeoLayer;
const overlayLayers = {};
const mapContainer = document.getElementById('map-container');
const contentContainer = document.querySelector('.content');
const mainWrapper = document.querySelector('.main-wrapper');
let mapLayoutTimer = null;
let mapResizeObserver = null;

const toggleContainer = document.getElementById('map-toggle');
const layerControls = document.getElementById('layer-controls');

function syncMapLayout() {
    if (contentContainer && mapContainer) {
        const contentRect = contentContainer.getBoundingClientRect();
        const availableWidth = Math.max(0, Math.floor(contentRect.width));
        const availableHeight = Math.max(0, Math.floor(contentRect.height || window.innerHeight));

        if (availableWidth > 0) {
            mapContainer.style.width = `${availableWidth}px`;
            mapContainer.style.maxWidth = `${availableWidth}px`;
        }

        if (availableHeight > 0) {
            mapContainer.style.height = `${availableHeight}px`;
        }

        const mapElement = document.getElementById('map');
        if (mapElement) {
            mapElement.style.width = '100%';
            mapElement.style.height = '100%';
        }
    }

    requestAnimationFrame(() => {
        map.invalidateSize();
    });

    clearTimeout(mapLayoutTimer);
    mapLayoutTimer = setTimeout(() => {
        map.invalidateSize();
    }, 350);
}

function queueMapLayoutSync() {
    requestAnimationFrame(() => {
        syncMapLayout();
    });

    setTimeout(syncMapLayout, 120);
    setTimeout(syncMapLayout, 320);
}

toggle.addEventListener('change', () => {
    if (toggle.checked) {
        map.removeLayer(normalLayer);
        map.addLayer(satelliteLayer);
        map.addLayer(labelLayer);

        toggleContainer.classList.add('satellite-active');
        layerControls.classList.add('satellite-active');
        if(statusBox) statusBox.classList.add('satellite-active');
    } else {
        map.removeLayer(satelliteLayer);
        map.removeLayer(labelLayer);
        map.addLayer(normalLayer);

        toggleContainer.classList.remove('satellite-active');
        layerControls.classList.remove('satellite-active');
        if(statusBox) statusBox.classList.remove('satellite-active');
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

function updateProvinceLabelVisibility() {
    // Check if ANY checkbox is checked
    const anyChecked = Object.values(overlayToggles).some(checkbox => checkbox && checkbox.checked);

    if (anyChecked) {
        // HIDE PROVINCE LABEL
        if (provinceLabelLayer && map.hasLayer(provinceLabelLayer)) {
            map.removeLayer(provinceLabelLayer);
        }

        // HIDE ALL MUNICIPALITY MARKERS
        municipalityMarkers.forEach(marker => {
            if (map.hasLayer(marker)) {
                map.removeLayer(marker);
            }
        });
    } else {
        // SHOW PROVINCE LABEL
        if (provinceLabelLayer && !map.hasLayer(provinceLabelLayer)) {
            provinceLabelLayer.addTo(map);
        }

        // SHOW ALL MUNICIPALITY MARKERS
        municipalityMarkers.forEach(marker => {
            if (!map.hasLayer(marker)) {
                marker.addTo(map);
            }
        });
    }
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

}

async function loadBaseMap() {
    const response = await fetch(buildAppUrl('maps/PANGASINAN.geojson'));

    if (!response.ok) {
        throw new Error('Unable to load the base Pangasinan boundary.');
    }

    const data = await response.json();

    // Clear previous markers
    municipalityMarkers.forEach(m => map.removeLayer(m));
    municipalityMarkers = [];

    // ✅ TRACKER: Prevent multiple labels for the same municipality
    const labeledNames = new Set();

    geoLayer = L.geoJSON(data, {
        style: getBaseStyle,
        onEachFeature: function(feature, layer) {
            const name = toTitleCase(getFeatureName(feature));


            layer.on('mouseover', function() {
                layer.setStyle({
                    color: '#fc756b',
                    weight: 2,
                    fillColor: '#bdbdbd',
                    fillOpacity: 0.75
                });

                if (layer.bringToFront) {
                    layer.bringToFront();
                }

                layer.openTooltip();
            });

            layer.on('mouseout', function() {
                if (selectedBaseLayer === layer) {
                    return;
                }

                geoLayer.resetStyle(layer);
                layer.closeTooltip();
            });

            layer.on('click', function() {
                const stat = getIrrigatedStatByName(name);
                updateInfoPanel(name);
                selectedMunicipality = stat;
                setSelectedBaseLayer(layer);
                document.getElementById('infoTitle').innerText = name;
                if (stat) {
const landData = {
    labels: [
        "Total Land Area (ha)",
        "PIA (ha)",
        "Irrigated Area (ha)",
        "Remaining Area (ha)"
    ],
    values: [
        Number(stat.total_land_area_ha) || 0,
        Number(stat.pia_area) || 0,
        Number(stat.irrigated_area) || 0,
        Number(stat.remaining_area) || 0
    ],
    colors: [
        "#1565c0",
        "#f9a825",
        "#2e7d32",
        "#ef6c00"
    ]
};
                    renderChart(landData);
                    renderLegend(landData);
                    openDetail();
                } else {
                    document.getElementById('extraData').innerHTML = "No data available";
                }
                showMiniMap(layer.toGeoJSON());
                openPanel();
            });

            // ✅ LOGIC: Only add a label if this name hasn't been used yet
            if (!labeledNames.has(name)) {
                const bounds = layer.getBounds();
                const size = bounds.getNorthEast().distanceTo(bounds.getSouthWest());

                // Skip small islands/polygons to ensure the label hits the main landmass
                if (size > 5000) {
                    const center = bounds.getCenter();

                    const marker = L.marker(center, {
                        icon: L.divIcon({
                            className: 'municipality-label',
                            html: name
                        })
                    }).addTo(map);

                    municipalityMarkers.push(marker);

                    // Mark this name as "Done"
                    labeledNames.add(name);
                }
            }
        }
    }).addTo(map);

    const provinceName = "PANGASINAN";

    provinceLabelLayer = L.tooltip({
        permanent: true,
        direction: 'center',
        className: 'province-label',
        pane: 'provincePane'
    })
    .setLatLng(geoLayer.getBounds().getCenter())
    .setContent(provinceName)
    .addTo(map);
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

function hasRenderableFeatures(geoJson) {
    const normalized = normalizeGeoJson(geoJson);

    return Array.isArray(normalized?.features) && normalized.features.length > 0;
}

function isSupportedOverlayFile(fileName) {
    const lowerName = String(fileName || '').toLowerCase();

    return lowerName.endsWith('.geojson')
        || lowerName.endsWith('.json')
        || lowerName.endsWith('.kml')
        || lowerName.endsWith('.kmz')
        || lowerName.endsWith('.zip')
        || lowerName.endsWith('.shp');
}

function getShapefileFamilyKey(fileName) {
    const lowerName = String(fileName || '').toLowerCase();

    if (lowerName.endsWith('.zip') || lowerName.endsWith('.shp')) {
        return lowerName.replace(/\.(zip|shp)$/i, '');
    }

    return null;
}

function pauseForUi() {
    return new Promise(resolve => {
        window.setTimeout(resolve, 0);
    });
}

async function convertStoredFileToGeoJson(fileUrl) {
    const safeUrl = buildAppUrl(fileUrl);
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

    if (lowerFileUrl.endsWith('.zip') || lowerFileUrl.endsWith('.shp')) {
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
            fillOpacity: 0.6
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

            if (categoryKey === 'land_boundary') {
                layer.bindTooltip(name, {
                    permanent: true,
                    direction: 'center',
                    className: 'municipality-label'
                }).openTooltip();
            }


    layer.on('click', function(e) {

        // 🔥 zoom to clicked overlay
        map.fitBounds(layer.getBounds());

        // 🔥 send ONLY this overlay to mini map
        showMiniMap(layer.toGeoJSON());
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
        const failedFiles = [];

        for (let index = 0; index < config.files.length; index++) {
            const file = config.files[index];

            updateStatus(`Loading ${config.label} (${index + 1}/${config.files.length})...`);

            try {
                const geoJson = await convertStoredFileToGeoJson(file.url);
                layers.push(createOverlayLayer(categoryKey, geoJson, file.name));
            } catch (error) {
                console.error('Failed to load file:', file.name, error);
                failedFiles.push(file.name);
            }

            if ((index + 1) % 5 === 0) {
                await new Promise(resolve => setTimeout(resolve, 0));
            }
        }

        if (!layers.length) {
            updateStatus('No valid polygons could be loaded for ' + config.label + '.', true);
            return;
        }

        overlayLayers[categoryKey] = L.featureGroup(layers);

        if (failedFiles.length) {
            console.warn('Overlay files that failed to load:', failedFiles);
            updateStatus(
                `${config.label} loaded with ${failedFiles.length} failed file(s): ${failedFiles.slice(0, 5).join(', ')}${failedFiles.length > 5 ? '...' : ''}`,
                true
            );
        }
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
    if (!checkbox) return;

    checkbox.addEventListener('change', async () => {
        const loader = document.getElementById('map-loader');

        if (checkbox.checked) {
            // 1. Show the loader immediately
            if (loader) loader.style.display = 'flex';

            try {
                // 2. Wait for the heavy map data to load
                await showOverlayCategory(categoryKey);
            } catch (error) {
                console.error("Error loading map layer:", error);
                updateStatus("Failed to load layer.", true);
            } finally {
                // 3. Hide the loader once finished (or if it fails)
                if (loader) loader.style.display = 'none';
            }
        } else {
            hideOverlayCategory(categoryKey);
        }

        // Update label visibility
        updateProvinceLabelVisibility();
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

    // 👉 If there are active overlays
//     activeCategories.forEach(categoryKey => {
//         if (overlayLayers[categoryKey]) {

//             overlayLayers[categoryKey].eachLayer(layer => {

//                 // Check if overlay is inside municipality
//                 if (feature.geometry && layer.getBounds().intersects(L.geoJSON(feature).getBounds())) {
//                     layersToShow.push(layer.toGeoJSON());
//                 }

//             });
//         }
//     });

//     // 👉 If overlays found → show them
//     if (layersToShow.length > 0) {
//         miniGeoLayer = L.geoJSON(layersToShow, {
//     style: function(feature) {
//         const category = feature.properties._category;

//         if (category && overlayStyles[category]) {
//             return overlayStyles[category]; // ✅ SAME COLOR AS MAIN MAP
//         }

//         return {
//             color: 'red',
//             weight: 2,
//             fillOpacity: 0.5
//         };
//     }
// }).addTo(miniMap);

//         miniMap.fitBounds(miniGeoLayer.getBounds());
//     } else {
//         // 👉 fallback if no overlay matched
//         miniGeoLayer = L.geoJSON(feature, {
//             style: {
//                 color: 'red',
//                 weight: 2,
//                 fillOpacity: 0.5
//             }
//         }).addTo(miniMap);

//         miniMap.fitBounds(miniGeoLayer.getBounds());
//     }

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

if (form) {
    const statusBoxUpload = document.getElementById('uploadStatus');
    const categorySelect = document.querySelector('select[name="category"]');
    const targetFolderSelect = document.getElementById('targetFolderSelect');
    const uploadSelectionInfo = document.getElementById('uploadSelectionInfo');

    function updateTargetFolderOptions() {
        const category = categorySelect.value;
        const folders = uploadTargets[category] || [{ value: '', label: 'Category root' }];

        targetFolderSelect.innerHTML = folders.map(folder =>
            `<option value="${folder.value}">${folder.label}</option>`
        ).join('');
    }

    function updateSelectionInfo(message) {
        if (uploadSelectionInfo) {
            uploadSelectionInfo.textContent = message;
        }
    }

    const fileInput = document.getElementById('fileInput');
    const folderInput = document.getElementById('folderInput');

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            folderInput.value = '';
        }

        updateSelectionInfo(
            fileInput.files.length
                ? `${fileInput.files.length} file(s) selected for upload.`
                : 'No files selected.'
        );
    });

    folderInput.addEventListener('change', () => {
        if (folderInput.files.length > 0) {
            fileInput.value = '';
        }

        if (folderInput.files.length > 0) {
            const firstPath = folderInput.files[0].webkitRelativePath || folderInput.files[0].name;
            const rootFolder = firstPath.split('/')[0];
            updateSelectionInfo(`${folderInput.files.length} file(s) selected from folder "${rootFolder}".`);
            return;
        }

        updateSelectionInfo('No files selected.');
    });

    categorySelect.addEventListener('change', updateTargetFolderOptions);
    updateTargetFolderOptions();

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const files = fileInput.files;
        const folderFiles = folderInput.files;

        const formData = new FormData();

        const category = categorySelect.value;
        formData.append('category', category);
        formData.append('target_folder', targetFolderSelect.value || '');

        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        if (files.length === 0 && folderFiles.length === 0) {
            alert('Please select files OR a folder');
            return;
        }

        if (files.length > 0 && folderFiles.length > 0) {
            alert('Please select only one: files OR folder');
            return;
        }

        if (files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
                formData.append('paths[]', files[i].name);
            }
        }

        if (folderFiles.length > 0) {
            for (let i = 0; i < folderFiles.length; i++) {
                formData.append('files[]', folderFiles[i]);
                formData.append('paths[]', folderFiles[i].webkitRelativePath);
            }
        }

        statusBoxUpload.style.display = 'block';
        statusBoxUpload.className = 'upload-status upload-loading';
        statusBoxUpload.innerHTML = 'Uploading...';
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;

        try {
            const response = await fetch("{{ route('map.upload') }}", {
                method: "POST",
                body: formData
            });

            const result = await response.json().catch(() => ({
                message: 'Upload failed. The server returned an invalid response.',
                files: []
            }));

            if (response.ok && result.files.length > 0) {
                statusBoxUpload.className = 'upload-status upload-success';
                statusBoxUpload.innerHTML = `✅ Uploaded ${result.files.length} file(s)!`;
                const targetLabel = targetFolderSelect.value ? ` to ${targetFolderSelect.value}` : '';
                statusBoxUpload.innerHTML = `Uploaded ${result.files.length} file(s)${targetLabel}.`;
                form.reset();
                updateTargetFolderOptions();
                updateSelectionInfo('No files selected.');
                irrigatedStats = await fetch('/irrigated-chart-data').then(res => res.json());
                window.setTimeout(() => window.location.reload(), 800);
            } else {
                throw new Error(result.message || 'Upload failed');
            }

        } catch (error) {
            statusBoxUpload.className = 'upload-status upload-error';
            statusBoxUpload.innerHTML = '❌ ' + error.message;
        } finally {
            submitButton.disabled = false;
        }
    });
}
const overlayPriority = {
    irrigated: 3,
    potential: 2,
    land_boundary: 1,
};
//Details

let municipalityData = [];
function getMunicipalityData(name) {
    return getIrrigatedStatByName(name);
}
function openPanel() {
    document.getElementById('infoPanel').classList.add('active');
}

function closeAllPanels() {
    // Removes 'active' from the chart panel
    document.getElementById('infoPanel').classList.remove('active');

    // Removes 'active' from the full details table
    document.getElementById('detailPanel').classList.remove('active');

    // Optional: Hide the miniMap if you want it to disappear too
    document.getElementById('miniMap').style.display = 'none';

    // Optional: Reset the map selection style
    if (selectedBaseLayer && geoLayer) {
        geoLayer.resetStyle(selectedBaseLayer);
    }
}



function openDetail() {

    if (!selectedMunicipality) {
        alert("No data selected");
        return;
    }

    const data = selectedMunicipality;
    const sourceFiles = Array.isArray(data.source_files) && data.source_files.length
        ? data.source_files.join(', ')
        : 'No DBF files matched';
    const irrigatedAreaDescription = data.irrigated_area_source === 'details_json'
        ? 'Fallback from details.json area_developed_ha'
        : 'Summed from matched irrigated DBF records';

    document.getElementById('detailContent').innerHTML = `
        <div class="detail-table">

            <div class="detail-row detail-header-row">
                <div>ATTRIBUTE</div>
                <div>VALUE</div>
                <div>DESCRIPTION</div>
            </div>

            <div class="detail-row">
                <div>Municipality</div>
                <div>${data.name}</div>
                <div>Matched municipality name</div>
            </div>

            <div class="detail-row">
                <div>Total Land Area</div>
                <div>${data.total_land_area_ha.toLocaleString()} ha</div>
                <div>Total land area</div>
            </div>

            <div class="detail-row">
                <div>PIA</div>
                <div>${Number(data.pia_area || 0).toLocaleString()} ha</div>
                <div>Potential irrigable area</div>
            </div>

            <div class="detail-row">
                <div>Irrigated Area</div>
                <div>${Number(data.irrigated_area || 0).toLocaleString()} ha</div>
                <div>${irrigatedAreaDescription}</div>
            </div>

            <div class="detail-row">
                <div>Remaining Area</div>
                <div>${Number(data.remaining_area || 0).toLocaleString()} ha</div>
                <div>Computed as PIA minus irrigated area</div>
            </div>

            <div class="detail-row">
                <div>Matched DBF Files</div>
                <div>${Number(data.dbf_file_count || 0)}</div>
                <div>Number of irrigated DBF files used</div>
            </div>

            <div class="detail-row">
                <div>Source Files</div>
                <div>${sourceFiles}</div>
                <div>Irrigated folder DBF matches</div>
            </div>

        </div>
    `;

    document.getElementById('detailPanel').classList.add('active');
}

function renderChart(landData) {
    const ctx = document.getElementById('landChart').getContext('2d');

    if (landChart) {
        landChart.destroy();
    }

    activeSliceIndex = null;

    // ✅ Base total land area only
    const totalLand = Number(landData.values[0]) || 0;

    landChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: landData.labels,
            datasets: [{
                data: landData.values,
                backgroundColor: landData.colors,

                offset: (ctx) => {
                    return ctx.dataIndex === activeSliceIndex ? 20 : 0;
                },

                borderWidth: (ctx) => {
                    return ctx.dataIndex === activeSliceIndex ? 3 : 1;
                },

                borderColor: (ctx) => {
                    return ctx.dataIndex === activeSliceIndex ? '#000' : '#fff';
                }
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;

                            const percent = totalLand > 0
                                ? ((value / totalLand) * 100).toFixed(2)
                                : 0;

                            return `${context.label}: ${value.toLocaleString()} (${percent}%)`;
                        }
                    }
                }
            }
        }
    });
}

function renderLegend(landData) {
    const container = document.getElementById('legendContainer');
    container.innerHTML = '';

    const totalLand = Number(landData.values[0]) || 0;

    landData.labels.forEach((label, index) => {
        const value = Number(landData.values[index]) || 0;

        const percent = totalLand > 0
            ? ((value / totalLand) * 100).toFixed(2)
            : 0;

        const item = document.createElement('div');
        item.className = 'legend-item active';

        item.innerHTML = `
            <div class="legend-color" style="background:${landData.colors[index]}"></div>
            ${label}: ${value.toLocaleString()} (${percent}%)
        `;

        item.onclick = function () {
            if (!landChart) return;

            activeSliceIndex = index;
            landChart.update();
        };

        container.appendChild(item);
    });
}

function updateInfoPanel(municipalityName) {
    document.getElementById("municipalityName").textContent = municipalityName + " Details";
}
const adminSidebar = document.getElementById('admin-sidebar');
const openBtn = document.getElementById('admin-toggle-btn');
const closeBtn = document.getElementById('close-sidebar');
const mainSidebar = document.getElementById('sidebar');

if (openBtn) {
    openBtn.style.display = 'flex';
    openBtn.style.opacity = '1';
    openBtn.style.visibility = 'visible';
}

// OPEN sidebar
if (openBtn && adminSidebar) {
    openBtn.addEventListener('click', () => {
        adminSidebar.classList.remove('sidebar-closed');
        syncMapLayout();
    });
}

// CLOSE sidebar
if (closeBtn && adminSidebar) {
    closeBtn.addEventListener('click', () => {
        adminSidebar.classList.add('sidebar-closed');
        syncMapLayout();
    });
}

if (adminSidebar) {
    adminSidebar.addEventListener('transitionend', syncMapLayout);
}

if (mainSidebar) {
    mainSidebar.addEventListener('transitionend', (event) => {
        if (event.propertyName === 'margin-left' || event.propertyName === 'transform') {
            syncMapLayout();
        }
    });
}

if (mainSidebar && 'MutationObserver' in window) {
    new MutationObserver(queueMapLayoutSync).observe(mainSidebar, {
        attributes: true,
        attributeFilter: ['class']
    });
}

if ('ResizeObserver' in window) {
    mapResizeObserver = new ResizeObserver(() => {
        syncMapLayout();
    });

    if (contentContainer) {
        mapResizeObserver.observe(contentContainer);
    }

    if (mainWrapper) {
        mapResizeObserver.observe(mainWrapper);
    }
}

window.addEventListener('resize', syncMapLayout);
document.addEventListener('DOMContentLoaded', syncMapLayout);

async function loadChart() {
    const res = await fetch('/irrigated-chart-data');
    const data = await res.json();
}

loadChart();
function updateChart(municipality, data) {
    const values = data[municipality];

    chart.data.labels = Object.keys(values);
    chart.data.datasets[0].data = Object.values(values);
    chart.data.datasets[0].label = municipality;

    chart.update();
}
function normalizeName(name) {
    return String(name || '')
        .toLowerCase()
        .replace(/city of/g, '')
        .replace(/municipality of/g, '')
        .replace(/_/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}

function normalizeName(name) {
    return String(name || '')
        .toLowerCase()
        .replace(/city of/g, '')
        .replace(/municipality of/g, '')
        .replace(/_/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}

function getIrrigatedStatByName(name) {
    const target = normalizeName(name);

    const key = Object.keys(irrigatedStats).find(k => {
        return normalizeName(k) === target;
    });

    return key ? irrigatedStats[key] : null;
}
</script>
<script>
function showMapLoader(message = 'Loading map data...') {
    const loader = document.getElementById('map-loader');
    const loaderText = document.getElementById('loader-text');

    if (loaderText) {
        loaderText.textContent = message;
    }

    if (loader) {
        loader.style.display = 'flex';
    }
}

function hideMapLoader() {
    const loader = document.getElementById('map-loader');
    if (loader) {
        loader.style.display = 'none';
    }
}


</script>
@endsection
