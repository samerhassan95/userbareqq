# Posts Integration with Product Orders - تحديث ربط البوستات بالمنتجات

## التحديثات المنفذة

### 1. Database Changes

#### Migration: `2026_05_24_120000_add_product_order_to_posts_table.php`
تم إضافة الحقول التالية لجدول `posts`:
- `product_order_id` - ربط البوست بأوردر معين
- `strategy_work_id` - ربط البوست بعمل استراتيجي معين

### 2. Model Updates

#### Post Model
```php
// New relationships
public function productOrder()
public function strategyWork()
```

#### ProductOrder Model
```php
// New relationship
public function posts()
```

#### StrategyWork Model
```php
// New relationship
public function posts()
```

### 3. New Resource

#### PostResource
Resource جديد لعرض بيانات البوست بشكل منظم مع:
- معلومات البوست الأساسية
- معلومات الـ Client
- معلومات الـ Product Order
- معلومات الـ Strategy Work
- معلومات المنشئ (Admin/Marketer/Designer)
- الـ Feedbacks

### 4. New Endpoints

#### Client Endpoints

**GET** `/api/client/product-orders/{orderId}/posts`
- جلب كل البوستات الخاصة بأوردر معين
- Response: مصفوفة من PostResource

**GET** `/api/client/product-orders/{orderId}/works`
- جلب كل الـ Strategy Works الخاصة بأوردر معين
- Response: مصفوفة من Works مع البوستات والصور

#### Admin Endpoints

**GET** `/api/admin/product-orders/{orderId}/posts`
- جلب كل البوستات الخاصة بأوردر معين (Admin view)
- Response: مصفوفة من PostResource

**GET** `/api/admin/product-orders/{orderId}/works`
- جلب كل الـ Strategy Works الخاصة بأوردر معين
- Response: مصفوفة من Works مع البوستات والصور

**POST** `/api/admin/product-orders/{orderId}/works`
- إنشاء Strategy Work جديد

**PUT** `/api/admin/strategy-works/{id}`
- تحديث Strategy Work

**DELETE** `/api/admin/strategy-works/{id}`
- حذف Strategy Work

### 5. ProductOrderResource Updates

تم تحديث `ProductOrderResource` لإضافة:

#### في `works_preview`:
```json
{
  "works_preview": [
    {
      "id": 1,
      "title": "Work Title",
      "posts": [
        {
          "id": 1,
          "title": "Post Title",
          "image": "http://domain.com/posts/image.jpg",
          "status": "pending",
          "is_approved": false
        }
      ],
      "posts_count": 3
    }
  ]
}
```

#### حقول جديدة في الـ Response:
```json
{
  "posts": [
    {
      "id": 1,
      "title": "Post Title",
      "description": "Post Description",
      "image": "http://domain.com/posts/image.jpg",
      "status": "pending",
      "is_approved": false,
      "approved_at": null,
      "created_at": "2026-05-24 12:00:00"
    }
  ],
  "posts_count": 5
}
```

### 6. Controller Updates

#### AdminPostController
تم إضافة validation للحقول الجديدة:
- `product_order_id` في store و update
- `strategy_work_id` في store و update

## Usage Examples

### 1. إنشاء بوست مرتبط بأوردر

```bash
POST /api/admin/posts
Content-Type: multipart/form-data

{
  "title": "New Social Media Post",
  "title_ar": "منشور جديد على السوشيال ميديا",
  "description": "Post description",
  "description_ar": "وصف المنشور",
  "image": [file],
  "client_id": 1,
  "product_order_id": 5,
  "strategy_work_id": 10,
  "status": "pending"
}
```

### 2. جلب بوستات أوردر معين (Client)

```bash
GET /api/client/product-orders/5/posts
Authorization: Bearer {client_token}

Response:
{
  "success": true,
  "message": "Posts retrieved successfully",
  "data": [
    {
      "id": 1,
      "title": "Post Title",
      "description": "Post Description",
      "image": "http://domain.com/posts/image.jpg",
      "status": "pending",
      "is_approved": false,
      "product_order": {
        "id": 5,
        "order_number": "ORD-000005",
        "product_name": "Social Media Strategy"
      },
      "strategy_work": {
        "id": 10,
        "title": "Week 1 - Facebook Post",
        "scheduled_date": "2026-05-25",
        "platforms": ["facebook", "instagram"]
      },
      "created_by": {
        "id": 1,
        "name": "Admin Name",
        "type": "Admin"
      },
      "feedbacks": [],
      "feedbacks_count": 0
    }
  ]
}
```

### 3. جلب Strategy Works مع البوستات والصور

```bash
GET /api/client/product-orders/5/works
Authorization: Bearer {client_token}

Response:
{
  "success": true,
  "message": "Strategy works retrieved successfully",
  "data": [
    {
      "id": 10,
      "title": "Week 1 - Facebook Post",
      "description": "Engaging content for Sunday",
      "scheduled_date": "2026-05-25",
      "scheduled_time": "10:00:00",
      "platforms": ["facebook", "instagram"],
      "status": "pending",
      "status_label": "Pending",
      "post_type": "image",
      "attachments": [],
      "notes": "This work is created by seeder",
      "posts": [
        {
          "id": 1,
          "title": "Post Title",
          "description": "Post Description",
          "image": "http://domain.com/posts/image.jpg",
          "status": "pending",
          "is_approved": false,
          "approved_at": null
        }
      ],
      "posts_count": 1,
      "created_at": "2026-05-24 12:00:00"
    }
  ]
}
```

### 3. جلب Strategy Works مع البوستات والصور

```bash
GET /api/client/product-orders/5/works
Authorization: Bearer {client_token}

Response:
{
  "success": true,
  "message": "Strategy works retrieved successfully",
  "data": [
    {
      "id": 10,
      "title": "Week 1 - Facebook Post",
      "description": "Engaging content for Sunday",
      "scheduled_date": "2026-05-25",
      "scheduled_time": "10:00:00",
      "platforms": ["facebook", "instagram"],
      "status": "pending",
      "status_label": "Pending",
      "post_type": "image",
      "attachments": [],
      "notes": "This work is created by seeder",
      "posts": [
        {
          "id": 1,
          "title": "Post Title",
          "description": "Post Description",
          "image": "http://domain.com/posts/image.jpg",
          "status": "pending",
          "is_approved": false,
          "approved_at": null
        }
      ],
      "posts_count": 1,
      "created_at": "2026-05-24 12:00:00"
    }
  ]
}
```

### 4. جلب تفاصيل أوردر مع البوستات

```bash
GET /api/client/product-orders/5
Authorization: Bearer {client_token}

Response:
{
  "success": true,
  "data": {
    "id": 5,
    "order_number": "ORD-000005",
    "product": {...},
    "works_preview": [
      {
        "id": 10,
        "title": "Week 1 - Facebook Post",
        "posts": [
          {
            "id": 1,
            "title": "Post Title",
            "image": "http://domain.com/posts/image.jpg",
            "status": "pending",
            "is_approved": false
          }
        ],
        "posts_count": 1
      }
    ],
    "posts": [
      {
        "id": 1,
        "title": "Post Title",
        "image": "http://domain.com/posts/image.jpg",
        "status": "pending",
        "is_approved": false
      }
    ],
    "posts_count": 1
  }
}
```

## Database Schema

### posts table (updated)
```sql
- id
- title
- title_ar
- description
- description_ar
- image
- status
- is_approved
- approved_at
- client_id
- product_order_id (NEW)
- strategy_work_id (NEW)
- created_by_id
- created_by_type
- updated_by_id
- updated_by_type
- created_at
- updated_at
- deleted_at
```

## Benefits

1. **ربط واضح**: كل بوست مرتبط بأوردر و strategy work محدد
2. **تتبع أفضل**: يمكن تتبع كل البوستات الخاصة بأوردر معين
3. **عرض منظم**: البوستات تظهر في تفاصيل الأوردر مباشرة
4. **سهولة الإدارة**: الـ Admin والـ Client يقدروا يشوفوا البوستات المرتبطة بكل أوردر

## Notes

- البوستات القديمة (قبل التحديث) هيكون `product_order_id` و `strategy_work_id` بتاعهم `null`
- يمكن إنشاء بوست بدون ربطه بأوردر (الحقول optional)
- عند حذف أوردر، البوستات المرتبطة بيه هتبقى `product_order_id = null` (nullOnDelete)

## Testing

تأكد من:
1. ✅ Migration تم تشغيله بنجاح
2. ✅ إنشاء بوست جديد مع `product_order_id`
3. ✅ جلب بوستات أوردر معين
4. ✅ عرض البوستات في تفاصيل الأوردر
5. ✅ التأكد من الـ permissions (Client يشوف بوستاته فقط)
