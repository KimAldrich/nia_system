# Event Calendar Improvements TODO

## Steps:

- [x]   1. Update AdminController.php: Add future-date validation to storeEvent() and filter events query in index() to future only.
- [x]   2. Update resources/views/admin/dashboard.blade.php:
    - Added month display ('d M').
    - Filtered eventsForMonth: && $e->event_date->gte(now()).
    - Flatpickr minDate="today".
    - JS openEventModal: past date check/alert.
    - CSS .day-num.past + blade class/style for past days.
- [ ]   3. Test changes: Verify past dates blocked, events auto-hide, months shown.
- [x]   4. Task complete - changes implemented per plan.
