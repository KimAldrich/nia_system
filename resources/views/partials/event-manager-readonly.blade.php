<style>
    .event-manager-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        border: none;
    }

    .event-manager-card .section-title {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .event-manager-legend-section {
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #f1f5f9;
    }

    .event-manager-label {
        font-size: 11px;
        font-weight: 700;
        color: #a0aec0;
        text-transform: uppercase;
        margin: 0 0 15px;
    }

    .event-manager-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .event-manager-card .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
    }

    .event-manager-card .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }

    .event-manager-card .calendar-carousel {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 20px;
    }

    .event-manager-card .nav-btn {
        background: #f8fafc;
        border: none;
        border-radius: 8px;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: 0.2s;
        color: #64748b;
        flex-shrink: 0;
    }

    .event-manager-card .nav-btn:hover:not(:disabled) {
        background: #e2e8f0;
        color: #1e293b;
    }

    .event-manager-card .nav-btn:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }

    .event-manager-card .calendar-viewport {
        flex: 1;
        overflow: hidden;
        position: relative;
        min-height: 280px;
    }

    .event-manager-card .month-block {
        display: none;
        animation: eventManagerSlideFade 0.3s ease;
    }

    .event-manager-card .month-block.active {
        display: block;
    }

    @keyframes eventManagerSlideFade {
        from {
            opacity: 0;
            transform: scale(0.98);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .event-manager-card .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .event-manager-card .calendar-header h4 {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
    }

    .event-manager-card .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        text-align: center;
        row-gap: 15px;
    }

    .event-manager-card .day-name {
        font-size: 11px;
        font-weight: 600;
        color: #a0aec0;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .event-manager-card .day-num {
        font-size: 13px;
        font-weight: 500;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        border-radius: 50%;
        color: #475569;
        transition: 0.2s;
    }

    .event-manager-card .day-num.empty {
        visibility: hidden;
    }

    .event-manager-card .day-num.has-event {
        font-weight: 700;
    }

    .event-manager-card .day-num.today {
        background: #4f46e5 !important;
        color: #ffffff !important;
        border: none !important;
        font-weight: 700;
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
    }

    .event-manager-upcoming {
        margin-top: 10px;
    }

    .event-manager-card .mini-event {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px 0;
        border-top: 1px solid #f1f5f9;
    }

    .event-manager-card .mini-event-date {
        font-size: 16px;
        font-weight: 700;
        color: #4f46e5;
        min-width: 30px;
        text-align: center;
        background: #e0e7ff;
        padding: 5px;
        border-radius: 8px;
        flex-shrink: 0;
    }

    .event-manager-card .mini-event-title {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
    }

    .event-manager-card .mini-event-time {
        font-size: 11px;
        color: #a0aec0;
        margin: 0;
    }

    .event-manager-card .event-tag {
        font-size: 10px;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 6px;
        display: inline-block;
        margin-top: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    @media (max-width: 768px) {
        .event-manager-card {
            padding: 20px;
        }

        .event-manager-card .calendar-viewport {
            min-height: auto;
        }
    }
</style>

@php
    $calendarYear = \Carbon\Carbon::now()->year;
    $calendarToday = \Carbon\Carbon::now();

    $upcomingEvents = collect($events ?? [])
        ->filter(function ($event) {
            return method_exists($event, 'isUpcoming')
                ? $event->isUpcoming()
                : $event->event_date->isFuture();
        })
        ->sortBy('event_date')
        ->values();
@endphp

<div class="ui-card event-manager-card">
    <div class="section-title">Event Manager</div>

    <div class="event-manager-legend-section">
        <p class="event-manager-label">Event Legend</p>
        <div class="event-manager-legend">
            @forelse($categories ?? [] as $cat)
                <div class="legend-item">
                    <div class="legend-dot" style="background: {{ $cat->color }};"></div>
                    {{ $cat->name }}
                </div>
            @empty
                <p style="font-size: 11px; color: #a0aec0; margin: 0;">No custom tags created yet.</p>
            @endforelse
        </div>
    </div>

    <div class="calendar-carousel">
        <button class="nav-btn" id="prevMonthBtn" onclick="changeMonth(-1)">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>

        <div class="calendar-viewport">
            @for ($m = 1; $m <= 12; $m++)
                @php
                    $monthDate = \Carbon\Carbon::createFromDate($calendarYear, $m, 1);
                    $daysInMonth = $monthDate->daysInMonth;
                    $firstDayOfWeek = $monthDate->dayOfWeek;

                    $eventsForMonth = $upcomingEvents
                        ->filter(function ($e) use ($calendarYear, $m) {
                            return $e->event_date->year == $calendarYear && $e->event_date->month == $m;
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
                                $isToday = $day == $calendarToday->day && $m == $calendarToday->month && $calendarYear == $calendarToday->year;
                                $ringColor = $hasEvent && $dayEvents->first()->category ? $dayEvents->first()->category->color : '#18181b';
                            @endphp

                            <div
                                class="day-num {{ $hasEvent ? 'has-event' : '' }} {{ $isToday ? 'today' : '' }}"
                                style="{{ $hasEvent && !$isToday ? 'border: 2px solid ' . $ringColor . '; color: ' . $ringColor . ';' : '' }}"
                            >
                                {{ $day }}
                            </div>
                        @endfor
                    </div>
                </div>
            @endfor
        </div>

        <button class="nav-btn" id="nextMonthBtn" onclick="changeMonth(1)">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
    </div>

    <div class="event-manager-upcoming">
        <p class="event-manager-label">Upcoming Schedule</p>

        @if ($upcomingEvents->count() > 0)
            @foreach ($upcomingEvents->take(5) as $event)
                <div class="mini-event">
                    <div class="mini-event-date">{{ $event->event_date->format('d') }}</div>
                    <div>
                        <h4 class="mini-event-title">{{ $event->title }}</h4>
                        <p class="mini-event-time">{{ $event->event_date->format('F') }} · {{ $event->event_time }}</p>

                        @if (!empty($event->category))
                            <span
                                class="event-tag"
                                style="background-color: {{ $event->category->color }}15; color: {{ $event->category->color }}; border: 1px solid {{ $event->category->color }}30;"
                            >
                                {{ $event->category->name }}
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <p style="font-size: 12px; color: #a0aec0; text-align: center; margin-top: 20px;">No upcoming events.</p>
        @endif
    </div>
</div>
