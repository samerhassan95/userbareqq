# Tickets API Contract (Client App Keys)

This document describes the exact keys and values expected by the Tickets feature in the mobile app.

## Endpoints used by app

- `GET client/client-tickets`
- `GET client/client-tickets?status={status}`
- `GET client/departments`
- `POST client/tickets` (create)
- `POST client/tickets/{id}` with `_method=put` (update)
- `DELETE client/tickets/{id}`
- `GET client/tickets/{ticketId}/replies`
- `POST client/ticket-reply` (client)
- `POST admin/ticket-reply` (admin mode in app)

---

## 1) Tickets list (client)

### `GET client/client-tickets`

### Response shape

```json
{
  "status": true,
  "message": "Tickets fetched successfully",
  "data": [
    {
      "id": 15,
      "subject": "Cannot upload file",
      "message": "I get an error when uploading",
      "status": "open",
      "priority": "High",
      "department": {
        "id": 2,
        "name": "Support"
      },
      "attachments": [
        {
          "path": "tickets/15/file.pdf",
          "url": "https://...",
          "name": "file.pdf"
        }
      ],
      "attachments_count": 1,
      "replies_count": 2,
      "latest_reply": {
        "reply": "We are checking this",
        "created_at": "2026-06-01 09:30:00",
        "is_from_admin": true
      },
      "created_by": {
        "id": 7,
        "name": "Mahmoud",
        "email": "m@example.com"
      },
      "created_at": "2026-06-01 09:00:00",
      "updated_at": "2026-06-01 09:30:00",
      "created_at_human": "30 minutes ago"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 10,
    "total": 45,
    "last_page": 5
  }
}
```

### Required keys

Top-level:
- `status`
- `message`
- `data`

Each ticket in `data[]`:
- `id`
- `subject`
- `message`
- `status`
- `priority`
- `department`
- `attachments`
- `attachments_count`
- `replies_count`
- `latest_reply` (nullable)
- `created_by`
- `created_at`
- `updated_at`
- `created_at_human`

Department keys:
- `id`
- `name`

Attachment keys:
- `path`
- `url`
- `name`

Latest reply keys:
- `reply`
- `created_at`
- `is_from_admin`

Created-by keys:
- `id`
- `name`
- `email`

---

## 2) Tickets list by status

### `GET client/client-tickets?status={status}`

Used in code as status-filtered endpoint.
Response shape should stay the same as tickets list.

### Status values

The UI expects status strings and color-maps by text:

- contains `open`
- contains `closed`
- contains `progress` (for in-progress style)

Recommended canonical values:
- `open`
- `closed`
- `in_progress`
- `answered`

---

## 3) Departments list

### `GET client/departments`

### Response shape

```json
{
  "status": true,
  "message": "Departments fetched successfully",
  "data": {
    "data": [
      {
        "id": 2,
        "name": "Support",
        "created_at": "2026-01-01 00:00:00",
        "updated_at": "2026-01-01 00:00:00"
      }
    ],
    "from": 1,
    "per_page": 10,
    "to": 1,
    "total": 1,
    "count": 1
  }
}
```

### Required keys

Top-level:
- `status`
- `message`
- `data`

Inside `data`:
- `data` (array of departments)
- `from`
- `per_page`
- `to`
- `total`
- `count`

Each department item:
- `id`
- `name`
- `created_at`
- `updated_at`

---

## 4) Create ticket (new flow)

### `POST client/tickets` (multipart/form-data)

### Request keys used by current create flow

```json
{
  "priority": "High",
  "department_id": 2,
  "subject": "Cannot upload file",
  "message": "I get an error when uploading",
  "attachments[]": ["<file>", "<file>"]
}
```

### Notes

- `attachments[]` is optional and can include multiple files.
- Accepted file types from UI picker:
  - `pdf`, `doc`, `docx`, `jpg`, `jpeg`, `png`

---

## 5) Create/update ticket (legacy flow still present)

Some screens/cubits still send legacy keys.

### Legacy create payload keys

```json
{
  "name": "Ticket title",
  "description": "Ticket description",
  "attachment": ["<file>", "<file>"],
  "department_id": "2",
  "priority": "High"
}
```

### Update payload keys

`POST client/tickets/{id}` with:

```json
{
  "_method": "put",
  "name": "Updated title",
  "description": "Updated description",
  "attachment": ["<file>"],
  "department_id": "2",
  "priority": "Medium"
}
```

Backend should continue accepting both create key styles (`subject/message` and `name/description`) for compatibility.

---

## 6) Delete ticket

### `DELETE client/tickets/{id}`

App only checks HTTP success/failure; standard wrapper recommended:

```json
{
  "status": true,
  "message": "Deleted successfully"
}
```

---

## 7) Ticket replies

### Get replies: `GET client/tickets/{ticketId}/replies`

### Response shape

```json
{
  "status": true,
  "message": "Replies fetched successfully",
  "data": [
    {
      "id": 100,
      "ticket_id": 15,
      "reply": "Please send screenshot",
      "attachments": [
        "https://..."
      ],
      "creator": {
        "id": 3,
        "name": "Support Agent",
        "type": "admin"
      },
      "is_from_admin": true,
      "is_from_client": false,
      "created_at": "2026-06-01 09:30:00",
      "created_at_human": "10 minutes ago"
    }
  ]
}
```

### Required keys

Top-level:
- `status`
- `message`
- `data`

Each reply item:
- `id`
- `ticket_id`
- `reply`
- `attachments` (array of strings/URLs)
- `creator`
- `is_from_admin`
- `is_from_client`
- `created_at`
- `created_at_human`

Creator keys:
- `id`
- `name`
- `type`

---

## 8) Create reply

### `POST client/ticket-reply` or `POST admin/ticket-reply`

### Request keys

```json
{
  "reply": "Any update?",
  "ticket_id": 15,
  "attachments[]": ["<file>", "<file>"]
}
```

`attachments[]` is optional.

---

## 9) Priority values used in app UI

Priority strings used and displayed:

- `Low`
- `Medium`
- `High`

UI color mapping is based on text contains:
- `high` -> warning/yellow
- `medium` -> blue
- others -> grey
