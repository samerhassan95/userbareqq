# Postman Collection Updates - تحديثات مجموعة Postman

## ✅ التحديثات الجديدة

---

## 🆕 Endpoints الجديدة المضافة:

### 1. Universal Login - Admin (Email)
**Location:** Universal Login (All Roles) folder

**Request:**
```
POST {{base_url}}/login
```

**Body:**
```json
{
    "identifier": "admin@bareqq.com",
    "password": "password123"
}
```

**Description:** تسجيل دخول Admin بالإيميل بدلاً من username

---

### 2. Get Strategy Works (All)
**Location:** Client - Product Orders folder

**Request:**
```
GET {{base_url}}/client/product-orders/7/works
```

**Headers:**
- `Authorization: Bearer {{client_token}}`
- `Accept-Language: {{lang}}`
- `API-Password: {{api_password}}`

**Description:** الحصول على جميع الأعمال المجدولة لـ strategy order

**Response Example:**
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
            "notes": "Sample work",
            "created_at": "2026-05-21 10:30:00"
        }
    ]
}
```

---

### 3. Get Strategy Works (By Date)
**Location:** Client - Product Orders folder

**Request:**
```
GET {{base_url}}/client/product-orders/7/works?date=2026-05-22
```

**Query Parameters:**
- `date`: YYYY-MM-DD format

**Headers:**
- `Authorization: Bearer {{client_token}}`
- `Accept-Language: {{lang}}`
- `API-Password: {{api_password}}`

**Description:** الحصول على الأعمال المجدولة في تاريخ محدد

---

## 🔄 Endpoints المحدثة:

### 1. Get Order Details (Updated)
**Location:** Client - Product Orders folder

**Request:**
```
GET {{base_url}}/client/product-orders/1
```

**New Response Fields (for strategy orders):**
```json
{
    "status": true,
    "data": {
        "id": 7,
        "strategy_id": 12,
        "starts_at": "2026-05-21",
        "ends_at": "2027-05-20",
        "strategy_tips": [
            {
                "id": 19,
                "text": "Post consistently...",
                "platforms": ["facebook", "instagram"]
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
                }
            ]
        },
        "works_preview": [
            {
                "id": 45,
                "title": "Post #1",
                "scheduled_date": "2026-05-22",
                "platforms": ["facebook"]
            }
        ]
    }
}
```

---

## 📝 التحديثات العامة:

### 1. Language Variable
**Added:** `{{lang}}` variable
- Default: `en`
- Options: `en` or `ar`

**Usage:** Set in Collection Variables
```
lang = en  (for English)
lang = ar  (for Arabic)
```

### 2. Accept-Language Header
**Added to ALL requests:**
```
Accept-Language: {{lang}}
```

**Description:** يتحكم في لغة الاستجابة (عربي/إنجليزي)

---

## 🔧 التعديلات على Requests الموجودة:

### 1. Create Product
**Updated Body:**
```json
{
    "name": "New Product",
    "name_ar": "منتج جديد",
    "description": "Product description",
    "description_ar": "وصف المنتج",
    "note": "Important note",
    "note_ar": "ملاحظة مهمة",
    "price": 1000,
    "product_role": "one_time",
    "category_id": 1
}
```

### 2. Create Addon
**Updated Body:**
```json
{
    "name": "New Addon",
    "name_ar": "إضافة جديدة",
    "description": "Addon description",
    "description_ar": "وصف الإضافة",
    "price": 500
}
```

### 3. Create Strategy Tip
**Updated Body:**
```json
{
    "text": "Post consistently at peak times",
    "text_ar": "انشر بانتظام في أوقات الذروة",
    "platforms": ["facebook", "instagram"],
    "sort_order": 1
}
```

### 4. Create One-Time Order
**Removed Fields:**
- ❌ `feature_id` (removed)
- ❌ `feature_name` (removed)

**Current Body:**
```json
{
    "product_id": 1,
    "product_role": "one_time",
    "total_price": "1000"
}
```

---

## 📊 Collection Structure:

```
Bareqq Complete API Collection
├── Universal Login (All Roles)
│   ├── Universal Login - Admin
│   ├── Universal Login - Admin (Email) ✨ NEW
│   ├── Universal Login - Client (Email)
│   ├── Universal Login - Designer (Email)
│   ├── Universal Login - Marketer (Email)
│   └── Universal Logout
├── Authentication
├── Client - Profile
├── Client - Social Media Credentials
├── Client - Products
├── Client - Product Orders
│   ├── Create One-Time Order (updated)
│   ├── Create Strategy Order (Monthly)
│   ├── Create Strategy Order (Yearly)
│   ├── Get My Orders
│   ├── Get Order Details (updated)
│   ├── Get Strategy Works (All) ✨ NEW
│   └── Get Strategy Works (By Date) ✨ NEW
├── Client - Invoices
├── Admin - Products (updated with Arabic fields)
├── Admin - Addons (updated with Arabic fields)
├── Admin - Product Orders
├── Admin - Strategy Tips (updated with Arabic fields)
└── Admin - Invoices
```

---

## 🧪 Testing Guide:

### Test 1: Language Switching
1. Set `lang` variable to `en`
2. Call any GET endpoint
3. Verify response is in English
4. Set `lang` variable to `ar`
5. Call same endpoint
6. Verify response is in Arabic

### Test 2: Admin Email Login
1. Use "Universal Login - Admin (Email)" request
2. Set identifier to admin email
3. Verify login successful
4. Check response includes email field

### Test 3: Strategy Order Details
1. Create a strategy order
2. Get order details
3. Verify response includes:
   - `strategy_id`
   - `starts_at`, `ends_at`
   - `strategy_tips[]`
   - `team_discussion`
   - `works_preview[]`

### Test 4: Strategy Works
1. Use "Get Strategy Works (All)" request
2. Verify returns all works
3. Use "Get Strategy Works (By Date)" request
4. Verify returns only works for that date

---

## 📋 Variables Reference:

### Collection Variables:
```
base_url = https://user.bareqq.com/api
api_password = Nf:upZTg^7A?Hj
lang = en  (or ar)
client_token = (auto-set after login)
admin_token = (auto-set after login)
designer_token = (auto-set after login)
marketer_token = (auto-set after login)
```

---

## ✅ Checklist:

- [x] Added `lang` variable
- [x] Added `Accept-Language` header to all requests
- [x] Added Admin Email Login example
- [x] Updated Create Product with Arabic fields
- [x] Updated Create Addon with Arabic fields
- [x] Updated Create Strategy Tip with Arabic fields
- [x] Removed features from One-Time Orders
- [x] Added Get Strategy Works (All)
- [x] Added Get Strategy Works (By Date)
- [x] Updated Get Order Details response documentation

---

## 🎉 Done!

الـ Postman Collection الآن محدثة بالكامل مع جميع الـ endpoints والتعديلات الجديدة! 🚀

**Total New Requests:** 3
**Total Updated Requests:** 10+
**New Features:** Localization, Admin Email, Strategy Works
