@extends('layouts.app')

@section('content')
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<style>
/* PAGE */
.page-container {
    padding: 25px;
}

/* HEADER */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.page-header h2 {
    margin: 0;
}

.back-btn {
    text-decoration: none;
    background: #0b5e2c;
    color: white;
    padding: 8px 14px;
    border-radius: 6px;
}

/* FILTER BAR */
.filter-bar {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    background: #f5f5f5;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 15px;
}

.filter-bar input,
.filter-bar select {
    padding: 8px 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

/* TABLE */
.table-container {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: #13311fd4;
    color: #ffffff;
}

th, td {
    padding: 12px;
    text-align: left;
}

tbody tr:nth-child(even) {
    background: #f9f9f9;
}

tbody tr:hover {
    background: #e8f5e9;
}

/* BUTTONS */
.btn-open {
    color: #0b5e2c;
    font-weight: bold;
    text-decoration: none;
}

.btn-delete {
    background: #e53935;
    color: white;
    border: none;
    padding: 6px 10px;
    border-radius: 5px;
    cursor: pointer;
}

.btn-delete:hover {
    background: #c62828;
}

.folders-panel {
    background: white;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    margin-bottom: 18px;
    padding: 16px;
}

.folders-panel h3 {
    margin: 0 0 12px 0;
    font-size: 16px;
}

.folder-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 10px;
}

.folder-item {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 10px;
    display: flex;
    justify-content: space-between;
    gap: 10px;
    align-items: center;
}

.folder-name {
    font-weight: bold;
    word-break: break-word;
}

.folder-meta {
    color: #666;
    font-size: 12px;
    margin-top: 4px;
}

/* PAGINATION */
.pagination {
    margin-top: 15px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
}

.pagination button {
    padding: 6px 12px;
    border: none;
    background: #0b5e2c;
    color: white;
    border-radius: 5px;
    cursor: pointer;
}

.pagination button:hover {
    background: #084a22;
}

/* EMPTY STATE */
.empty {
    text-align: center;
    padding: 20px;
    color: #888;
}
</style>

<div class="page-container">

    <!-- HEADER -->
    <div class="page-header">
        <h2>📁 Uploaded Files</h2>
        <a href="/map" class="back-btn">← Back to Map</a>
    </div>

    <!-- FILTERS -->
    <div class="filter-bar">
        <input type="text" id="searchInput" placeholder="🔍 Search..." onkeyup="applyFilters()">

        <select id="categoryFilter" onchange="applyFilters()">
            <option value="">All Categories</option>        </select>

        <select id="folderFilter" onchange="applyFilters()">
            <option value="">All Folders</option>
        </select>

        <select id="municipalityFilter" onchange="applyFilters()">
            <option value="">All Municipalities</option>
        </select>
    </div>

    @if(isset($foldersData) && count($foldersData))
    <div class="folders-panel">
        <h3>Folders</h3>
        <div class="folder-list">
            @foreach($foldersData as $folderIndex => $folder)
            <div class="folder-item" id="folder-{{ $folderIndex }}">
                <div>
                    <div class="folder-name">{{ $folder['name'] }}</div>
                    <div class="folder-meta">{{ $folder['category'] }} - {{ $folder['file_count'] }} file(s)</div>
                </div>
                <button class="btn-delete"
                    data-folder="{{ $folder['path'] }}"
                    data-id="{{ $folderIndex }}"
                    onclick="deleteFolder(this)">
                    Delete Folder
                </button>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- TABLE -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Folder</th>
                    <th>Municipality</th>
                    <th>View</th>
                    <th>Delete</th>
                </tr>
            </thead>

            <tbody>
            @if(isset($filesData) && count($filesData))
                @foreach($filesData as $index => $file)

                @php
                    $name = pathinfo($file['name'], PATHINFO_FILENAME);
                    $name = preg_replace('/\s*\(.*?\)\s*/', '', $name);
                    $name = str_replace('-', ' ', $name);
                    $name = explode('_', $name)[0];
                    $municipalityRaw = explode(' ', trim($name))[0];

                    $municipality = strtolower($municipalityRaw);
                    $municipalityDisplay = ucfirst($municipality);
                @endphp

                <tr class="data-row"
                    data-name="{{ strtolower($file['name']) }}"
                    data-category="{{ strtolower($file['category']) }}"
                    data-folder="{{ strtolower($file['folder']) }}"
                    data-municipality="{{ $municipality }}"
                    id="row-{{ $index }}">

                    <td>{{ $file['name'] }}</td>
                    <td>{{ $file['category'] }}</td>
                    <td>{{ $file['folder'] }}</td>
                    <td>{{ $municipalityDisplay }}</td>

                    <td>
                        <a href="{{ $file['url'] }}" target="_blank" class="btn-open">Open</a>
                    </td>

                    <td>
                        <button class="btn-delete"
                            data-path="{{ $file['path'] }}"
                            data-id="{{ $index }}"
                            onclick="deleteFile(this)">
                            Delete
                        </button>
                    </td>
                </tr>

                @endforeach
            @else
                <tr>
                    <td colspan="6" class="empty">❌ No files found</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div class="pagination">
        <button onclick="prevPage()">⬅ Prev</button>
        <span id="pageInfo"></span>
        <button onclick="nextPage()">Next ➡</button>
    </div>

</div>
<script>
let currentPage = 1;
const rowsPerPage = 10;

const rows = Array.from(document.querySelectorAll('.data-row'));
let filteredRows = [...rows]; // ✅ IMPORTANT FIX

// 🔽 POPULATE FILTERS
function populateFilters() {
    const categories = new Set();
    const folders = new Set();
    const municipalities = new Set();

    rows.forEach(row => {
        if (row.dataset.category) categories.add(row.dataset.category);
        if (row.dataset.folder) folders.add(row.dataset.folder);
        if (row.dataset.municipality) municipalities.add(row.dataset.municipality);
    });

    const catFilter = document.getElementById('categoryFilter');
    const folderFilter = document.getElementById('folderFilter');
    const muniFilter = document.getElementById('municipalityFilter');

    Array.from(categories).sort().forEach(c => {
        catFilter.innerHTML += `<option value="${c}">${c}</option>`;
    });

    Array.from(folders).sort().forEach(f => {
        folderFilter.innerHTML += `<option value="${f}">${f}</option>`;
    });

    Array.from(municipalities).sort().forEach(m => {
        const display = m.charAt(0).toUpperCase() + m.slice(1);
        muniFilter.innerHTML += `<option value="${m}">${display}</option>`;
    });
}

// 🔎 FILTERING
function applyFilters() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value;
    const folder = document.getElementById('folderFilter').value;
    const municipality = document.getElementById('municipalityFilter').value;

    filteredRows = rows.filter(row => {
        const name = row.dataset.name;
        const cat = row.dataset.category;
        const fol = row.dataset.folder;
        const muni = row.dataset.municipality;

        const matchSearch =
            name.includes(search) ||
            cat.includes(search) ||
            fol.includes(search) ||
            muni.includes(search);

        const matchCategory = !category || cat === category;
        const matchFolder = !folder || fol === folder;
        const matchMunicipality = !municipality || muni === municipality;

        return matchSearch && matchCategory && matchFolder && matchMunicipality;
    });

    currentPage = 1;
    paginate();
}

// 📄 PAGINATION (FIXED)
function paginate() {
    // hide all first
    rows.forEach(row => row.style.display = 'none');

    const totalPages = Math.ceil(filteredRows.length / rowsPerPage);

    filteredRows.forEach((row, index) => {
        if (
            index >= (currentPage - 1) * rowsPerPage &&
            index < currentPage * rowsPerPage
        ) {
            row.style.display = '';
        }
    });

    document.getElementById('pageInfo').innerText =
        `Page ${currentPage} of ${totalPages || 1}`;
}

// ➡ NEXT
function nextPage() {
    const totalPages = Math.ceil(filteredRows.length / rowsPerPage);

    if (currentPage < totalPages) {
        currentPage++;
        paginate();
    }
}

// ⬅ PREV
function prevPage() {
    if (currentPage > 1) {
        currentPage--;
        paginate();
    }
}

//  DELETE
async function deleteFile(btn) {
    const path = btn.getAttribute('data-path');
    const rowId = btn.getAttribute('data-id');

    if (!confirm('Delete this file?')) return;

    const response = await fetch('/map/delete', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ path: path })
    });

    const result = await response.json();

    if (response.ok) {
        document.getElementById('row-' + rowId).remove();
        alert(result.message || 'Deleted. Other users have been notified.');

        // 🔥 update rows after delete
        location.reload(); // simplest reliable fix
    } else {
        alert(result.message);
    }
}

async function deleteFolder(btn) {
    const folder = btn.getAttribute('data-folder');
    const folderId = btn.getAttribute('data-id');

    if (!confirm('Delete this folder and all files inside it?')) return;

    const response = await fetch('/map/delete-folder', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ folder: folder })
    });

    const result = await response.json();

    if (response.ok) {
        const item = document.getElementById('folder-' + folderId);
        if (item) item.remove();
        alert(result.message || 'Folder deleted.');
        location.reload();
    } else {
        alert(result.message || 'Folder delete failed.');
    }
}

// INIT
populateFilters();
paginate();
</script>

@endsection
