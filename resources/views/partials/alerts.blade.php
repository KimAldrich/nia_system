@if (session('error') || session('status') || $errors->any())
    <div class="app-alert-stack">
        @if (session('error'))
            <div class="app-alert app-alert-error" role="alert" aria-live="polite">
                <span class="app-alert-icon" aria-hidden="true">!</span>
                <div>
                    <div class="app-alert-title">Something went wrong</div>
                    <div>{{ session('error') }}</div>
                </div>
            </div>
        @endif

        @if (session('status'))
            <div class="app-alert app-alert-info" role="status" aria-live="polite">
                <span class="app-alert-icon" aria-hidden="true">i</span>
                <div>
                    <div class="app-alert-title">Notice</div>
                    <div>{{ session('status') }}</div>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="app-alert app-alert-error" role="alert" aria-live="assertive">
                <span class="app-alert-icon" aria-hidden="true">!</span>
                <div>
                    <div class="app-alert-title">
                        {{ $errors->count() === 1 ? 'Please fix this issue' : 'Please fix the following issues' }}
                    </div>
                    <ul class="app-alert-list">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
@endif
