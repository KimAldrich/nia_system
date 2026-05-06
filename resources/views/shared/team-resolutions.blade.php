@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }
        .content { background-color: #f7f8fa; font-family: 'Poppins', sans-serif; padding: 40px; color: #0c4d05; }
        .header-title { font-size: 28px; font-weight: 700; margin-bottom: 8px; letter-spacing: -0.5px; }
        .header-desc { color: #a1a1aa; font-size: 13px; margin-bottom: 25px; font-weight: 500; }
        .tab-nav { display: flex; border-bottom: 1px solid #e4e4e7; margin-bottom: 25px; gap: 20px; }
        .tab-btn { background: transparent; border: none; padding: 10px 15px; font-size: 14px; font-weight: 600; color: #a1a1aa; cursor: pointer; margin-bottom: -1px; border-bottom: 2px solid transparent; transition: all 0.2s; font-family: 'Poppins', sans-serif; }
        .tab-btn:hover { color: #0c4d05; }
        .tab-btn.active { color: #0c4d05; border-bottom: 2px solid #0c4d05; }
        .tab-pane { display: none; animation: fadeIn 0.2s ease-in-out; }
        .tab-pane.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        .resolution-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 16px; align-items: start; }
        .ui-card { background: #ffffff; border-radius: 10px; padding: 18px; border: 1px solid #d8e7d5; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03); display: flex; flex-direction: column; transition: border-color 0.2s, box-shadow 0.2s; min-width: 0; }
        .ui-card:hover { border-color: #0c4d05; box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06); }
        .btn-dark { background: #126e08; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; text-align: center; display: block; transition: 0.2s; border: none; cursor: pointer; width: 100%; font-family: 'Poppins', sans-serif; }
        .btn-dark:hover { background: #0c4d05; }
        .btn-outline { background: #ffffff; color: #0c4d05; border: 1px solid #0c4d05; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; transition: 0.2s; font-family: 'Poppins', sans-serif; }
        .btn-outline:hover { border-color: #0c4d05; background: #ecfdf5; }
        .modern-uploader { display: flex; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03); overflow: hidden; border: 1px solid #e4e4e7; max-width: 800px; min-height: 350px; }
        .uploader-left { flex: 1; padding: 30px; display: flex; flex-direction: column; align-items: center; justify-content: center; border-right: 1px solid #e4e4e7; position: relative; transition: background 0.2s ease; }
        .uploader-left:hover, .uploader-left.dragover { background: #f0fdf4; }
        .file-input-hidden { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10; }
        .upload-icon { width: 48px; height: 48px; margin-bottom: 15px; color: #0c4d05; }
        .upload-title { color: #0c4d05; font-size: 14px; font-weight: 600; margin-bottom: 6px; }
        .upload-or { font-size: 11px; color: #a1a1aa; margin-bottom: 15px; }
        .browse-btn { background: #126e08; color: #fff; border: none; border-radius: 8px; padding: 10px 16px; font-size: 12px; font-weight: 600; font-family: 'Poppins', sans-serif; cursor: pointer; }
        .uploader-right { width: 280px; padding: 24px; display: flex; flex-direction: column; }
        .file-list-container { flex: 1; overflow-y: auto; border: 1px dashed #d4d4d8; border-radius: 10px; padding: 12px; background: #fafafa; }
        .empty-state { color: #a1a1aa; font-size: 12px; text-align: center; padding: 20px 10px; }
        .file-list-item { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 0; border-bottom: 1px solid #e4e4e7; }
        .file-list-item:last-child { border-bottom: none; }
        .file-list-name { font-size: 12px; font-weight: 600; color: #18181b; word-break: break-word; }
        .file-remove { background: transparent; border: none; color: #ef4444; font-size: 11px; font-weight: 600; cursor: pointer; }
        .resolution-card-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 14px; padding-bottom: 14px; border-bottom: 1px solid #edf2ed; }
        .resolution-title-wrap { min-width: 0; flex: 1; }
        .resolution-card-title { margin: 0; font-size: 15px; line-height: 1.35; font-weight: 700; color: #18181b; overflow-wrap: anywhere; }
        .resolution-file-count { display: inline-flex; margin-top: 7px; color: #71717a; font-size: 11px; font-weight: 600; }
        .resolution-files-grid { display: flex; flex-direction: column; gap: 10px; }
        .attachment-card { display: grid; grid-template-columns: 46px minmax(0, 1fr) auto; gap: 12px; align-items: center; padding: 12px; border: 1px solid #e4e4e7; border-radius: 8px; background: #fafafa; min-width: 0; }
        .attachment-card:hover { background: #ffffff; border-color: #b9d8b5; }
        .attachment-preview { width: 46px; height: 46px; border-radius: 8px; border: 1px solid #d8e7d5; background: #ecfdf5; position: relative; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .attachment-link { position: absolute; inset: 0; z-index: 2; background: transparent; cursor: pointer; }
        .attachment-type { color: #0c4d05; font-size: 10px; font-weight: 800; letter-spacing: 0; text-transform: uppercase; max-width: 38px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .attachment-fallback { display: contents; }
        .attachment-fallback-icon, .attachment-fallback-label { display: none; }
        .attachment-meta { flex: 1; min-width: 0; }
        .attachment-name { margin: 0 0 4px 0; font-size: 13px; line-height: 1.35; font-weight: 600; color: #18181b; overflow-wrap: anywhere; }
        .attachment-date { font-size: 11px; color: #71717a; margin: 0; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .attachment-actions { display: flex; gap: 8px; align-items: center; }
        .attachment-actions .btn-dark, .attachment-actions .btn-outline { width: auto; min-width: 74px; padding: 8px 10px; }
        .available-files-empty { grid-column: 1 / -1; background: #ffffff; padding: 34px; border-radius: 10px; text-align: center; border: 1px solid #e4e4e7; }
        .available-files-empty p { color: #71717a; font-weight: 500; font-size: 13px; margin: 0; }
        @media (max-width: 640px) {
            .resolution-card-header { flex-direction: column; align-items: stretch; }
            .resolution-list { grid-template-columns: 1fr; }
            .attachment-card { grid-template-columns: 42px minmax(0, 1fr); }
            .attachment-preview { width: 42px; height: 42px; }
            .attachment-actions { grid-column: 1 / -1; justify-content: flex-start; }
        }
    </style>

    <h1 class="header-title">{{ $headerTitle }}</h1>
    <p class="header-desc">{{ $headerDesc }}</p>

    <div class="tab-nav">
        <button class="tab-btn active" onclick="switchTab(event, 'available-resolutions')">Available Files</button>
        @if (auth()->check() && in_array(auth()->user()->role, [$teamRole, 'admin']))
            <button class="tab-btn" onclick="switchTab(event, 'upload-resolution')">Upload Files</button>
        @endif
    </div>

    <div id="available-resolutions" class="tab-pane active">
        @include('partials.resolution-attachments-manager', [
            'resolutions' => $resolutions,
            'deleteRouteName' => $deleteRouteName,
            'canDelete' => auth()->check() && in_array(auth()->user()->role, [$teamRole, 'admin']),
        ])
    </div>

    <div id="upload-resolution" class="tab-pane">
        <form action="{{ route($uploadRouteName) }}" method="POST" enctype="multipart/form-data"
            data-async-target="#resolutionsList" data-async-reset="true" data-file-upload-feedback="true"
            data-async-success-modal="#appFeedbackModal" data-async-success-title="Upload Complete"
            data-async-error-modal="#appFeedbackModal" data-async-error-title="Upload Failed">
            @csrf
            <div class="modern-uploader">
                <div class="uploader-left" id="dropzone">
                    <input type="file" name="documents[]" class="file-input-hidden" id="file-input" required multiple
                        accept=".pdf,.doc,.docx,.xls,.xlsx">
                    <svg class="upload-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"></path>
                    </svg>
                    <div class="upload-title">Drag & drop files</div>
                    <div class="upload-or">or</div>
                    <button type="button" class="browse-btn">Browse Files</button>
                    <p style="font-size: 11px; color: #a1a1aa; margin-top: 15px; text-align: center; font-weight: 500;">Files with the same cleaned title will be grouped under one status entry.</p>
                </div>

                <div class="uploader-right">
                    <h3 style="font-size: 14px; font-weight: 600; margin-top: 0; margin-bottom: 15px; color: #18181b;">Upload Queue</h3>
                    <div class="file-list-container" id="file-list">
                        <div class="empty-state">No file selected.</div>
                    </div>
                    <button type="submit" class="btn-dark" id="submit-btn" style="padding: 12px; font-size: 13px; display: none; margin-top: 15px;">Upload Files</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function switchTab(event, tabId) {
            document.querySelectorAll('.tab-pane').forEach((pane) => pane.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach((button) => button.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            event.currentTarget.classList.add('active');
        }

        const fileInput = document.getElementById('file-input');
        const fileList = document.getElementById('file-list');
        const submitBtn = document.getElementById('submit-btn');
        const dropzone = document.getElementById('dropzone');
        let selectedFiles = [];

        function renderFileList() {
            if (!fileList || !submitBtn || !fileInput) return;
            fileList.innerHTML = '';

            if (!selectedFiles.length) {
                fileList.innerHTML = '<div class="empty-state">No file selected.</div>';
                submitBtn.style.display = 'none';
                fileInput.value = '';
                return;
            }

            selectedFiles.forEach((file, index) => {
                const item = document.createElement('div');
                item.className = 'file-list-item';
                item.innerHTML = `<div class="file-list-name">${file.name}</div><button type="button" class="file-remove" data-index="${index}">Remove</button>`;
                fileList.appendChild(item);
            });

            fileList.querySelectorAll('.file-remove').forEach((button) => {
                button.addEventListener('click', () => {
                    selectedFiles.splice(Number(button.dataset.index), 1);
                    syncInputFiles();
                    renderFileList();
                });
            });

            submitBtn.style.display = 'block';
        }

        function syncInputFiles() {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach((file) => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;
        }

        function mergeFiles(files) {
            selectedFiles = [...selectedFiles, ...Array.from(files)];
            syncInputFiles();
            renderFileList();
        }

        if (fileInput) {
            fileInput.addEventListener('change', (event) => mergeFiles(event.target.files));
        }

        if (dropzone) {
            ['dragenter', 'dragover'].forEach((eventName) => {
                dropzone.addEventListener(eventName, (event) => {
                    event.preventDefault();
                    dropzone.classList.add('dragover');
                });
            });

            ['dragleave', 'drop'].forEach((eventName) => {
                dropzone.addEventListener(eventName, (event) => {
                    event.preventDefault();
                    dropzone.classList.remove('dragover');
                });
            });

            dropzone.addEventListener('drop', (event) => mergeFiles(event.dataTransfer.files));
            dropzone.querySelector('.browse-btn')?.addEventListener('click', () => fileInput.click());
        }
    </script>
@endsection
