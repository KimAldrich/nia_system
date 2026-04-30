<div id="resolutionsList" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
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
                        'original_name' => $resolution->original_name,
                        'created_at' => $resolution->created_at,
                    ],
                ]);
            }
        @endphp

        <div class="ui-card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 14px;">
                <div style="min-width: 0;">
                    <h4 style="margin: 0 0 4px 0; font-size: 15px; font-weight: 700; color: #18181b; word-break: break-word;">
                        {{ $resolution->title }}
                    </h4>
                    <p style="margin: 0; font-size: 11px; color: #94a3b8; font-weight: 500;">
                        {{ $files->count() }} {{ \Illuminate\Support\Str::plural('file', $files->count()) }}
                    </p>
                </div>

                @if (\App\Models\IaResolution::isCompletedStatus($resolution->status))
                    <span class="status-badge badge-dark">{{ $statusLabel }}</span>
                @elseif($resolution->status == \App\Models\IaResolution::STATUS_ONGOING)
                    <span class="status-badge badge-light">On-Going</span>
                @else
                    <span class="status-badge badge-outline">{{ $statusLabel }}</span>
                @endif
            </div>

            <div style="display: grid; gap: 10px;">
                @foreach($files as $file)
                    <div style="border: 1px solid #e4e4e7; border-radius: 10px; padding: 12px; background: #fafafa;">
                        <div style="margin-bottom: 10px; min-width: 0;">
                            <div style="font-size: 12px; font-weight: 600; color: #18181b; word-break: break-word;">
                                {{ $file->original_name }}
                            </div>
                            <div style="font-size: 10px; color: #94a3b8; margin-top: 3px;">
                                Uploaded {{ optional($file->created_at)->format('M d, Y') ?? optional($resolution->created_at)->format('M d, Y') }}
                            </div>
                        </div>

                        <div style="display: flex; gap: 8px;">
                            <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="btn-dark"
                                style="flex: 1; padding: 10px 14px; text-align: center; min-width: 100px;">
                                Download
                            </a>

                            @if (!empty($canDelete))
                                <form action="{{ route($deleteRouteName, $file->id) }}" method="POST"
                                    style="margin: 0; flex: 1;"
                                    data-async-target="#resolutionsList"
                                    data-async-confirm="Delete this file from {{ $resolution->title }}?"
                                    data-async-success="silent">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-outline"
                                        style="width: 100%; padding: 10px 14px; min-width: 100px; background: #f87171; color: #fff; border: 1px solid #f87171;">
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
        <div style="grid-column: 1 / -1; background: #ffffff; padding: 40px; border-radius: 12px; text-align: center; border: 1px solid #e4e4e7;">
            <p style="color: #a1a1aa; font-weight: 500; font-size: 13px;">No files have been uploaded yet.</p>
        </div>
    @endforelse
</div>
