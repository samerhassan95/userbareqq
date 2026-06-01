# Notification System - Testing Guide

## 🎯 Current Status

The notification system is **complete and ready for final testing**. All code has been deployed, and we're ready to test the previously failing scenarios.

---

## ⚠️ Important: Firebase Credentials

The Firebase credentials file was **NOT pushed to git** (for security reasons). You need to deploy it manually to the server.

### Quick Deploy Firebase Credentials

**Option 1: Using SCP (from your local machine)**
```bash
scp E:\bareqq-clone\codgoo\bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json root@user.bareqq.com:/www/wwwroot/user.bareqq.com/storage/firebase/
```

**Option 2: Manual copy on server**
```bash
# If you have the file on the server already
cd /www/wwwroot/user.bareqq.com
cp /path/to/bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json storage/firebase/
chmod 600 storage/firebase/bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json
```

See `FIREBASE_CREDENTIALS_SETUP.md` for detailed instructions.

---

## 🚀 Deployment Steps

### 1. Pull Latest Code

```bash
cd /www/wwwroot/user.bareqq.com
git pull origin main
```

### 2. Deploy Firebase Credentials

```bash
# Use one of the methods above to copy the file
# Then verify:
ls -lh storage/firebase/bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json
```

### 3. Run Deployment Script

```bash
bash deploy_notification_system.sh
```

This will:
- Add missing notification templates to database
- Check Firebase credentials
- Clear cache
- Restart PHP-FPM

---

## 🧪 Testing

### Run the Test Script

```bash
bash test_fixed_scenarios.sh
```

This will guide you through testing:
- **Scenario 5:** Post Team Assignment
- **Scenario 6:** Feedback on Post

### What to Test

#### Scenario 5: Post Team Assignment
```bash
# Endpoint: POST /admin/posts/{post_id}/assign-team
# Use: Admin token
# Expected: Designer + Marketer notified

curl -X POST "https://user.bareqq.com/api/admin/posts/1/assign-team" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "designer_id": 1,
    "marketer_id": 1
  }'
```

#### Scenario 6: Feedback
```bash
# Endpoint: POST /posts/{post_id}/feedback
# Use: Client token
# Expected: Admin + Designer + Marketer notified (not Client)

curl -X POST "https://user.bareqq.com/api/posts/1/feedback" \
  -H "Authorization: Bearer YOUR_CLIENT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "comment": "Please change the background color",
    "status": "pending"
  }'
```

---

## ✅ Verification

### Check Database

```sql
-- Recent notifications
SELECT id, notifiable_type, notifiable_id, title, created_at 
FROM notifications 
ORDER BY created_at DESC LIMIT 10;

-- Count by role
SELECT notifiable_type, COUNT(*) as total 
FROM notifications 
GROUP BY notifiable_type;
```

### Check Logs

```bash
# Recent notification logs
tail -50 /www/wwwroot/user.bareqq.com/storage/logs/laravel.log | grep -i "notification"

# Team assignment logs
tail -50 /www/wwwroot/user.bareqq.com/storage/logs/laravel.log | grep -i "team"

# Feedback logs
tail -50 /www/wwwroot/user.bareqq.com/storage/logs/laravel.log | grep -i "feedback"
```

---

## 📊 Expected Results

### Scenario 5 Success Criteria
- ✅ 2 new notifications in database
- ✅ 1 for Designer (notifiable_type = 'App\Models\Designer')
- ✅ 1 for Marketer (notifiable_type = 'App\Models\Marketer')
- ✅ Title: "Post Assignment"
- ✅ Logs show: "Notification saved to database"

### Scenario 6 Success Criteria
- ✅ 3 new notifications in database
- ✅ 1 for Admin
- ✅ 1 for Designer
- ✅ 1 for Marketer
- ✅ Client who added feedback NOT notified
- ✅ Title: "New Feedback"
- ✅ Logs show: "Notification saved to database" (3 times)

---

## 🐛 If Tests Fail

### Check Templates Exist

```bash
mysql -u userbareqq -p userbareqq -e "SELECT type, title_en FROM notification_templates WHERE type IN ('post_team_assigned', 'post_feedback_received');"
```

Should return:
```
+------------------------+------------------+
| type                   | title_en         |
+------------------------+------------------+
| post_team_assigned     | Post Assignment  |
| post_feedback_received | New Feedback     |
+------------------------+------------------+
```

### Check Firebase Credentials

```bash
ls -lh /www/wwwroot/user.bareqq.com/storage/firebase/bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json
```

If missing, deploy using instructions in `FIREBASE_CREDENTIALS_SETUP.md`

### Re-run Deployment

```bash
cd /www/wwwroot/user.bareqq.com
bash deploy_notification_system.sh
```

### Check Full Logs

```bash
tail -100 /www/wwwroot/user.bareqq.com/storage/logs/laravel.log
```

---

## 📚 Documentation Files

- `NOTIFICATION_SYSTEM_COMPLETE.md` - Complete implementation summary
- `FINAL_DEPLOYMENT_AND_TEST.md` - Detailed deployment guide
- `QUICK_TEST_CHECKLIST.md` - Quick testing checklist
- `FIREBASE_CREDENTIALS_SETUP.md` - Firebase credentials setup guide
- `test_fixed_scenarios.sh` - Automated test script
- `deploy_notification_system.sh` - Deployment script
- `deploy_firebase_credentials.sh` - Firebase deployment script

---

## 🎉 Success!

When both scenarios pass:
- ✅ All 7 notification scenarios are working
- ✅ System is 100% complete
- ✅ Production ready
- ✅ No further action needed

---

## 📞 Quick Reference

### Test Command
```bash
bash test_fixed_scenarios.sh
```

### Check Notifications
```bash
mysql -u userbareqq -p userbareqq -e "SELECT id, notifiable_type, title, created_at FROM notifications ORDER BY created_at DESC LIMIT 10;"
```

### Check Logs
```bash
tail -50 /www/wwwroot/user.bareqq.com/storage/logs/laravel.log | grep -i "notification"
```

### Clear Cache
```bash
cd /www/wwwroot/user.bareqq.com
php artisan config:clear && php artisan cache:clear
systemctl restart php-fpm-82
```

---

**Ready to test! 🚀**
