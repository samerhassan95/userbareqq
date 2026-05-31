# Complete Notification Workflow - Based on Actual System

## 🎯 System Overview

This is a **Social Media Management & Marketing Strategy Platform** where:
- **Clients** order marketing services (strategy, posts, content)
- **Admin** manages orders, creates posts, assigns team
- **Designers** create visual content for posts
- **Marketers** create marketing content and strategies
- **Everyone** collaborates on posts with feedback and approvals

---

## 📊 Complete Workflow

### Phase 1: Client Orders a Service

```
CLIENT → Creates Product Order (Strategy or One-Time)
```

**Endpoints:**
- `POST /client/product-orders` (one_time or strategy)
- Strategy durations: month, three_months, six_months, year

**What happens:**
1. Client selects a product (e.g., "Social Media Strategy")
2. Client chooses duration (for strategy products)
3. Client uploads payment proof
4. Order is created with status: `pending_payment`

**Notifications:**
- ✅ **ADMIN** gets notified: "New order received from {client_name}"
- ✅ **CLIENT** gets confirmation: "Your order has been received"

---

### Phase 2: Admin Approves Payment

```
ADMIN → Approves Payment
```

**Endpoint:**
- `POST /admin/product-orders/{id}/approve-payment`

**What happens:**
1. Admin reviews payment proof
2. Admin approves payment
3. Order status changes to: `in_progress`

**Notifications:**
- ✅ **CLIENT** gets notified: "Your payment has been approved. Work will begin soon!"

---

### Phase 3: Admin Assigns Team Members

```
ADMIN → Assigns Designers & Marketers to Order
```

**Endpoint:**
- `POST /admin/product-orders/{id}/team`

**Body:**
```json
{
    "designer_ids": [1, 2],
    "marketer_ids": [1]
}
```

**What happens:**
1. Admin assigns designers and marketers to the order
2. Team members are now available for all posts in this order

**Notifications:**
- ✅ **DESIGNER(S)** get notified: "You've been assigned to {client_name}'s project"
- ✅ **MARKETER(S)** get notified: "You've been assigned to {client_name}'s project"
- ✅ **CLIENT** gets notified: "Your team has been assigned"

---

### Phase 4: Admin/Marketer Creates Posts

```
ADMIN or MARKETER → Creates Post for Client
```

**Endpoints:**
- `POST /admin/posts` (Admin creates)
- `POST /marketer/posts` (Marketer creates)

**Body:**
```json
{
    "title": "Summer Sale Campaign",
    "description": "Promote summer products",
    "client_id": 1,
    "product_order_id": 7,
    "scheduled_at": "2026-06-01 10:00:00",
    "images[]": [files]
}
```

**What happens:**
1. Post is created with status: `pending`
2. Post is linked to client and product order
3. Can be scheduled for future publishing

**Notifications:**
- ✅ **CLIENT** gets notified: "New post created for your review"
- ✅ **DESIGNER(S)** (if assigned to order) get notified: "New post needs design work"
- ✅ **MARKETER(S)** (if assigned to order) get notified: "New post created"

---

### Phase 5: Admin Assigns Specific Team to Post

```
ADMIN → Assigns Specific Designer/Marketer to Post
```

**Endpoint:**
- `POST /admin/posts/{id}/team`

**Body:**
```json
{
    "designer_id": 1,
    "marketer_id": 1
}
```

**What happens:**
1. Specific team members are assigned to work on this post
2. They can now edit and work on the post

**Notifications:**
- ✅ **ASSIGNED DESIGNER** gets notified: "You've been assigned to post: {title}"
- ✅ **ASSIGNED MARKETER** gets notified: "You've been assigned to post: {title}"

---

### Phase 6: Designer/Marketer Works on Post

```
DESIGNER or MARKETER → Updates Post Content
```

**Endpoints:**
- `PUT /designer/posts/{id}` (Designer updates)
- `PUT /marketer/posts/{id}` (Marketer updates)

**What happens:**
1. Designer uploads images/graphics
2. Marketer writes copy/content
3. Post is updated with new content

**Notifications:**
- ✅ **CLIENT** gets notified: "Post has been updated - ready for review"
- ✅ **ADMIN** gets notified: "Post updated by {designer/marketer}"

---

### Phase 7: Feedback Loop

```
CLIENT/ADMIN/DESIGNER/MARKETER → Adds Feedback
```

**Endpoint:**
- `POST /posts/{id}/feedback` (Shared endpoint - all roles)

**Body:**
```json
{
    "comment": "Please change the background color to blue"
}
```

**What happens:**
1. Anyone can add feedback/comments to the post
2. Feedback is visible to all team members
3. Post remains in `pending` status

**Notifications:**
- ✅ **POST CREATOR** (Designer/Marketer) gets notified: "{user} added feedback: {comment}"
- ✅ **CLIENT** gets notified (if feedback from team): "Team added feedback to your post"
- ✅ **ADMIN** gets notified: "New feedback on post: {title}"
- ✅ **ALL TEAM MEMBERS** on the post get notified

---

### Phase 8: Post Approval

```
CLIENT or ADMIN or MARKETER → Approves Post
```

**Endpoint:**
- `POST /posts/{id}/approve` (Shared endpoint)

**What happens:**
1. Post status changes to: `approved`
2. Post is ready for publishing
3. If scheduled, will be published at scheduled time
4. No more feedback can be added

**Notifications:**
- ✅ **DESIGNER** gets notified: "Your post has been approved! 🎉"
- ✅ **MARKETER** gets notified: "Your post has been approved! 🎉"
- ✅ **CLIENT** gets notified: "Post approved and scheduled for {date}"
- ✅ **ADMIN** gets notified: "Post approved by {user}"

---

### Phase 9: Post Publishing

```
SYSTEM → Publishes Post (if scheduled)
```

**What happens:**
1. At scheduled time, post is published
2. Post status changes to: `published`

**Notifications:**
- ✅ **CLIENT** gets notified: "Your post has been published! 🚀"
- ✅ **DESIGNER** gets notified: "Post published successfully"
- ✅ **MARKETER** gets notified: "Post published successfully"

---

### Phase 10: Order Completion

```
ADMIN → Marks Order as Completed
```

**Endpoint:**
- `PUT /admin/product-orders/{id}/status`

**Body:**
```json
{
    "status": "completed"
}
```

**What happens:**
1. All posts are approved and published
2. Order is marked as completed
3. Deliverables are uploaded

**Notifications:**
- ✅ **CLIENT** gets notified: "Your order has been completed! Thank you!"
- ✅ **DESIGNER(S)** get notified: "Project completed"
- ✅ **MARKETER(S)** get notified: "Project completed"

---

## 🔔 Notification Events Summary

### 🔴 ADMIN Notifications

| Event | Trigger | Endpoint |
|-------|---------|----------|
| New Order | Client creates order | `POST /client/product-orders` |
| Payment Uploaded | Client uploads payment proof | `POST /client/product-orders` |
| Post Updated | Designer/Marketer updates post | `PUT /designer/posts/{id}` |
| Feedback Added | Anyone adds feedback | `POST /posts/{id}/feedback` |
| Post Approved | Client approves post | `POST /posts/{id}/approve` |

### 🔵 CLIENT Notifications

| Event | Trigger | Endpoint |
|-------|---------|----------|
| Order Confirmed | Admin approves payment | `POST /admin/product-orders/{id}/approve-payment` |
| Team Assigned | Admin assigns team | `POST /admin/product-orders/{id}/team` |
| Post Created | Admin/Marketer creates post | `POST /admin/posts` |
| Post Updated | Designer/Marketer updates post | `PUT /designer/posts/{id}` |
| Feedback Added | Team adds feedback | `POST /posts/{id}/feedback` |
| Post Approved | Post is approved | `POST /posts/{id}/approve` |
| Post Published | Post is published | System (scheduled) |
| Order Completed | Admin completes order | `PUT /admin/product-orders/{id}/status` |

### 🟢 DESIGNER Notifications

| Event | Trigger | Endpoint |
|-------|---------|----------|
| Assigned to Order | Admin assigns to order | `POST /admin/product-orders/{id}/team` |
| Assigned to Post | Admin assigns to specific post | `POST /admin/posts/{id}/team` |
| Post Created | New post in their order | `POST /admin/posts` |
| Feedback Added | Anyone adds feedback to their post | `POST /posts/{id}/feedback` |
| Post Approved | Their post is approved | `POST /posts/{id}/approve` |
| Post Published | Their post is published | System (scheduled) |
| Order Completed | Project is completed | `PUT /admin/product-orders/{id}/status` |

### 🟡 MARKETER Notifications

| Event | Trigger | Endpoint |
|-------|---------|----------|
| Assigned to Order | Admin assigns to order | `POST /admin/product-orders/{id}/team` |
| Assigned to Post | Admin assigns to specific post | `POST /admin/posts/{id}/team` |
| Post Created | New post in their order | `POST /admin/posts` |
| Feedback Added | Anyone adds feedback to their post | `POST /posts/{id}/feedback` |
| Post Approved | Their post is approved | `POST /posts/{id}/approve` |
| Post Published | Their post is published | System (scheduled) |
| Order Completed | Project is completed | `PUT /admin/product-orders/{id}/status` |

---

## 🎨 Notification Implementation

### Where to Add Notifications

#### 1. ProductOrderController

```php
// When client creates order
public function store(Request $request) {
    // ... create order
    
    // Notify admin
    $this->sendNotification(
        Admin::all(),
        'New Order Received',
        "New order from {$client->name}",
        'order_created'
    );
    
    // Notify client
    $this->sendNotification(
        $client,
        'Order Received',
        'Your order has been received',
        'order_confirmed'
    );
}

// When admin approves payment
public function approvePayment($id) {
    // ... approve payment
    
    // Notify client
    $this->sendNotification(
        $order->client,
        'Payment Approved',
        'Your payment has been approved',
        'payment_approved'
    );
}

// When admin assigns team
public function assignTeam($id, Request $request) {
    // ... assign team
    
    // Notify designers
    foreach ($designers as $designer) {
        $this->sendNotification(
            $designer,
            'New Project Assignment',
            "You've been assigned to {$client->name}'s project",
            'team_assigned'
        );
    }
    
    // Notify marketers
    foreach ($marketers as $marketer) {
        $this->sendNotification(
            $marketer,
            'New Project Assignment',
            "You've been assigned to {$client->name}'s project",
            'team_assigned'
        );
    }
    
    // Notify client
    $this->sendNotification(
        $order->client,
        'Team Assigned',
        'Your team has been assigned',
        'team_assigned'
    );
}
```

#### 2. PostController

```php
// When post is created
public function store(Request $request) {
    // ... create post
    
    // Notify client
    $this->sendNotification(
        $post->client,
        'New Post Created',
        "New post created: {$post->title}",
        'post_created'
    );
    
    // Notify team members assigned to order
    if ($post->product_order_id) {
        $teamMembers = $post->productOrder->teamMembers;
        foreach ($teamMembers as $member) {
            $this->sendNotification(
                $member,
                'New Post',
                "New post needs your work: {$post->title}",
                'post_created'
            );
        }
    }
}

// When team is assigned to post
public function assignTeam($id, Request $request) {
    // ... assign team
    
    // Notify assigned designer
    if ($designer) {
        $this->sendNotification(
            $designer,
            'Post Assignment',
            "You've been assigned to: {$post->title}",
            'post_assigned'
        );
    }
    
    // Notify assigned marketer
    if ($marketer) {
        $this->sendNotification(
            $marketer,
            'Post Assignment',
            "You've been assigned to: {$post->title}",
            'post_assigned'
        );
    }
}

// When post is updated
public function update($id, Request $request) {
    // ... update post
    
    // Notify client
    $this->sendNotification(
        $post->client,
        'Post Updated',
        "Post updated: {$post->title}",
        'post_updated'
    );
    
    // Notify admin
    $this->sendNotification(
        Admin::all(),
        'Post Updated',
        "Post updated by {$user->name}",
        'post_updated'
    );
}
```

#### 3. PostFeedbackController

```php
// When feedback is added
public function store($postId, Request $request) {
    // ... create feedback
    
    // Notify post creator (designer/marketer)
    if ($post->designer) {
        $this->sendNotification(
            $post->designer,
            'New Feedback',
            "{$user->name} added feedback: {$feedback->comment}",
            'feedback_added'
        );
    }
    
    if ($post->marketer) {
        $this->sendNotification(
            $post->marketer,
            'New Feedback',
            "{$user->name} added feedback: {$feedback->comment}",
            'feedback_added'
        );
    }
    
    // Notify client
    $this->sendNotification(
        $post->client,
        'New Feedback',
        "New feedback on: {$post->title}",
        'feedback_added'
    );
    
    // Notify admin
    $this->sendNotification(
        Admin::all(),
        'New Feedback',
        "Feedback on: {$post->title}",
        'feedback_added'
    );
}
```

#### 4. Post Approval

```php
// When post is approved
public function approve($id) {
    // ... approve post
    
    // Notify designer
    if ($post->designer) {
        $this->sendNotification(
            $post->designer,
            'Post Approved! 🎉',
            "Your post has been approved: {$post->title}",
            'post_approved'
        );
    }
    
    // Notify marketer
    if ($post->marketer) {
        $this->sendNotification(
            $post->marketer,
            'Post Approved! 🎉',
            "Your post has been approved: {$post->title}",
            'post_approved'
        );
    }
    
    // Notify client
    $this->sendNotification(
        $post->client,
        'Post Approved',
        "Post approved: {$post->title}",
        'post_approved'
    );
    
    // Notify admin
    $this->sendNotification(
        Admin::all(),
        'Post Approved',
        "Post approved by {$user->name}",
        'post_approved'
    );
}
```

---

## 🚀 Implementation Steps

### Step 1: Create Notification Templates

```sql
INSERT INTO notification_templates (type, title, message, title_ar, message_ar) VALUES
('order_created', 'New Order', 'New order from {client_name}', 'طلب جديد', 'طلب جديد من {client_name}'),
('order_confirmed', 'Order Received', 'Your order has been received', 'تم استلام الطلب', 'تم استلام طلبك'),
('payment_approved', 'Payment Approved', 'Your payment has been approved', 'تم قبول الدفع', 'تم قبول دفعتك'),
('team_assigned', 'Team Assigned', 'You have been assigned to a project', 'تم التعيين', 'تم تعيينك لمشروع'),
('post_created', 'New Post', 'New post created: {title}', 'منشور جديد', 'تم إنشاء منشور جديد: {title}'),
('post_assigned', 'Post Assignment', 'You have been assigned to a post', 'تعيين منشور', 'تم تعيينك لمنشور'),
('post_updated', 'Post Updated', 'Post has been updated', 'تحديث المنشور', 'تم تحديث المنشور'),
('feedback_added', 'New Feedback', 'New feedback added', 'ملاحظات جديدة', 'تمت إضافة ملاحظات جديدة'),
('post_approved', 'Post Approved', 'Post has been approved', 'تمت الموافقة', 'تمت الموافقة على المنشور'),
('post_published', 'Post Published', 'Post has been published', 'تم النشر', 'تم نشر المنشور'),
('order_completed', 'Order Completed', 'Your order has been completed', 'اكتمل الطلب', 'اكتمل طلبك');
```

### Step 2: Create Helper Method

Add to your controllers:

```php
protected function sendNotification($user, $title, $message, $type, $data = [])
{
    if (!$user->device_token) {
        return;
    }
    
    $firebaseService = app(FirebaseService::class);
    $notificationRepo = app(NotificationRepository::class);
    
    $firebaseService->sendNotification(
        $user->device_token,
        $title,
        $message,
        array_merge($data, ['notification_type' => $type])
    );
    
    $notificationRepo->createNotification(
        $user,
        $title,
        $message,
        $user->device_token,
        $type,
        $data
    );
}
```

### Step 3: Add Notifications to Controllers

Update these controllers:
- ✅ `ProductOrderController` - order creation, payment approval, team assignment
- ✅ `PostController` - post creation, updates, team assignment
- ✅ `PostFeedbackController` - feedback creation
- ✅ Post approval logic - wherever posts are approved

---

## ✨ Summary

This is a **collaborative content creation platform** where:

1. **Clients** order services
2. **Admin** manages and assigns work
3. **Designers & Marketers** create content
4. **Everyone** collaborates with feedback
5. **Clients** approve final work

**All roles need notifications** at every step to stay informed and collaborate effectively!
