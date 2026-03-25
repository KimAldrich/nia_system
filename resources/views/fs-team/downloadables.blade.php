@extends('layouts.app')
@section('title', 'Downloadables')

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Base Styling */
        /* Add this line right here to fix all width/padding calculations */
        * {
            box-sizing: border-box;
        }

        /* Base Styling */
        .content {
            background-color: #f7f8fa;
            font-family: 'Poppins', sans-serif;
            padding: 40px;
            color: #111;
        }

        .header-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .content {
            background-color: #f7f8fa;
            font-family: 'Poppins', sans-serif;
            padding: 40px;
            color: #111;
        }

        .header-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .header-desc {
            color: #a1a1aa;
            font-size: 13px;
            margin-bottom: 25px;
            font-weight: 500;
        }

        /* Minimalist Tab Navigation */
        .tab-nav {
            display: flex;
            border-bottom: 1px solid #e4e4e7;
            margin-bottom: 25px;
            gap: 20px;
        }

        .tab-btn {
            background: transparent;
            border: none;
            padding: 10px 15px;
            font-size: 14px;
            font-weight: 600;
            color: #a1a1aa;
            cursor: pointer;
            margin-bottom: -1px;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
            font-family: 'Poppins', sans-serif;
        }

        .tab-btn:hover {
            color: #18181b;
        }

        .tab-btn.active {
            color: #18181b;
            border-bottom: 2px solid #18181b;
        }

        .tab-pane {
            display: none;
            animation: fadeIn 0.2s ease-in-out;
        }

        .tab-pane.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Sleek, Compact Cards */
        .ui-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 18px;
            border: 1px solid #e4e4e7;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .ui-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.04);
        }

        /* Modern Buttons & Forms */
        .btn-dark {
            background: #18181b;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            display: block;
            transition: 0.2s;
            border: none;
            cursor: pointer;
            width: 100%;
            font-family: 'Poppins', sans-serif;
        }

        .btn-dark:hover {
            background: #3f3f46;
        }

        .btn-outline {
            background: #ffffff;
            color: #18181b;
            border: 1px solid #d4d4d8;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            font-family: 'Poppins', sans-serif;
        }

        .btn-outline:hover {
            border-color: #18181b;
            background: #f4f4f5;
        }

        .file-input-wrapper {
            display: flex;
            gap: 8px;
            margin-top: 6px;
            align-items: center;
        }

        .file-input-sm {
            width: 100%;
            font-size: 11px;
            padding: 5px 8px;
            border: 1px dashed #d4d4d8;
            border-radius: 6px;
            background: #fafafa;
            color: #71717a;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
        }

        /* Modern Uploader Styles (Compact) */
        .modern-uploader {
            display: flex;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            overflow: hidden;
            border: 1px solid #e4e4e7;
            max-width: 800px;
            min-height: 350px;
        }

        .uploader-left {
            flex: 1;
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-right: 1px solid #e4e4e7;
            position: relative;
            transition: background 0.2s ease;
        }

        .uploader-left:hover,
        .uploader-left.dragover {
            background: #fafafa;
        }

        .file-input-hidden {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 10;
        }

        .upload-icon {
            width: 48px;
            height: 48px;
            margin-bottom: 15px;
            color: #18181b;
        }

        .upload-title {
            color: #18181b;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .upload-or {
            color: #a1a1aa;
            font-size: 12px;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .browse-btn {
            background: #18181b;
            color: white;
            padding: 8px 24px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            border: none;
            pointer-events: none;
            font-family: 'Poppins', sans-serif;
        }

        .uploader-right {
            flex: 1;
            padding: 30px;
            display: flex;
            flex-direction: column;
            background: #ffffff;
        }

        .file-list-container {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 15px;
        }

        .empty-state {
            color: #a1a1aa;
            font-size: 13px;
            text-align: center;
            margin-top: 40px;
            font-weight: 500;
        }

        .file-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f4f4f5;
            animation: slideIn 0.2s ease;
        }

        .file-type-ring {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2px solid #18181b;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #18181b;
            font-size: 10px;
            font-weight: bold;
            margin-right: 12px;
        }

        .file-details {
            flex: 1;
        }

        .file-name {
            font-size: 13px;
            color: #18181b;
            font-weight: 600;
            margin: 0 0 2px 0;
        }

        .file-size {
            font-size: 11px;
            color: #a1a1aa;
            margin: 0;
            font-weight: 500;
        }

        .file-status {
            color: #18181b;
            font-weight: bold;
            font-size: 14px;
            margin-left: 12px;
        }
    </style>

    <h1 class="header-title">Downloadable Forms</h1>
    <p class="header-desc">Access and manage official documents for the regional office and field offices.</p>

    @if(session('success'))
        <div
            style="background: #18181b; color: #ffffff; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
            <svg style="width:18px; height:18px; color:#4ade80;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="tab-nav">
        <button class="tab-btn active" onclick="switchTab(event, 'available-forms')">Available Forms</button>

        @if(auth()->check() && in_array(auth()->user()->role, ['fs_team', 'admin']))
            <button class="tab-btn" onclick="switchTab(event, 'upload-form')">Upload a Form</button>
        @endif
    </div>

    <div id="available-forms" class="tab-pane active">
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px;">

            @forelse($files as $file)
                @php
                    $extension = pathinfo($file->file_path, PATHINFO_EXTENSION);
                @endphp

                <div class="ui-card">
                    <div style="margin-bottom: 15px; border-radius: 8px; overflow: hidden; border: 1px solid #e4e4e7; height: 120px; background: #fafafa; position: relative; transition: opacity 0.2s;"
                        onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">

                        <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank"
                            style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 10; background: transparent; cursor: pointer;"
                            title="Click to view or download document"></a>

                        @if(strtolower($extension) === 'pdf')
                            <iframe src="{{ asset('storage/' . $file->file_path) }}#page=1&view=Fit&toolbar=0&navpanes=0"
                                width="100%" height="100%" scrolling="no"
                                style="border: none; transform: scale(0.95); transform-origin: top center; pointer-events: none; overflow: hidden;">
                            </iframe>
                        @else
                            <div
                                style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                @if(in_array(strtolower($extension), ['xls', 'xlsx']))
                                    <div style="font-size: 32px; margin-bottom: 5px;">📊</div><span
                                        style="font-size: 12px; font-weight: 600; color: #18181b;">Excel Sheet</span>
                                @elseif(in_array(strtolower($extension), ['doc', 'docx']))
                                    <div style="font-size: 32px; margin-bottom: 5px;">📝</div><span
                                        style="font-size: 12px; font-weight: 600; color: #18181b;">Word Doc</span>
                                @else
                                    <div style="font-size: 32px; margin-bottom: 5px;">📁</div><span
                                        style="font-size: 12px; font-weight: 600; color: #18181b;">Document</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div style="flex: 1;">
                        <h4
                            style="margin:0 0 2px 0; font-size: 14px; font-weight: 600; color: #18181b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $file->title }}
                        </h4>
                        <p
                            style="font-size: 11px; color: #a1a1aa; margin: 0 0 15px 0; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $file->original_name }}
                        </p>
                    </div>

                    <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="btn-dark"
                        style="margin-bottom: 15px;">Download</a>

                    @if(auth()->check() && in_array(auth()->user()->role, ['fs_team', 'admin']))
                        <hr style="border: 0; border-top: 1px solid #f4f4f5; margin-bottom: 12px;">
                        <form action="{{ route('fs.downloadables.update', $file->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <label
                                style="font-size: 10px; color: #a1a1aa; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Update
                                File</label>
                            <div class="file-input-wrapper">
                                <input type="file" name="document" required class="file-input-sm">
                                <button type="submit" class="btn-outline">Replace</button>
                            </div>
                        </form>
                    @endif
                </div>
            @empty
                <div
                    style="grid-column: 1 / -1; background: #ffffff; padding: 40px; border-radius: 12px; text-align: center; border: 1px solid #e4e4e7;">
                    <p style="color: #a1a1aa; font-weight: 500; font-size: 13px;">No documents have been uploaded yet.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div id="upload-form" class="tab-pane">
        <form action="{{ route('fs.downloadables.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modern-uploader">
                <div class="uploader-left" id="dropzone">
                    <input type="file" name="document" class="file-input-hidden" id="file-input" required
                        accept=".pdf,.doc,.docx,.xls,.xlsx">

                    <svg class="upload-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5">
                        </path>
                    </svg>

                    <div class="upload-title">Drag & drop file</div>
                    <div class="upload-or">or</div>
                    <button type="button" class="browse-btn">Browse Files</button>
                    <p style="font-size: 11px; color: #a1a1aa; margin-top: 15px; text-align: center; font-weight: 500;">
                        System will auto-name based on file.</p>
                </div>

                <div class="uploader-right">
                    <h3 style="font-size: 14px; font-weight: 600; margin-top: 0; margin-bottom: 15px; color: #18181b;">
                        Upload Queue</h3>
                    <div class="file-list-container" id="file-list">
                        <div class="empty-state">No file selected.</div>
                    </div>
                    <button type="submit" class="btn-dark" id="submit-btn"
                        style="padding: 12px; font-size: 13px; display: none;">Upload Document</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function switchTab(event, tabId) {
            document.querySelectorAll('.tab-pane').forEach(function (pane) { pane.classList.remove('active'); });
            document.querySelectorAll('.tab-btn').forEach(function (btn) { btn.classList.remove('active'); });
            document.getElementById(tabId).classList.add('active');
            event.currentTarget.classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', function () {
            const dropzone = document.getElementById('dropzone');
            const fileInput = document.getElementById('file-input');
            const fileList = document.getElementById('file-list');
            const submitBtn = document.getElementById('submit-btn');

            dropzone.addEventListener('dragover', () => dropzone.classList.add('dragover'));
            dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
            dropzone.addEventListener('drop', () => dropzone.classList.remove('dragover'));

            fileInput.addEventListener('change', function () {
                if (this.files && this.files.length > 0) {
                    const file = this.files[0];
                    let ext = file.name.split('.').pop().substring(0, 3).toUpperCase();
                    let sizeMB = (file.size / (1024 * 1024)).toFixed(2);

                    fileList.innerHTML = `
                                                                    <div class="file-item">
                                                                        <div class="file-type-ring">${ext}</div>
                                                                        <div class="file-details">
                                                                            <h4 class="file-name" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;">${file.name}</h4>
                                                                            <p class="file-size">${sizeMB} MB / ${sizeMB} MB</p>
                                                                        </div>
                                                                        <div class="file-status">✓</div>
                                                                    </div>
                                                                `;
                    submitBtn.style.display = 'block';
                } else {
                    fileList.innerHTML = '<div class="empty-state">No file selected.</div>';
                    submitBtn.style.display = 'none';
                }
            });
        });
    </script>
@endsection