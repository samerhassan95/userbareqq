# Step-by-Step Notification Testing Guide

## 🎯 Prerequisites

Before starting, make sure:
- ✅ Notification templates are seeded
- ✅ Cache is cleared
- ✅ You have Postman collection imported
- ✅ You have test accounts for: Admin, Client, Designer, Marketer

---

## 📝 Test Scenario 1: Order Creation

### Expected Notifications:
- ✅ **Client**: "Order Confirmed"
- ✅ **Admin**: "New Order Received"

### Steps:

#### 1. Login as Client
```
POST {{base_url}}/client/login
Body:
{
  "email": "client@example.com",
  "password": "password"
}
```
**Save the token as:** `client_token`

#### 2. Create Order
```
POST {{base_url}}/client/product-orders
Headers:
  Authorization: Bearer {{client_token}}
Body:
{
  "product_id": 1,
  "duration": "1_month",
  "addons": []
}
```
**Note the order_id from response**

#### 3. Check Database
```bash
# Check client notification
mysql -u userbareqq -p userbareqq -e "SELECT id, title, message FROM notifications WHERE notifiable_type = 'App\\Models\\Client' ORDER BY created_at DESC LIMIT 1;"

# Expected: "Order Confirmed" - "Your order #X has been received..."

# Check admin notification
mysql -u userbareqq -p userbareqq -e "SELECT id, title, message FROM notifications WHERE notifiable_type = 'App\\Models\\Admin' ORDER BY created_at DESC LIMIT 1;"

# Expected: "New Order Received" - "New order #X from..."
```

#### 4. Check Logs
```bash
tail -20 /www/wwwroot/user.bareqq.com/storage/logs/laravel.log | grep -i notification
```

**✅ PASS if:** Both client and admin have 1 new notification each

---

## 📝 Test Scenario 2: Payment Approval

### Expected Notifications:
- ✅ **Client**: "Payment Approved"

### Steps:

#### 1. Login as Admin
```
POST {{base_url}}/admin/login
Body:
{
  "username": "admin",
  "password": "password"
}
```
**Save the token as:** `admin_token`

#### 2. Approve Payment
```
POST {{base_url}}/admin/product-orders/{order_id}/approve-payment
Headers:
  Authorization: Bearer {{admin_token}}
```

#### 3. Check Database
```bash
mysql -u userbareqq -p userbareqq -e "SELECT id, title, message FROM notifications WHERE notifiable_type = 'App\\Models\\Client' ORDER BY created_at DESC LIMIT 2;"

# Expected: Latest should be "Payment Approved"
```

**✅ PASS if:** Client has "Payment Approved" notification

---

## 📝 Test Scenario 3: Team Assignment to Order

### Expected Notifications:
- ✅ **Client**: "Team Assigned"
- ✅ **Designer**: "New Project Assignment"
- ✅ **Marketer**: "New Project Assignment"

### Steps:

#### 1. Assign Team (as Admin)
```
POST {{base_url}}/admin/product-orders/{order_id}/team
Headers:
  Authorization: Bearer {{admin_token}}
Body:
{
  "designer_ids": [1],
  "marketer_ids": [1]
}
```

#### 2. Check Database
```bash
# Client notification
mysql -u userbareqq -p userbareqq -e "SELECT title FROM notifications WHERE notifiable_type = 'App\\Models\\Client' ORDER BY created_at DESC LIMIT 1;"
# Expected: "Team Assigned"

# Designer notification
mysql -u userbareqq -p userbareqq -e "SELECT title FROM notifications WHERE notifiable_type = 'App\\Models\\Designer' ORDER BY created_at DESC LIMIT 1;"
# Expected: "New Project Assignment"

# Marketer notification
mysql -u userbareqq -p userbareqq -e "SELECT title FROM notifications WHERE notifiable_type = 'App\\Models\\Marketer' ORDER BY created_at DESC LIMIT 1;"
# Expected: "New Project Assignment"
```

**✅ PASS if:** All 3 roles have new notifications

---

## 📝 Test Scenario 4: Post Creation

### Expected Notifications:
- ✅ **Client**: "New Post Created"
- ✅ **Designer**: "New Post Created" (if assigned to order)
- ✅ **Marketer**: "New Post Created" (if assigned to order)

### Steps:

#### 1. Create Post (as Admin)
```
POST {{base_url}}/admin/posts
Headers:
  Authorization: Bearer {{admin_token}}
Body:
{
  "product_order_id": {order_id},
  "content": "Test post content",
  "platform": "instagram",
  "scheduled_at": "2026-06-01 10:00:00"
}
```
**Note the post_id from response**

#### 2. Check Database
```bash
# Count notifications for each role
mysql -u userbareqq -p userbareqq -e "
SELECT 
  notifiable_type,
  COUNT(*) as count,
  GROUP_CONCAT(title SEPARATOR ', ') as titles
FROM notifications 
GROUP BY notifiable_type;"
```

**✅ PASS if:** Client, Designer, and Marketer all have "New Post Created" notification

---

## 📝 Test Scenario 5: Team Assignment to Post

### Expected Notifications:
- ✅ **Designer**: "Post Assignment"
- ✅ **Marketer**: "Post Assignment"

### Steps:

#### 1. Assign Team to Post (as Admin)
```
POST {{base_url}}/admin/posts/{post_id}/team
Headers:
  Authorization: Bearer {{admin_token}}
Body:
{
  "designer_ids": [1],
  "marketer_ids": [1]
}
```

#### 2. Check Database
```bash
mysql -u userbareqq -p userbareqq -e "SELECT title, message FROM notifications WHERE notifiable_type IN ('App\\Models\\Designer', 'App\\Models\\Marketer') ORDER BY created_at DESC LIMIT 2;"
```

**✅ PASS if:** Both Designer and Marketer have "Post Assignment" notification

---

## 📝 Test Scenario 6: Feedback Added

### Expected Notifications:
- ✅ **Admin**: "New Feedback"
- ✅ **Client**: "New Feedback"
- ✅ **Designer**: "New Feedback"
- ✅ **Marketer**: "New Feedback"

### Steps:

#### 1. Add Feedback (can be any role)
```
POST {{base_url}}/posts/{post_id}/feedback
Headers:
  Authorization: Bearer {{client_token}}
Body:
{
  "feedback": "This looks great!",
  "rating": 5
}
```

#### 2. Check Database
```bash
mysql -u userbareqq -p userbareqq -e "SELECT notifiable_type, title FROM notifications WHERE title LIKE '%Feedback%' ORDER BY created_at DESC LIMIT 4;"
```

**✅ PASS if:** All 4 roles (Admin, Client, Designer, Marketer) have "New Feedback" notification

---

## 📝 Test Scenario 7: Post Approval

### Expected Notifications:
- ✅ **Admin**: "Post Approved"
- ✅ **Client**: "Post Approved"
- ✅ **Designer**: "Post Approved"
- ✅ **Marketer**: "Post Approved"

### Steps:

#### 1. Approve Post
```
POST {{base_url}}/posts/{post_id}/approve
Headers:
  Authorization: Bearer {{admin_token}}
```

#### 2. Check Database
```bash
mysql -u userbareqq -p userbareqq -e "SELECT notifiable_type, title, message FROM notifications WHERE title LIKE '%Approved%' ORDER BY created_at DESC LIMIT 4;"
```

**✅ PASS if:** All 4 roles have "Post Approved" notification with 🎉 emoji

---

## 📝 Test Scenario 8: Order Status Update

### Expected Notifications:
- ✅ **Client**: "Order Status Updated" or "Order Completed"

### Steps:

#### 1. Update Order Status (as Admin)
```
PUT {{base_url}}/admin/product-orders/{order_id}/status
Headers:
  Authorization: Bearer {{admin_token}}
Body:
{
  "status": "in_progress"
}
```

#### 2. Check Database
```bash
mysql -u userbareqq -p userbareqq -e "SELECT title, message FROM notifications WHERE notifiable_type = 'App\\Models\\Client' ORDER BY created_at DESC LIMIT 1;"
```

**✅ PASS if:** Client has "Order Status Updated" notification

#### 3. Complete Order
```
PUT {{base_url}}/admin/product-orders/{order_id}/status
Headers:
  Authorization: Bearer {{admin_token}}
Body:
{
  "status": "completed"
}
```

#### 4. Check Database
```bash
mysql -u userbareqq -p userbareqq -e "SELECT title, message FROM notifications WHERE notifiable_type = 'App\\Models\\Client' ORDER BY created_at DESC LIMIT 1;"
```

**✅ PASS if:** Client has "Order Completed" notification

---

## 📝 Test Scenario 9: Post Update

### Expected Notifications:
- ✅ **Client**: "Post Updated"

### Steps:

#### 1. Update Post (as Admin)
```
PUT {{base_url}}/admin/posts/{post_id}
Headers:
  Authorization: Bearer {{admin_token}}
Body:
{
  "content": "Updated post content",
  "platform": "instagram"
}
```

#### 2. Check Database
```bash
mysql -u userbareqq -p userbareqq -e "SELECT title, message FROM notifications WHERE notifiable_type = 'App\\Models\\Client' AND title = 'Post Updated' ORDER BY created_at DESC LIMIT 1;"
```

**✅ PASS if:** Client has "Post Updated" notification

---

## 🧪 Final Verification: GET /notifications Endpoint

### Test for Each Role:

#### 1. Client Notifications
```
GET {{base_url}}/notifications
Headers:
  Authorization: Bearer {{client_token}}
```
**Expected:** Array of notifications with titles like "Order Confirmed", "Payment Approved", "Team Assigned", etc.

#### 2. Admin Notifications
```
GET {{base_url}}/notifications
Headers:
  Authorization: Bearer {{admin_token}}
```
**Expected:** Array of notifications with titles like "New Order Received", "New Feedback", "Post Approved"

#### 3. Designer Notifications
```
POST {{base_url}}/designer/login
Body: { "email": "designer@example.com", "password": "password" }

GET {{base_url}}/notifications
Headers:
  Authorization: Bearer {{designer_token}}
```
**Expected:** Array of notifications with titles like "New Project Assignment", "Post Assignment", "New Feedback"

#### 4. Marketer Notifications
```
POST {{base_url}}/marketer/login
Body: { "email": "marketer@example.com", "password": "password" }

GET {{base_url}}/notifications
Headers:
  Authorization: Bearer {{marketer_token}}
```
**Expected:** Array of notifications with titles like "New Project Assignment", "Post Assignment", "New Feedback"

---

## 📊 Expected Final Counts

After completing all scenarios:

| Role | Expected Notifications |
|------|----------------------|
| Admin | 3 (New Order, Feedback, Approval) |
| Client | 8 (Order Confirmed, Payment Approved, Team Assigned, Status Update, Completed, Post Created, Post Updated, Feedback, Approval) |
| Designer | 5 (Project Assignment, Post Created, Post Assignment, Feedback, Approval) |
| Marketer | 5 (Project Assignment, Post Created, Post Assignment, Feedback, Approval) |

---

## 🔍 Quick Check Commands

### Count all notifications by role:
```bash
mysql -u userbareqq -p userbareqq -e "
SELECT 
  SUBSTRING_INDEX(notifiable_type, '\\\\', -1) as role,
  COUNT(*) as total,
  SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread
FROM notifications 
GROUP BY notifiable_type;"
```

### View all notification titles:
```bash
mysql -u userbareqq -p userbareqq -e "
SELECT 
  SUBSTRING_INDEX(notifiable_type, '\\\\', -1) as role,
  title,
  created_at
FROM notifications 
ORDER BY created_at DESC 
LIMIT 20;"
```

### Clear all notifications (for retesting):
```bash
mysql -u userbareqq -p userbareqq -e "TRUNCATE TABLE notifications;"
```

---

## ✅ Success Criteria

All tests pass if:
- ✅ Each scenario creates the expected notifications in database
- ✅ GET /notifications returns correct data for each role
- ✅ Notification counts match expected values
- ✅ No errors in Laravel logs
- ✅ All notification messages are properly formatted

---

## 🐛 Troubleshooting

### If notifications not created:
1. Check logs: `tail -50 /www/wwwroot/user.bareqq.com/storage/logs/laravel.log`
2. Verify templates exist: `SELECT * FROM notification_templates;`
3. Check SendsNotifications trait is loaded: `php artisan clear-compiled`

### If GET /notifications returns empty:
1. Check authentication: Add debug logging to NotificationController
2. Verify notifiable_type format: Should be `App\Models\Client` not `Client`
3. Clear cache: `php artisan config:clear && php artisan cache:clear`

### If specific role not getting notifications:
1. Check if user exists: `SELECT * FROM {role}s WHERE id = 1;`
2. Check notification logic in controller
3. Verify SendsNotifications trait is used in controller

---

## 🚀 Quick Start

Run the automated test script:
```bash
sh test_all_notifications.sh
```

This will guide you through each scenario step-by-step!
