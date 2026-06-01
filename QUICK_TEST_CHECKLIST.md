# Quick Test Checklist for Fixed Scenarios

## 🎯 What We're Testing

Previously failing scenarios that are now fixed:
- ✅ **Scenario 5:** Post Team Assignment
- ✅ **Scenario 6:** Feedback on Post

**Root Cause of Previous Failures:**
- Missing notification templates (`post_team_assigned` and `post_feedback_received`)
- Templates have been added to the seeder and deployed

---

## 📋 Pre-Test Setup

### 1. Deploy Firebase Credentials (if not already done)

```bash
# On server
cd /www/wwwroot/user.bareqq.com

# Copy Firebase credentials
cp bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json storage/firebase/

# Verify file exists
ls -lh storage/firebase/bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json

# Clear cache
php artisan config:clear
php artisan cache:clear

# Restart PHP-FPM
systemctl restart php-fpm-82
```

### 2. Verify Templates Exist

```bash
mysql -u userbareqq -p userbareqq -e "SELECT type, title_en, title_ar FROM notification_templates WHERE type IN ('post_team_assigned', 'post_feedback_received');"
```

**Expected Output:**
```
+----------------------+------------------+------------------+
| type                 | title_en         | title_ar         |
+----------------------+------------------+------------------+
| post_team_assigned   | Post Assignment  | تعيين منشور      |
| post_feedback_received| New Feedback    | ملاحظات جديدة    |
+----------------------+------------------+------------------+
```

---

## 🧪 Test Scenario 5: Post Team Assignment

### Step 1: Call the Endpoint

**Use:** Admin token  
**Endpoint:** `POST /admin/posts/{post_id}/assign-team`

```bash
curl -X POST "https://user.bareqq.com/api/admin/posts/1/assign-team" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "designer_id": 1,
    "marketer_id": 1
  }'
```

### Step 2: Verify Notifications

```bash
mysql -u userbareqq -p userbareqq -e "SELECT id, notifiable_type, notifiable_id, title, created_at FROM notifications WHERE notifiable_type IN ('App\\\\Models\\\\Designer', 'App\\\\Models\\\\Marketer') ORDER BY created_at DESC LIMIT 5;"
```

### Step 3: Check Logs

```bash
tail -50 /www/wwwroot/user.bareqq.com/storage/logs/laravel.log | grep -i "team"
```

### ✅ Success Criteria

- [ ] 2 new notifications created (1 Designer, 1 Marketer)
- [ ] Title: "Post Assignment"
- [ ] Logs show: "Notification saved to database"
- [ ] No errors in logs

---

## 🧪 Test Scenario 6: Feedback on Post

### Step 1: Call the Endpoint

**Use:** Client token (so Admin + Designer + Marketer get notified)  
**Endpoint:** `POST /posts/{post_id}/feedback`

```bash
curl -X POST "https://user.bareqq.com/api/posts/1/feedback" \
  -H "Authorization: Bearer YOUR_CLIENT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "comment": "Please change the background color to blue",
    "status": "pending"
  }'
```

### Step 2: Verify Notifications

```bash
mysql -u userbareqq -p userbareqq -e "SELECT id, notifiable_type, notifiable_id, title, message, created_at FROM notifications ORDER BY created_at DESC LIMIT 10;"
```

### Step 3: Check Logs

```bash
tail -50 /www/wwwroot/user.bareqq.com/storage/logs/laravel.log | grep -i "feedback"
```

### ✅ Success Criteria

- [ ] 3 new notifications created (Admin, Designer, Marketer)
- [ ] Title: "New Feedback"
- [ ] Client who added feedback is NOT notified
- [ ] Logs show: "Notification saved to database" (3 times)
- [ ] No errors in logs

---

## 📊 Quick Verification

### Count All Notifications by Role

```bash
mysql -u userbareqq -p userbareqq -e "
SELECT 
    notifiable_type, 
    COUNT(*) as total 
FROM notifications 
GROUP BY notifiable_type;
"
```

### View Latest 20 Notifications

```bash
mysql -u userbareqq -p userbareqq -e "SELECT id, notifiable_type, notifiable_id, title, created_at FROM notifications ORDER BY created_at DESC LIMIT 20;"
```

---

## 🎯 Expected Final State

After running both tests, you should have:

| Role | Notification Count | Latest Notifications |
|------|-------------------|---------------------|
| Admin | 4+ | New Order, Post Approved, New Feedback |
| Client | 6+ | Order Confirmed, Payment Approved, New Post, Post Approved |
| Designer | 5+ | New Post, Post Assignment, New Feedback, Post Approved |
| Marketer | 5+ | New Post, Post Assignment, New Feedback, Post Approved |

---

## 🐛 If Tests Fail

### Check Templates
```bash
mysql -u userbareqq -p userbareqq -e "SELECT COUNT(*) FROM notification_templates;"
# Should return 15
```

### Re-run Seeder
```bash
cd /www/wwwroot/user.bareqq.com
php artisan db:seed --class=NotificationTemplatesSeeder
```

### Check Firebase Credentials
```bash
ls -lh /www/wwwroot/user.bareqq.com/storage/firebase/bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json
```

### View Full Logs
```bash
tail -100 /www/wwwroot/user.bareqq.com/storage/logs/laravel.log
```

---

## ✅ All Tests Pass?

If both scenarios work correctly:

1. ✅ Notification system is 100% complete
2. ✅ All 7 scenarios are working
3. ✅ System is production-ready
4. ✅ No further action needed

**Congratulations! 🎉**
