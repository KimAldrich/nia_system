<div id="resolutionsList" class="resolution-list">
    @forelse($resolutions as $resolution)
        @php
            $resolutionTeam = $resolution->team;
            $statusLabel = \App\Models\IaResolution::displayStatusLabel($resolution->status, $resolutionTeam);
            $files = $resolution->files;

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
                <div class="resolution-title-wrap">
                    <h4 class="resolution-card-title">{{ $resolution->title }}</h4>
                    <span class="resolution-file-count">
                        {{ $files->count() }} {{ \Illuminate\Support\Str::plural('file', $files->count()) }}
                    </span>
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
                        $typeLabel = strtoupper($extension ?: 'FILE');
                        $uploadedDate = optional($file->created_at)->format('M d, Y') ?? optional($resolution->created_at)->format('M d, Y');
                    @endphp

                    <div class="attachment-card">
                        <div class="attachment-preview">
                            <a href="{{ $file->file_url }}" target="_blank" class="attachment-link"
                                title="Open {{ $file->original_name }}"></a>
                            <span class="attachment-type">{{ $typeLabel }}</span>
                        </div>

                        <div class="attachment-meta">
                            <h5 class="attachment-name">{{ $file->original_name }}</h5>
                            <p class="attachment-date">Uploaded {{ $uploadedDate }}</p>
                        </div>

                        <div class="attachment-actions">
                            <a href="{{ $file->file_url }}" target="_blank" class="btn-dark" style="text-align: center;">
                                Open
                            </a>

                            @if (!empty($canDelete))
                                <form action="{{ route($deleteRouteName, $file->id) }}" method="POST"
                                    style="margin: 0;"
                                    data-async-target="#resolutionsList"
                                    data-async-confirm="Delete this file from {{ $resolution->title }}?"
                                    data-async-success="silent">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-outline"
                                        style="background: #f87171; color: #fff; border: 1px solid #f87171;">
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
        <div class="available-files-empty">
            <p>No files have been uploaded yet.</p>
        </div>
    @endforelse
</div>
