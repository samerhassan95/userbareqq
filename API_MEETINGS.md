# Meetings API Contract (Client App Keys)

This document lists the **exact keys and values** expected by the mobile app for meetings.
Use these field names as-is in backend request/response payloads.

## Base endpoints used by app

- `GET client/meetings`
- `GET client/meetings/filter?status={status}`
- `GET client/meetings/{meetingId}/join`
- `POST client/meetings`
- `DELETE client/meetings/{meetingId}`
- `GET client/available-slots?date={yyyy-mm-dd}`
- `GET client/unbooked-slots`

---

## 1) List meetings

### `GET client/meetings`
### `GET client/meetings/filter?status={status}`

### Query params

- `status` (optional int): used by filtered endpoint
- `task_id` (optional int): app may send it when filtering meetings by task

### Response shape

```json
{
  "data": [
    {
      "id": 10,
      "slot_id": 7,
      "client_id": 33,
      "description": "Kickoff call",
      "meeting_date": "2026-06-01",
      "meeting_name": "Project Kickoff",
      "start_time": "10:00",
      "end_time": "10:30",
      "jitsi_url": "https://meet.jit.si/room-name",
      "status": "confirmed",
      "project": {
        "id": 99,
        "name": "Brand Strategy"
      },
      "team": [
        {
          "id": 1,
          "name": "Ahmed",
          "image": "https://..."
        }
      ],
      "created_at": "2026-06-01 10:00:00",
      "updated_at": "2026-06-01 10:05:00"
    }
  ]
}
```

### Required keys for each meeting item

- `id`
- `slot_id`
- `client_id`
- `description`
- `meeting_name`
- `start_time`
- `end_time`
- `jitsi_url`
- `status`
- `project`
- `team`
- `created_at`
- `updated_at`

### Date key compatibility

The app reads date from:

- `meeting_date` (preferred)
- `date` (fallback)

Send `meeting_date` to avoid ambiguity.

### Accepted status values

The app maps these strings (case-insensitive):

- `completed`, `complete`
- `canceled`, `cancelled`, `cancel`
- `waiting`, `request sent`, `request_sent`, `pending`
- `confirmed`, `confirm`

If unknown/missing, app falls back to `waiting`.

---

## 2) Join meeting

### `GET client/meetings/{meetingId}/join`

### Response shape

```json
{
  "status": true,
  "message": "Joined successfully",
  "data": {
    "meeting_id": 10,
    "meeting_name": "Project Kickoff",
    "jitsi_url": "https://meet.jit.si/room-name",
    "project_name": "Brand Strategy",
    "start_time": "10:00",
    "end_time": "10:30",
    "status": "confirmed"
  }
}
```

### Required keys

Top level:
- `status`
- `message`
- `data`

`data` object:
- `meeting_id`
- `meeting_name`
- `jitsi_url`
- `project_name`
- `start_time`
- `end_time`
- `status`

---

## 3) Create meeting

### `POST client/meetings`

### Request body keys expected by app

```json
{
  "slot_id": 7,
  "client_id": 33,
  "start_time": "10:00",
  "project_id": "99",
  "meeting_name": "Project Kickoff",
  "description": "Kickoff call",
  "end_time": "10:30",
  "task_id": "501"
}
```

### Field notes

- `slot_id` (required int)
- `client_id` (sent by app from logged-in user id)
- `start_time` (required string)
- `meeting_name` (required string)
- `description` (required string)
- `end_time` (required string)
- `project_id` (optional string)
- `task_id` (optional string)

### Success behavior used in app

The app currently treats successful HTTP response as success and shows local success text.
Returning standard wrapper is recommended:

```json
{
  "status": true,
  "message": "Meeting created successfully"
}
```

---

## 4) Delete meeting

### `DELETE client/meetings/{meetingId}`

### Response keys required by app

```json
{
  "status": true,
  "message": "Meeting deleted successfully"
}
```

Important: app checks `status == true`.

---

## 5) Available slots

### `GET client/available-slots?date={yyyy-mm-dd}`

### Response shape

```json
{
  "status": true,
  "data": [
    {
      "slot_id": 7,
      "date": "2026-06-01",
      "start_time": "10:00",
      "end_time": "10:30",
      "status": true
    }
  ]
}
```

### Slot keys

- `slot_id`
- `date`
- `start_time`
- `end_time`
- `status` (bool)

### Special case in app

If backend returns `404` for no slots, app handles it as empty slots.
Preferred response is `200` with empty `data: []`.

---

## 6) Unbooked slots

### `GET client/unbooked-slots`

### Response shape

```json
{
  "status": true,
  "message": "Unbooked slots retrieved",
  "data": [
    {
      "slot_id": 7,
      "date": "2026-06-01",
      "start_time": "10:00",
      "end_time": "10:30"
    }
  ]
}
```

### Required keys

Top level:
- `status`
- `message`
- `data`

Each item:
- `slot_id`
- `date`
- `start_time`
- `end_time`

---

## 7) Meeting screen translation keys (UI only)

From `lib/features/meeting/presentation/meeting_view.dart`, current keys are:

- `'Meeting'.tr()`
- `'coming_soon'.tr()`

These are localization keys only and not part of API payloads.
