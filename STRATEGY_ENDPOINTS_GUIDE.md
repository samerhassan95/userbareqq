# Strategy Endpoints Guide - دليل نقاط النهاية الاستراتيجية

## ✅ تم إضافة جميع الـ Endpoints المطلوبة

---

## 📋 الـ Endpoints الجديدة

### 1. Get Strategy Order Details (محدث)
**Endpoint:** `GET /api/client/product-orders/{orderId}`

**Headers:**
```
Authorization: Bearer {client_token}
Accept-Language: en  (or ar)
API-Password: Nf:upZTg^7A?Hj
```

**Response (Strategy Order):**
```json
{
    "status": true,
    "message": "Orders retrieved successfully",
    "data": {
        "id": 7,
        "order_number": "ORD-000007",
        "client": {
            "id": 2,
            "name": "Client Name",
            "email": "client@example.com"
        },
        "product": {
            "id": 2,
            "name": "Social Media Strategy",
            "image": "https://...",
            "role": "strategy",
            "role_label": "Strategy Subscription"
        },
        "strategy_id": 12,
        "duration": "year",
        "duration_label": "Year",
        "total_price": 20000,
        "currency": "EGP",
        "status": "paid",
        "status_label": "Paid - Awaiting Delivery",
        "starts_at": "2026-05-21",
        "ends_at": "2027-05-20",
        "strategy_tips": [
            {
                "id": 19,
                "text": "Post consistently at peak engagement times",
                "platforms": ["facebook", "instagram", "twitter"]
            },
            {
                "id": 20,
                "text": "Use video content for higher engagement",
                "platforms": ["tiktok", "instagram"]
            }
        ],
        "team_discussion": {
            "discussion_id": 7,
            "team_count": 4,
            "title": "Strategy Team Chat",
            "team": [
                {
                    "id": 1,
                    "name": "Ahmed Designer",
                    "avatar": "https://...",
                    "type": "Designer",
                    "role": "designer"
                },
                {
                    "id": 2,
                    "name": "Sara Marketer",
                    "avatar": "https://...",
                    "type": "Marketer",
                    "role": "marketer"
                }
            ]
        },
        "works_preview": [
            {
                "id": 45,
                "title": "Post #1 - May 22",
                "description": "Engaging content for Thursday",
                "scheduled_date": "2026-05-22",
                "scheduled_time": "10:00:00",
                "platforms": ["facebook", "instagram"],
                "status": "pending",
                "post_type": "image"
            }
        ],
        "payment": {
            "invoice_id": 9,
            "payment_status": "paid",
            "payment_proof": "https://..."
        },
        "subscription": {
            "id": 12,
            "status": "active",
            "starts_at": "2026-05-21",
            "expires_at": "2027-05-20"
        },
        "created_at": "2026-05-21 10:30:00",
        "updated_at": "2026-05-21 10:30:00"
    }
}
```

---

### 2. Get Strategy Works (جديد)
**Endpoint:** `GET /api/client/product-orders/{orderId}/works`

**Query Parameters:**
- `date` (optional): Filter by date in `YYYY-MM-DD` format

**Examples:**
```
GET /api/client/product-orders/7/works
GET /api/client/product-orders/7/works?date=2026-05-22
```

**Headers:**
```
Authorization: Bearer {client_token}
Accept-Language: en  (or ar)
API-Password: Nf:upZTg^7A?Hj
```

**Response:**
```json
{
    "status": true,
    "message": "Strategy works retrieved successfully",
    "data": [
        {
            "id": 45,
            "title": "Post #1 - May 22",
            "description": "Engaging content for Thursday",
            "scheduled_date": "2026-05-22",
            "scheduled_time": "10:00:00",
            "platforms": ["facebook", "instagram"],
            "status": "pending",
            "status_label": "Pending",
            "post_type": "image",
            "attachments": [],
            "notes": "Sample work created by seeder",
            "created_at": "2026-05-21 10:30:00"
        },
        {
            "id": 46,
            "title": "Post #2 - May 23",
            "description": "Video content for Friday",
            "scheduled_date": "2026-05-23",
            "scheduled_time": "14:00:00",
            "platforms": ["tiktok", "instagram"],
            "status": "in_progress",
            "status_label": "In progress",
            "post_type": "video",
            "attachments": ["https://..."],
            "notes": null,
            "created_at": "2026-05-21 10:30:00"
        }
    ]
}
```

---

## 🗄️ Database Tables الجديدة

### 1. `strategy_team_members`
```sql
- id
- product_order_id (FK to product_orders)
- member_id (polymorphic)
- member_type (Designer, Marketer, Employee, Admin)
- role (designer, marketer, manager, etc.)
- created_at
- updated_at
```

### 2. `strategy_works`
```sql
- id
- product_order_id (FK to product_orders)
- title
- title_ar
- description
- description_ar
- scheduled_date
- scheduled_time
- platforms (JSON: ['facebook', 'instagram'])
- status (pending, in_progress, completed, cancelled)
- post_type (image, video, text, carousel)
- attachments (JSON: URLs)
- notes
- created_at
- updated_at
```

---

## 🔧 Models الجديدة

### 1. `StrategyTeamMember`
- Relationships:
  - `productOrder()` - belongsTo ProductOrder
  - `member()` - morphTo (Designer, Marketer, etc.)

### 2. `StrategyWork`
- Relationships:
  - `productOrder()` - belongsTo ProductOrder

---

## 📝 Postman Collection Updates

### Add to "Client - Product Orders" folder:

#### Request 1: Get Strategy Order Details
```
GET {{base_url}}/client/product-orders/7
Headers:
  Authorization: Bearer {{client_token}}
  Accept-Language: {{lang}}
  API-Password: {{api_password}}
```

#### Request 2: Get All Strategy Works
```
GET {{base_url}}/client/product-orders/7/works
Headers:
  Authorization: Bearer {{client_token}}
  Accept-Language: {{lang}}
  API-Password: {{api_password}}
```

#### Request 3: Get Strategy Works by Date
```
GET {{base_url}}/client/product-orders/7/works?date=2026-05-22
Headers:
  Authorization: Bearer {{client_token}}
  Accept-Language: {{lang}}
  API-Password: {{api_password}}
```

---

## 🚀 Deployment Steps

### 1. Run Migrations
```bash
php artisan migrate --force
```

### 2. Run Seeder (Optional - for test data)
```bash
php artisan db:seed --class=StrategyDataSeeder
```

### 3. Clear Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan optimize:clear
```

---

## 🧪 Testing

### Test 1: Get Strategy Order Details
```bash
curl -X GET "https://user.bareqq.com/api/client/product-orders/7" \
  -H "Authorization: Bearer CLIENT_TOKEN" \
  -H "Accept-Language: en" \
  -H "API-Password: Nf:upZTg^7A?Hj"
```

**Expected:** Response includes `strategy_id`, `starts_at`, `ends_at`, `strategy_tips`, `team_discussion`, `works_preview`

### Test 2: Get All Works
```bash
curl -X GET "https://user.bareqq.com/api/client/product-orders/7/works" \
  -H "Authorization: Bearer CLIENT_TOKEN" \
  -H "Accept-Language: en" \
  -H "API-Password: Nf:upZTg^7A?Hj"
```

### Test 3: Get Works by Date
```bash
curl -X GET "https://user.bareqq.com/api/client/product-orders/7/works?date=2026-05-22" \
  -H "Authorization: Bearer CLIENT_TOKEN" \
  -H "Accept-Language: en" \
  -H "API-Password: Nf:upZTg^7A?Hj"
```

---

## 📋 Admin Endpoints (للمستقبل)

يمكن إضافة هذه الـ endpoints لاحقاً:

### 1. Manage Team Members
- `POST /api/admin/product-orders/{orderId}/team-members` - Add team member
- `DELETE /api/admin/product-orders/{orderId}/team-members/{memberId}` - Remove team member

### 2. Manage Works
- `POST /api/admin/product-orders/{orderId}/works` - Create work
- `PUT /api/admin/works/{workId}` - Update work
- `DELETE /api/admin/works/{workId}` - Delete work

---

## ✅ Checklist

- [x] Create `strategy_team_members` table
- [x] Create `strategy_works` table
- [x] Create `StrategyTeamMember` model
- [x] Create `StrategyWork` model
- [x] Update `ProductOrder` model with relationships
- [x] Update `ProductOrderResource` with strategy fields
- [x] Update `ProductOrderRepository` to load relationships
- [x] Create `StrategyWorkController`
- [x] Add routes for strategy works
- [x] Create `StrategyDataSeeder` for test data
- [x] Update documentation

---

## 🎉 Done!

جميع الـ endpoints المطلوبة للـ Strategy Details Screen جاهزة الآن! 🚀
