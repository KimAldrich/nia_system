<<<<<<< ours
<div id="resolutionsList" class="resolution-list">
=======
<div id="resolutionsList" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px;">
>>>>>>> theirs
    @forelse($resolutions as $resolution)
        @php
            $resolutionTeam = $resolution->team;
            $statusLabel = \App\Models\IaResolution::displayStatusLabel($resolution->status, $resolutionTeam);
            $files = $resolution->files;
<<<<<<< ours

=======
>>>>>>> theirs
            if ($files->isEmpty() && $resolution->file_path) {
                $files = collect([
                    (object) [
                        'id' => $resolution->id,
                        'file_path' => $resolution->file_path,
                        'file_url' => app(\App\Services\DocumentStorageService::class)->url($resolution->file_path),
                        'preview_url' => app(\App\Services\DocumentStorageService::class)->previewUrl($resolution->file_path),
                        'original_name' => $resolution->original_name,
                        'created_at' => $resolution->created_at,
                    ],
                ]);
            }
        @endphp

        <div class="ui-card">
            <div class="resolution-card-header">
<<<<<<< ours
                <div class="resolution-title-wrap">
                    <h4 class="resolution-card-title">{{ $resolution->title }}</h4>
                    <span class="resolution-file-count">
                        {{ $files->count() }} {{ \Illuminate\Support\Str::plural('file', $files->count()) }}
                    </span>
=======
                <div style="min-width: 0;">
                    <h4 class="resolution-card-title">{{ $resolution->title }}</h4>
>>>>>>> theirs
                </div>

                @if (\App\Models\IaResolution::isCompletedStatus($resolution->status))
                    <span class="status-badge badge-dark">{{ $statusLabel }}</span>
                @elseif($resolution->status == \App\Models\IaResolution::STATUS_ONGOING)
                    <span class="status-badge badge-light">On-Going</span>
                @else
                    <span class="status-badge badge-outline">{{ $statusLabel }}</span>
                @endif
            </div>

            <div class="resolution-files-grid">
                @foreach($files as $file)
                    @php
                        $extension = strtolower(pathinfo($file->file_path, PATHINFO_EXTENSION));
<<<<<<< ours
                        $typeLabel = strtoupper($extension ?: 'FILE');
                        $uploadedDate = optional($file->created_at)->format('M d, Y') ?? optional($resolution->created_at)->format('M d, Y');
                    @endphp

                    <div class="attachment-card">
                        <div class="attachment-preview">
                            <a href="{{ $file->file_url }}" target="_blank" class="attachment-link"
                                title="Open {{ $file->original_name }}"></a>
                            <span class="attachment-type">{{ $typeLabel }}</span>
=======
                        $uploadedDate = optional($file->created_at)->format('M d, Y') ?? optional($resolution->created_at)->format('M d, Y');
                    @endphp
                    <div class="attachment-card">
                        <div class="attachment-preview">
                            <a href="{{ $file->file_url }}" target="_blank" class="attachment-link"
                                title="Click to view or download document"></a>

                            @if ($extension === 'pdf')
                                <iframe src="{{ $file->preview_url }}"
                                    class="attachment-frame" scrolling="no"></iframe>
                            @else
                                <div class="attachment-fallback">
                                    @if (in_array($extension, ['xls', 'xlsx']))
                                        <div class="attachment-fallback-icon">📊</div>
                                        <span class="attachment-fallback-label">Excel Sheet</span>
                                    @elseif(in_array($extension, ['doc', 'docx']))
                                        <div class="attachment-fallback-icon">📝</div>
                                        <span class="attachment-fallback-label">Word Doc</span>
                                    @else
                                        <div class="attachment-fallback-icon">📁</div>
                                        <span class="attachment-fallback-label">Document</span>
                                    @endif
                                </div>
                            @endif
>>>>>>> theirs
                        </div>

                        <div class="attachment-meta">
                            <h5 class="attachment-name">{{ $file->original_name }}</h5>
                            <p class="attachment-date">Uploaded {{ $uploadedDate }}</p>
                        </div>

                        <div class="attachment-actions">
<<<<<<< ours
                            <a href="{{ $file->file_url }}" target="_blank" class="btn-dark" style="text-align: center;">
                                Open
=======
                            <a href="{{ $file->file_url }}" target="_blank" class="btn-dark"
                                style="flex: 1; padding: 10px 14px; text-align: center; min-width: 100px;">
                                Download
>>>>>>> theirs
                            </a>

                            @if (!empty($canDelete))
                                <form action="{{ route($deleteRouteName, $file->id) }}" method="POST"
<<<<<<< ours
                                    style="margin: 0;"
=======
                                    style="margin: 0; flex: 1;"
>>>>>>> theirs
                                    data-async-target="#resolutionsList"
                                    data-async-confirm="Delete this file from {{ $resolution->title }}?"
                                    data-async-success="silent">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-outline"
<<<<<<< ours
                                        style="background: #f87171; color: #fff; border: 1px solid #f87171;">
=======
                                        style="width: 100%; padding: 10px 14px; min-width: 100px; background: #f87171; color: #fff; border: 1px solid #f87171;">
>>>>>>> theirs
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
<<<<<<< ours
        <div class="available-files-empty">
            <p>No files have been uploaded yet.</p>
=======
        <div style="grid-column: 1 / -1; background: #ffffff; padding: 40px; border-radius: 12px; text-align: center; border: 1px solid #e4e4e7;">
            <p style="color: #a1a1aa; font-weight: 500; font-size: 13px;">No files have been uploaded yet.</p>
>>>>>>> theirs
        </div>
    @endforelse
</div>
