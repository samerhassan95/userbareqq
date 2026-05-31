# Notification Events by Role

## Overview
This document lists all the events that trigger notifications for each role in the system.

---

## 🔴 ADMIN Notifications

### 1. **Meeting Created**
- **Trigger:** When a new meeting is created
- **File:** `app/Http/Controllers/MeetingController.php`
- **Type:** `meeting_created`
- **Data:** `meeting_id`

### 2. **Chat Message from Client**
- **Trigger:** When a client sends a chat message
- **File:** `app/Http/Controllers/NotificationController.php`
- **Type:** `chat_message`
- **Data:** `sender_id`, `chat_id`, `sender_type`, `message`, `userId`

### 3. **Broadcast Notifications**
- **Trigger:** Admin can send broadcast notifications to all users
- **File:** `app/Http/Controllers/NotificationController.php`
- **Type:** Custom (from notification templates)

---

## 🔵 CLIENT Notifications

### 1. **Product Created/Updated**
- **Trigger:** When admin creates or updates a product
- **File:** `app/Http/Controllers/ProductController.php`
- **Type:** From notification template
- **Data:** `notification_type`

### 2. **Task Created**
- **Trigger:** When a task is created for the client
- **File:** `app/Http/Controllers/TaskController.php`
- **Type:** `create_task`
- **Data:** `task_id`

### 3. **Task Status Updated**
- **Trigger:** When task status changes
- **File:** `app/Http/Controllers/TaskController.php`
- **Type:** `update_task_status`
- **Data:** `task_id`

### 4. **Invoice Created**
- **Trigger:** When an invoice is generated
- **File:** `app/Http/Controllers/InvoiceController.php`
- **Type:** `invoice_created`
- **Data:** `invoice_id`

### 5. **Ticket Reply**
- **Trigger:** When admin replies to a support ticket
- **File:** `app/Http/Controllers/TicketReplyController.php`
- **Type:** `ticket_answered`
- **Data:** `ticket_id`

### 6. **Meeting Status Updated**
- **Trigger:** When meeting status changes
- **File:** `app/Http/Controllers/MeetingController.php`
- **Type:** `meeting_status_updated`
- **Data:** `meeting_id`

### 7. **Meeting Canceled**
- **Trigger:** When a meeting is canceled
- **File:** `app/Http/Controllers/MeetingController.php`
- **Type:** `meeting_canceled`
- **Data:** `meeting_id`

### 8. **Chat Message from Admin**
- **Trigger:** When admin sends a chat message
- **File:** `app/Http/Controllers/NotificationController.php`
- **Type:** `chat_message`
- **Data:** `sender_id`, `chat_id`, `sender_type`, `message`, `userId`

---

## 🟢 DESIGNER Notifications

### 1. **Post Assigned for Design**
- **Trigger:** When a designer is assigned to a post/design task
- **File:** Based on post team members assignment
- **Type:** `post_assigned`
- **Data:** `post_id`, `product_order_id`

### 2. **Post Feedback Received**
- **Trigger:** When client or admin provides feedback on a design
- **File:** `app/Models/PostFeedback.php` (polymorphic relationship)
- **Type:** `post_feedback`
- **Data:** `post_id`, `feedback_id`

### 3. **Post Approved/Rejected**
- **Trigger:** When a post design is approved or rejected
- **File:** Based on post approval workflow
- **Type:** `post_status_updated`
- **Data:** `post_id`, `status`

### 4. **Strategy Work Assigned**
- **Trigger:** When designer is assigned to strategy work
- **File:** Based on strategy team members
- **Type:** `strategy_assigned`
- **Data:** `strategy_work_id`, `product_order_id`

---

## 🟡 MARKETER Notifications

### 1. **Post Assigned for Marketing**
- **Trigger:** When a marketer is assigned to a post/marketing task
- **File:** Based on post team members assignment
- **Type:** `post_assigned`
- **Data:** `post_id`, `product_order_id`

### 2. **Post Feedback Received**
- **Trigger:** When client or admin provides feedback on marketing content
- **File:** `app/Models/PostFeedback.php` (polymorphic relationship)
- **Type:** `post_feedback`
- **Data:** `post_id`, `feedback_id`

### 3. **Post Approved/Rejected**
- **Trigger:** When a marketing post is approved or rejected
- **File:** Based on post approval workflow
- **Type:** `post_status_updated`
- **Data:** `post_id`, `status`

### 4. **Strategy Work Assigned**
- **Trigger:** When marketer is assigned to strategy work
- **File:** Based on strategy team members
- **Type:** `strategy_assigned`
- **Data:** `strategy_work_id`, `product_order_id`

### 5. **Post Scheduled**
- **Trigger:** When a post is scheduled for publishing
- **File:** Based on post scheduling
- **Type:** `post_scheduled`
- **Data:** `post_id`, `scheduled_at`

---

## 📊 Notification Types Summary

| Notification Type | Admin | Client | Designer | Marketer |
|-------------------|-------|--------|----------|----------|
| meeting_created | ✅ | ❌ | ❌ | ❌ |
| chat_message | ✅ | ✅ | ❌ | ❌ |
| product_created | ❌ | ✅ | ❌ | ❌ |
| create_task | ❌ | ✅ | ❌ | ❌ |
| update_task_status | ❌ | ✅ | ❌ | ❌ |
| invoice_created | ❌ | ✅ | ❌ | ❌ |
| ticket_answered | ❌ | ✅ | ❌ | ❌ |
| meeting_status_updated | ❌ | ✅ | ❌ | ❌ |
| meeting_canceled | ❌ | ✅ | ❌ | ❌ |
| post_assigned | ❌ | ❌ | ✅ | ✅ |
| post_feedback | ❌ | ❌ | ✅ | ✅ |
| post_status_updated | ❌ | ❌ | ✅ | ✅ |
| strategy_assigned | ❌ | ❌ | ✅ | ✅ |
| post_scheduled | ❌ | ❌ | ❌ | ✅ |

---

## 🔔 How Notifications Are Triggered

### Automatic Triggers (Observers)
These notifications are sent automatically when certain database events occur:

1. **ScreenObserver** - When screens are created/updated
2. **ScreenReviewObserver** - When screen reviews are added
3. **ImplementedApiObserver** - When APIs are implemented/tested
4. **ImplementedApiReviewObserver** - When API reviews are added
5. **RequestedApiObserver** - When APIs are requested
6. **RequestStatusObserver** - When request statuses change

### Manual Triggers (Controllers)
These notifications are sent from controller actions:

1. **ProductController** - Product creation/updates
2. **TaskController** - Task creation/updates
3. **InvoiceController** - Invoice creation
4. **TicketReplyController** - Ticket replies
5. **MeetingController** - Meeting creation/updates/cancellation
6. **NotificationController** - Chat messages and broadcasts

---

## 🎯 Testing Notifications

### For Admin:
```bash
# 1. Create a meeting
POST /admin/meetings

# 2. Send a chat message
POST /admin/chat/send

# Expected: Admin receives notification
GET /notifications (with admin_token)
```

### For Client:
```bash
# 1. Admin creates a product
POST /admin/products

# 2. Admin creates an invoice
POST /admin/invoices

# 3. Admin replies to ticket
POST /admin/tickets/{id}/reply

# Expected: Client receives notifications
GET /notifications (with client_token)
```

### For Designer:
```bash
# 1. Admin assigns designer to a post
POST /admin/posts/{id}/assign-team

# 2. Client provides feedback on design
POST /client/posts/{id}/feedback

# Expected: Designer receives notifications
GET /notifications (with designer_token)
```

### For Marketer:
```bash
# 1. Admin assigns marketer to a post
POST /admin/posts/{id}/assign-team

# 2. Admin schedules a post
POST /admin/posts/{id}/schedule

# Expected: Marketer receives notifications
GET /notifications (with marketer_token)
```

---

## 📝 Notification Templates

Notifications use templates stored in the `notification_templates` table:

```sql
SELECT * FROM notification_templates;
```

Each template has:
- `type` - Unique identifier (e.g., 'meeting_created')
- `title` - Notification title (supports placeholders)
- `message` - Notification message (supports placeholders)
- `title_ar` - Arabic title
- `message_ar` - Arabic message

### Placeholder Variables
Templates can use placeholders like:
- `{client_name}` - Client's name
- `{task_id}` - Task ID
- `{meeting_date}` - Meeting date
- `{product_name}` - Product name

---

## 🚀 Adding New Notification Types

To add a new notification type:

### 1. Create Template
```sql
INSERT INTO notification_templates (type, title, message, title_ar, message_ar)
VALUES (
    'new_notification_type',
    'New Notification',
    'You have a new notification',
    'إشعار جديد',
    'لديك إشعار جديد'
);
```

### 2. Send Notification in Code
```php
use App\Services\FirebaseService;
use App\Repositories\NotificationRepository;

$firebaseService = app(FirebaseService::class);
$notificationRepo = app(NotificationRepository::class);

// Get the user (admin, client, designer, or marketer)
$user = Client::find(1);

// Send notification
$title = 'New Notification';
$message = 'You have a new notification';
$type = 'new_notification_type';

$firebaseService->sendNotification(
    $user->device_token,
    $title,
    $message,
    ['notification_type' => $type]
);

$notificationRepo->createNotification(
    $user,
    $title,
    $message,
    $user->device_token,
    $type
);
```

---

## 🔍 Checking Notification History

### View all notifications for a user:
```sql
-- Admin notifications
SELECT * FROM notifications 
WHERE notifiable_type = 'App\\Models\\Admin' 
AND notifiable_id = 1 
ORDER BY created_at DESC;

-- Client notifications
SELECT * FROM notifications 
WHERE notifiable_type = 'App\\Models\\Client' 
AND notifiable_id = 1 
ORDER BY created_at DESC;

-- Designer notifications
SELECT * FROM notifications 
WHERE notifiable_type = 'App\\Models\\Designer' 
AND notifiable_id = 1 
ORDER BY created_at DESC;

-- Marketer notifications
SELECT * FROM notifications 
WHERE notifiable_type = 'App\\Models\\Marketer' 
AND notifiable_id = 1 
ORDER BY created_at DESC;
```

### Count unread notifications:
```sql
SELECT 
    notifiable_type,
    COUNT(*) as unread_count
FROM notifications
WHERE is_read = 0
GROUP BY notifiable_type;
```

---

## 💡 Important Notes

1. **Device Token Required**: Users must have a valid `device_token` to receive push notifications
2. **Firebase Setup**: Firebase Cloud Messaging (FCM) must be configured
3. **Polymorphic Relationships**: Notifications use polymorphic relationships to support multiple user types
4. **Localization**: Notifications support both English and Arabic based on `Accept-Language` header
5. **Real-time**: Notifications are sent in real-time via Firebase
6. **Database Storage**: All notifications are stored in the database for history

---

## 🐛 Troubleshooting

### User not receiving notifications?
1. Check if `device_token` exists: `SELECT device_token FROM clients WHERE id = 1;`
2. Check Firebase configuration in `config/firebase.php`
3. Check notification templates exist: `SELECT * FROM notification_templates;`
4. Check Laravel logs: `storage/logs/laravel.log`

### Notifications not showing in GET /notifications?
1. Verify user is authenticated
2. Check `notifiable_type` matches exactly (e.g., `App\Models\Client`)
3. Check `notifiable_id` matches the user's ID
4. Run: `SELECT * FROM notifications WHERE notifiable_id = 1 AND notifiable_type = 'App\\Models\\Client';`

---

## ✨ Summary

- **Admin**: Receives meeting and chat notifications
- **Client**: Receives product, task, invoice, ticket, and meeting notifications
- **Designer**: Receives post assignment, feedback, and approval notifications
- **Marketer**: Receives post assignment, feedback, scheduling, and approval notifications

All roles can view their notifications via `GET /notifications` endpoint.
