# Notification System Implementation Overview

## 1. NOTIFICATION ROUTES
**File:** [routes/api.php](routes/api.php#L52-L58)

```php
// Shared Routes - Notifications (All Authenticated Roles)
Route::middleware(['auth:admin,client,employee,designer,marketer'])->group(function () {
    Route::get('notifications', [NotificationController::class, 'getNotifications']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markNotificationAsRead']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllNotificationsAsRead']);
});
```

### Endpoints Summary:
| Endpoint | Method | Purpose | Roles |
|----------|--------|---------|-------|
| `/notifications` | GET | Retrieve all notifications for authenticated user | Admin, Client, Employee, Designer, Marketer |
| `/notifications/{id}/read` | POST | Mark specific notification as read | Admin, Client, Employee, Designer, Marketer |
| `/notifications/read-all` | POST | Mark all notifications as read | Admin, Client, Employee, Designer, Marketer |

**Roles with Access:** Admin, Client, Employee, Designer, Marketer (All authenticated roles)

---

## 2. NOTIFICATION CONTROLLER
**File:** [app/Http/Controllers/NotificationController.php](app/Http/Controllers/NotificationController.php)

### Methods:

#### a) `getNotifications(Request $request)`
- **Purpose:** Retrieve all notifications for the authenticated user
- **Authentication:** Checks all 5 guards (admin, client, employee, designer, marketer)
- **Query:** Filters by `notifiable_id` and `notifiable_type`
- **Ordering:** Latest first
- **Response:** Returns array of notifications with id, title, message, data, is_read, created_at, notification_type

#### b) `markNotificationAsRead($id, Request $request)`
- **Purpose:** Mark a single notification as read
- **Authentication:** Checks all 5 guards
- **Validation:** Ensures user owns the notification (checks notifiable_id and notifiable_type)
- **Error Handling:** Returns 404 if notification not found
- **Response:** Updates `is_read` to true

#### c) `markAllNotificationsAsRead(Request $request)`
- **Purpose:** Mark all notifications as read for authenticated user
- **Authentication:** Checks all 5 guards
- **Query:** Updates all notifications where notifiable_id and notifiable_type match current user
- **Response:** Confirms all notifications marked as read

#### d) `sendNotification(Request $request)`
- **Purpose:** Send notifications (admin function)
- **Validation:**
  - `type` (required, must exist in notification_templates)
  - `notifiable_id` (optional)
  - `notifiable_type` (optional, can be: admin, client, employee)
  - `data` (optional array)
- **Functionality:**
  - Case 1: Send to specific user (if notifiable_id provided)
  - Case 2: Send to all clients & employees with FCM token
- **Process:**
  1. Fetch notification template by type
  2. Replace placeholders in message with data values
  3. Get FCM token from user
  4. Call Firebase to send push notification
  5. Create database notification record

#### e) `sendChatNotification(Request $request)`
- **Purpose:** Send chat-specific notifications
- **Validation:**
  - `receiver_id` (optional)
  - `sender_id` (required)
  - `sender_type` (required: client or admin)
  - `message` (optional)
  - `imageUrl` (optional)
  - `audio` (optional)
- **Functionality:**
  - If sender is client: sends to all admins
  - If sender is admin: sends to specific client receiver
  - Uses Firebase for push + creates DB record

---

## 3. NOTIFICATION MODEL
**File:** [app/Models/Notification.php](app/Models/Notification.php)

### Schema:
```php
protected $fillable = [
    'title',
    'message',
    'token',
    'is_read',
    'notifiable_id',
    'notifiable_type',
    'data',
    'notification_template_id'
];

protected $casts = [
    'data' => 'array',
];
```

### Relationships:

**1. Polymorphic Relationship - `notifiable()`**
```php
public function notifiable()
{
    return $this->morphTo();
}
```
- Allows notifications to be associated with any model (Admin, Client, Employee, Designer, Marketer)
- Uses `notifiable_type` and `notifiable_id` columns

**2. Template Relationship - `template()`**
```php
public function template()
{
    return $this->belongsTo(NotificationTemplate::class, 'notification_template_id');
}
```
- Links to notification template for type information

---

## 4. NOTIFICATION TEMPLATE MODEL
**File:** [app/Models/NotificationTemplate.php](app/Models/NotificationTemplate.php)

### Schema:
```php
protected $fillable = ['type', 'title', 'message'];
```

### Columns:
| Column | Type | Purpose |
|--------|------|---------|
| id | bigint | Primary key |
| type | string (unique) | Template identifier (e.g., 'chat_message', 'screen_review') |
| title | string | Notification title |
| message | text | Notification message template (supports placeholders like {name}, {message}) |
| timestamps | - | created_at, updated_at |

### Supported Types (from observers):
- `screen_created` - When a screen is created
- `screen_updated` - When a screen is updated
- `screen_review` - When screen is sent for review
- `screen_implemented` - When screen is implemented
- `screen_integrated` - When screen is integrated
- `screen_dev_mode_enabled` - When dev mode is enabled
- `request_status_updated` - When request status changes
- `chat_message` - Chat message notifications

---

## 5. NOTIFICATION REPOSITORY
**File:** [app/Repositories/NotificationRepository.php](app/Repositories/NotificationRepository.php)

### Method: `createNotification()`

```php
public function createNotification(
    $notifiable,              // User object (Admin, Client, Employee, etc.)
    $title,                   // Notification title
    $message,                 // Notification message
    $deviceToken,             // FCM device token
    $notificationType,        // Type string or array (for chat)
    $extraData = []           // Additional data to store
)
```

**Process:**
1. If `$notificationType` is array (chat message):
   - Uses 'chat_message' template
   - Merges device_token with notificationType
   
2. Otherwise:
   - Fetches template by type
   - Sets type to notificationType

3. Creates Notification record with:
   - `notifiable_id` and `notifiable_type` (polymorphic)
   - Title and message
   - Data array (merged with device_token)
   - `is_read` = false
   - Link to template

---

## 6. DATABASE SCHEMA

### notifications table
**File:** [database/migrations/2026_05_31_000004_create_notifications_table.php](database/migrations/2026_05_31_000004_create_notifications_table.php)

```php
Schema::create('notifications', function (Blueprint $table) {
    $table->id();
    $table->string('title')->nullable();
    $table->text('message')->nullable();
    $table->string('token')->nullable();                    // FCM token
    $table->boolean('is_read')->default(false);             // Read status
    $table->unsignedBigInteger('notifiable_id');            // Polymorphic ID
    $table->string('notifiable_type');                      // Polymorphic type
    $table->json('data')->nullable();                       // Extra data
    $table->foreignId('notification_template_id')
        ->nullable()
        ->constrained('notification_templates')
        ->onDelete('cascade');
    $table->timestamps();
    
    $table->index(['notifiable_type', 'notifiable_id']);   // Query optimization
});
```

### notification_templates table
**File:** [database/migrations/2026_05_31_000003_create_notification_templates_table.php](database/migrations/2026_05_31_000003_create_notification_templates_table.php)

```php
Schema::create('notification_templates', function (Blueprint $table) {
    $table->id();
    $table->string('type')->unique();      // Unique template type
    $table->string('title');               // Template title
    $table->text('message');               // Template message
    $table->timestamps();
});
```

---

## 7. HOW NOTIFICATIONS ARE CREATED

### Flow 1: From Observers (Automatic)

**Example: ScreenObserver.php**

When a screen is created/updated, observers automatically trigger notifications:

```php
// 1. Get notification template
$template = NotificationTemplate::where('type', 'screen_created')->first();

// 2. Prepare message (can contain placeholders)
$title = $template->title;
$message = $template->message;

// 3. Send via Firebase
app(FirebaseService::class)->sendNotification(
    $user->device_token,
    $title,
    $message
);

// 4. Save to database
app(NotificationRepository::class)->createNotification(
    $user,                      // Notifiable user
    $title,
    $message,
    $user->device_token,        // FCM token
    'screen_created',           // Type
    ['screen_id' => $screen->id] // Extra data
);
```

**Observers Using Notifications:**
- [app/Observers/ScreenObserver.php](app/Observers/ScreenObserver.php) - Screen events
- [app/Observers/ScreenReviewObserver.php](app/Observers/ScreenReviewObserver.php) - Review requests
- [app/Observers/RequestStatusObserver.php](app/Observers/RequestStatusObserver.php) - Request status changes

### Flow 2: Programmatic (Manual)

Via NotificationController:

```php
// POST /notifications (internal admin endpoint)
// Validates notification type
// Sends to specific user OR all users with FCM token
// Creates database record + Firebase push
```

### Flow 3: Chat Notifications

```php
// POST /notifications/send-chat
// Validates sender/receiver
// Handles image/audio attachments
// Routes based on sender type (client → admins, admin → specific client)
```

---

## 8. HOW NOTIFICATIONS ARE RETRIEVED

### Client Retrieves:
```php
GET /notifications

// Returns:
{
    "status": true,
    "data": [
        {
            "id": 1,
            "title": "Screen Review",
            "message": "Your screen has been reviewed",
            "data": { "screen_id": 123 },
            "is_read": false,
            "created_at": "2026-05-31 10:30:00",
            "notification_type": "screen_review"
        }
    ]
}
```

### Query Logic:
1. Authenticate user (checks 5 guards)
2. Query notifications where:
   - `notifiable_id` = user.id
   - `notifiable_type` = user class (e.g., "App\Models\Client")
3. Order by latest first
4. Load template relationship
5. Map to response format

---

## 9. ROLE ACCESS CONTROL

### Notification Endpoints Access:

All endpoints are protected by middleware:
```php
Route::middleware(['auth:admin,client,employee,designer,marketer'])->group(function () {
```

This means:
- **Admin** - Can access (guard: admin)
- **Client** - Can access (guard: client)
- **Employee** - Can access (guard: employee)
- **Designer** - Can access (guard: designer)
- **Marketer** - Can access (guard: marketer)

### User Model Relationships:

Each user type has notification relationship:

**Admin** - [app/Models/Admin.php](app/Models/Admin.php)
```php
// Inherits Notifiable trait
// Can be polymorphic notifiable
```

**Client** - [app/Models/Client.php](app/Models/Client.php)
```php
public function notifications()
{
    return $this->morphMany(Notification::class, 'notifiable');
}
```

**Employee** - [app/Models/Employee.php](app/Models/Employee.php)
```php
// Inherits Notifiable trait
```

### Notification Visibility:
- Users can only see their own notifications
- Queries filter by `notifiable_id` and `notifiable_type`
- No cross-role notification visibility (privacy enforced at query level)

---

## 10. FIREBASE INTEGRATION

**File:** [app/Services/FirebaseService.php](app/Services/FirebaseService.php)

### Key Methods:

#### `sendNotification()`
```php
public function sendNotification(
    $deviceToken,              // FCM device token
    $title,                    // Notification title
    $message,                  // Notification message
    $type = null,              // Notification type
    $extraData = []            // Additional data
)
```

#### `sendChatNotification()`
```php
public function sendChatNotification(
    $token,                    // Device token
    $messageData               // Chat message data
)
```

### Requirements:
- Device token stored in user model (`device_token` or `fcm_token`)
- Firebase initialized (credentials from .env)
- Token must be valid and not null

---

## 11. CURRENT ISSUES & NOTES

1. **FCM Token Fields**: Inconsistency in naming
   - Admin uses `device_token`
   - Client uses `fcm_token` or `device_token`
   - Employee uses `device_token`

2. **Message Placeholders**: Supported placeholders can be added dynamically:
   ```php
   $message = "Hello {name}, your screen {screen_id} was reviewed";
   // Replace with $data array
   ```

3. **Notification Templates**: Must be seeded with required types before notifications can be sent

4. **Polymorphic Design**: Allows future extension to other user types (Designers, Marketers)

---

## 12. POSTMAN COLLECTION ENDPOINTS

**Base URL:** `{{base_url}}`

### Get My Notifications
```
GET /notifications
Header: Authorization: Bearer {{token}}
```

### Mark Single Notification as Read
```
POST /notifications/1/read
Header: Authorization: Bearer {{token}}
```

### Mark All Notifications as Read
```
POST /notifications/read-all
Header: Authorization: Bearer {{token}}
```

---

## Files Summary

| File | Purpose |
|------|---------|
| [routes/api.php](routes/api.php) | Route definitions |
| [app/Http/Controllers/NotificationController.php](app/Http/Controllers/NotificationController.php) | Controller with 5 main methods |
| [app/Models/Notification.php](app/Models/Notification.php) | Notification model with polymorphic relationship |
| [app/Models/NotificationTemplate.php](app/Models/NotificationTemplate.php) | Template model |
| [app/Repositories/NotificationRepository.php](app/Repositories/NotificationRepository.php) | Repository for creating notifications |
| [app/Services/FirebaseService.php](app/Services/FirebaseService.php) | Firebase integration |
| [database/migrations/2026_05_31_000003_create_notification_templates_table.php](database/migrations/2026_05_31_000003_create_notification_templates_table.php) | Templates table migration |
| [database/migrations/2026_05_31_000004_create_notifications_table.php](database/migrations/2026_05_31_000004_create_notifications_table.php) | Notifications table migration |
| [app/Observers/ScreenObserver.php](app/Observers/ScreenObserver.php) | Automatic notifications for screen events |
| [app/Observers/ScreenReviewObserver.php](app/Observers/ScreenReviewObserver.php) | Automatic notifications for screen review |
| [app/Observers/RequestStatusObserver.php](app/Observers/RequestStatusObserver.php) | Automatic notifications for request status |

