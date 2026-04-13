@extends('layouts.app')
@section('title', 'Master Dashboard')

@section('content')

    <style>
        /* Calendar Specific Soft-UI Styles */
        .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .calendar-header h4 { margin: 0; font-size: 15px; font-weight: 700; color: #1e293b; }
        .calendar-carousel { display: flex; align-items: center; gap: 10px; }
        
        .nav-btn { background: #f8fafc; border: none; border-radius: 8px; width: 32px; height: 32px; cursor: pointer; color: #64748b; font-weight: bold; font-size: 14px; transition: 0.2s;}
        .nav-btn:hover { background: #e2e8f0; color: #1e293b; }
        
        .calendar-viewport { flex: 1; }
        .month-block { display: none; }
        .month-block.active { display: block; }
        
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; row-gap: 15px; margin-bottom: 15px; }
        .day-name { font-size: 11px; font-weight: 600; color: #a0aec0; margin-bottom: 5px; text-transform: uppercase; }
        .day-num { font-size: 13px; font-weight: 500; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; margin: 0 auto; border-radius: 50%; color: #475569; transition: 0.2s;}
        .day-num.empty { visibility: hidden; }
        
        .day-num.today { background: #4f46e5 !important; color: #ffffff !important; font-weight: 700; border: none !important; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3); }

        .mini-event { display: flex; align-items: center; gap: 15px; padding: 12px 0; border-top: 1px solid #f1f5f9; }
        .mini-event-date { font-size: 16px; font-weight: 700; color: #4f46e5; min-width: 30px; text-align: center; background: #e0e7ff; padding: 5px; border-radius: 8px;}
        .mini-event-title { font-size: 13px; font-weight: 600; color: #1e293b; margin: 0; }
        .mini-event-time { font-size: 11px; color: #a0aec0; margin: 0; }
        
        .legend-item { display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; }
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; }
    </style>

    @php
        $totalResolutions = $resolutions->count();
        $validatedResolutions = $resolutions->where('status', 'validated')->count();
        $pendingResolutions = $resolutions->whereIn('status', ['on-going', 'not-validated'])->count();
        $totalDownloads = $downloadables->count();
    @endphp

    <h2 class="page-title">{{ $pageTitle ?? 'Master Dashboard' }}</h2>

    <div class="kpi-grid">
        
        <div class="kpi-card">
            <div class="kpi-title">Total Validated</div>
            <div class="kpi-value">{{ $validatedResolutions }}</div>
            <div class="kpi-icon green">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="kpi-trend">
                <span class="trend-up">+ Active</span>
                <span class="trend-text">Resolutions</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-title">Pending Action</div>
            <div class="kpi-value">{{ $pendingResolutions }}</div>
            <div class="kpi-icon orange">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="kpi-trend">
                <span class="trend-down">Attention</span>
                <span class="trend-text">Needed</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-title">Total Forms</div>
            <div class="kpi-value">{{ $totalDownloads }}</div>
            <div class="kpi-icon blue">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            </div>
            <div class="kpi-trend">
                <span class="trend-text">Available to download</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-title">Upcoming Events</div>
            <div class="kpi-value">{{ isset($events) ? $events->count() : '0' }}</div>
            <div class="kpi-icon purple">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div class="kpi-trend">
                <span class="trend-text">Scheduled this month</span>
            </div>
        </div>

    </div>

    <div class="dashboard-main-grid">
        
        <div class="main-column">
            <div class="card">
                <div class="section-title">
                    <span>Recent Resolutions Overview</span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Document Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resolutions->take(5) as $res)
                            <tr>
                                <td>
                                    <a href="{{ asset('storage/' . $res->file_path) }}" target="_blank" style="color: #1e293b; font-weight: 600; text-decoration: none;">
                                        {{ $res->title }}
                                    </a><br>
                                    <span style="font-size: 11px; color: #94a3b8;">{{ $res->created_at->format('M d, Y') }} | Team: {{ strtoupper(str_replace('_', ' ', $res->team)) }}</span>
                                </td>
                                <td>
                                    @if ($res->status == 'validated')
                                        <span class="badge badge-completed">Validated</span>
                                    @elseif($res->status == 'on-going')
                                        <span class="badge badge-progress">On-Going</span>
                                    @else
                                        <span class="badge badge-pending">Not-Validated</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" style="text-align:center; color:#94a3b8; padding: 30px 0;">No recent resolutions.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="side-column">
            <div class="card">
                <div class="section-title" style="margin-bottom: 15px;">
                    <span>Calendar</span>
                    <svg width="20" height="20" fill="none" stroke="#a0aec0" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
                </div>
                
                <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #f1f5f9;">
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        @forelse($categories ?? [] as $cat)
                            <div class="legend-item">
                                <div class="legend-dot" style="background: {{ $cat->color }};"></div>
                                {{ $cat->name }}
                            </div>
                        @empty
                            <p style="font-size: 11px; color: #a0aec0;">No tags available.</p>
                        @endforelse
                    </div>
                </div>

                @php
                    $today = \Carbon\Carbon::now();
                    $eventDays = isset($events) ? $events->map(function ($e) { return $e->event_date->format('j'); })->toArray() : [];
                @endphp

                <div class="calendar-carousel">
                    <button class="nav-btn" id="prevMonthBtn" onclick="changeMonth(-1)">&lt;</button>

                    <div class="calendar-viewport">
                        @php
                            $currentYear = \Carbon\Carbon::now()->year;
                        @endphp

                        @for ($m = 1; $m <= 12; $m++)
                            @php
                                $monthDate = \Carbon\Carbon::createFromDate($currentYear, $m, 1);
                                $daysInMonth = $monthDate->daysInMonth;
                                $firstDayOfWeek = $monthDate->dayOfWeek;

                                $eventsForMonth = collect($events ?? [])->filter(function ($e) use ($currentYear, $m) {
                                    return $e->event_date->year == $currentYear && $e->event_date->month == $m;
                                })->groupBy(function ($e) {
                                    return $e->event_date->format('j');
                                });
                            @endphp

                            <div class="month-block" id="month-{{ $m }}">
                                <div class="calendar-header">
                                    <h4>{{ $monthDate->format('F Y') }}</h4>
                                </div>

                                <div class="calendar-grid">
                                    <div class="day-name">Sun</div><div class="day-name">Mon</div><div class="day-name">Tue</div>
                                    <div class="day-name">Wed</div><div class="day-name">Thu</div><div class="day-name">Fri</div><div class="day-name">Sat</div>

                                    @for ($i = 0; $i < $firstDayOfWeek; $i++)
                                        <div class="day-num empty"></div>
                                    @endfor

                                    @for ($day = 1; $day <= $daysInMonth; $day++)
                                        @php
                                            $dayEvents = $eventsForMonth->get($day);
                                            $hasEvent = $dayEvents ? true : false;
                                            $isToday = $day == $today->day && $m == $today->month;
                                            
                                            // Dynamic Category Color
                                            $ringColor = $hasEvent && $dayEvents->first()->category ? $dayEvents->first()->category->color : '#18181b';
                                        @endphp

                                        <div class="day-num {{ $hasEvent ? 'has-event' : '' }} {{ $isToday ? 'today' : '' }}"
                                             style="{{ $hasEvent && !$isToday ? 'border: 2px solid ' . $ringColor . '; color: ' . $ringColor . '; font-weight: 700;' : '' }}">
                                            {{ $day }}
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        @endfor
                    </div>

                    <button class="nav-btn" id="nextMonthBtn" onclick="changeMonth(1)">&gt;</button>
                </div>

                <div style="margin-top: 15px;">
                    <p style="font-size: 11px; font-weight: 700; color: #a0aec0; text-transform: uppercase; margin-bottom: 10px;">
                        Upcoming Schedule
                    </p>

                    @if (isset($events) && $events->count() > 0)
                        @foreach ($events as $event)
                            <div class="mini-event">
                                <div class="mini-event-date">{{ $event->event_date->format('d') }}</div>
                                <div>
                                    <h4 class="mini-event-title">{{ $event->title }}</h4>
                                    <p class="mini-event-time">{{ $event->event_time }}</p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p style="font-size: 12px; color: #a0aec0; text-align: center; margin-top: 20px;">No upcoming events.</p>
                    @endif
                </div>

            </div>
        </div>

    </div>

    <script>
        let activeMonth = new Date().getMonth() + 1;

        document.addEventListener('DOMContentLoaded', function() {
            updateCalendarView();
        });

        function changeMonth(direction) {
            activeMonth += direction;
            if (activeMonth < 1) activeMonth = 1;
            if (activeMonth > 12) activeMonth = 12;
            updateCalendarView();
        }

        function updateCalendarView() {
            document.querySelectorAll('.month-block').forEach(block => {
                block.classList.remove('active');
            });

            const current = document.getElementById('month-' + activeMonth);
            if (current) current.classList.add('active');

            document.getElementById('prevMonthBtn').disabled = (activeMonth === 1);
            document.getElementById('nextMonthBtn').disabled = (activeMonth === 12);
        }
    </script>
@endsection