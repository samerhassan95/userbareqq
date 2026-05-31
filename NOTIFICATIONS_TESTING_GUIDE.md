# Notifications Endpoint Testing Guide

## Overview
The `/notifications` endpoint is a shared endpoint accessible to all authenticated roles: Admin, Client, Employee, Designer, and Marketer.

## Endpoints

### 1. GET `/notifications` - Get My Notifications
Retrieves all notifications for the authenticated user.

### 2. POST `/notifications/{id}/read` - Mark Notification as Read
Marks a specific notification as read.

### 3. POST `/notifications/read-all` - Mark All Notifications as Read
Marks all notifications for the authenticated user as read.

---

## Pre-Testing Setup

### Step 1: Run Migration
```bash
php artisan migrate
```

This adds `device_token` field to `designers`, `marketers`, and `employees` tables.

### Step 2: Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Step 3: Verify Database Structure
Check that all user tables have `device_token` column:
- ✅ admins
- ✅ clients
- ✅ employees
- ✅ designers
- ✅ marketers

---

## Testing Checklist

### For Each Role (Admin, Client, Designer, Marketer, Employee)

#### ✅ Test 1: Login and Get Token
1. Use Universal Login endpoint: `POST /login`
2. Save the token to the appropriate variable in Postman
3. Verify the response includes `type` field matching the role

**Postman Variables:**
- `admin_token`
- `client_token`
- `designer_token`
- `marketer_token`
- `employee_token` (if exists in collection)

#### ✅ Test 2: Get Notifications (Empty State)
**Request:**
```
GET {{base_url}}/notifications
Authorization: Bearer {{role_token}}
Accept-Language: en
```

**Expected Response:**
```json
{
    "status": true,
    "data": []
}
```

#### ✅ Test 3: Create Test Notification
You need to manually insert a notification for testing:

```sql
-- For Admin (ID 1)
INSERT INTO notifications (notifiable_id, notifiable_type, title, message, is_read, created_at, updated_at)
VALUES (1, 'App\\Models\\Admin', 'Test Notification', 'This is a test message', 0, NOW(), NOW());

-- For Client (ID 1)
INSERT INTO notifications (notifiable_id, notifiable_type, title, message, is_read, created_at, updated_at)
VALUES (1, 'App\\Models\\Client', 'Test Notification', 'This is a test message', 0, NOW(), NOW());

-- For Designer (ID 1)
INSERT INTO notifications (notifiable_id, notifiable_type, title, message, is_read, created_at, updated_at)
VALUES (1, 'App\\Models\\Designer', 'Test Notification', 'This is a test message', 0, NOW(), NOW());

-- For Marketer (ID 1)
INSERT INTO notifications (notifiable_id, notifiable_type, title, message, is_read, created_at, updated_at)
VALUES (1, 'App\\Models\\Marketer', 'Test Notification', 'This is a test message', 0, NOW(), NOW());

-- For Employee (ID 1)
INSERT INTO notifications (notifiable_id, notifiable_type, title, message, is_read, created_at, updated_at)
VALUES (1, 'App\\Models\\Employee', 'Test Notification', 'This is a test message', 0, NOW(), NOW());
```

#### ✅ Test 4: Get Notifications (With Data)
**Request:**
```
GET {{base_url}}/notifications
Authorization: Bearer {{role_token}}
Accept-Language: en
```

**Expected Response:**
```json
{
    "status": true,
    "data": [
        {
            "id": 1,
            "title": "Test Notification",
            "message": "This is a test message",
            "data": null,
            "is_read": false,
            "created_at": "2026-05-31T12:00:00.000000Z",
            "notification_type": null
        }
    ]
}
```

#### ✅ Test 5: Mark Single Notification as Read
**Request:**
```
POST {{base_url}}/notifications/1/read
Authorization: Bearer {{role_token}}
Accept-Language: en
```

**Expected Response:**
```json
{
    "status": true,
    "message": "Notification marked as read."
}
```

#### ✅ Test 6: Verify Notification is Read
**Request:**
```
GET {{base_url}}/notifications
Authorization: Bearer {{role_token}}
```

**Expected Response:**
```json
{
    "status": true,
    "data": [
        {
            "id": 1,
            "title": "Test Notification",
            "message": "This is a test message",
            "data": null,
            "is_read": true,  // ← Should be true now
            "created_at": "2026-05-31T12:00:00.000000Z",
            "notification_type": null
        }
    ]
}
```

#### ✅ Test 7: Mark All Notifications as Read
**Request:**
```
POST {{base_url}}/notifications/read-all
Authorization: Bearer {{role_token}}
Accept-Language: en
```

**Expected Response:**
```json
{
    "status": true,
    "message": "All notifications marked as read."
}
```

---

## Error Cases to Test

### ❌ Test 8: Unauthorized Access (No Token)
**Request:**
```
GET {{base_url}}/notifications
```

**Expected Response:**
```json
{
    "status": false,
    "message": "Unauthorized"
}
```
**Status Code:** 401

### ❌ Test 9: Invalid Token
**Request:**
```
GET {{base_url}}/notifications
Authorization: Bearer invalid_token_here
```

**Expected Response:**
```json
{
    "message": "Unauthenticated."
}
```
**Status Code:** 401

### ❌ Test 10: Mark Non-Existent Notification as Read
**Request:**
```
POST {{base_url}}/notifications/99999/read
Authorization: Bearer {{role_token}}
```

**Expected Response:**
```json
{
    "status": false,
    "message": "Notification not found."
}
```
**Status Code:** 404

### ❌ Test 11: Access Another User's Notification
Create a notification for User ID 2, then try to mark it as read using User ID 1's token.

**Expected Response:**
```json
{
    "status": false,
    "message": "Notification not found."
}
```
**Status Code:** 404

---

## Localization Testing

### Test 12: Arabic Language
**Request:**
```
GET {{base_url}}/notifications
Authorization: Bearer {{role_token}}
Accept-Language: ar
```

Verify that if notification templates have Arabic translations, they are returned correctly.

---

## Testing Matrix

| Role | Login | Get Notifications | Mark as Read | Mark All as Read | Unauthorized |
|------|-------|-------------------|--------------|------------------|--------------|
| Admin | ✅ | ✅ | ✅ | ✅ | ✅ |
| Client | ✅ | ✅ | ✅ | ✅ | ✅ |
| Designer | ✅ | ✅ | ✅ | ✅ | ✅ |
| Marketer | ✅ | ✅ | ✅ | ✅ | ✅ |
| Employee | ✅ | ✅ | ✅ | ✅ | ✅ |

---

## Postman Collection Updates

### Update Existing Requests

The current Postman collection has 3 requests in "Shared Notifications (All Roles)" folder. Each request uses `{{client_token}}` by default.

**To test all roles:**

1. **Duplicate each request 4 times** and rename them:
   - Get My Notifications - Admin
   - Get My Notifications - Client
   - Get My Notifications - Designer
   - Get My Notifications - Marketer
   - Get My Notifications - Employee

2. **Update the Authorization header** for each:
   - Admin: `Bearer {{admin_token}}`
   - Client: `Bearer {{client_token}}`
   - Designer: `Bearer {{designer_token}}`
   - Marketer: `Bearer {{marketer_token}}`
   - Employee: `Bearer {{employee_token}}`

3. **Repeat for all 3 endpoints**:
   - GET `/notifications`
   - POST `/notifications/{id}/read`
   - POST `/notifications/read-all`

---

## Common Issues & Solutions

### Issue 1: "Unauthorized" even with valid token
**Solution:** Verify the token is not expired. JWT tokens have expiration. Re-login to get a fresh token.

### Issue 2: Empty notifications array
**Solution:** Manually insert test notifications using the SQL queries above.

### Issue 3: "Notification not found" for valid ID
**Solution:** Ensure the notification's `notifiable_type` matches the authenticated user's model class exactly:
- `App\Models\Admin`
- `App\Models\Client`
- `App\Models\Designer`
- `App\Models\Marketer`
- `App\Models\Employee`

### Issue 4: Designer/Marketer cannot receive push notifications
**Solution:** Ensure the migration has been run and the models have `device_token` in `$fillable`.

---

## Additional Verification

### Verify Route Middleware
Check `routes/api.php`:
```php
Route::middleware(['auth:admin,client,employee,designer,marketer'])->group(function () {
    Route::get('notifications', [NotificationController::class, 'getNotifications']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markNotificationAsRead']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllNotificationsAsRead']);
});
```

### Verify Auth Guards
Check `config/auth.php` has all guards:
- ✅ admin
- ✅ client
- ✅ employee
- ✅ designer
- ✅ marketer

### Verify Models Implement JWTSubject
All user models should implement `Tymon\JWTAuth\Contracts\JWTSubject` and have:
- `getJWTIdentifier()`
- `getJWTCustomClaims()`

---

## Success Criteria

✅ All 5 roles can successfully:
1. Login and receive a valid JWT token
2. Retrieve their own notifications
3. Mark individual notifications as read
4. Mark all notifications as read
5. Cannot access other users' notifications
6. Receive proper error messages for invalid requests

---

## Next Steps After Testing

1. **Update Postman Collection** with all role variations
2. **Document any bugs** found during testing
3. **Test push notifications** (requires Firebase setup and device tokens)
4. **Test notification templates** if they exist
5. **Load testing** with multiple notifications per user

---

## Notes

- The endpoint uses **polymorphic relationships** (`notifiable_id` + `notifiable_type`)
- Each role sees **only their own notifications**
- Notifications are ordered by **latest first** (`->latest()`)
- The `is_read` field is a **boolean** (0 = unread, 1 = read)
- The endpoint supports **localization** via `Accept-Language` header
