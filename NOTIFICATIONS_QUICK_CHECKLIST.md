# Notifications Endpoint - Quick Testing Checklist

## 🚀 Quick Start (5 Steps)

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Seed Test Data
```bash
# Import the SQL file or run it in your database client
mysql -u your_user -p your_database < test_notifications_seed.sql
```

### 3. Clear Cache
```bash
php artisan config:clear && php artisan cache:clear && php artisan route:clear
```

### 4. Login as Each Role
Use Postman "Universal Login (All Roles)" folder to login as:
- ✅ Admin
- ✅ Client  
- ✅ Designer
- ✅ Marketer
- ✅ Employee (if exists)

### 5. Test Notifications
For each role, test these 3 endpoints:
- ✅ `GET /notifications` - Should return 3 notifications
- ✅ `POST /notifications/{id}/read` - Should mark as read
- ✅ `POST /notifications/read-all` - Should mark all as read

---

## ✅ Testing Matrix

| Test Case | Admin | Client | Designer | Marketer | Employee |
|-----------|-------|--------|----------|----------|----------|
| Login & Get Token | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ |
| GET /notifications | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ |
| POST /notifications/1/read | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ |
| POST /notifications/read-all | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ |
| Unauthorized (no token) | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ |
| Invalid notification ID | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ |

---

## 🔍 Quick Verification Commands

### Check if device_token exists in all tables
```sql
DESCRIBE admins;
DESCRIBE clients;
DESCRIBE designers;
DESCRIBE marketers;
DESCRIBE employees;
```
Look for `device_token` column in each table.

### Check test notifications
```sql
SELECT notifiable_type, COUNT(*) as total 
FROM notifications 
GROUP BY notifiable_type;
```
Should show 3 notifications for each role.

### Check unread notifications
```sql
SELECT notifiable_type, COUNT(*) as unread 
FROM notifications 
WHERE is_read = 0 
GROUP BY notifiable_type;
```

---

## 🐛 Common Issues

| Issue | Solution |
|-------|----------|
| "Unauthorized" with valid token | Re-login to get fresh token |
| Empty notifications array | Run `test_notifications_seed.sql` |
| "Notification not found" | Check `notifiable_type` matches exactly |
| Designer/Marketer missing device_token | Run migration again |

---

## 📝 Expected Responses

### Success - GET /notifications
```json
{
    "status": true,
    "data": [
        {
            "id": 1,
            "title": "Welcome Admin",
            "message": "Your admin account is active",
            "data": null,
            "is_read": false,
            "created_at": "2026-05-31T12:00:00.000000Z",
            "notification_type": null
        }
    ]
}
```

### Success - Mark as Read
```json
{
    "status": true,
    "message": "Notification marked as read."
}
```

### Error - Unauthorized
```json
{
    "status": false,
    "message": "Unauthorized"
}
```

### Error - Not Found
```json
{
    "status": false,
    "message": "Notification not found."
}
```

---

## 🎯 Success Criteria

All checkboxes in the testing matrix should be ✅ for the endpoint to be considered fully working.

---

## 📚 Full Documentation

See `NOTIFICATIONS_TESTING_GUIDE.md` for detailed testing instructions.
