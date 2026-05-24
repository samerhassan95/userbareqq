# Admin Clients & Content Management Endpoints

## Overview
تم إضافة endpoints جديدة للـ Admin لإدارة العملاء وعرض المحتوى (Products & Strategy Tips) بالإنجليزي والعربي للتعديل عليها.

---

## 1. Clients Management

### 1.1 Get All Clients
**Endpoint:** `GET /api/admin/clients`

**Headers:**
```
Authorization: Bearer {admin_token}
Accept-Language: en|ar
```

**Query Parameters:**
- `search` (optional): البحث بالاسم أو البريد الإلكتروني أو رقم الهاتف
- `role` (optional): تصفية حسب الدور (client, designer, marketer)
- `per_page` (optional): عدد العناصر في الصفحة (default: 15)

**Response Example:**
```json
{
    "success": true,
    "message": "Clients retrieved successfully",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "name": "Ahmed Mohamed",
                "email": "ahmed@example.com",
                "phone": "+201234567890",
                "role": "client",
                "created_at": "2026-05-20T10:00:00.000000Z",
                "subscriptions": [],
                "invoices": []
            }
        ],
        "total": 50,
        "per_page": 15
    }
}
```

---

### 1.2 Get Client Details
**Endpoint:** `GET /api/admin/clients/{id}`

**Headers:**
```
Authorization: Bearer {admin_token}
Accept-Language: en|ar
```

**Response Example:**
```json
{
    "success": true,
    "message": "Client retrieved successfully",
    "data": {
        "id": 1,
        "name": "Ahmed Mohamed",
        "email": "ahmed@example.com",
        "phone": "+201234567890",
        "role": "client",
        "created_at": "2026-05-20T10:00:00.000000Z",
        "subscriptions": [
            {
                "id": 1,
                "product_id": 2,
                "status": "active",
                "start_date": "2026-05-01",
                "end_date": "2026-06-01"
            }
        ],
        "invoices": [],
        "social_credentials": []
    }
}
```

---

## 2. Content Management - Products

### 2.1 Get All Products Content
**Endpoint:** `GET /api/admin/content/products`

**Headers:**
```
Authorization: Bearer {admin_token}
Accept-Language: en|ar
```

**Query Parameters:**
- `type` (optional): تصفية حسب النوع (service, subscription)

**Response Example:**
```json
{
    "success": true,
    "message": "Products content retrieved successfully",
    "data": [
        {
            "id": 1,
            "name_en": "Website Design",
            "name_ar": "تصميم موقع إلكتروني",
            "description_en": "Professional website design service...",
            "description_ar": "خدمة تصميم مواقع إلكترونية احترافية...",
            "type": "service",
            "role": null,
            "price_monthly": null,
            "price_yearly": null,
            "created_at": "2026-05-20T10:00:00.000000Z",
            "updated_at": "2026-05-20T10:00:00.000000Z"
        },
        {
            "id": 2,
            "name_en": "Social Media Management",
            "name_ar": "إدارة وسائل التواصل الاجتماعي",
            "description_en": "Complete social media management...",
            "description_ar": "إدارة كاملة لوسائل التواصل الاجتماعي...",
            "type": "subscription",
            "role": "marketer",
            "price_monthly": 500,
            "price_yearly": 5000,
            "created_at": "2026-05-20T10:00:00.000000Z",
            "updated_at": "2026-05-20T10:00:00.000000Z"
        }
    ]
}
```

---

### 2.2 Get Single Product Content
**Endpoint:** `GET /api/admin/content/products/{id}`

**Headers:**
```
Authorization: Bearer {admin_token}
Accept-Language: en|ar
```

**Response Example:**
```json
{
    "success": true,
    "message": "Product content retrieved successfully",
    "data": {
        "id": 1,
        "name_en": "Website Design",
        "name_ar": "تصميم موقع إلكتروني",
        "description_en": "Professional website design service with modern UI/UX...",
        "description_ar": "خدمة تصميم مواقع إلكترونية احترافية مع واجهة مستخدم حديثة...",
        "type": "service",
        "role": null,
        "price_monthly": null,
        "price_yearly": null,
        "created_at": "2026-05-20T10:00:00.000000Z",
        "updated_at": "2026-05-20T10:00:00.000000Z"
    }
}
```

---

## 3. Content Management - Strategy Tips

### 3.1 Get All Strategy Tips Content
**Endpoint:** `GET /api/admin/content/strategy-tips`

**Headers:**
```
Authorization: Bearer {admin_token}
Accept-Language: en|ar
```

**Query Parameters:**
- `product_id` (optional): تصفية حسب معرف المنتج

**Response Example:**
```json
{
    "success": true,
    "message": "Strategy tips content retrieved successfully",
    "data": [
        {
            "id": 1,
            "product_id": 2,
            "title_en": "Define Your Target Audience",
            "title_ar": "حدد جمهورك المستهدف",
            "description_en": "Understanding your target audience is crucial...",
            "description_ar": "فهم جمهورك المستهدف أمر بالغ الأهمية...",
            "order": 1,
            "created_at": "2026-05-20T10:00:00.000000Z",
            "updated_at": "2026-05-20T10:00:00.000000Z",
            "product": {
                "id": 2,
                "name_en": "Social Media Management",
                "name_ar": "إدارة وسائل التواصل الاجتماعي"
            }
        }
    ]
}
```

---

### 3.2 Get Single Strategy Tip Content
**Endpoint:** `GET /api/admin/content/strategy-tips/{id}`

**Headers:**
```
Authorization: Bearer {admin_token}
Accept-Language: en|ar
```

**Response Example:**
```json
{
    "success": true,
    "message": "Strategy tip content retrieved successfully",
    "data": {
        "id": 1,
        "product_id": 2,
        "title_en": "Define Your Target Audience",
        "title_ar": "حدد جمهورك المستهدف",
        "description_en": "Understanding your target audience is crucial for effective social media marketing...",
        "description_ar": "فهم جمهورك المستهدف أمر بالغ الأهمية للتسويق الفعال عبر وسائل التواصل الاجتماعي...",
        "order": 1,
        "created_at": "2026-05-20T10:00:00.000000Z",
        "updated_at": "2026-05-20T10:00:00.000000Z",
        "product": {
            "id": 2,
            "name_en": "Social Media Management",
            "name_ar": "إدارة وسائل التواصل الاجتماعي"
        }
    }
}
```

---

## Usage Notes

### للـ Clients Management:
- يمكن للـ Admin البحث عن العملاء بالاسم أو البريد الإلكتروني أو رقم الهاتف
- يمكن تصفية العملاء حسب الدور (client, designer, marketer)
- يتم عرض الـ subscriptions والـ invoices مع كل عميل

### للـ Content Management:
- هذه الـ endpoints مخصصة لعرض المحتوى بالإنجليزي والعربي معاً
- يمكن استخدامها لعرض البيانات قبل التعديل عليها
- للتعديل الفعلي، استخدم الـ endpoints الموجودة مسبقاً:
  - `PUT /api/admin/products/{id}` - لتعديل المنتج
  - `PUT /api/admin/strategy-tips/{id}` - لتعديل النصيحة

---

## Testing in Postman

تم إضافة جميع الـ endpoints في الـ Postman Collection تحت:
- **Admin - Clients**: للـ endpoints الخاصة بالعملاء
- **Admin - Content Management**: للـ endpoints الخاصة بعرض المحتوى

تأكد من:
1. تسجيل الدخول كـ Admin أولاً
2. استخدام الـ `admin_token` في الـ Authorization header
3. تحديد اللغة في الـ `Accept-Language` header (en أو ar)
