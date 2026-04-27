# Fix Active Projects UI Consistency Across Teams

✅ **PLAN APPROVED** - Status: In Progress | Target: 15 mins

## 📋 Implementation Steps (3 Phases)

### **PHASE 1: Create Shared Partial (1 file)**

```
✅ 1. CREATE: resources/views/partials/active-projects-table.blade.php
    - Standardized sleek-table + responsive wrapper
    - Identical row markup + status badges
    - Parameterized: $resolutions, $teamRole
    - Async status updates with team auth
```

### **PHASE 2: Update 5 Dashboard Files (5 files)**

```
✅ 2. UPDATE: resources/views/fs-team/dashboard.blade.php
✅ 3. UPDATE: resources/views/pao_team/dashboard.blade.php
✅ 4. UPDATE: resources/views/row_team/dashboard.blade.php
✅ 5. UPDATE: resources/views/rpwsis_team/dashboard.blade.php
✅ 6. UPDATE: resources/views/cm_team/dashboard.blade.php
```

**Each**: Replace entire "Active Projects" `ui-card` → `@include('partials.active-projects-table', [...])`

### **PHASE 3: Test & Verify**

```
[ ] 7. TEST: Responsive on all 5 dashboards (mobile/tablet)
[ ] 8. TEST: Status dropdown works (team member permissions)
[ ] 9. TEST: Visual consistency (table styling identical)
[ ] 10. CLEANUP: Remove TODO.md
```

## **Expected Result:**

```
🎉 ALL 5 TEAMS: Identical Active Projects table
✅ Same table class, responsive, row markup, badges
✅ Single maintainable partial file
✅ No backend changes needed
```

**Next:** PHASE 3 → Test dashboards
**Status:** ✅ PHASE 1 & 2 COMPLETE → Ready for testing
