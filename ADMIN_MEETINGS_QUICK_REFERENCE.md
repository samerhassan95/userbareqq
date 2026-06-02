# Admin Meetings - Quick Reference

## Status Overview ✅

All meeting endpoints are **IMPLEMENTED** in the codebase. Here's what's available:

| Endpoint | Method | Route | Purpose |
|----------|--------|-------|---------|
| Get All Meetings | `GET` | `/admin/meetings` | List all meetings with filters |
| Get Meeting Details | `GET` | `/admin/meetings/{id}` | Get specific meeting info |
| Update Status | `PUT` | `/admin/meetings/{id}/status` | Change meeting status |
| Add Team Members | `POST` | `/admin/meetings/{id}/team` | Manually assign team members |
| Remove Team Member | `DELETE` | `/admin/meetings/{id}/team/{teamMemberId}` | Remove from meeting |
| Auto-Sync from Posts | `POST` | `/admin/meetings/{id}/team/sync-from-strategy` | Auto-assign from strategy posts |

---

## Meeting Status Flow 🔄

```
Initial State
    ↓
waiting / request_sent
    ├─→ confirmed  ─→ completed (terminal)
    └─→ canceled (terminal)
```

### Status Descriptions
- **waiting/request_sent** - Initial state, awaiting confirmation
- **confirmed** - Meeting approved and confirmed
- **completed** - Meeting has finished (cannot change)
- **canceled** - Meeting has been cancelled (cannot change)

---

## Team Auto-Sync Feature 🤖

### What It Does
Automatically adds all team members from a strategy's posts to a meeting.

**Example:**
```
Strategy Order (ID: 12) has these posts:
├── Post 1: Designer A, Marketer B
├── Post 2: Designer A, Designer C  
└── Post 3: Marketer D

Meeting for Strategy 12
After Sync → Team = [Designer A, Designer C, Marketer B, Marketer D]
```

### How to Use
```bash
POST /admin/meetings/1/team/sync-from-strategy
```

**Requirements:**
- Meeting must have `strategy_id` set
- Strategy order must have posts
- Posts must have team members assigned

**Response:**
```json
{
    "status": true,
    "message": "4 team member(s) synced from strategy posts.",
    "synced": [...],
    "skipped": [...]
}
```

---

## Manual Team Member Assignment 👥

### Add Team Members
```bash
POST /admin/meetings/1/team

{
    "members": [
        {"type": "designer", "id": 5},
        {"type": "marketer", "id": 3}
    ]
}
```

### Remove Team Member
```bash
DELETE /admin/meetings/1/team/7
```

**Note:** Use the `team_member_id` from meeting details, not the employee ID.

---

## Status Changes 📊

### Confirm Meeting
```bash
PUT /admin/meetings/1/status

{
    "status": "confirmed"
}
```

### Cancel Meeting
```bash
PUT /admin/meetings/1/status

{
    "status": "canceled"
}
```

### Mark as Completed
```bash
PUT /admin/meetings/1/status

{
    "status": "completed"
}
```

### Effects
- Client receives Firebase notification
- Notification includes meeting name and new status
- Completed/canceled meetings cannot be reopened

---

## Database Tables

### meetings
```
id, slot_id, client_id, strategy_id, meeting_name, description,
date, start_time, end_time, jitsi_url, status, notes,
created_at, updated_at
```

### meeting_team_members
```
id, meeting_id, employee_type (designer/marketer), employee_id,
created_at, updated_at

Unique Constraint: [meeting_id, employee_type, employee_id]
```

---

## API Response Format

### Success
```json
{
    "status": true,
    "message": "...",
    "data": { ... }
}
```

### Error
```json
{
    "status": false,
    "message": "..."
}
```

---

## Postman Collection Updates ✨

Added complete **Admin - Meetings** section with all 6 endpoints:
1. Get All Meetings
2. Get Meeting Details  
3. Update Meeting Status
4. Cancel Meeting (dedicated endpoint)
5. Add Team Members to Meeting
6. Remove Team Member from Meeting
7. Auto-Sync Team from Strategy Posts

**Location:** Between "Admin - Team Members" and "Client Meetings" sections

---

## Implementation Highlights

### Features
- ✅ Automatic team deduplication
- ✅ Transaction-based operations
- ✅ Firebase notification on status change
- ✅ Idempotent auto-sync (safe to call multiple times)
- ✅ Efficient batch loading (prevents N+1 queries)
- ✅ Comprehensive error handling
- ✅ Terminal state protection (cannot reopen completed/canceled)

### Security
- ✅ Admin authentication required
- ✅ Input validation on all fields
- ✅ Employee existence verification
- ✅ Status transition validation

---

## Common Tasks

### Create and Configure Meeting
```
1. Client creates meeting (POST /api/client/meetings)
2. Admin views meeting (GET /admin/meetings/1)
3. Admin syncs team (POST /admin/meetings/1/team/sync-from-strategy)
4. Admin adds extra members if needed (POST /admin/meetings/1/team)
5. Admin confirms meeting (PUT /admin/meetings/1/status -> "confirmed")
6. After meeting: Mark complete (PUT /admin/meetings/1/status -> "completed")
```

### Cancel Meeting
```
1. Admin navigates to meeting
2. Admin updates status to "canceled"
3. Client automatically receives cancellation notification
```

### View Meeting Team
```
1. GET /admin/meetings/1
2. Response includes full team array with names and photos
```

---

## Files Modified

- **Postman Collection:** `Bareqq_Complete_API.postman_collection.json`
  - Added complete "Admin - Meetings" section
  
- **Documentation:** `ADMIN_MEETINGS_GUIDE.md` (new)
  - Comprehensive guide with examples

---

## Next Steps

1. ✅ **Test Endpoints** - Use Postman to test all 6 endpoints
2. ✅ **Test Auto-Sync** - Create strategy with posts and team, sync to meeting
3. ✅ **Test Notifications** - Confirm Firebase notifications work on status changes
4. ✅ **Frontend Integration** - Build UI for admin meeting management

---

## Questions?

Refer to:
- **Full Details:** `ADMIN_MEETINGS_GUIDE.md`
- **Code:** `app/Http/Controllers/Admin/AdminMeetingController.php`
- **Model:** `app/Models/Meeting.php`
- **Routes:** `routes/admin.php` (lines 85-94)

