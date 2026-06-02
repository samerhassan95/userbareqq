# Admin Meetings Management Guide

## Overview
The Admin Meetings system allows administrators to manage meetings, assign team members, automatically sync team members from strategy posts, and track meeting status changes.

---

## API Endpoints

### 1. Get All Meetings
**Route:** `GET /admin/meetings`

**Query Parameters:**
- `status` - Filter by status (waiting, request_sent, confirmed, completed, canceled)
- `strategy_id` - Filter by strategy order ID
- `date` - Filter by meeting date (YYYY-MM-DD format)

**Response:**
```json
{
    "status": true,
    "data": [
        {
            "id": 1,
            "meeting_name": "Strategy Planning",
            "description": "Monthly planning meeting",
            "meeting_date": "2026-06-15",
            "start_time": "10:00",
            "end_time": "11:00",
            "jitsi_url": "https://meet.jitsi.org/...",
            "status": "confirmed",
            "notes": "Preparation for Q2 campaign",
            "client": {
                "id": 1,
                "name": "Client Name",
                "email": "client@example.com"
            },
            "strategy": {
                "id": 12,
                "name": "Digital Marketing Strategy"
            },
            "team": [
                {
                    "team_member_id": 1,
                    "id": 5,
                    "name": "John Designer",
                    "image": "https://...",
                    "type": "designer"
                },
                {
                    "team_member_id": 2,
                    "id": 3,
                    "name": "Sarah Marketer",
                    "image": "https://...",
                    "type": "marketer"
                }
            ],
            "created_at": "2026-06-01 10:30:00",
            "updated_at": "2026-06-01 14:45:00"
        }
    ]
}
```

---

### 2. Get Meeting Details
**Route:** `GET /admin/meetings/{id}`

**Response:** Same format as single meeting from Get All Meetings

---

### 3. Update Meeting Status
**Route:** `PUT /admin/meetings/{id}/status`

**Request Body:**
```json
{
    "status": "confirmed"
}
```

**Valid Status Values:**
| Status | Description | Allowed Transitions |
|--------|-------------|-------------------|
| `waiting` | Initial state | → confirmed, canceled |
| `request_sent` | Default state | → confirmed, canceled |
| `confirmed` | Meeting approved | → completed, canceled |
| `completed` | Meeting finished | (cannot change) |
| `canceled` | Meeting cancelled | (cannot change) |

**Status Flow Diagram:**
```
waiting/request_sent  ─→  confirmed  ─→  completed
        ↓                    ↓
        └─────→ canceled ────┴──→ (terminal state)
```

**Response:**
```json
{
    "status": true,
    "message": "Meeting status updated to 'confirmed' successfully.",
    "data": {
        "id": 1,
        "status": "confirmed"
    }
}
```

**Business Rules:**
- Cannot change status from `completed` or `canceled` to another status
- Status changes trigger Firebase push notification to the client
- Notification includes meeting name and new status

---

### 4. Cancel Meeting
**Route:** `PUT /admin/meetings/{id}/status`

**Request Body:**
```json
{
    "status": "canceled"
}
```

**Response:**
```json
{
    "status": true,
    "message": "Meeting status updated to 'canceled' successfully.",
    "data": {
        "id": 1,
        "status": "canceled"
    }
}
```

**Effects:**
- Sets meeting status to "canceled"
- Client receives Firebase notification about cancellation
- Canceled meetings cannot be reopened or changed

---

### 5. Add Team Members to Meeting
**Route:** `POST /admin/meetings/{id}/team`

**Request Body:**
```json
{
    "members": [
        {
            "type": "designer",
            "id": 5
        },
        {
            "type": "marketer",
            "id": 3
        }
    ]
}
```

**Parameters:**
- `members` (array, required) - List of team members to add
  - `type` (string, required) - Either "designer" or "marketer"
  - `id` (integer, required) - Employee ID

**Response:**
```json
{
    "status": true,
    "message": "2 team member(s) added.",
    "added": [
        {
            "type": "designer",
            "id": 5,
            "name": "John Designer",
            "image": "https://example.com/photo.jpg"
        },
        {
            "type": "marketer",
            "id": 3,
            "name": "Sarah Marketer",
            "image": "https://example.com/photo.jpg"
        }
    ],
    "skipped": [
        {
            "type": "designer",
            "id": 7,
            "reason": "Already in team"
        }
    ]
}
```

**Features:**
- Automatically verifies designer/marketer exists
- Prevents duplicate team members
- Returns detailed response with added and skipped members
- All operations wrapped in transaction for data integrity

---

### 6. Remove Team Member from Meeting
**Route:** `DELETE /admin/meetings/{id}/team/{teamMemberId}`

**Path Parameters:**
- `id` - Meeting ID
- `teamMemberId` - Meeting team member record ID (not the employee ID)

**Response:**
```json
{
    "status": true,
    "message": "Team member removed from meeting."
}
```

---

### 7. Auto-Sync Team from Strategy Posts
**Route:** `POST /admin/meetings/{id}/team/sync-from-strategy`

**How It Works:**
1. Retrieves the meeting's linked strategy order
2. Finds all posts associated with that strategy order
3. Extracts all designers and marketers assigned to those posts
4. Adds them as team members to the meeting
5. Skips duplicates automatically

**Request:** No body required

**Response:**
```json
{
    "status": true,
    "message": "3 team member(s) synced from strategy posts.",
    "synced": [
        {
            "type": "designer",
            "id": 5
        },
        {
            "type": "marketer",
            "id": 3
        },
        {
            "type": "designer",
            "id": 8
        }
    ],
    "skipped": [
        {
            "type": "marketer",
            "id": 2
        }
    ]
}
```

**Prerequisites:**
- Meeting must have a `strategy_id` set (linked to a strategy order)
- Strategy order must have posts with team members assigned

**Example Scenario:**
```
Strategy Order (ID: 12)
├── Post 1
│   ├── Designer A (ID: 5)
│   └── Marketer B (ID: 3)
├── Post 2
│   ├── Designer A (ID: 5)  ← Duplicate, will be skipped
│   └── Designer C (ID: 8)
└── Post 3
    └── Marketer D (ID: 9)

Meeting Linked to Strategy 12
├── Initial Team: [Marketer B (ID: 3)]
└── After Sync:
    ├── Marketer B (ID: 3) ← Already existed
    ├── Designer A (ID: 5) ← Added from Post 1
    ├── Designer C (ID: 8) ← Added from Post 2
    └── Marketer D (ID: 9) ← Added from Post 3
```

**Notes:**
- Idempotent: Safe to call multiple times without creating duplicates
- Uses efficient database queries to avoid N+1 problems
- Resolves Laravel morph types (App\Models\Designer → 'designer')

---

## Team Member Auto-Sync Concept

### Problem Solved
When creating a meeting for a strategy order, the admin previously had to manually add each team member who worked on the strategy's posts. This was tedious and error-prone.

### Solution
The auto-sync feature automatically gathers all team members from posts in a strategy and adds them to any meeting for that strategy.

### Data Flow
```
Posts with Team Assignments
    ↓
    ├── Post A → Designer 1, Marketer 2
    ├── Post B → Designer 1, Designer 3
    └── Post C → Marketer 4
    ↓
Auto-Sync Endpoint
    ↓
Meeting Team Members (deduplicated)
    ├── Designer 1
    ├── Designer 3
    ├── Marketer 2
    └── Marketer 4
```

---

## Meeting Status Flow

### Status Transitions

**Waiting/Request Sent** (Initial States)
- Client receives meeting notification
- Can transition to: confirmed or canceled
- Cannot mark as completed directly

**Confirmed**
- Meeting has been approved
- Can transition to: completed or canceled
- Client receives confirmation notification

**Completed**
- Meeting has finished
- Terminal state - cannot change status
- Cannot reopen or cancel

**Canceled**
- Meeting has been cancelled
- Terminal state - cannot change status
- Client receives cancellation notification
- Cannot reopen or complete

### Best Practices

1. **Creating Meetings**
   - Start with `waiting` or `request_sent` status
   - Add team members (manually or via auto-sync)
   - Wait for confirmation

2. **Before Meeting Starts**
   - Update status to `confirmed` once approved
   - Ensure all team members are assigned
   - Send confirmation notification to client

3. **After Meeting**
   - Update status to `completed`
   - Cannot revert or make changes after this

4. **Cancellations**
   - Set status to `canceled` with note explaining reason
   - Client will be notified
   - Previous changes are preserved in database

---

## Firebase Notifications

When meeting status is changed, the following Firebase notification is sent to the client:

**Event:** `meeting_status_updated`

**Data Included:**
- `meeting_id` - The meeting ID
- `status` - New status value
- `meeting_name` - Name of the meeting

**Template Variables Replaced:**
- `{meeting_name}` - Actual meeting name
- `{status}` - Capitalized status (Confirmed, Completed, etc.)

**Example Notification:**
```
Title: "Meeting Status Update"
Message: "Your meeting 'Strategy Planning' is now Confirmed"
```

---

## Database Schema

### meetings Table
```sql
CREATE TABLE meetings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    slot_id BIGINT NULLABLE (FK to available_slots),
    client_id BIGINT NOT NULL (FK to clients),
    strategy_id BIGINT NULLABLE (FK to product_orders),
    meeting_name VARCHAR(255) NOT NULL,
    description TEXT NULLABLE,
    date DATE NULLABLE,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    jitsi_url VARCHAR(255) NULLABLE,
    status VARCHAR(50) DEFAULT 'waiting',
    notes TEXT NULLABLE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### meeting_team_members Table
```sql
CREATE TABLE meeting_team_members (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    meeting_id BIGINT NOT NULL (FK to meetings, CASCADE DELETE),
    employee_type VARCHAR(50) NOT NULL, -- 'designer' or 'marketer'
    employee_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_meeting_employee (meeting_id, employee_type, employee_id)
);
```

---

## Error Handling

### Common Errors

**404 - Meeting Not Found**
```json
{
    "status": false,
    "message": "Meeting not found"
}
```

**422 - Cannot Change Completed/Canceled Meeting**
```json
{
    "status": false,
    "message": "Cannot change status of a completed meeting."
}
```

**422 - No Strategy Linked**
```json
{
    "status": false,
    "message": "This meeting has no strategy order linked. Cannot auto-sync team."
}
```

**422 - Invalid Status**
```json
{
    "status": false,
    "message": "The selected status is invalid.",
    "allowed": ["waiting", "request_sent", "confirmed", "completed", "canceled"]
}
```

---

## Implementation Examples

### Example 1: Create and Configure a Meeting

```bash
# 1. Create meeting (via client endpoint or manual DB)
POST /api/client/meetings
{
    "date": "2026-06-15",
    "start_time": "10:00",
    "strategy_id": 12,
    "meeting_name": "Q2 Strategy Review",
    "description": "Review Q2 digital marketing strategy",
    "end_time": "11:00"
}

# 2. Auto-sync team members from strategy posts
POST /admin/meetings/1/team/sync-from-strategy

# Response: 3 team members synced from strategy

# 3. Add any additional team members
POST /admin/meetings/1/team
{
    "members": [
        {"type": "designer", "id": 12}
    ]
}

# 4. Confirm meeting when ready
PUT /admin/meetings/1/status
{
    "status": "confirmed"
}

# 5. After meeting ends
PUT /admin/meetings/1/status
{
    "status": "completed"
}
```

### Example 2: Cancel a Meeting

```bash
PUT /admin/meetings/5/status
{
    "status": "canceled"
}

# Response: Client receives notification about cancellation
```

### Example 3: Update Team Members

```bash
# Get current team
GET /admin/meetings/1
# Reviews current team in response

# Add new designer
POST /admin/meetings/1/team
{
    "members": [
        {"type": "designer", "id": 15}
    ]
}

# Remove team member (get teamMemberId from meeting details)
DELETE /admin/meetings/1/team/7
```

---

## Best Practices

1. **Always Use Auto-Sync First**
   - Call sync-from-strategy endpoint first to get all strategy post team members
   - Then manually add any additional team members if needed

2. **Team Member Timing**
   - Add team members before confirming meeting
   - Don't remove team members after meeting is in progress

3. **Status Management**
   - Use clear status values that make sense in workflow
   - Always transition from initial state → confirmed → completed
   - Only use canceled for actual cancellations, not no-shows

4. **Client Notifications**
   - Ensure client has device_token for receiving notifications
   - Status updates automatically notify client
   - No separate notification call needed

5. **Error Recovery**
   - If auto-sync fails, manually add team members
   - Check that strategy_id is set on meeting
   - Verify team members exist before adding

---

## Related Endpoints

- **Client Meetings:** `/api/client/meetings` - Client can view/create meetings
- **Posts Team Management:** `/admin/posts/{id}/team` - Manage post team members
- **Strategy Team Management:** `/admin/product-orders/{id}/team` - Manage strategy order team
- **Notifications:** `/notifications` - Get/mark notifications as read
- **Available Slots:** `/api/client/available-slots` - Check slot availability

---

## Troubleshooting

**Problem: Auto-sync returns no members**
- Check that meeting has strategy_id set
- Verify the strategy order has posts
- Verify those posts have team members assigned

**Problem: Cannot change meeting status**
- Check if meeting is in completed or canceled state
- These are terminal states and cannot be changed

**Problem: Team member not being added**
- Ensure employee ID is correct
- Verify employee type (designer or marketer) is correct
- Check if member is already in the team (will be skipped)
- Verify the designer/marketer exists in database

**Problem: Client not receiving notifications**
- Check client has device_token set
- Verify notification template for 'meeting_status_updated' exists
- Check Firebase credentials are configured

