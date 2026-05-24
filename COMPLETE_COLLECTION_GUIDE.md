# 📮 Postman Collection - دليل شامل

## ✅ حالة الكوليكشن: جاهزة 100%

---

## 📋 المحتويات

### 1. ✅ الترجمة (Localization)
- **كل الـ endpoints** تدعم الترجمة
- استخدم الـ header: `Accept-Language: en` أو `Accept-Language: ar`
- كل الرسائل مترجمة في:
  - `lang/en/messages.php`
  - `lang/ar/messages.php`

### 2. ✅ الـ Endpoints المتاحة

#### Admin Endpoints
- ✅ Products Management (CRUD)
- ✅ Strategy Tips Management
- ✅ Clients Management (List, View)
- ✅ Content Management (Products & Tips with EN/AR)
- ✅ Product Orders Management
- ✅ Posts Management (Create, Edit, Delete)

#### Client Endpoints
- ✅ Products (List, View)
- ✅ Product Orders (Create, View, Upload Payment)
- ✅ Strategy Works (View All, Filter by Date)
- ✅ Invoices (List, View)
- ✅ Posts (View, Feedback, Approve)

#### Marketer Endpoints
- ✅ Posts (Create, Edit)

#### Designer Endpoints
- ✅ Posts (Edit, Add Design)

### 3. ✅ المميزات الخاصة

#### Pagination
- كل الـ list endpoints تدعم الـ pagination
- استخدم `pagination=false` للحصول على كل الداتا
- استخدم `pagination=true&per_page=15` للـ pagination

#### Filters
- **Products**: category, product_role, search
- **Posts**: status, is_approved, client_id, search
- **Strategy Works**: date
- **Orders**: status, product_id

#### Search
- بحث في الـ title والـ description (EN & AR)
- متاح في: Products, Posts, Clients

---

## 🗂️ الداتا التجريبية (Seeders)

### المتاح حالياً:
1. ✅ **ServiceAppSeeder** - تطبيقات الخدمات
2. ✅ **BundlePackageSeeder** - باقات البندل
3. ✅ **CustomBundleSeeder** - البندل المخصص
4. ✅ **ProductDataSeeder** - المنتجات مع الـ Strategy Tips
5. ✅ **StrategyDataSeeder** - فريق الاستراتيجية والأعمال
6. ✅ **PostsDataSeeder** - المنشورات مع الـ Feedbacks

### تشغيل الـ Seeders:
```bash
# Run all seeders
php artisan db:seed

# Or run specific seeder
php artisan db:seed --class=StrategyDataSeeder
php artisan db:seed --class=PostsDataSeeder
```

---

## 📊 بنية الداتا

### Products
```json
{
  "name": "Product Name",
  "name_ar": "اسم المنتج",
  "description": "Description",
  "description_ar": "الوصف",
  "product_role": "one_time" or "strategy",
  "monthly_price": 100,
  "three_month_price": 270,
  "six_month_price": 510,
  "yearly_price": 960
}
```

### Strategy Works
```json
{
  "title": "Post Title",
  "title_ar": "عنوان المنشور",
  "description": "Description",
  "description_ar": "الوصف",
  "scheduled_date": "2026-05-24",
  "scheduled_time": "10:00:00",
  "platforms": ["facebook", "instagram"],
  "status": "pending",
  "post_type": "image"
}
```

### Posts
```json
{
  "title": "Post Title",
  "title_ar": "عنوان المنشور",
  "description": "Description",
  "description_ar": "الوصف",
  "client_id": 1,
  "status": "pending",
  "is_approved": false,
  "image": "image.jpg"
}
```

### Post Feedbacks
```json
{
  "post_id": 1,
  "client_id": 1,
  "comment": "Please change the color"
}
```

---

## 🔐 Authentication

### Admin Token
```
Bearer {{admin_token}}
```

### Client Token
```
Bearer {{client_token}}
```

### Marketer Token
```
Bearer {{marketer_token}}
```

### Designer Token
```
Bearer {{designer_token}}
```

---

## 🎯 الـ Endpoints الرئيسية

### 1. Admin - Clients & Content
```
GET /api/admin/clients
  - List all clients with pagination

GET /api/admin/clients/{id}
  - Get client details

GET /api/admin/content/products
  - Get products with EN & AR content

GET /api/admin/content/strategy-tips
  - Get strategy tips with EN & AR content
```

### 2. Client - Strategy Works
```
GET /api/client/product-orders/{orderId}/works
  - Get all strategy works for an order

GET /api/client/product-orders/{orderId}/works?date=2026-05-24
  - Filter works by specific date
```

### 3. Client - Posts
```
GET /api/client/posts
  - Get my posts

POST /api/client/posts/{id}/feedback
  - Add feedback to post
  Body: { "comment": "Your feedback" }

POST /api/client/posts/{id}/approve
  - Approve post (locks it)

GET /api/client/posts/{id}/feedbacks
  - Get all feedbacks for a post
```

### 4. Admin/Marketer - Posts
```
POST /api/admin/posts
  - Create post
  Body: {
    "title": "Title",
    "description": "Description",
    "client_id": 1,
    "image": file
  }

PUT /api/admin/posts/{id}
  - Update post (only if not approved)
```

---

## 🧪 Testing Workflow

### 1. Test Products
```
1. GET /api/client/products
2. GET /api/client/products/{id}
3. Test with Accept-Language: en
4. Test with Accept-Language: ar
```

### 2. Test Strategy Works
```
1. Create a strategy order
2. Run StrategyDataSeeder
3. GET /api/client/product-orders/{orderId}/works
4. GET /api/client/product-orders/{orderId}/works?date=2026-05-24
```

### 3. Test Posts Workflow
```
1. Admin creates post: POST /api/admin/posts
2. Client views post: GET /api/client/posts
3. Client adds feedback: POST /api/client/posts/{id}/feedback
4. Admin edits post: PUT /api/admin/posts/{id}
5. Client approves: POST /api/client/posts/{id}/approve
6. Try to edit (should fail): PUT /api/admin/posts/{id}
```

---

## 📝 الحقول المهمة

### ⚠️ تغييرات مهمة:
- ✅ استخدم `description` (ليس `content`)
- ✅ استخدم `product_role` (ليس `type`)
- ✅ Status values: `pending`, `approved`, `rejected` (ليس draft/published)
- ✅ Strategy products: 3 periods (3 months, 6 months, yearly)

### ✅ الحقول الإلزامية:
- **Create Post**: title, description, client_id
- **Create Order**: product_id, subscription_period (for strategy)
- **Add Feedback**: comment

---

## 🚀 Quick Start

### 1. Setup Database
```bash
php artisan migrate:fresh
php artisan db:seed
```

### 2. Import Postman Collection
```
File: Bareqq_Complete_API.postman_collection.json
```

### 3. Set Variables
```
base_url: http://your-domain.com/api
admin_token: your_admin_token
client_token: your_client_token
lang: en or ar
```

### 4. Test Endpoints
```
1. Login as Admin
2. Test Admin endpoints
3. Login as Client
4. Test Client endpoints
5. Test with both languages
```

---

## ✅ Checklist

### Database
- [x] Migrations created
- [x] Models created
- [x] Relationships defined
- [x] Seeders created

### API
- [x] Controllers implemented
- [x] Routes defined
- [x] Validation added
- [x] Translations added

### Postman
- [x] All endpoints added
- [x] Correct field names
- [x] Language header included
- [x] Pagination parameters
- [x] Filter parameters
- [x] Example requests

### Documentation
- [x] API documentation
- [x] Workflow guides
- [x] Testing guides
- [x] Deployment steps

---

## 📞 الدعم

إذا واجهت أي مشكلة:
1. تحقق من الـ error message
2. تأكد من الـ field names
3. تأكد من الـ authentication token
4. تحقق من الـ Accept-Language header
5. راجع الـ documentation

---

**Status**: ✅ جاهز للاستخدام
**Date**: May 24, 2026
**Version**: 1.0.0

---

## 🎉 الخلاصة

الكوليكشن جاهزة بالكامل مع:
- ✅ كل الـ endpoints
- ✅ الترجمة الكاملة (EN/AR)
- ✅ الداتا التجريبية
- ✅ الـ Filters والـ Pagination
- ✅ الـ Documentation الشاملة

**جاهز للتجربة! 🚀**
