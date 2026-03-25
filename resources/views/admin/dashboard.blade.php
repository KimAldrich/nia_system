@extends('layouts.app')
@section('title', 'Admin Master Dashboard')

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <style>
        * { box-sizing: border-box; }
        .content { background-color: #f7f8fa; font-family: 'Poppins', sans-serif; padding: 40px; color: #111; }
        .header-title { font-size: 32px; font-weight: 700; margin-bottom: 20px; letter-spacing: -0.5px; }
        .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 40px; }
        .ui-card { background: #ffffff; border-radius: 12px; padding: 25px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03); margin-bottom: 30px; border: 1px solid #e4e4e7; }
        .ui-card.dark { background: #18181b; color: #ffffff; border: none; }
        .section-title { font-size: 18px; font-weight: 600; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        
        /* Modal Styles */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); backdrop-filter: blur(2px); z-index: 1000; display: none; align-items: center; justify-content: center; }
        .modal-overlay.active { display: flex; animation: fadeIn 0.2s; }
        .modal-box { background: white; padding: 30px; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Table Styles */
        .sleek-table { width: 100%; border-collapse: collapse; }
        .sleek-table th { text-align: left; padding-bottom: 15px; color: #a1a1aa; font-weight: 500; font-size: 12px; text-transform: uppercase; border-bottom: 1px solid #f4f4f5; }
        .sleek-table td { padding: 15px 0; border-bottom: 1px solid #f4f4f5; font-size: 13px; font-weight: 500; }
        .status-badge { padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 600; display: inline-block; min-width: 90px; text-align: center; }
        .badge-dark { background: #18181b; color: #fff; }
        .badge-light { background: #f4f4f5; color: #18181b; }
        .badge-outline { border: 1px solid #e4e4e7; color: #71717a; }

        /* Calendar Pagination & Layout */
        .calendar-carousel { display: flex; align-items: center; justify-content: space-between; gap: 15px; margin-bottom: 20px; }
        .nav-btn { background: #ffffff; border: 1px solid #e4e4e7; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; color: #18181b; box-shadow: 0 2px 5px rgba(0,0,0,0.02); flex-shrink: 0; }
        .nav-btn:hover:not(:disabled) { background: #18181b; color: #ffffff; border-color: #18181b; }
        .nav-btn:disabled { opacity: 0.3; cursor: not-allowed; }
        .calendar-viewport { flex: 1; overflow: hidden; position: relative; min-height: 280px; }
        
        /* Individual Month Blocks */
        .month-block { display: none; animation: slideFade 0.3s ease; }
        .month-block.active { display: block; }
        @keyframes slideFade { from { opacity: 0; transform: scale(0.98); } to { opacity: 1; transform: scale(1); } }

        .calendar-header { display: flex; justify-content: center; align-items: center; margin-bottom: 15px; }
        .calendar-header h4 { margin: 0; font-size: 15px; font-weight: 700; text-transform: uppercase; color: #18181b; letter-spacing: 0.5px; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; row-gap: 12px; }
        .day-name { font-size: 10px; font-weight: 600; color: #a1a1aa; text-transform: uppercase; margin-bottom: 8px; }
        .day-num { font-size: 12px; font-weight: 600; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; margin: 0 auto; border-radius: 50%; color: #18181b; }
        .day-num.empty { visibility: hidden; }
        .day-num.has-event { border: 2px solid #18181b; }
        .day-num.today { background: #f4f4f5; }
        .day-num.clickable { cursor: pointer; transition: 0.2s; }
        .day-num.clickable:hover { background: #18181b !important; color: white !important; border-color: #18181b !important; }

        /* Dynamic Tags & Events */
        .mini-event { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-top: 1px solid #f4f4f5; }
        .mini-event-date { font-size: 16px; font-weight: 700; color: #18181b; min-width: 30px; text-align: center;}
        .mini-event-title { font-size: 13px; font-weight: 600; color: #18181b; margin: 0; }
        .mini-event-time { font-size: 11px; color: #a1a1aa; margin: 0; }
        .event-tag { font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 6px; display: inline-block; margin-top: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        .legend-item { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 600; color: #71717a; text-transform: uppercase; }
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; }
        
        .chart-wrapper { position: relative; height: 220px; width: 100%; }
    </style>

    <h1 class="header-title">Admin Master Dashboard</h1>

    @if(session('success'))
        <div style="background: #18181b; color: #ffffff; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
            <svg style="width:18px; height:18px; color:#4ade80;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; font-weight: 500;">
            @foreach ($errors->all() as $error)
                <div style="margin-bottom: 4px;">⚠️ {{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="dashboard-grid">
        <div class="main-column">
            <div class="ui-card">
                <div class="section-title">Agency Resolutions Overview</div>
                <table class="sleek-table">
                    <thead>
                        <tr>
                            <th>Document Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resolutions as $res)
                        <tr>
                            <td>
                                <strong>{{ $res->title }}</strong><br>
                                <span style="font-size: 11px; color: #a1a1aa;">{{ $res->created_at->format('M d, Y') }}</span>
                            </td>
                            <td>
                                @if($res->status == 'validated')
                                    <span class="status-badge badge-dark">Validated</span>
                                @elseif($res->status == 'on-going')
                                    <span class="status-badge badge-light">On-Going</span>
                                @else
                                    <span class="status-badge badge-outline">Not-Validated</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" style="text-align:center; color:#a1a1aa; padding: 30px 0;">No projects uploaded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="ui-card">
                <div class="section-title">Analytics</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <div><p style="font-size: 13px; font-weight: 600; margin-bottom: 15px; color: #71717a;">Upload Activity</p><div class="chart-wrapper"><canvas id="barChart"></canvas></div></div>
                    <div><p style="font-size: 13px; font-weight: 600; margin-bottom: 15px; color: #71717a;">Completion Rate</p><div class="chart-wrapper"><canvas id="doughnutChart"></canvas></div></div>
                </div>
            </div>
        </div>

        <div class="side-column">
            <div class="ui-card">
                <div class="section-title" style="margin-bottom: 15px;">Event Manager</div>

                <div style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #f4f4f5;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <p style="font-size: 11px; font-weight: 700; color: #a1a1aa; text-transform: uppercase; margin: 0;">Event Legend</p>
                        <button onclick="document.getElementById('categoryModal').classList.add('active')" style="background: none; border: 1px solid #e4e4e7; border-radius: 6px; font-size: 11px; padding: 4px 8px; cursor: pointer; color: #18181b; font-weight: 600;">⚙️ Manage Tags</button>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                        @forelse($categories as $cat)
                            <div class="legend-item"><div class="legend-dot" style="background: {{ $cat->color }};"></div>{{ $cat->name }}</div>
                        @empty
                            <p style="font-size: 11px; color: #a1a1aa; margin: 0;">No custom tags created yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="calendar-carousel">
                    <button class="nav-btn" id="prevMonthBtn" onclick="changeMonth(-1)">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                    </button>

                    <div class="calendar-viewport">
                        @php
                            $currentYear = \Carbon\Carbon::now()->year;
                            $today = \Carbon\Carbon::now();
                        @endphp

                        @for($m = 1; $m <= 12; $m++)
                            @php
                                $monthDate = \Carbon\Carbon::createFromDate($currentYear, $m, 1);
                                $daysInMonth = $monthDate->daysInMonth;
                                $firstDayOfWeek = $monthDate->dayOfWeek;
                                
                                $eventsForMonth = isset($events) ? $events->filter(function($e) use ($currentYear, $m) {
                                    return $e->event_date->year == $currentYear && $e->event_date->month == $m;
                                })->groupBy(function($e) {
                                    return $e->event_date->format('j');
                                }) : collect();
                            @endphp

                            <div class="month-block" id="month-{{ $m }}">
                                <div class="calendar-header">
                                    <h4>{{ $monthDate->format('F Y') }}</h4>
                                </div>
                                
                                <div class="calendar-grid">
                                    <div class="day-name">Sun</div><div class="day-name">Mon</div><div class="day-name">Tue</div><div class="day-name">Wed</div><div class="day-name">Thu</div><div class="day-name">Fri</div><div class="day-name">Sat</div>
                                    
                                    @for($i = 0; $i < $firstDayOfWeek; $i++)
                                        <div class="day-num empty"></div>
                                    @endfor

                                    @for($day = 1; $day <= $daysInMonth; $day++)
                                        @php
                                            $dayEvents = $eventsForMonth->get($day);
                                            $hasEvent = $dayEvents ? true : false;
                                            $isToday = ($day == $today->day && $m == $today->month && $currentYear == $today->year);
                                            $dateString = $monthDate->format('Y-m-') . str_pad($day, 2, '0', STR_PAD_LEFT);
                                            
                                            $ringColor = ($hasEvent && $dayEvents->first()->category) ? $dayEvents->first()->category->color : '#18181b';
                                        @endphp
                                        
                                        <div class="day-num clickable {{ $hasEvent ? 'has-event' : '' }} {{ $isToday ? 'today' : '' }}" 
                                             style="{{ $hasEvent ? 'border-color: ' . $ringColor . '; color: ' . $ringColor . ';' : '' }}"
                                             onclick="openEventModal('{{ $dateString }}')" title="Click to add event">
                                            {{ $day }}
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        @endfor
                    </div>

                    <button class="nav-btn" id="nextMonthBtn" onclick="changeMonth(1)">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>

                <div style="margin-top: 10px;">
                    <p style="font-size: 11px; font-weight: 700; color: #a1a1aa; text-transform: uppercase; margin-bottom: 10px;">Upcoming Schedule</p>
                    
                    @if(isset($events) && $events->count() > 0)
                        @foreach($events->where('event_date', '>=', \Carbon\Carbon::today())->take(5) as $event)
                            <div class="mini-event">
                                <div style="display: flex; gap: 15px; align-items: center;">
                                    <div class="mini-event-date">{{ $event->event_date->format('d') }}</div>
                                    <div>
                                        <h4 class="mini-event-title">{{ $event->title }}</h4>
                                        <p class="mini-event-time">{{ $event->event_time }}</p>
                                        @if($event->category)
                                            <span class="event-tag" style="background-color: {{ $event->category->color }}15; color: {{ $event->category->color }}; border: 1px solid {{ $event->category->color }}30;">
                                                {{ $event->category->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <form action="{{ route('admin.events.destroy', $event->id ?? 0) }}" method="POST" style="margin: 0;">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="background: none; border: none; color: #f87171; cursor: pointer; font-size: 18px; padding: 0 5px;" title="Delete Event">×</button>
                                </form>
                            </div>
                        @endforeach
                    @else
                        <p style="font-size: 12px; color: #a1a1aa; text-align: center; margin-top: 20px;">No upcoming events.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="eventModal">
        <div class="modal-box">
            <h3 style="margin-top: 0; font-size: 18px; color: #18181b;">Schedule Event</h3>
            <p style="font-size: 12px; color: #a1a1aa; margin-bottom: 20px;">Adding event for: <strong id="displayDate" style="color: #18181b;"></strong></p>
            
            <form action="{{ route('admin.events.store') }}" method="POST" id="eventForm">
                @csrf
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #71717a; margin-bottom: 5px; text-transform: uppercase;">Event Date</label>
                    <input type="text" name="event_date" id="eventDateInput" required style="width: 100%; padding: 10px; border: 1px solid #e4e4e7; border-radius: 6px; font-family: 'Poppins', sans-serif; outline: none; background: white; cursor: pointer;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #71717a; margin-bottom: 5px; text-transform: uppercase;">Event Title</label>
                    <input type="text" name="title" required placeholder="e.g. System Maintenance" style="width: 100%; padding: 10px; border: 1px solid #e4e4e7; border-radius: 6px; font-family: 'Poppins', sans-serif; outline: none;">
                </div>

                <input type="hidden" name="event_time" id="finalTimeInput">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 600; color: #71717a; margin-bottom: 5px; text-transform: uppercase;">Start Time</label>
                        <input type="text" id="startTime" required placeholder="Select time" style="width: 100%; padding: 10px; border: 1px solid #e4e4e7; border-radius: 6px; font-family: 'Poppins', sans-serif; outline: none; background: white; cursor: pointer;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 600; color: #71717a; margin-bottom: 5px; text-transform: uppercase;">End Time</label>
                        <input type="text" id="endTime" required placeholder="Select time" style="width: 100%; padding: 10px; border: 1px solid #e4e4e7; border-radius: 6px; font-family: 'Poppins', sans-serif; outline: none; background: white; cursor: pointer;">
                    </div>
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #71717a; margin-bottom: 5px; text-transform: uppercase;">Category Tag</label>
                    <select name="event_category_id" required style="width: 100%; padding: 10px; border: 1px solid #e4e4e7; border-radius: 6px; font-family: 'Poppins', sans-serif; outline: none; background: white; cursor: pointer;">
                        @forelse($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @empty
                            <option value="" disabled selected>Add a tag first!</option>
                        @endforelse
                    </select>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="closeEventModal()" style="flex: 1; padding: 10px; background: white; border: 1px solid #d4d4d8; border-radius: 6px; cursor: pointer; font-weight: 600; font-family: 'Poppins', sans-serif; color: #18181b;">Cancel</button>
                    <button type="submit" style="flex: 1; padding: 10px; background: #18181b; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-family: 'Poppins', sans-serif;">Save Event</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="categoryModal">
        <div class="modal-box">
            <h3 style="margin-top: 0; font-size: 18px; color: #18181b;">Manage Event Tags</h3>
            
            <div style="margin-bottom: 20px; max-height: 150px; overflow-y: auto;">
                @if($categories->count() > 0)
                    @foreach($categories as $cat)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f4f4f5;">
                            <div class="legend-item"><div class="legend-dot" style="background: {{ $cat->color }};"></div>{{ $cat->name }}</div>
                            <form action="{{ route('admin.categories.destroy', $cat->id ?? 0) }}" method="POST" style="margin: 0;">
                                @csrf @method('DELETE')
                                <button type="submit" style="background: none; border: none; color: #f87171; cursor: pointer; font-size: 14px;">Delete</button>
                            </form>
                        </div>
                    @endforeach
                @else
                    <p style="font-size: 12px; color: #a1a1aa; text-align: center;">No custom tags created yet.</p>
                @endif
            </div>

            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <p style="font-size: 12px; font-weight: 600; margin-bottom: 10px;">Add New Tag</p>
                <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <select name="color" required style="width: 130px; padding: 8px 10px; border: 1px solid #e4e4e7; border-radius: 6px; font-family: 'Poppins', sans-serif; outline: none; background: white; cursor: pointer;">
                        <option value="#3b82f6">🔵 Blue</option>
                        <option value="#eab308">🟡 Yellow</option>
                        <option value="#ef4444">🔴 Red</option>
                        <option value="#22c55e">🟢 Green</option>
                        <option value="#8b5cf6">🟣 Violet</option>
                        <option value="#f97316">🟠 Orange</option>
                    </select>
                    <input type="text" name="name" required placeholder="Tag Name" style="flex: 1; padding: 8px 10px; border: 1px solid #e4e4e7; border-radius: 6px; font-family: 'Poppins', sans-serif; outline: none;">
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="document.getElementById('categoryModal').classList.remove('active')" style="flex: 1; padding: 10px; background: white; border: 1px solid #d4d4d8; border-radius: 6px; cursor: pointer; font-weight: 600; font-family: 'Poppins', sans-serif; color: #18181b;">Close</button>
                    <button type="submit" style="flex: 1; padding: 10px; background: #18181b; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-family: 'Poppins', sans-serif;">Save Tag</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Start the calendar on the exact current real-world month (1-12)
        let activeMonth = {{ \Carbon\Carbon::now()->month }};

        // Initialize the view immediately when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            updateCalendarView();
            
            // Dummy Charts
            Chart.defaults.font.family = "'Poppins', sans-serif";
            const ctxBar = document.getElementById('barChart').getContext('2d');
            new Chart(ctxBar, { type: 'bar', data: { labels: ['W1', 'W2', 'W3', 'W4'], datasets: [{ data: [12, 19, 15, 22], backgroundColor: '#18181b', borderRadius: 6 }] }, options: { plugins: { legend: { display: false } } } });
            const ctxPie = document.getElementById('doughnutChart').getContext('2d');
            new Chart(ctxPie, { type: 'doughnut', data: { labels: ['Done', 'Pending'], datasets: [{ data: [70, 30], backgroundColor: ['#18181b', '#e4e4e7'], borderWidth: 0 }] }, options: { cutout: '75%', plugins: { legend: { position: 'bottom' } } } });

            // 1. Activate the Visual Pickers
            flatpickr("#eventDateInput", { dateFormat: "Y-m-d" });
            flatpickr("#startTime", { enableTime: true, noCalendar: true, dateFormat: "h:i K", defaultDate: "09:00" });
            flatpickr("#endTime", { enableTime: true, noCalendar: true, dateFormat: "h:i K", defaultDate: "10:30" });

            // 2. Automatically merge the times when the user clicks "Save"
            document.getElementById('eventForm').addEventListener('submit', function(e) {
                let start = document.getElementById('startTime').value;
                let end = document.getElementById('endTime').value;
                document.getElementById('finalTimeInput').value = start + ' - ' + end;
            });
        });

        // The button click function
        function changeMonth(direction) {
            activeMonth += direction;
            if (activeMonth < 1) activeMonth = 1;
            if (activeMonth > 12) activeMonth = 12;
            updateCalendarView();
        }

        // Hides everything and only shows the active month
        function updateCalendarView() {
            document.querySelectorAll('.month-block').forEach(block => {
                block.classList.remove('active');
            });
            
            const currentBlock = document.getElementById('month-' + activeMonth);
            if(currentBlock) {
                currentBlock.classList.add('active');
            }

            // Disable the Left button if we are on January, disable Right if on December
            document.getElementById('prevMonthBtn').disabled = (activeMonth === 1);
            document.getElementById('nextMonthBtn').disabled = (activeMonth === 12);
        }

        // Modal Controls
        function openEventModal(dateStr) {
            document.getElementById('eventDateInput').value = dateStr;
            document.getElementById('displayDate').innerText = dateStr;
            document.getElementById('eventModal').classList.add('active');
        }

        function closeEventModal() {
            document.getElementById('eventModal').classList.remove('active');
        }
    </script>
@endsection