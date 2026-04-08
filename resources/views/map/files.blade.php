@extends('layouts.app')

@section('content')
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<a href="/map">Back to Map</a>
<h2>Uploaded Files</h2>

<!-- 🔎 SEARCH + FILTERS -->
<div style="margin-bottom: 15px;">
    <input type="text" id="searchInput" placeholder="Search..." onkeyup="applyFilters()">

    <select id="categoryFilter" onchange="applyFilters()">
        <option value="">All Categories</option>
    </select>

    <select id="folderFilter" onchange="applyFilters()">
        <option value="">All Folders</option>
    </select>

    <select id="municipalityFilter" onchange="applyFilters()">
        <option value="">All Municipalities</option>
    </select>
</div>

<table border="1" width="100%">
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
    // 🔥 CLEAN MUNICIPALITY FROM FILENAME
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
        <a href="{{ $file['url'] }}" target="_blank">Open</a>
    </td>
    <td>
        <button
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
    <td colspan="6">❌ No files found</td>
</tr>
@endif
</tbody>
</table>

<!-- 📄 PAGINATION -->
<div style="margin-top: 10px;">
    <button onclick="prevPage()">Prev</button>
    <span id="pageInfo"></span>
    <button onclick="nextPage()">Next</button>
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
        alert('Deleted');

        // 🔥 update rows after delete
        location.reload(); // simplest reliable fix
    } else {
        alert(result.message);
    }
}

// INIT
populateFilters();
paginate();
</script>

@endsection
