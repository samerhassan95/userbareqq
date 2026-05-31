# Complete Notification System by Role

## 🎯 Overview

This document shows **exactly when** each role (Admin, Client, Designer, Marketer) receives notifications in the current implementation.

---

## 🔴 ADMIN Notifications

### When Admin Gets Notified:

#### 1. **New Order Created** ✅ IMPLEMENTED
- **Trigger:** Client creates a new product order
- **Endpoint:** `POST /client/product-orders`
- **Controller:** `ProductOrderController@store`
- **Template:** `order_created`
- **Message:** "New order #{order_id} from {client_name}"

#### 2. **Feedback Added to Post** ✅ IMPLEMENTED
- **Trigger:** Anyone (Client/Designer/Marketer) adds feedback to a post
- **Endpoint:** `POST /posts/{id}/feedback`
- **Controller:** `PostFeedbackController@store`
- **Template:** `post_feedback_received`
- **Message:** "New feedback on post #{post_id}"

#### 3. **Post Approved** ✅ IMPLEMENTED
- **Trigger:** Post is approved
- **Endpoint:** `POST /posts/{id}/approve`
- **Controller:** `PostController@approve`
- **Template:** `post_approved`
- **Message:** "Post #{post_id} has been approved! 🎉"

### Total Admin Notifications: **3 types**

---

## 🔵 CLIENT Notifications

### When Client Gets Notified:

#### 1. **Order Confirmed** ✅ IMPLEMENTED
- **Trigger:** Client creates their own order
- **Endpoint:** `POST /client/product-orders`
- **Controller:** `ProductOrderController@store`
- **Template:** `order_confirmed`
- **Message:** "Your order #{order_id} has been received and is being processed"

#### 2. **Payment Approved** ✅ IMPLEMENTED
- **Trigger:** Admin approves payment proof
- **Endpoint:** `POST /admin/product-orders/{id}/approve-payment`
- **Controller:** `AdminProductOrderController@approvePayment`
- **Template:** `payment_approved`
- **Message:** "Your payment for order #{order_id} has been approved"

#### 3. **Team Assigned to Order** ✅ IMPLEMENTED
- **Trigger:** Admin assigns designers/marketers to client's order
- **Endpoint:** `POST /admin/product-orders/{id}/team`
- **Controller:** `AdminProductOrderController@assignTeam`
- **Template:** `team_assigned_notification_client`
- **Message:** "A team has been assigned to your order #{order_id}"

#### 4. **Order Status Updated** ✅ IMPLEMENTED
- **Trigger:** Admin changes order status
- **Endpoint:** `PUT /admin/product-orders/{id}/status`
- **Controller:** `AdminProductOrderController@updateStatus`
- **Template:** `order_status_updated` or `order_completed`
- **Message:** "Your order #{order_id} status: {status}" or "Your order #{order_id} is complete!"

#### 5. **New Post Created** ✅ IMPLEMENTED
- **Trigger:** Admin creates a post for client's order
- **Endpoint:** `POST /admin/posts`
- **Controller:** `AdminPostController@store`
- **Template:** `post_created`
- **Message:** "New post created for your order #{order_id}"

#### 6. **Post Updated** ✅ IMPLEMENTED
- **Trigger:** Admin updates a post
- **Endpoint:** `PUT /admin/posts/{id}`
- **Controller:** `AdminPostController@update`
- **Template:** `post_updated`
- **Message:** "Post #{post_id} has been updated"

#### 7. **Feedback Added to Post** ✅ IMPLEMENTED
- **Trigger:** Anyone adds feedback to client's post
- **Endpoint:** `POST /posts/{id}/feedback`
- **Controller:** `PostFeedbackController@store`
- **Template:** `post_feedback_received`
- **Message:** "New feedback on post #{post_id}"

#### 8. **Post Approved** ✅ IMPLEMENTED
- **Trigger:** Post is approved
- **Endpoint:** `POST /posts/{id}/approve`
- **Controller:** `PostController@approve`
- **Template:** `post_approved`
- **Message:** "Post #{post_id} has been approved! 🎉"

### Total Client Notifications: **8 types**

---

## 🟢 DESIGNER Notifications

### When Designer Gets Notified:

#### 1. **Assigned to Order** ✅ IMPLEMENTED
- **Trigger:** Admin assigns designer to a product order
- **Endpoint:** `POST /admin/product-orders/{id}/team`
- **Controller:** `AdminProductOrderController@assignTeam`
- **Template:** `team_assigned_to_order`
- **Message:** "You've been assigned to order #{order_id}"

#### 2. **New Post Created (if assigned to order)** ✅ IMPLEMENTED
- **Trigger:** Admin creates post for an order where designer is assigned
- **Endpoint:** `POST /admin/posts`
- **Controller:** `AdminPostController@store`
- **Template:** `post_created`
- **Message:** "New post created for order #{order_id}"

#### 3. **Assigned to Specific Post** ✅ IMPLEMENTED
- **Trigger:** Admin assigns designer to a specific post
- **Endpoint:** `POST /admin/posts/{id}/team`
- **Controller:** `AdminPostController@assignTeam`
- **Template:** `post_team_assigned`
- **Message:** "You've been assigned to post #{post_id}"

#### 4. **Feedback Added to Post** ✅ IMPLEMENTED
- **Trigger:** Anyone adds feedback to a post where designer is assigned
- **Endpoint:** `POST /posts/{id}/feedback`
- **Controller:** `PostFeedbackController@store`
- **Template:** `post_feedback_received`
- **Message:** "New feedback on post #{post_id}"

#### 5. **Post Approved** ✅ IMPLEMENTED
- **Trigger:** Post is approved (designer was part of team)
- **Endpoint:** `POST /posts/{id}/approve`
- **Controller:** `PostController@approve`
- **Template:** `post_approved`
- **Message:** "Post #{post_id} has been approved! 🎉"

### Total Designer Notifications: **5 types**

---

## 🟡 MARKETER Notifications

### When Marketer Gets Notified:

#### 1. **Assigned to Order** ✅ IMPLEMENTED
- **Trigger:** Admin assigns marketer to a product order
- **Endpoint:** `POST /admin/product-orders/{id}/team`
- **Controller:** `AdminProductOrderController@assignTeam`
- **Template:** `team_assigned_to_order`
- **Message:** "You've been assigned to order #{order_id}"

#### 2. **New Post Created (if assigned to order)** ✅ IMPLEMENTED
- **Trigger:** Admin creates post for an order where marketer is assigned
- **Endpoint:** `POST /admin/posts`
- **Controller:** `AdminPostController@store`
- **Template:** `post_created`
- **Message:** "New post created for order #{order_id}"

#### 3. **Assigned to Specific Post** ✅ IMPLEMENTED
- **Trigger:** Admin assigns marketer to a specific post
- **Endpoint:** `POST /admin/posts/{id}/team`
- **Controller:** `AdminPostController@assignTeam`
- **Template:** `post_team_assigned`
- **Message:** "You've been assigned to post #{post_id}"

#### 4. **Feedback Added to Post** ✅ IMPLEMENTED
- **Trigger:** Anyone adds feedback to a post where marketer is assigned
- **Endpoint:** `POST /posts/{id}/feedback`
- **Controller:** `PostFeedbackController@store`
- **Template:** `post_feedback_received`
- **Message:** "New feedback on post #{post_id}"

#### 5. **Post Approved** ✅ IMPLEMENTED
- **Trigger:** Post is approved (marketer was part of team)
- **Endpoint:** `POST /posts/{id}/approve`
- **Controller:** `PostController@approve`
- **Template:** `post_approved`
- **Message:** "Post #{post_id} has been approved! 🎉"

### Total Marketer Notifications: **5 types**

---

## 📊 Notification Matrix

| Event | Admin | Client | Designer | Marketer | Template Type |
|-------|-------|--------|----------|----------|---------------|
| Client creates order | ✅ | ✅ | ❌ | ❌ | `order_created`, `order_confirmed` |
| Admin approves payment | ❌ | ✅ | ❌ | ❌ | `payment_approved` |
| Admin assigns team to order | ❌ | ✅ | ✅ | ✅ | `team_assigned_to_order`, `team_assigned_notification_client` |
| Admin updates order status | ❌ | ✅ | ❌ | ❌ | `order_status_updated`, `order_completed` |
| Admin creates post | ❌ | ✅ | ✅* | ✅* | `post_created` |
| Admin updates post | ❌ | ✅ | ❌ | ❌ | `post_updated` |
| Admin assigns team to post | ❌ | ❌ | ✅ | ✅ | `post_team_assigned` |
| Anyone adds feedback | ✅ | ✅ | ✅ | ✅ | `post_feedback_received` |
| Post approved | ✅ | ✅ | ✅ | ✅ | `post_approved` |

*Only if assigned to the order

---

## 🔔 Notification Templates

All 13 notification templates with English and Arabic:

| Type | English Title | Arabic Title |
|------|---------------|--------------|
| `order_created` | New Order Received | طلب جديد |
| `order_confirmed` | Order Confirmed | تأكيد الطلب |
| `payment_approved` | Payment Approved | تمت الموافقة على الدفع |
| `team_assigned_to_order` | New Project Assignment | تعيين مشروع جديد |
| `team_assigned_notification_client` | Team Assigned | تم تعيين الفريق |
| `order_status_updated` | Order Status Updated | تحديث حالة الطلب |
| `order_completed` | Order Completed | اكتمال الطلب |
| `post_created` | New Post Created | منشور جديد |
| `post_updated` | Post Updated | تحديث المنشور |
| `post_team_assigned` | Post Assignment | تعيين منشور |
| `post_feedback_received` | New Feedback | ملاحظات جديدة |
| `post_approved` | Post Approved | تمت الموافقة على المنشور |
| `post_rejected` | Post Rejected | رفض المنشور |

---

## 🧪 Testing Guide

### Test Admin Notifications

```bash
# 1. Login as client and create order
POST /client/login
POST /client/product-orders

# 2. Check admin notifications
POST /admin/login
GET /notifications (with admin token)
# Expected: "New Order Received" notification

# 3. Add feedback to a post
POST /posts/{id}/feedback

# 4. Check admin notifications again
GET /notifications
# Expected: "New Feedback" notification
```

### Test Client Notifications

```bash
# 1. Login as client and create order
POST /client/login
POST /client/product-orders

# 2. Check client notifications
GET /notifications (with client token)
# Expected: "Order Confirmed" notification

# 3. Login as admin and approve payment
POST /admin/login
POST /admin/product-orders/{id}/approve-payment

# 4. Check client notifications again
GET /notifications (with client token)
# Expected: "Payment Approved" notification

# 5. Admin assigns team
POST /admin/product-orders/{id}/team

# 6. Check client notifications
GET /notifications (with client token)
# Expected: "Team Assigned" notification
```

### Test Designer Notifications

```bash
# 1. Admin assigns designer to order
POST /admin/login
POST /admin/product-orders/{id}/team
# Body: { "designer_ids": [1] }

# 2. Check designer notifications
POST /designer/login
GET /notifications (with designer token)
# Expected: "New Project Assignment" notification

# 3. Admin creates post for that order
POST /admin/posts
# Body: { "product_order_id": {order_id} }

# 4. Check designer notifications
GET /notifications (with designer token)
# Expected: "New Post Created" notification
```

### Test Marketer Notifications

```bash
# 1. Admin assigns marketer to order
POST /admin/login
POST /admin/product-orders/{id}/team
# Body: { "marketer_ids": [1] }

# 2. Check marketer notifications
POST /marketer/login
GET /notifications (with marketer token)
# Expected: "New Project Assignment" notification

# 3. Admin assigns marketer to specific post
POST /admin/posts/{id}/team
# Body: { "marketer_ids": [1] }

# 4. Check marketer notifications
GET /notifications (with marketer token)
# Expected: "Post Assignment" notification
```

---

## 🚀 Quick Test Commands

### Check notifications in database:

```sql
-- Admin notifications
SELECT id, title, message, created_at 
FROM notifications 
WHERE notifiable_type = 'App\\Models\\Admin' 
ORDER BY created_at DESC LIMIT 5;

-- Client notifications
SELECT id, title, message, created_at 
FROM notifications 
WHERE notifiable_type = 'App\\Models\\Client' 
ORDER BY created_at DESC LIMIT 5;

-- Designer notifications
SELECT id, title, message, created_at 
FROM notifications 
WHERE notifiable_type = 'App\\Models\\Designer' 
ORDER BY created_at DESC LIMIT 5;

-- Marketer notifications
SELECT id, title, message, created_at 
FROM notifications 
WHERE notifiable_type = 'App\\Models\\Marketer' 
ORDER BY created_at DESC LIMIT 5;
```

### Count notifications by role:

```sql
SELECT 
    notifiable_type,
    COUNT(*) as total,
    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread
FROM notifications
GROUP BY notifiable_type;
```

---

## 💡 Key Points

1. **Designers and Marketers** get notified when:
   - Assigned to an order
   - Post created for their order
   - Assigned to specific post
   - Feedback added
   - Post approved

2. **Clients** get notified for:
   - Every step of their order journey
   - All post updates
   - Feedback and approvals

3. **Admins** get notified for:
   - New orders (to take action)
   - Feedback (to monitor)
   - Approvals (to track progress)

4. **All notifications**:
   - Saved to database (even without device_token)
   - Support English and Arabic
   - Include relevant data (order_id, post_id, etc.)
   - Can be marked as read

---

## ✅ Summary

- **Total Notification Types:** 13
- **Admin Notifications:** 3 types
- **Client Notifications:** 8 types
- **Designer Notifications:** 5 types
- **Marketer Notifications:** 5 types
- **Languages:** English + Arabic
- **Status:** ✅ Fully Implemented

All roles have comprehensive notification coverage for their relevant workflows!
