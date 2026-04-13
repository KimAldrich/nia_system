@extends('layouts.app')
@section('title', 'Programming Team Dashboard')

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        * {
            box-sizing: border-box;
        }

        .content {
            background-color: #f7f8fa;
            font-family: 'Poppins', sans-serif;
            padding: 40px;
            color: #0c4d05;
            /* ✅ */
        }

        .header-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 30px;
            letter-spacing: -0.5px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
        }

        .ui-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            margin-bottom: 30px;
            border: 1px solid #e4e4e7;
        }

        .ui-card.dark {
            background: #0c4d05;
            /* ✅ */
            color: #ffffff;
            border: none;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-hero {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-hero h3 {
            margin: 0 0 5px 0;
            font-size: 18px;
            font-weight: 600;
        }

        .status-hero p {
            margin: 0;
            font-size: 13px;
            color: #a1a1aa;
        }

        .squiggle-line {
            width: 80px;
            height: auto;
            opacity: 0.8;
        }

        .sleek-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sleek-table th {
            text-align: left;
            padding-bottom: 15px;
            color: #a1a1aa;
            font-weight: 500;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #f4f4f5;
        }

        .sleek-table td {
            padding: 15px 0;
            border-bottom: 1px solid #f4f4f5;
            font-size: 13px;
            font-weight: 500;
            vertical-align: middle;
        }

        .sleek-table tr:last-child td {
            border-bottom: none;
            padding-bottom: 0;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
            text-align: center;
            min-width: 90px;
        }

        .badge-dark {
            background: #0c4d05;
            /* ✅ */
            color: #fff;
        }

        .badge-light {
            background: #fda611;
            /* ✅ */
            color: #ffffff;
        }

        .badge-outline {
            border: 1px solid #e4e4e7;
            color: #71717a;
        }

        .status-select {
            padding: 6px 10px;
            border-radius: 8px;
            border: 1px solid #e4e4e7;
            font-family: 'Poppins', sans-serif;
            font-size: 11px;
            font-weight: 600;
            background: #ffffff;
            color: #18181b;
            cursor: pointer;
            outline: none;
            transition: 0.2s;
        }

        .status-select:hover {
            border-color: #18181b;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .calendar-header h4 {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .calendar-carousel {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-btn {
            background: #fff;
            border: 1px solid #0c4d05;
            /* ✅ */
            border-radius: 50%;
            width: 32px;
            height: 32px;
            cursor: pointer;
        }

        .calendar-viewport {
            flex: 1;
        }

        .month-block {
            display: none;
        }

        .month-block.active {
            display: block;
        }

        .cal-nav {
            display: flex;
            gap: 10px;
        }

        .cal-nav button {
            background: none;
            border: 1px solid #0c4d05;
            /* ✅ */
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #a1a1aa;
            transition: 0.2s;
        }

        .cal-nav button:hover {
            border-color: #18181b;
            color: #18181b;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            row-gap: 15px;
            margin-bottom: 25px;
        }

        .day-name {
            font-size: 11px;
            font-weight: 600;
            color: #a1a1aa;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .day-num {
            font-size: 13px;
            font-weight: 600;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            border-radius: 50%;
            color: #18181b;
        }

        .day-num.empty {
            visibility: hidden;
        }

        .day-num.has-event {
            border: 2px solid #18181b;
        }

        .day-num.today {
            background: #4fc94d;
            /* ✅ */
        }

        .mini-event {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 0;
            border-top: 1px solid #f4f4f5;
        }

        .mini-event-date {
            font-size: 16px;
            font-weight: 700;
            color: #18181b;
            min-width: 30px;
            text-align: center;
        }

        .mini-event-title {
            font-size: 13px;
            font-weight: 600;
            color: #18181b;
            margin: 0;
        }

        .mini-event-time {
            font-size: 11px;
            color: #a1a1aa;
            margin: 0;
        }

        .chart-wrapper {
            position: relative;
            height: 220px;
            width: 100%;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 600;
            color: #71717a;
            text-transform: uppercase;
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }
    </style>

    <h1 class="header-title">Programming Team Dashboard</h1>

    <div class="dashboard-grid">

        <div class="main-column">

            <div class="ui-card dark">
                <div class="status-hero">
                    <div>
                        <h3>Project Status Overview</h3>
                        <p>Track your deliverables, resolutions, and milestones.</p>
                    </div>
                    <svg class="squiggle-line" viewBox="0 0 100 30" fill="none" stroke="white" stroke-width="3"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 15 Q 15 5, 25 15 T 45 15 T 65 15 T 85 15 T 95 5" />
                    </svg>
                </div>
            </div>

            <div class="ui-card">
                <div class="section-title">Active Projects</div>
                <table class="sleek-table">
                    <thead>
                        <tr>
                            <th>Document Name</th>
                            <th>Status</th>

                            @if (auth()->check() && in_array(auth()->user()->role, ['pao_team', 'admin']))
                                <th style="text-align: right;">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resolutions as $res)
                            <tr>
                                <td>
                                    <strong>{{ $res->title }}</strong><br>
                                    <span
                                        style="font-size: 11px; color: #a1a1aa;">{{ $res->created_at->format('M d, Y') }}</span>
                                </td>
                                <td>
                                    @if ($res->status == 'validated')
                                        <span class="status-badge badge-dark">Validated</span>
                                    @elseif($res->status == 'on-going')
                                        <span class="status-badge badge-light">On-Going</span>
                                    @else
                                        <span class="status-badge badge-outline">Not-Validated</span>
                                    @endif
                                </td>

                                @if (auth()->check() && in_array(auth()->user()->role, ['pao_team', 'admin']))
                                    <td style="text-align: right;">
                                        <form action="{{ route('pao.resolutions.update_status', $res->id) }}"
                                            method="POST">
                                            @csrf
                                            <select name="status" class="status-select" onchange="this.form.submit()">
                                                <option value="not-validated"
                                                    {{ $res->status == 'not-validated' ? 'selected' : '' }}>
                                                    Not-Validated</option>
                                                <option value="on-going" {{ $res->status == 'on-going' ? 'selected' : '' }}>
                                                    On-Going
                                                </option>
                                                <option value="validated"
                                                    {{ $res->status == 'validated' ? 'selected' : '' }}>
                                                    Validated</option>
                                            </select>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->check() && in_array(auth()->user()->role, ['pao_team', 'admin']) ? '3' : '2' }}"
                                    style="text-align:center; color:#a1a1aa; padding: 30px 0;">
                                    No projects uploaded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="ui-card">
                <div class="section-title">
                    Analytics
                    <span style="font-size: 12px; color: #a1a1aa; font-weight: 500;">Project Status</span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <div>
                        <p style="font-size: 13px; font-weight: 600; margin-bottom: 15px; color: #71717a;">Upload Activity
                        </p>
                        <div class="chart-wrapper"><canvas id="barChart"></canvas></div>
                    </div>
                    <div>
                        <p style="font-size: 13px; font-weight: 600; margin-bottom: 15px; color: #71717a;">Completion Rate
                        </p>
                        <div class="chart-wrapper"><canvas id="doughnutChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="side-column">
            <div class="ui-card">
                <div class="section-title" style="margin-bottom: 15px;">New Events</div>

                <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #f4f4f5;">
                    <p
                        style="font-size: 11px; font-weight: 700; color: #a1a1aa; text-transform: uppercase; margin-bottom: 10px;">
                        Event Legend
                    </p>

                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        @forelse($categories as $cat)
                            <div class="legend-item">
                                <div class="legend-dot" style="background: {{ $cat->color }};"></div>
                                {{ $cat->name }}
                            </div>
                        @empty
                            <p style="font-size: 11px; color: #a1a1aa;">No tags available.</p>
                        @endforelse
                    </div>
                </div>

                @php
                    $today = \Carbon\Carbon::now();
                    $daysInMonth = $today->daysInMonth;
                    $firstDayOfWeek = $today->copy()->startOfMonth()->dayOfWeek;

                    $eventDays = isset($events)
                        ? $events
                            ->map(function ($e) {
                                return $e->event_date->format('j');
                            })
                            ->toArray()
                        : [];
                @endphp

                <div class="calendar-carousel">
                    <button class="nav-btn" id="prevMonthBtn" onclick="changeMonth(-1)">
                        &lt;
                    </button>

                    <div class="calendar-viewport">
                        @php
                            $currentYear = \Carbon\Carbon::now()->year;
                            $today = \Carbon\Carbon::now();
                        @endphp

                        @for ($m = 1; $m <= 12; $m++)
                            @php
                                $monthDate = \Carbon\Carbon::createFromDate($currentYear, $m, 1);
                                $daysInMonth = $monthDate->daysInMonth;
                                $firstDayOfWeek = $monthDate->dayOfWeek;

                                $eventsForMonth = $events
                                    ->filter(function ($e) use ($currentYear, $m) {
                                        return $e->event_date->year == $currentYear && $e->event_date->month == $m;
                                    })
                                    ->groupBy(function ($e) {
                                        return $e->event_date->format('j');
                                    });
                            @endphp

                            <div class="month-block" id="month-{{ $m }}">
                                <div class="calendar-header">
                                    <h4>{{ $monthDate->format('F Y') }}</h4>
                                </div>

                                <div class="calendar-grid">
                                    <div class="day-name">Sun</div>
                                    <div class="day-name">Mon</div>
                                    <div class="day-name">Tue</div>
                                    <div class="day-name">Wed</div>
                                    <div class="day-name">Thu</div>
                                    <div class="day-name">Fri</div>
                                    <div class="day-name">Sat</div>

                                    @for ($i = 0; $i < $firstDayOfWeek; $i++)
                                        <div class="day-num empty"></div>
                                    @endfor

                                    @for ($day = 1; $day <= $daysInMonth; $day++)
                                        @php
                                            $dayEvents = $eventsForMonth->get($day);
                                            $hasEvent = $dayEvents ? true : false;
                                            $isToday = $day == $today->day && $m == $today->month;
                                            $ringColor =
                                                $hasEvent && $dayEvents->first()->category
                                                    ? $dayEvents->first()->category->color
                                                    : '#18181b';
                                        @endphp

                                        <div class="day-num {{ $hasEvent ? 'has-event' : '' }} {{ $isToday ? 'today' : '' }}"
                                            style="{{ $hasEvent ? 'border-color:' . $ringColor . '; color:' . $ringColor : '' }}">
                                            {{ $day }}
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        @endfor
                    </div>

                    <button class="nav-btn" id="nextMonthBtn" onclick="changeMonth(1)">
                        &gt;
                    </button>
                </div>

                <div style="margin-top: 10px;">
                    <p
                        style="font-size: 11px; font-weight: 700; color: #a1a1aa; text-transform: uppercase; margin-bottom: 10px;">
                        Upcoming Schedule</p>

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
                        <p style="font-size: 12px; color: #a1a1aa; text-align: center; margin-top: 20px;">No upcoming
                            events.
                        </p>
                    @endif
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Chart.defaults.font.family = "'Poppins', sans-serif";
            Chart.defaults.color = '#a1a1aa';

            const ctxBar = document.getElementById('barChart').getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    datasets: [{
                        label: 'Uploads',
                        data: [5, 12, 8, 15],
                        backgroundColor: '#0c4d05',
                        borderRadius: 6,
                        barPercentage: 0.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f4f4f5'
                            },
                            border: {
                                display: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            }
                        }
                    }
                }
            });

            const ctxDoughnut = document.getElementById('doughnutChart').getContext('2d');
            new Chart(ctxDoughnut, {
                type: 'doughnut',
                data: {
                    labels: ['Validated', 'On-Going', 'Pending'],
                    datasets: [{
                        data: [45, 30, 25],
                        backgroundColor: ['#0c4d05', '#fda611', '#e1e1ef'],
                        borderColor: '#e4e4e7',
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    }
                }
            });
        });

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
