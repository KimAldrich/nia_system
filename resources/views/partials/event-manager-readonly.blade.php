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
        text-align: center;
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

    .event-manager-card .custom-pagination {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-top: 18px;
        gap: 8px;
        font-family: 'Poppins', sans-serif;
        flex-wrap: wrap;
    }

    .event-manager-card .custom-pagination .page-item {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        padding: 0 8px;
        border-radius: 8px;
        background: #ffffff;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid #e2e8f0;
        transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        line-height: 1;
    }

    .event-manager-card .custom-pagination .page-item:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #1e293b;
    }

    .event-manager-card .custom-pagination .page-item.active {
        background: #4f46e5;
        color: #ffffff;
        border-color: #4f46e5;
    }

    .event-manager-card .custom-pagination .page-item.disabled {
        background: #f8fafc;
        color: #cbd5e1;
        cursor: not-allowed;
        border-color: #f1f5f9;
    }

    .event-manager-card .custom-pagination .page-item svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
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
    $containerId = $containerId ?? 'eventManagerReadonlyCard';
    $calendarYear = \Carbon\Carbon::now()->year;
    $calendarToday = \Carbon\Carbon::now();
    $eventTeamLabels = [
        'all' => 'All Teams (General Event)',
        'fs_team' => 'FS Team',
        'rpwsis_team' => 'Social and Environmental Team',
        'cm_team' => 'Contract Management Team',
        'row_team' => 'Right Of Way Team',
        'pcr_team' => 'Program Completion Report Team',
        'pao_team' => 'Programming Team',
    ];

    $upcomingEvents = collect($events ?? [])
        ->filter(function ($event) {
            return method_exists($event, 'isUpcoming')
                ? $event->isUpcoming()
                : $event->event_date->isFuture();
        })
        ->sortBy('event_date')
        ->values();

    $displayEvents = $upcomingEvents;
@endphp

<div id="{{ $containerId }}" class="ui-card event-manager-card">
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

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px;">
        <label>
            <span class="event-manager-label" style="display:block; margin-bottom:6px;">Filter by Tag</span>
            <select id="{{ $containerId }}TagFilter" class="table-toolbar__select">
                <option value="">All tags</option>
                @foreach(($categories ?? []) as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span class="event-manager-label" style="display:block; margin-bottom:6px;">Filter by Team</span>
            <select id="{{ $containerId }}TeamFilter" class="table-toolbar__select">
                <option value="">All teams</option>
                @foreach($eventTeamLabels as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
    </div>

    <div class="calendar-carousel">
        <button class="nav-btn" id="{{ $containerId }}PrevMonthBtn" onclick="changeReadonlyMonth('{{ $containerId }}', -1)">
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

                <div class="month-block {{ $m === $calendarToday->month ? 'active' : '' }}" id="{{ $containerId }}-month-{{ $m }}" style="{{ $m === $calendarToday->month ? 'display:block;' : 'display:none;' }}">
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
                                data-date="{{ $monthDate->format('Y-m-') . str_pad($day, 2, '0', STR_PAD_LEFT) }}"
                                onclick="openReadonlyEventDateDetails('{{ $containerId }}', '{{ $monthDate->format('Y-m-') . str_pad($day, 2, '0', STR_PAD_LEFT) }}')"
                                style="{{ $hasEvent && !$isToday ? 'border: 2px solid ' . $ringColor . '; color: ' . $ringColor . ';' : '' }}{{ $hasEvent ? '; cursor:pointer;' : '' }}"
                            >
                                {{ $day }}
                            </div>
                        @endfor
                    </div>
                </div>
            @endfor
        </div>

        <button class="nav-btn" id="{{ $containerId }}NextMonthBtn" onclick="changeReadonlyMonth('{{ $containerId }}', 1)">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
    </div>

    <div class="event-manager-upcoming">
        <p class="event-manager-label">Upcoming Schedule</p>

        @if (count($displayEvents) > 0)
            @foreach ($displayEvents as $event)
                <div class="mini-event readonly-event-item"
                    data-event-id="{{ $event->id }}"
                    data-category-id="{{ $event->event_category_id }}"
                    data-team="{{ $event->team }}"
                    onclick="openReadonlyEventDetails('{{ $containerId }}', {{ $event->id }})"
                    style="cursor:pointer;">
                    <div class="mini-event-date">{{ $event->event_date->format('d') }}</div>
                    <div>
                        <h4 class="mini-event-title">{{ $event->title }}</h4>
                        <p class="mini-event-time">{{ $event->event_date->format('F') }} · {{ $event->event_time }}</p>

                        @if (!empty($event->team))
                            <p class="mini-event-time" style="margin-top:4px;">{{ $eventTeamLabels[$event->team] ?? strtoupper(str_replace('_', ' ', $event->team)) }}</p>
                        @endif
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

<div class="modal-overlay" id="{{ $containerId }}DetailsModal">
    <div class="modal-box">
        <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; margin-bottom:18px;">
            <div>
                <h3 style="margin:0; font-size:18px; color:#1e293b;" id="{{ $containerId }}DetailsTitle">Event Details</h3>
                <p style="margin:6px 0 0; font-size:12px; color:#64748b;" id="{{ $containerId }}DetailsMeta"></p>
            </div>
            <button type="button" onclick="closeReadonlyEventDetails('{{ $containerId }}')" style="background:none; border:none; font-size:28px; line-height:1; cursor:pointer;">&times;</button>
        </div>
        <div id="{{ $containerId }}DetailsBody" style="display:grid; gap:12px; color:#334155; font-size:14px;"></div>
        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="button" onclick="closeReadonlyEventDetails('{{ $containerId }}')" class="modern-btn modern-btn-outline" style="flex:1;">Close</button>
        </div>
    </div>
</div>

@php
    $readonlyEventEntries = $displayEvents->map(function ($event) use ($eventTeamLabels) {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'event_date' => optional($event->event_date)->format('Y-m-d'),
            'event_date_label' => optional($event->event_date)->format('F d, Y'),
            'event_time' => $event->event_time,
            'event_category_id' => $event->event_category_id,
            'category_name' => optional($event->category)->name,
            'category_color' => optional($event->category)->color,
            'team' => $event->team,
            'team_label' => $eventTeamLabels[$event->team] ?? strtoupper(str_replace('_', ' ', (string) $event->team)),
            'reminder_minutes' => $event->reminder_minutes,
            'recurrence_pattern' => $event->recurrence_pattern ?? 'none',
            'recurrence_until_label' => optional($event->recurrence_until)->format('F d, Y'),
        ];
    })->values();
@endphp

<script>
    (() => {
        const containerId = @json($containerId);
        let activeMonth = {{ $calendarToday->month }};
        const events = @json($readonlyEventEntries);

        window.changeReadonlyMonth = function (targetId, direction) {
            if (targetId !== containerId) return;
            activeMonth += direction;
            if (activeMonth < 1) activeMonth = 1;
            if (activeMonth > 12) activeMonth = 12;
            updateReadonlyCalendarView();
        };

        const updateReadonlyCalendarView = () => {
            document.querySelectorAll(`#${containerId} .month-block`).forEach((block) => {
                block.classList.remove('active');
                block.style.display = 'none';
            });

            const activeBlock = document.getElementById(`${containerId}-month-${activeMonth}`);
            if (activeBlock) {
                activeBlock.classList.add('active');
                activeBlock.style.display = 'block';
            }
            const prevButton = document.getElementById(`${containerId}PrevMonthBtn`);
            const nextButton = document.getElementById(`${containerId}NextMonthBtn`);
            if (prevButton) prevButton.disabled = activeMonth === 1;
            if (nextButton) nextButton.disabled = activeMonth === 12;
        };

        const getFilteredEvents = () => {
            const tagValue = document.getElementById(`${containerId}TagFilter`)?.value || '';
            const teamValue = document.getElementById(`${containerId}TeamFilter`)?.value || '';
            return events.filter((event) => {
                const matchesTag = !tagValue || String(event.event_category_id || '') === String(tagValue);
                const matchesTeam = !teamValue || String(event.team || '') === String(teamValue);
                return matchesTag && matchesTeam;
            });
        };

        window.applyReadonlyEventFilters = function (targetId) {
            if (targetId !== containerId) return;

            const filteredEvents = getFilteredEvents();
            const visibleIds = new Set(filteredEvents.map((event) => String(event.id)));
            const eventsByDate = filteredEvents.reduce((carry, event) => {
                if (!carry[event.event_date]) carry[event.event_date] = [];
                carry[event.event_date].push(event);
                return carry;
            }, {});

            document.querySelectorAll(`#${containerId} .readonly-event-item`).forEach((item) => {
                item.style.display = visibleIds.has(String(item.dataset.eventId)) ? '' : 'none';
            });

            document.querySelectorAll(`#${containerId} .day-num[data-date]`).forEach((dayNode) => {
                const dayEvents = eventsByDate[dayNode.dataset.date] || [];
                const firstEvent = dayEvents[0] || null;
                const isToday = dayNode.classList.contains('today');
                dayNode.classList.toggle('has-event', dayEvents.length > 0);
                if (dayEvents.length > 0 && !isToday) {
                    dayNode.style.border = `2px solid ${firstEvent.category_color || '#18181b'}`;
                    dayNode.style.color = firstEvent.category_color || '#18181b';
                    dayNode.style.cursor = 'pointer';
                } else if (!isToday) {
                    dayNode.style.border = '';
                    dayNode.style.color = '';
                    dayNode.style.cursor = '';
                }
            });
        };

        window.openReadonlyEventDetails = function (targetId, eventId) {
            if (targetId !== containerId) return;
            const event = events.find((entry) => String(entry.id) === String(eventId));
            if (!event) return;
            document.getElementById(`${containerId}DetailsTitle`).innerText = event.title || 'Event Details';
            document.getElementById(`${containerId}DetailsMeta`).innerText = `${event.event_date_label} · ${event.event_time}`;
            document.getElementById(`${containerId}DetailsBody`).innerHTML = `
                <div><strong>Team:</strong> ${event.team_label || 'N/A'}</div>
                <div><strong>Tag:</strong> ${event.category_name || 'Uncategorized'}</div>
                <div><strong>Reminder:</strong> ${event.reminder_minutes ? `${event.reminder_minutes} minute(s) before` : 'No reminder'}</div>
                <div><strong>Recurring:</strong> ${event.recurrence_pattern && event.recurrence_pattern !== 'none' ? `${event.recurrence_pattern} until ${event.recurrence_until_label || 'the scheduled end'}` : 'No'}</div>
                <div><strong>Details:</strong><br>${(event.description || 'No additional details provided.').replace(/\n/g, '<br>')}</div>
            `;
            document.getElementById(`${containerId}DetailsModal`).classList.add('active');
        };

        window.openReadonlyEventDateDetails = function (targetId, dateStr) {
            if (targetId !== containerId) return;
            const dayEvents = getFilteredEvents().filter((event) => event.event_date === dateStr);
            if (!dayEvents.length) return;

            document.getElementById(`${containerId}DetailsTitle`).innerText = `Events on ${dateStr}`;
            document.getElementById(`${containerId}DetailsMeta`).innerText = `${dayEvents.length} event(s) scheduled`;
            document.getElementById(`${containerId}DetailsBody`).innerHTML = dayEvents.map((event) => `
                <button type="button" onclick="openReadonlyEventDetails('${containerId}', ${event.id})" style="text-align:left; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:12px; cursor:pointer;">
                    <strong>${event.title}</strong><br>
                    <span style="font-size:12px; color:#64748b;">${event.event_time} · ${event.team_label || 'N/A'} · ${event.category_name || 'Uncategorized'}</span>
                </button>
            `).join('');
            document.getElementById(`${containerId}DetailsModal`).classList.add('active');
        };

        window.closeReadonlyEventDetails = function (targetId) {
            if (targetId !== containerId) return;
            document.getElementById(`${containerId}DetailsModal`).classList.remove('active');
        };

        let initialized = false;

        const bindFilters = () => {
            if (initialized) return;
            initialized = true;

            document.getElementById(`${containerId}TagFilter`)?.addEventListener('change', () => window.applyReadonlyEventFilters(containerId));
            document.getElementById(`${containerId}TeamFilter`)?.addEventListener('change', () => window.applyReadonlyEventFilters(containerId));
            updateReadonlyCalendarView();
            window.applyReadonlyEventFilters(containerId);
        };

        bindFilters();
    })();
</script>
