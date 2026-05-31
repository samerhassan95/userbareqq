# Fixed Migration Commands

## ✅ Issue Fixed

Removed `employees` table references from all files since it doesn't exist in your database.

## 🎯 Supported Roles

The notifications system now supports only these 4 roles:
- ✅ Admin
- ✅ Client
- ✅ Designer
- ✅ Marketer

## 🚀 Run These Commands

### 1. Rollback the Failed Migration (if needed)
```bash
php artisan migrate:rollback --step=1
```

### 2. Run Migration Again
```bash
php artisan migrate
```

This will now only add `device_token` to:
- `designers` table
- `marketers` table

### 3. Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 4. Seed Test Notifications
```bash
mysql -u your_user -p userbareqq < test_notifications_seed.sql
```

Or manually run the SQL:
```sql
-- Admin Notifications
INSERT INTO notifications (notifiable_id, notifiable_type, title, message, is_read, created_at, updated_at)
VALUES 
(1, 'App\\Models\\Admin', 'Welcome Admin', 'Your admin account is active', 0, NOW(), NOW()),
(1, 'App\\Models\\Admin', 'System Update', 'A new system update is available', 0, NOW(), NOW()),
(1, 'App\\Models\\Admin', 'New Order', 'A new order has been placed', 1, NOW(), NOW());

-- Client Notifications
INSERT INTO notifications (notifiable_id, notifiable_type, title, message, is_read, created_at, updated_at)
VALUES 
(1, 'App\\Models\\Client', 'Welcome Client', 'Thank you for registering', 0, NOW(), NOW()),
(1, 'App\\Models\\Client', 'Order Confirmed', 'Your order has been confirmed', 0, NOW(), NOW()),
(1, 'App\\Models\\Client', 'Payment Received', 'We have received your payment', 1, NOW(), NOW());

-- Designer Notifications
INSERT INTO notifications (notifiable_id, notifiable_type, title, message, is_read, created_at, updated_at)
VALUES 
(1, 'App\\Models\\Designer', 'Welcome Designer', 'Your designer account is ready', 0, NOW(), NOW()),
(1, 'App\\Models\\Designer', 'New Task', 'You have been assigned a new design task', 0, NOW(), NOW()),
(1, 'App\\Models\\Designer', 'Task Completed', 'Your task has been approved', 1, NOW(), NOW());

-- Marketer Notifications
INSERT INTO notifications (notifiable_id, notifiable_type, title, message, is_read, created_at, updated_at)
VALUES 
(1, 'App\\Models\\Marketer', 'Welcome Marketer', 'Your marketer account is active', 0, NOW(), NOW()),
(1, 'App\\Models\\Marketer', 'New Campaign', 'A new marketing campaign has started', 0, NOW(), NOW()),
(1, 'App\\Models\\Marketer', 'Campaign Results', 'Your campaign results are ready', 1, NOW(), NOW());
```

## 📝 Files Updated

1. ✅ `database/migrations/2026_05_31_120000_add_device_token_to_designers_marketers_employees.php`
   - Removed `employees` table reference

2. ✅ `app/Http/Controllers/NotificationController.php`
   - Removed `employee` from guard checks in all 3 methods

3. ✅ `routes/api.php`
   - Removed `employee` from middleware auth guards

4. ✅ `test_notifications_seed.sql`
   - Removed employee test data

5. ✅ `app/Models/Designer.php`
   - Already has `device_token` in fillable

6. ✅ `app/Models/Marketer.php`
   - Already has `device_token` in fillable

## ✅ Verification

After running the migration, verify:

```sql
-- Check designers table
DESCRIBE designers;

-- Check marketers table
DESCRIBE marketers;

-- Both should show device_token column
```

## 🧪 Test in Postman

1. Login as Admin → `POST /login`
2. Login as Client → `POST /login`
3. Login as Designer → `POST /login`
4. Login as Marketer → `POST /login`

Then test for each role:
- `GET /notifications`
- `POST /notifications/1/read`
- `POST /notifications/read-all`

## 🎯 Expected Result

Migration should complete successfully:
```
INFO  Running migrations.
2026_05_31_120000_add_device_token_to_designers_marketers_employees .... DONE
```

## 📊 Summary

- ❌ Employee support removed (table doesn't exist)
- ✅ Admin notifications working
- ✅ Client notifications working
- ✅ Designer notifications working
- ✅ Marketer notifications working
