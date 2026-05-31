# Notification System - Quick Start Guide

## 🚀 Deploy in 3 Steps

### Step 1: Seed Templates
```bash
php artisan db:seed --class=NotificationTemplatesSeeder
```

### Step 2: Clear Cache
```bash
php artisan config:clear && php artisan cache:clear
```

### Step 3: Test
Use Postman collection to test workflows.

---

## 🧪 Quick Test Commands

### Test as Client
```bash
# 1. Login
POST {{base_url}}/login
Body: {"identifier": "test@client.com", "password": "password123"}

# 2. Create Order
POST {{base_url}}/client/product-orders
Body: {"product_id": 1, "product_role": "one_time", "total_price": 1000}

# 3. Check Notifications
GET {{base_url}}/notifications
Authorization: Bearer {{client_token}}
```

### Test as Admin
```bash
# 1. Login
POST {{base_url}}/login
Body: {"identifier": "admin@bareqq.com", "password": "password123"}

# 2. Check Notifications
GET {{base_url}}/notifications
Authorization: Bearer {{admin_token}}

# 3. Approve Payment
POST {{base_url}}/admin/product-orders/1/approve-payment
```

---

## 📋 What Gets Notified

| Action | Who Gets Notified |
|--------|-------------------|
| Client creates order | Client + Admin |
| Admin approves payment | Client |
| Admin assigns team | Team + Client |
| Admin creates post | Client + Team |
| Anyone adds feedback | Everyone |
| Anyone approves post | Everyone |

---

## 🔍 Verify Installation

```sql
-- Check templates exist
SELECT COUNT(*) FROM notification_templates;
-- Should return: 13

-- Check notifications table
DESCRIBE notifications;
-- Should have: notifiable_id, notifiable_type, title, message, is_read

-- Check device_token exists
DESCRIBE clients;
DESCRIBE designers;
DESCRIBE marketers;
-- All should have: device_token column
```

---

## 📱 Notification Endpoints

```bash
# Get my notifications
GET /notifications

# Mark one as read
POST /notifications/{id}/read

# Mark all as read
POST /notifications/read-all
```

---

## 🎯 Expected Response

```json
{
    "status": true,
    "data": [
        {
            "id": 1,
            "title": "Order Confirmed",
            "message": "Your order #1 has been received",
            "data": {"order_id": 1},
            "is_read": false,
            "created_at": "2026-05-31T12:00:00.000000Z",
            "notification_type": "order_confirmed"
        }
    ]
}
```

---

## ✅ Success Checklist

- [ ] Templates seeded (13 templates)
- [ ] Cache cleared
- [ ] Test order creation → Notifications sent
- [ ] Test GET /notifications → Notifications retrieved
- [ ] Test mark as read → Status updated
- [ ] No errors in logs

---

## 📚 Full Documentation

- `IMPLEMENTATION_COMPLETE.md` - Complete implementation details
- `DEPLOYMENT_SUMMARY.md` - Deployment overview
- `NOTIFICATION_WORKFLOW_COMPLETE.md` - Workflow explanation
- `IMPLEMENT_NOTIFICATIONS_GUIDE.md` - Step-by-step guide

---

## 🎊 Done!

Your notification system is ready. Users will now receive real-time notifications for all important events!
