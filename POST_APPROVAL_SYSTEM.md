# Post Approval System - Multi-Party Approval

## Overview
نظام الموافقة على البوستات يتطلب موافقة من 3 أطراف:
1. **Client** (صاحب الأوردر)
2. **Admin** (الإدارة)
3. **Marketer** (المسوق)

البوست يصبح **fully approved** فقط عندما يوافق عليه الثلاثة أطراف.

## Database Fields

### posts table - New Fields
```sql
client_approved BOOLEAN DEFAULT FALSE
client_approved_at TIMESTAMP NULL
admin_approved BOOLEAN DEFAULT FALSE
admin_approved_at TIMESTAMP NULL
marketer_approved BOOLEAN DEFAULT FALSE
marketer_approved_at TIMESTAMP NULL
approved_by_client_id BIGINT NULL
approved_by_admin_id BIGINT NULL
approved_by_marketer_id BIGINT NULL
```

### Existing Fields
```sql
is_approved BOOLEAN DEFAULT FALSE  -- TRUE when all 3 parties approve
approved_at TIMESTAMP NULL         -- When fully approved
status VARCHAR                     -- 'pending', 'approved', 'rejected'
```

## API Endpoints

### 1. Client Approve Post
```
POST {{base_url}}/client/posts/{id}/approve
```

**Headers:**
```
Authorization: Bearer {{client_token}}
Accept-Language: ar
```

**Response:**
```json
{
    "success": true,
    "message": "Post approved by client successfully",
    "data": {
        "post": {
            "id": 1,
            "title": "Marketing Campaign",
            "title_ar": "حملة تسويقية",
            "client_approved": true,
            "client_approved_at": "2026-05-25T12:00:00.000000Z",
            "admin_approved": false,
            "marketer_approved": false,
            "is_approved": false,
            "status": "pending"
        },
        "approval_status": {
            "client_approved": true,
            "client_approved_at": "2026-05-25T12:00:00.000000Z",
            "admin_approved": false,
            "admin_approved_at": null,
            "marketer_approved": false,
            "marketer_approved_at": null,
            "is_fully_approved": false
        }
    }
}
```

### 2. Admin Approve Post
```
POST {{base_url}}/admin/posts/{id}/approve
```

**Headers:**
```
Authorization: Bearer {{admin_token}}
Accept-Language: ar
```

**Response:**
```json
{
    "success": true,
    "message": "Post approved by admin successfully",
    "data": {
        "post": {
            "id": 1,
            "title": "Marketing Campaign",
            "client_approved": true,
            "admin_approved": true,
            "admin_approved_at": "2026-05-25T12:30:00.000000Z",
            "marketer_approved": false,
            "is_approved": false,
            "status": "pending"
        },
        "approval_status": {
            "client_approved": true,
            "client_approved_at": "2026-05-25T12:00:00.000000Z",
            "admin_approved": true,
            "admin_approved_at": "2026-05-25T12:30:00.000000Z",
            "marketer_approved": false,
            "marketer_approved_at": null,
            "is_fully_approved": false
        }
    }
}
```

### 3. Marketer Approve Post
```
POST {{base_url}}/marketer/posts/{id}/approve
```

**Headers:**
```
Authorization: Bearer {{marketer_token}}
Accept-Language: ar
```

**Response (Final Approval):**
```json
{
    "success": true,
    "message": "Post approved by marketer successfully",
    "data": {
        "post": {
            "id": 1,
            "title": "Marketing Campaign",
            "client_approved": true,
            "admin_approved": true,
            "marketer_approved": true,
            "marketer_approved_at": "2026-05-25T13:00:00.000000Z",
            "is_approved": true,
            "approved_at": "2026-05-25T13:00:00.000000Z",
            "status": "approved"
        },
        "approval_status": {
            "client_approved": true,
            "client_approved_at": "2026-05-25T12:00:00.000000Z",
            "admin_approved": true,
            "admin_approved_at": "2026-05-25T12:30:00.000000Z",
            "marketer_approved": true,
            "marketer_approved_at": "2026-05-25T13:00:00.000000Z",
            "is_fully_approved": true
        }
    }
}
```

## Approval Flow

### Step 1: Client Approves
```
Client → POST /client/posts/1/approve
Result: client_approved = true
Status: is_approved = false (waiting for admin & marketer)
```

### Step 2: Admin Approves
```
Admin → POST /admin/posts/1/approve
Result: admin_approved = true
Status: is_approved = false (waiting for marketer)
```

### Step 3: Marketer Approves (Final)
```
Marketer → POST /marketer/posts/1/approve
Result: marketer_approved = true
Status: is_approved = true ✅ FULLY APPROVED
       approved_at = now()
       status = 'approved'
```

## Approval Order
الموافقات يمكن أن تحدث بأي ترتيب:
- ✅ Client → Admin → Marketer
- ✅ Admin → Client → Marketer
- ✅ Marketer → Admin → Client
- ✅ أي ترتيب آخر

البوست يصبح fully approved فقط عندما يوافق الثلاثة.

## Error Responses

### Already Approved by Same Party
```json
{
    "success": false,
    "message": "Post already approved by you"
}
```

### Post Not Found
```json
{
    "success": false,
    "message": "Post not found"
}
```

## Model Methods

### Post Model - New Methods

```php
// Approve by specific party
$post->approveByClient($clientId);
$post->approveByAdmin($adminId);
$post->approveByMarketer($marketerId);

// Check if fully approved
$post->isFullyApproved(); // Returns boolean

// Get approval status
$post->getApprovalStatus(); // Returns array with all approval info

// Check if can be edited (old method still works)
$post->canBeEdited(); // Returns false if is_approved = true
```

## Frontend Implementation Example

### Display Approval Status
```javascript
function displayApprovalStatus(post) {
    const status = {
        client: post.client_approved ? '✅' : '⏳',
        admin: post.admin_approved ? '✅' : '⏳',
        marketer: post.marketer_approved ? '✅' : '⏳'
    };
    
    console.log(`
        Client: ${status.client}
        Admin: ${status.admin}
        Marketer: ${status.marketer}
        Fully Approved: ${post.is_approved ? 'YES ✅' : 'NO ⏳'}
    `);
}
```

### Approve Post (Client)
```javascript
async function approvePost(postId) {
    const response = await fetch(`${baseUrl}/client/posts/${postId}/approve`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${clientToken}`,
            'Accept-Language': 'ar'
        }
    });
    
    const result = await response.json();
    
    if (result.success) {
        console.log('Approval Status:', result.data.approval_status);
        
        if (result.data.approval_status.is_fully_approved) {
            alert('Post is now fully approved! 🎉');
        } else {
            alert('Your approval recorded. Waiting for others...');
        }
    }
}
```

## Migration

### Run Migration
```bash
php artisan migrate
```

### Migration File
`database/migrations/2026_05_25_093246_add_approval_fields_to_posts_table.php`

## Updated Files
- ✅ `database/migrations/2026_05_25_093246_add_approval_fields_to_posts_table.php`
- ✅ `app/Models/Post.php`
- ✅ `app/Http/Controllers/Client/ClientPostController.php`
- ✅ `app/Http/Controllers/Admin/AdminPostController.php`
- ✅ `app/Http/Controllers/Client/MarketerPostController.php`
- ✅ `routes/posts.php`
- ✅ `Bareqq_Complete_API.postman_collection.json`

## Testing Workflow

### 1. Create Post (Admin)
```bash
POST /admin/posts
```

### 2. Client Approves
```bash
POST /client/posts/1/approve
# Result: client_approved = true, is_approved = false
```

### 3. Check Status
```bash
GET /client/posts/1
# Shows: client_approved = true, admin_approved = false, marketer_approved = false
```

### 4. Admin Approves
```bash
POST /admin/posts/1/approve
# Result: admin_approved = true, is_approved = false
```

### 5. Marketer Approves (Final)
```bash
POST /marketer/posts/1/approve
# Result: marketer_approved = true, is_approved = true ✅
```

### 6. Verify Full Approval
```bash
GET /admin/posts/1
# Shows: is_approved = true, status = 'approved', approved_at = timestamp
```

## Notes
- كل طرف يمكنه الموافقة مرة واحدة فقط
- الموافقة لا يمكن إلغاؤها (no undo)
- البوست يصبح locked من التعديل عندما `is_approved = true`
- يتم حفظ ID الشخص الذي وافق من كل طرف
- يتم حفظ تاريخ ووقت كل موافقة

## Future Enhancements
- إضافة notifications عند كل موافقة
- إضافة approval comments (سبب الموافقة)
- إضافة rejection system (رفض البوست)
- إضافة approval history log
