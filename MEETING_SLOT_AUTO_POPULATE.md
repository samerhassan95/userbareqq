# Meeting Creation: Auto-Populate from Slot ID

## Overview
Meeting creation now automatically retrieves date, start_time, and end_time from the `slot_id`. Clients no longer need to send these fields manually.

## What Changed

### Previous Behavior (❌ Old)
Clients had to send:
```json
{
  "date": "2026-06-15",
  "start_time": "10:00",
  "end_time": "11:00",
  "meeting_name": "Project Kickoff",
  "description": "Kickoff call",
  "strategy_id": 12
}
```

### New Behavior (✅ Current)
Clients only need to send:
```json
{
  "slot_id": 1,
  "meeting_name": "Project Kickoff",
  "description": "Kickoff call for new project",
  "strategy_id": 12
}
```

The system automatically:
1. Validates the `slot_id` exists in `available_slots` table
2. Retrieves `date`, `start_time`, and `end_time` from that slot
3. Checks if the slot is already booked
4. Creates the meeting with auto-populated time information

## Technical Implementation

### Updated Files

1. **`app/Http/Requests/MeetingRequest.php`**
   - `slot_id` is now **required** and validated with `exists:available_slots,id`
   - `date`, `start_time`, `end_time` are now **optional** (auto-populated)

2. **`app/Http/Controllers/MeetingController.php`**
   - `store()` method retrieves slot details automatically
   - No longer requires date/time in request
   - Uses slot data for overlap checking

3. **`app/Http/Controllers/Client/ClientMeetingController.php`**
   - `store()` method simplified to only require `slot_id`
   - Validates slot exists before creating meeting
   - Auto-populates date/time from slot

4. **Postman Collection**
   - Updated "Create Meeting" request body to reflect new requirements

### Validation Rules

```php
// Required fields
'slot_id'      => 'required|exists:available_slots,id'
'meeting_name' => 'required|string|max:255'

// Optional fields
'description'  => 'nullable|string|max:1000'
'strategy_id'  => 'nullable|exists:product_orders,id'
```

### Flow Diagram

```
1. Client sends: slot_id + meeting_name + description
                 ↓
2. System validates slot_id exists
                 ↓
3. System retrieves: slot.date, slot.start_time, slot.end_time
                 ↓
4. System checks for overlapping meetings
                 ↓
5. Meeting created with auto-populated date/time
```

## API Endpoints

### Create Meeting
**Endpoint:** `POST /api/meetings`

**Headers:**
```
Authorization: Bearer {client_token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "slot_id": 1,                    // REQUIRED - ID from available_slots table
  "meeting_name": "Project Kickoff",  // REQUIRED
  "description": "Kickoff call",      // OPTIONAL
  "strategy_id": 12                   // OPTIONAL
}
```

**Success Response (201):**
```json
{
  "status": true,
  "message": "Meeting request sent successfully",
  "data": {
    "id": 1,
    "slot_id": 1,
    "client_id": 2,
    "meeting_name": "Project Kickoff",
    "description": "Kickoff call",
    "date": "2026-06-15",
    "start_time": "10:00:00",
    "end_time": "11:00:00",
    "jitsi_url": "https://meet.jit.si/meeting-abc123",
    "status": "Request Sent",
    "created_at": "2026-06-02 10:00:00"
  }
}
```

**Error Responses:**

**404 - Invalid Slot:**
```json
{
  "status": false,
  "message": "Invalid slot ID. Slot not found."
}
```

**409 - Slot Already Booked:**
```json
{
  "status": false,
  "message": "This time slot is already booked. Please choose another time."
}
```

**422 - Validation Error:**
```json
{
  "status": false,
  "message": "The slot id field is required."
}
```

## Getting Available Slots

To get valid `slot_id` values, use the available slots endpoint (accessible by both admin and client):

**Endpoint:** `GET /api/meetings/available-slots?date=2026-06-15`

**Headers:**
```
Authorization: Bearer {client_token or admin_token}
```

**Response:**
```json
{
  "status": true,
  "data": [
    {
      "slot_id": 1718449200,
      "date": "2026-06-15",
      "start_time": "10:00",
      "end_time": "11:00",
      "status": true
    },
    {
      "slot_id": 1718452800,
      "date": "2026-06-15",
      "start_time": "11:00",
      "end_time": "12:00",
      "status": true
    }
  ]
}
```

**Also Available:**
- `GET /api/meetings/unbooked-slots` - Get all unbooked slots for next 7 days

## Database Schema

### available_slots Table
```sql
id              BIGINT PRIMARY KEY
date            DATE
start_time      TIME
end_time        TIME
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

### meetings Table
```sql
id              BIGINT PRIMARY KEY
slot_id         BIGINT (references available_slots.id)
client_id       BIGINT
strategy_id     BIGINT (nullable)
meeting_name    VARCHAR(255)
description     TEXT
date            DATE (auto-populated from slot)
start_time      TIME (auto-populated from slot)
end_time        TIME (auto-populated from slot)
jitsi_url       VARCHAR(255)
status          VARCHAR(50)
notes           TEXT (nullable)
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

## Benefits

1. **Consistency:** Date/time always matches the slot - no mismatches
2. **Simplicity:** Fewer fields for clients to send
3. **Validation:** Slot must exist in database before meeting creation
4. **Data Integrity:** Single source of truth for time information
5. **User Experience:** Clients select from available slots, system handles the rest

## Migration Notes

### For Frontend Developers
- Update meeting creation forms to only send `slot_id`
- Remove date/time picker fields from meeting creation
- Use "Available Slots" endpoint to populate slot selection
- Display slot date/time to users for selection, but send only `slot_id`

### For API Consumers
- Remove `date`, `start_time`, `end_time` from POST request body
- Add `slot_id` as required field
- First call `/api/meetings/available-slots?date={date}` to get valid slots
- Then call `/api/meetings` with selected `slot_id`

## Testing Checklist

- [ ] Create meeting with valid `slot_id` → Success
- [ ] Create meeting without `slot_id` → 422 Validation error
- [ ] Create meeting with invalid `slot_id` → 404 Not found
- [ ] Create meeting with already booked `slot_id` → 409 Conflict
- [ ] Verify date/time auto-populated from slot
- [ ] Verify overlap detection works correctly
- [ ] Test with multiple clients booking different slots
- [ ] Test slot booking conflicts

## Backward Compatibility

⚠️ **Breaking Change:** This is a breaking API change. 

Old requests with `date`, `start_time`, `end_time` will be **ignored** if they don't include a valid `slot_id`.

## Support

For issues or questions, refer to:
- Main API documentation: `API_MEETINGS.md`
- Admin meeting guide: `ADMIN_MEETINGS_COMPLETE.md`
- Quick reference: `ADMIN_MEETINGS_QUICK_REFERENCE.md`
