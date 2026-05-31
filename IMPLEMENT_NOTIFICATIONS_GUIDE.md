# Implementation Guide: Add Notifications to All Workflows

## 🎯 Overview

Based on the Postman collection analysis, this platform is a **Social Media Management System** where:
- Clients order marketing services
- Admin assigns designers & marketers
- Team creates posts collaboratively
- Everyone provides feedback
- Posts get approved and published

**Current Status:** Notification endpoints exist but notifications are NOT being sent during the workflow.

**Goal:** Add notification triggers at every important step.

---

## 📋 Files to Modify

### Controllers to Update:
1. ✅ `app/Http/Controllers/Client/ProductOrderController.php`
2. ✅ `app/Http/Controllers/Admin/AdminProductOrderController.php`
3. ✅ `app/Http/Controllers/Admin/AdminPostController.php`
4. ✅ `app/Http/Controllers/Client/MarketerPostController.php`
5. ✅ `app/Http/Controllers/Client/DesignerPostController.php`
6. ✅ `app/Http/Controllers/PostController.php` (shared approval/feedback)

---

## 🔧 Step 1: Create Notification Templates

Run this SQL first:

```sql
-- Clear existing templates (optional)
-- DELETE FROM notification_templates;

INSERT INTO notification_templates (type, title, message, title_ar, message_ar, created_at, updated_at) VALUES
-- Order notifications
('order_created', 'New Order Received', 'New order #{order_id} from {client_name}', 'طلب جديد', 'طلب جديد #{order_id} من {client_name}', NOW(), NOW()),
('order_confirmed', 'Order Confirmed', 'Your order #{order_id} has been received and is being processed', 'تأكيد الطلب', 'تم استلام طلبك #{order_id} وجاري معالجته', NOW(), NOW()),
('payment_approved', 'Payment Approved', 'Your payment for order #{order_id} has been approved. Work will begin soon!', 'تمت الموافقة على الدفع', 'تمت الموافقة على دفعتك للطلب #{order_id}. سيبدأ العمل قريباً!', NOW(), NOW()),

-- Team assignment
('team_assigned_to_order', 'New Project Assignment', 'You have been assigned to {client_name}\'s project', 'تعيين مشروع جديد', 'تم تعيينك لمشروع {client_name}', NOW(), NOW()),
('team_assigned_notification_client', 'Team Assigned', 'Your team has been assigned to your project', 'تم تعيين الفريق', 'تم تعيين فريقك لمشروعك', NOW(), NOW()),

-- Post notifications
('post_created', 'New Post Created', 'New post "{title}" created for your review', 'منشور جديد', 'تم إنشاء منشور جديد "{title}" للمراجعة', NOW(), NOW()),
('post_assigned', 'Post Assignment', 'You have been assigned to work on post: {title}', 'تعيين منشور', 'تم تعيينك للعمل على منشور: {title}', NOW(), NOW()),
('post_updated', 'Post Updated', 'Post "{title}" has been updated', 'تحديث المنشور', 'تم تحديث المنشور "{title}"', NOW(), NOW()),

-- Feedback notifications
('feedback_added', 'New Feedback', '{user_name} added feedback on post: {title}', 'ملاحظات جديدة', '{user_name} أضاف ملاحظات على المنشور: {title}', NOW(), NOW()),

-- Approval notifications
('post_approved', 'Post Approved! 🎉', 'Post "{title}" has been approved', 'تمت الموافقة على المنشور', 'تمت الموافقة على المنشور "{title}"', NOW(), NOW()),
('post_published', 'Post Published 🚀', 'Your post "{title}" has been published successfully', 'تم نشر المنشور', 'تم نشر منشورك "{title}" بنجاح', NOW(), NOW()),

-- Order completion
('order_completed', 'Order Completed', 'Your order #{order_id} has been completed. Thank you!', 'اكتمل الطلب', 'اكتمل طلبك #{order_id}. شكراً لك!', NOW(), NOW()),
('order_status_changed', 'Order Status Updated', 'Your order #{order_id} status: {status}', 'تحديث حالة الطلب', 'حالة طلبك #{order_id}: {status}', NOW(), NOW());
```

---

## 🔧 Step 2: Add Helper Trait

Create `app/Traits/SendsNotifications.php`:

```php
<?php

namespace App\Traits;

use App\Services\FirebaseService;
use App\Repositories\NotificationRepository;
use Illuminate\Support\Facades\Log;

trait SendsNotifications
{
    /**
     * Send notification to a single user or multiple users
     */
    protected function sendNotification($users, string $title, string $message, string $type, array $data = [])
    {
        // Convert single user to array
        if (!is_array($users) && !($users instanceof \Illuminate\Support\Collection)) {
            $users = [$users];
        }

        $firebaseService = app(FirebaseService::class);
        $notificationRepo = app(NotificationRepository::class);

        foreach ($users as $user) {
            if (!$user || !$user->device_token) {
                Log::info("Skipping notification for user without device_token", [
                    'user_id' => $user->id ?? 'unknown',
                    'type' => $type
                ]);
                continue;
            }

            try {
                // Send Firebase push notification
                $firebaseService->sendNotification(
                    $user->device_token,
                    $title,
                    $message,
                    array_merge($data, ['notification_type' => $type])
                );

                // Save to database
                $notificationRepo->createNotification(
                    $user,
                    $title,
                    $message,
                    $user->device_token,
                    $type,
                    $data
                );

                Log::info("Notification sent successfully", [
                    'user_id' => $user->id,
                    'type' => $type,
                    'title' => $title
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to send notification", [
                    'user_id' => $user->id,
                    'type' => $type,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Send notification to all admins
     */
    protected function notifyAdmins(string $title, string $message, string $type, array $data = [])
    {
        $admins = \App\Models\Admin::whereNotNull('device_token')->get();
        $this->sendNotification($admins, $title, $message, $type, $data);
    }
}
```

---

## 🔧 Step 3: Update ProductOrderController (Client)

File: `app/Http/Controllers/Client/ProductOrderController.php`

Add at the top:
```php
use App\Traits\SendsNotifications;
```

Add to class:
```php
use SendsNotifications;
```

In the `store()` method, after order is created:

```php
public function store(Request $request)
{
    // ... existing code to create order ...
    
    // After order is created successfully
    
    // Notify client
    $this->sendNotification(
        auth()->user(),
        'Order Confirmed',
        "Your order #{$order->id} has been received and is being processed",
        'order_confirmed',
        ['order_id' => $order->id]
    );
    
    // Notify all admins
    $this->notifyAdmins(
        'New Order Received',
        "New order #{$order->id} from {$order->client->name}",
        'order_created',
        [
            'order_id' => $order->id,
            'client_id' => $order->client_id,
            'client_name' => $order->client->name
        ]
    );
    
    // ... return response ...
}
```

---

## 🔧 Step 4: Update AdminProductOrderController

File: `app/Http/Controllers/Admin/AdminProductOrderController.php`

Add trait:
```php
use App\Traits\SendsNotifications;

class AdminProductOrderController extends Controller
{
    use SendsNotifications;
    
    // ... existing code ...
}
```

### A. In `approvePayment()` method:

```php
public function approvePayment($id)
{
    // ... existing code to approve payment ...
    
    // Notify client
    $this->sendNotification(
        $order->client,
        'Payment Approved',
        "Your payment for order #{$order->id} has been approved. Work will begin soon!",
        'payment_approved',
        ['order_id' => $order->id]
    );
    
    // ... return response ...
}
```

### B. In `assignTeam()` method:

```php
public function assignTeam($id, Request $request)
{
    // ... existing code to assign team ...
    
    $order = ProductOrder::with('client')->findOrFail($id);
    $designerIds = $request->designer_ids ?? [];
    $marketerIds = $request->marketer_ids ?? [];
    
    // ... assign team logic ...
    
    // Notify designers
    if (!empty($designerIds)) {
        $designers = \App\Models\Designer::whereIn('id', $designerIds)->get();
        $this->sendNotification(
            $designers,
            'New Project Assignment',
            "You have been assigned to {$order->client->name}'s project",
            'team_assigned_to_order',
            [
                'order_id' => $order->id,
                'client_name' => $order->client->name
            ]
        );
    }
    
    // Notify marketers
    if (!empty($marketerIds)) {
        $marketers = \App\Models\Marketer::whereIn('id', $marketerIds)->get();
        $this->sendNotification(
            $marketers,
            'New Project Assignment',
            "You have been assigned to {$order->client->name}'s project",
            'team_assigned_to_order',
            [
                'order_id' => $order->id,
                'client_name' => $order->client->name
            ]
        );
    }
    
    // Notify client
    $this->sendNotification(
        $order->client,
        'Team Assigned',
        'Your team has been assigned to your project',
        'team_assigned_notification_client',
        ['order_id' => $order->id]
    );
    
    // ... return response ...
}
```

### C. In `updateStatus()` method:

```php
public function updateStatus($id, Request $request)
{
    // ... existing code to update status ...
    
    $order = ProductOrder::with('client')->findOrFail($id);
    $newStatus = $request->status;
    
    // ... update status logic ...
    
    // Notify client
    $statusMessages = [
        'pending_payment' => 'Waiting for payment',
        'in_progress' => 'Work in progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled'
    ];
    
    $this->sendNotification(
        $order->client,
        'Order Status Updated',
        "Your order #{$order->id} status: {$statusMessages[$newStatus]}",
        $newStatus === 'completed' ? 'order_completed' : 'order_status_changed',
        [
            'order_id' => $order->id,
            'status' => $newStatus
        ]
    );
    
    // If completed, notify team members
    if ($newStatus === 'completed') {
        $teamMembers = $order->teamMembers; // Assuming relationship exists
        if ($teamMembers) {
            $this->sendNotification(
                $teamMembers,
                'Project Completed',
                "Project for {$order->client->name} has been completed",
                'order_completed',
                ['order_id' => $order->id]
            );
        }
    }
    
    // ... return response ...
}
```

---

## 🔧 Step 5: Update AdminPostController

File: `app/Http/Controllers/Admin/AdminPostController.php`

Add trait and update methods:

### A. In `store()` method (Create Post):

```php
public function store(Request $request)
{
    // ... existing code to create post ...
    
    $post = Post::create($data);
    
    // Notify client
    $this->sendNotification(
        $post->client,
        'New Post Created',
        "New post \"{$post->title}\" created for your review",
        'post_created',
        [
            'post_id' => $post->id,
            'title' => $post->title
        ]
    );
    
    // Notify team members if post is linked to an order
    if ($post->product_order_id) {
        $order = $post->productOrder;
        if ($order && $order->teamMembers) {
            $this->sendNotification(
                $order->teamMembers,
                'New Post',
                "New post \"{$post->title}\" needs your work",
                'post_created',
                [
                    'post_id' => $post->id,
                    'title' => $post->title,
                    'order_id' => $order->id
                ]
            );
        }
    }
    
    // ... return response ...
}
```

### B. In `assignTeam()` method:

```php
public function assignTeam($id, Request $request)
{
    // ... existing code to assign team ...
    
    $post = Post::findOrFail($id);
    $designerId = $request->designer_id;
    $marketerId = $request->marketer_id;
    
    // ... assign team logic ...
    
    // Notify designer
    if ($designerId) {
        $designer = \App\Models\Designer::find($designerId);
        $this->sendNotification(
            $designer,
            'Post Assignment',
            "You have been assigned to work on post: {$post->title}",
            'post_assigned',
            [
                'post_id' => $post->id,
                'title' => $post->title
            ]
        );
    }
    
    // Notify marketer
    if ($marketerId) {
        $marketer = \App\Models\Marketer::find($marketerId);
        $this->sendNotification(
            $marketer,
            'Post Assignment',
            "You have been assigned to work on post: {$post->title}",
            'post_assigned',
            [
                'post_id' => $post->id,
                'title' => $post->title
            ]
        );
    }
    
    // ... return response ...
}
```

---

## 🔧 Step 6: Update Designer/Marketer Post Controllers

Files:
- `app/Http/Controllers/Client/DesignerPostController.php`
- `app/Http/Controllers/Client/MarketerPostController.php`

In `update()` method:

```php
public function update($id, Request $request)
{
    // ... existing code to update post ...
    
    $post = Post::with('client')->findOrFail($id);
    
    // ... update logic ...
    
    // Notify client
    $this->sendNotification(
        $post->client,
        'Post Updated',
        "Post \"{$post->title}\" has been updated - ready for review",
        'post_updated',
        [
            'post_id' => $post->id,
            'title' => $post->title
        ]
    );
    
    // Notify admin
    $this->notifyAdmins(
        'Post Updated',
        "Post \"{$post->title}\" updated by " . auth()->user()->name,
        'post_updated',
        [
            'post_id' => $post->id,
            'title' => $post->title,
            'updated_by' => auth()->user()->name
        ]
    );
    
    // ... return response ...
}
```

---

## 🔧 Step 7: Update PostController (Shared Endpoints)

File: `app/Http/Controllers/PostController.php`

### A. In `addFeedback()` method:

```php
public function addFeedback($id, Request $request)
{
    // ... existing code to add feedback ...
    
    $post = Post::with(['client', 'designer', 'marketer'])->findOrFail($id);
    $feedback = PostFeedback::create($data);
    
    $currentUser = $this->getCurrentUser(); // Helper to get current authenticated user
    
    // Notify post creator (designer/marketer) - but not if they're the one adding feedback
    if ($post->designer && $post->designer->id !== $currentUser->id) {
        $this->sendNotification(
            $post->designer,
            'New Feedback',
            "{$currentUser->name} added feedback on post: {$post->title}",
            'feedback_added',
            [
                'post_id' => $post->id,
                'title' => $post->title,
                'feedback' => $feedback->comment
            ]
        );
    }
    
    if ($post->marketer && $post->marketer->id !== $currentUser->id) {
        $this->sendNotification(
            $post->marketer,
            'New Feedback',
            "{$currentUser->name} added feedback on post: {$post->title}",
            'feedback_added',
            [
                'post_id' => $post->id,
                'title' => $post->title,
                'feedback' => $feedback->comment
            ]
        );
    }
    
    // Notify client (if not the one adding feedback)
    if ($post->client->id !== $currentUser->id) {
        $this->sendNotification(
            $post->client,
            'New Feedback',
            "New feedback on post: {$post->title}",
            'feedback_added',
            [
                'post_id' => $post->id,
                'title' => $post->title
            ]
        );
    }
    
    // Notify admin
    $this->notifyAdmins(
        'New Feedback',
        "Feedback on post: {$post->title}",
        'feedback_added',
        [
            'post_id' => $post->id,
            'title' => $post->title
        ]
    );
    
    // ... return response ...
}

// Helper method
protected function getCurrentUser()
{
    foreach (['admin', 'client', 'designer', 'marketer'] as $guard) {
        if (auth()->guard($guard)->check()) {
            return auth()->guard($guard)->user();
        }
    }
    return null;
}
```

### B. In `approve()` method:

```php
public function approve($id)
{
    // ... existing code to approve post ...
    
    $post = Post::with(['client', 'designer', 'marketer'])->findOrFail($id);
    
    // ... approval logic ...
    
    $currentUser = $this->getCurrentUser();
    
    // Notify designer
    if ($post->designer) {
        $this->sendNotification(
            $post->designer,
            'Post Approved! 🎉',
            "Your post \"{$post->title}\" has been approved",
            'post_approved',
            [
                'post_id' => $post->id,
                'title' => $post->title
            ]
        );
    }
    
    // Notify marketer
    if ($post->marketer) {
        $this->sendNotification(
            $post->marketer,
            'Post Approved! 🎉',
            "Your post \"{$post->title}\" has been approved",
            'post_approved',
            [
                'post_id' => $post->id,
                'title' => $post->title
            ]
        );
    }
    
    // Notify client (if not the one approving)
    if ($post->client->id !== $currentUser->id) {
        $this->sendNotification(
            $post->client,
            'Post Approved',
            "Post \"{$post->title}\" has been approved",
            'post_approved',
            [
                'post_id' => $post->id,
                'title' => $post->title,
                'scheduled_at' => $post->scheduled_at
            ]
        );
    }
    
    // Notify admin
    $this->notifyAdmins(
        'Post Approved',
        "Post \"{$post->title}\" approved by {$currentUser->name}",
        'post_approved',
        [
            'post_id' => $post->id,
            'title' => $post->title,
            'approved_by' => $currentUser->name
        ]
    );
    
    // ... return response ...
}
```

---

## 🧪 Testing the Implementation

### 1. Test Order Creation
```bash
# Client creates order
POST /client/product-orders

# Expected notifications:
# - Client: "Order Confirmed"
# - Admin: "New Order Received"
```

### 2. Test Payment Approval
```bash
# Admin approves payment
POST /admin/product-orders/1/approve-payment

# Expected notifications:
# - Client: "Payment Approved"
```

### 3. Test Team Assignment
```bash
# Admin assigns team
POST /admin/product-orders/1/team
{
    "designer_ids": [1],
    "marketer_ids": [1]
}

# Expected notifications:
# - Designer: "New Project Assignment"
# - Marketer: "New Project Assignment"
# - Client: "Team Assigned"
```

### 4. Test Post Creation
```bash
# Admin creates post
POST /admin/posts

# Expected notifications:
# - Client: "New Post Created"
# - Team members: "New Post"
```

### 5. Test Feedback
```bash
# Anyone adds feedback
POST /posts/1/feedback

# Expected notifications:
# - Designer: "New Feedback"
# - Marketer: "New Feedback"
# - Client: "New Feedback"
# - Admin: "New Feedback"
```

### 6. Test Approval
```bash
# Client approves post
POST /posts/1/approve

# Expected notifications:
# - Designer: "Post Approved! 🎉"
# - Marketer: "Post Approved! 🎉"
# - Admin: "Post Approved"
```

---

## ✅ Verification Checklist

After implementation:

- [ ] Notification templates created in database
- [ ] SendsNotifications trait created
- [ ] ProductOrderController updated (client)
- [ ] AdminProductOrderController updated
- [ ] AdminPostController updated
- [ ] DesignerPostController updated
- [ ] MarketerPostController updated
- [ ] PostController updated (shared endpoints)
- [ ] Test each workflow end-to-end
- [ ] Verify notifications appear in GET /notifications
- [ ] Verify Firebase push notifications work
- [ ] Check logs for any errors

---

## 🚀 Deployment Steps

1. Run SQL to create notification templates
2. Create SendsNotifications trait
3. Update all controllers
4. Test in development
5. Deploy to production
6. Monitor logs for any issues

---

## 📊 Expected Results

After implementation, users will receive notifications for:

- **Admin**: New orders, post updates, feedback, approvals
- **Client**: Order confirmations, team assignments, post updates, feedback, approvals
- **Designer**: Project assignments, post assignments, feedback, approvals
- **Marketer**: Project assignments, post assignments, feedback, approvals

All notifications will be:
- ✅ Sent via Firebase (push notifications)
- ✅ Stored in database (viewable via GET /notifications)
- ✅ Localized (English/Arabic)
- ✅ Logged for debugging
