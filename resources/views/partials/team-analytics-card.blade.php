@php
    $analytics = $analytics ?? [];
    $monthlyUploadsTotal = $analytics['monthlyUploadsTotal'] ?? 0;
    $weeklyUploadsTotal = $analytics['weeklyUploadsTotal'] ?? 0;
    $completionRate = $analytics['completionRate'] ?? 0;
    $validatedCount = $analytics['validatedCount'] ?? 0;
    $ongoingCount = $analytics['ongoingCount'] ?? 0;
    $notValidatedCount = $analytics['pendingCount'] ?? 0;
    $monthRangeLabel = $analytics['monthRangeLabel'] ?? '';
    $weekRangeLabel = $analytics['weekRangeLabel'] ?? '';
@endphp

<div class="ui-card">
    <div class="section-title">
        Analytics
        <span style="font-size: 12px; color: #a1a1aa; font-weight: 500;">Monthly and weekly upload summary</span>
    </div>
    <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 18px;">
        <div style="padding: 10px 14px; border-radius: 14px; background: #f8fafc; border: 1px solid #e4e4e7; min-width: 160px;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8;">Uploaded This 6 Months</div>
            <div style="font-size: 22px; font-weight: 700; color: #18181b; margin-top: 2px;">{{ $monthlyUploadsTotal }}</div>
        </div>
        <div style="padding: 10px 14px; border-radius: 14px; background: #f8fafc; border: 1px solid #e4e4e7; min-width: 160px;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8;">Uploaded This Week</div>
            <div style="font-size: 22px; font-weight: 700; color: #18181b; margin-top: 2px;">{{ $weeklyUploadsTotal }}</div>
        </div>
        <div style="padding: 10px 14px; border-radius: 14px; background: #f0fdf4; border: 1px solid #bbf7d0; min-width: 160px;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #15803d;">Completion Rate</div>
            <div style="font-size: 22px; font-weight: 700; color: #166534; margin-top: 2px;">{{ number_format($completionRate, $completionRate == floor($completionRate) ? 0 : 1) }}%</div>
        </div>
        <div style="padding: 10px 14px; border-radius: 14px; background: #fffbeb; border: 1px solid #fde68a; min-width: 160px;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #b45309;">On-Going</div>
            <div style="font-size: 22px; font-weight: 700; color: #92400e; margin-top: 2px;">{{ $ongoingCount }}</div>
        </div>
        <div style="padding: 10px 14px; border-radius: 14px; background: #ecfdf5; border: 1px solid #bbf7d0; min-width: 160px;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #15803d;">Validated</div>
            <div style="font-size: 22px; font-weight: 700; color: #166534; margin-top: 2px;">{{ $validatedCount }}</div>
        </div>
        <div style="padding: 10px 14px; border-radius: 14px; background: #fff7ed; border: 1px solid #fed7aa; min-width: 160px;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #c2410c;">Not Validated</div>
            <div style="font-size: 22px; font-weight: 700; color: #7c2d12; margin-top: 2px;">{{ $notValidatedCount }}</div>
        </div>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px;">
        <div>
            <p style="font-size: 13px; font-weight: 600; margin-bottom: 4px; color: #71717a;">Monthly Upload Activity</p>
            <p style="font-size: 11px; color: #a1a1aa; margin-bottom: 15px;">{{ $monthRangeLabel }}</p>
            <div class="chart-wrapper"><canvas id="monthlyChart"></canvas></div>
        </div>
        <div>
            <p style="font-size: 13px; font-weight: 600; margin-bottom: 4px; color: #71717a;">Weekly Upload Activity</p>
            <p style="font-size: 11px; color: #a1a1aa; margin-bottom: 15px;">{{ $weekRangeLabel }}</p>
            <div class="chart-wrapper"><canvas id="weeklyChart"></canvas></div>
        </div>
    </div>
</div>
