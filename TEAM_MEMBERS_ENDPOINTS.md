# Team Members Endpoints Documentation

## Overview
These endpoints allow admins to retrieve information about team members (designers and marketers) in the system.

## Endpoints

### 1. Get All Designers
**Endpoint:** `GET /api/admin/designers`

**Authentication:** Admin Bearer Token Required

**Query Parameters:**
- `search` (optional): Search by username, email, or phone
- `pagination` (optional): Enable/disable pagination (default: true)
- `per_page` (optional): Items per page (default: 15)

**Response:**
```json
{
  "status": true,
  "message": "Designers retrieved successfully",
  "data": [
    {
      "id": 1,
      "username": "designer1",
      "email": "designer@example.com",
      "phone": "+1234567890",
      "created_at": "2026-05-20 10:00:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 10,
    "last_page": 1
  }
}
```

---

### 2. Get All Marketers
**Endpoint:** `GET /api/admin/marketers`

**Authentication:** Admin Bearer Token Required

**Query Parameters:**
- `search` (optional): Search by username, email, or phone
- `pagination` (optional): Enable/disable pagination (default: true)
- `per_page` (optional): Items per page (default: 15)

**Response:**
```json
{
  "status": true,
  "message": "Marketers retrieved successfully",
  "data": [
    {
      "id": 1,
      "username": "marketer1",
      "email": "marketer@example.com",
      "phone": "+1234567890",
      "created_at": "2026-05-20 10:00:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 5,
    "last_page": 1
  }
}
```

---

### 3. Get All Team Members (Combined)
**Endpoint:** `GET /api/admin/team-members`

**Authentication:** Admin Bearer Token Required

**Query Parameters:**
- `search` (optional): Search by username, email, or phone
- `pagination` (optional): Enable/disable pagination (default: true)
- `per_page` (optional): Items per page (default: 15)

**Response:**
```json
{
  "status": true,
  "message": "Team members retrieved successfully",
  "data": {
    "designers": [
      {
        "id": 1,
        "username": "designer1",
        "email": "designer@example.com",
        "phone": "+1234567890",
        "type": "designer",
        "created_at": "2026-05-20 10:00:00"
      }
    ],
    "marketers": [
      {
        "id": 1,
        "username": "marketer1",
        "email": "marketer@example.com",
        "phone": "+1234567890",
        "type": "marketer",
        "created_at": "2026-05-20 10:00:00"
      }
    ],
    "all": [
      {
        "id": 1,
        "username": "designer1",
        "email": "designer@example.com",
        "phone": "+1234567890",
        "type": "designer",
        "created_at": "2026-05-20 10:00:00"
      },
      {
        "id": 1,
        "username": "marketer1",
        "email": "marketer@example.com",
        "phone": "+1234567890",
        "type": "marketer",
        "created_at": "2026-05-20 10:00:00"
      }
    ]
  },
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 15,
    "last_page": 1
  }
}
```

**Note:** The combined endpoint returns three arrays:
- `designers`: All designers matching the search criteria
- `marketers`: All marketers matching the search criteria
- `all`: Combined array of both designers and marketers

---

## Use Cases

### 1. Assigning Team Members to Posts
When creating or updating a post, you can use these endpoints to:
- Get a list of available designers to assign
- Get a list of available marketers to assign
- Get all team members in one call for a dropdown/selection UI

### 2. Team Management
- View all team members in the system
- Search for specific team members by name, email, or phone
- Filter by role (designer or marketer)

### 3. Reporting
- Get total count of designers and marketers
- Track team member creation dates
- Generate team reports

---

## Testing in Postman

1. **Import the Collection:**
   - Import `Bareqq_Complete_API.postman_collection.json`

2. **Set Variables:**
   - `base_url`: https://user.bareqq.com/api
   - `admin_token`: Your admin authentication token
   - `lang`: en or ar

3. **Test Endpoints:**
   - Navigate to "Admin - Team Members" folder
   - Run "Get All Designers"
   - Run "Get All Marketers"
   - Run "Get All Team Members (Combined)"

4. **Test Search:**
   - Add `?search=john` to search for team members with username "john"
   - Test pagination with `?per_page=5&pagination=true`

---

## Implementation Details

**Controller:** `App\Http\Controllers\Admin\AdminTeamController`

**Routes:** Defined in `routes/admin.php`

**Models:**
- `App\Models\Designer`
- `App\Models\Marketer`

**Authentication:** Admin middleware required

**Localization:** Supports English (en) and Arabic (ar) via Accept-Language header
