<style>
    .event-manager-card {
        background: #ffffff;
        border-radius: 24px;
        padding: 28px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        border: 1px solid #eef2f7;
    }

    .event-manager-card .section-title {
        display: block;
        margin-bottom: 24px;
        font-size: 18px;
        font-weight: 700;
        color: #1e293b;
    }

    .event-manager-label {
        margin: 0 0 12px;
        font-size: 11px;
        font-weight: 700;
        color: #a3b1c6;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .event-manager-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 14px;
        margin-bottom: 18px;
    }

    .event-manager-card .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
    }

    .event-manager-card .legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 999px;
        flex-shrink: 0;
    }

    .event-manager-divider {
        border: 0;
        border-top: 1px solid #eef2f7;
        margin: 0 0 18px;
    }

    .event-manager-card .calendar-carousel {
        display: grid;
        grid-template-columns: 48px minmax(0, 1fr) 48px;
        gap: 14px;
        align-items: center;
        margin-bottom: 20px;
    }

    .event-manager-card .nav-btn {
        width: 40px;
        height: 40px;
        border: none;
        border-radius: 14px;
        background: #f8fafc;
        color: #64748b;
        font-size: 22px;
        line-height: 1;
        cursor: pointer;
        transition: 0.2s ease;
        justify-self: center;
    }

    .event-manager-card .nav-btn:hover:not(:disabled) {
        background: #eef2ff;
        color: #4f46e5;
    }

    .event-manager-card .nav-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .event-manager-card .calendar-viewport {
        min-width: 0;
    }

    .event-manager-card .month-block {
        display: none;
    }

    .event-manager-card .month-block.active {
        display: block;
    }

    .event-manager-card .calendar-header {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 18px;
    }

    .event-manager-card .calendar-header h4 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #1e293b;
    }

    .event-manager-card .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 14px 10px;
        text-align: center;
    }

    .event-manager-card .day-name {
        font-size: 12px;
        font-weight: 700;
        color: #a3b1c6;
        text-transform: uppercase;
    }

    .event-manager-card .day-num {
        width: 36px;
        height: 36px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        color: #334155;
        font-size: 14px;
        font-weight: 600;
        border: 2px solid transparent;
    }

    .event-manager-card .day-num.empty {
        visibility: hidden;
    }

    .event-manager-card .day-num.today {
        background: #5b50f0 !important;
        color: #ffffff !important;
        border-color: #5b50f0 !important;
        box-shadow: 0 10px 22px rgba(91, 80, 240, 0.28);
    }

    .event-manager-upcoming {
        margin-top: 8px;
    }

    .event-manager-card .mini-event {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 0 0;
        border-top: 1px solid #eef2f7;
    }

    .event-manager-card .mini-event + .mini-event {
        margin-top: 14px;
    }

    .event-manager-card .mini-event-date {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: #e6e9ff;
        color: #5b50f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .event-manager-card .mini-event-title {
        margin: 0 0 2px;
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        text-transform: uppercase;
    }

    .event-manager-card .mini-event-time {
        margin: 0;
        font-size: 12px;
        color: #94a3b8;
    }

    .event-manager-card .event-tag {
        display: inline-flex;
        align-items: center;
        margin-top: 10px;
        padding: 6px 12px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    @media (max-width: 768px) {
        .event-manager-card {
            padding: 20px;
            border-radius: 18px;
        }

        .event-manager-card .calendar-carousel {
            grid-template-columns: 40px minmax(0, 1fr) 40px;
            gap: 10px;
        }

        .event-manager-card .calendar-grid {
            gap: 12px 6px;
        }

        .event-manager-card .day-num {
            width: 32px;
            height: 32px;
            font-size: 13px;
        }
    }
</style>

@php
    $calendarYear = \Carbon\Carbon::now()->year;
    $calendarToday = \Carbon\Carbon::now();
@endphp

<div class="ui-card event-manager-card">
    <div class="section-title">Event Manager</div>

    <p class="event-manager-label">Event Legend</p>
    <div class="event-manager-legend">
        @forelse($categories ?? [] as $cat)
            <div class="legend-item">
                <div class="legend-dot" style="background: {{ $cat->color }};"></div>
                {{ $cat->name }}
            </div>
        @empty
            <p style="font-size: 12px; color: #94a3b8; margin: 0;">No tags available.</p>
        @endforelse
    </div>

    <hr class="event-manager-divider">

    <div class="calendar-carousel">
        <button class="nav-btn" id="prevMonthBtn" onclick="changeMonth(-1)">&#8249;</button>

        <div class="calendar-viewport">
            @for ($m = 1; $m <= 12; $m++)
                @php
                    $monthDate = \Carbon\Carbon::createFromDate($calendarYear, $m, 1);
                    $daysInMonth = $monthDate->daysInMonth;
                    $firstDayOfWeek = $monthDate->dayOfWeek;

                    $eventsForMonth = collect($events ?? [])
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
                                $ringColor = $hasEvent && $dayEvents->first()->category ? $dayEvents->first()->category->color : '#5b50f0';
                            @endphp

                            <div class="day-num {{ $hasEvent ? 'has-event' : '' }} {{ $isToday ? 'today' : '' }}"
                                style="{{ $hasEvent && !$isToday ? 'border-color:' . $ringColor . '; color:' . $ringColor . ';' : '' }}">
                                {{ $day }}
                            </div>
                        @endfor
                    </div>
                </div>
            @endfor
        </div>

        <button class="nav-btn" id="nextMonthBtn" onclick="changeMonth(1)">&#8250;</button>
    </div>

    <div class="event-manager-upcoming">
        <p class="event-manager-label">Upcoming Schedule</p>

        @if (isset($events) && $events->count() > 0)
            @foreach ($events->take(5) as $event)
                <div class="mini-event">
                    <div class="mini-event-date">{{ $event->event_date->format('d') }}</div>
                    <div>
                        <h4 class="mini-event-title">{{ $event->title }}</h4>
                        <p class="mini-event-time">{{ $event->event_time }}</p>
                        @if (!empty($event->category))
                            <span class="event-tag"
                                style="background-color: {{ $event->category->color }}15; color: {{ $event->category->color }}; border: 1px solid {{ $event->category->color }}30;">
                                {{ $event->category->name }}
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <p style="font-size: 12px; color: #94a3b8; margin: 0;">No upcoming events.</p>
        @endif
    </div>
</div>
