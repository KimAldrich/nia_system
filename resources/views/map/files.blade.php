@extends('layouts.app')

@section('content')
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<h2>Uploaded Files</h2>

<table border="1" width="100%">
    <thead>
        <tr>
            <th>Name</th>
            <th>Category</th>
            <th>Folder</th>
            <th>View</th>
            <th>Delete</th>
        </tr>
    </thead>

<tbody>
@if(isset($filesData) && count($filesData))
    @foreach($filesData as $index => $file)
        <tr id="row-{{ $index }}">
            <td>{{ $file['name'] }}</td>
            <td>{{ $file['category'] }}</td>
            <td>{{ $file['folder'] }}</td>
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
        <td colspan="5">❌ No files found</td>
    </tr>
@endif
</tbody>
</table>
<script>
    async function deleteFile(btn) {

    const path = btn.getAttribute('data-path');
    const rowId = btn.getAttribute('data-id');

    console.log("Deleting:", path); // DEBUG

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

    console.log(result); // DEBUG

    if (response.ok) {
        document.getElementById('row-' + rowId).remove();
        alert('Deleted');
    } else {
        alert(result.message);
    }
}
</script>
<!-- <script>
async function deleteFile(path, rowId) {

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
    } else {
        alert(result.message);
    }
}
</script> -->

@endsection
