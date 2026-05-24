# 📅 مدد الاشتراك للمنتجات الاستراتيجية

## 🎯 نظام الاشتراكات

المنتجات الاستراتيجية (Strategy Products) تقدم **3 مدد اشتراك**:

---

## 1️⃣ اشتراك 3 أشهر

### الوصف:
اشتراك لمدة 3 أشهر (ربع سنة)

### الحقل:
```php
'three_month_price' => 1200.00
```

### المميزات:
- ✅ مناسب للعملاء الذين يريدون تجربة الخدمة
- ✅ التزام قصير المدى
- ✅ مرونة في التجديد أو الإلغاء

### مثال:
```json
{
    "product_id": 2,
    "name": "Social Media Management",
    "three_month_price": 1200.00,
    "duration": "3 months"
}
```

---

## 2️⃣ اشتراك 6 أشهر

### الوصف:
اشتراك لمدة 6 أشهر (نصف سنة)

### الحقل:
```php
'six_month_price' => 2200.00
```

### المميزات:
- ✅ خصم أفضل من 3 أشهر
- ✅ التزام متوسط المدى
- ✅ توازن بين السعر والمرونة

### مثال:
```json
{
    "product_id": 2,
    "name": "Social Media Management",
    "six_month_price": 2200.00,
    "duration": "6 months"
}
```

---

## 3️⃣ اشتراك سنوي

### الوصف:
اشتراك لمدة سنة كاملة (12 شهر)

### الحقل:
```php
'yearly_price' => 4000.00
```

### المميزات:
- ✅ أفضل سعر (خصم كبير)
- ✅ التزام طويل المدى
- ✅ استقرار في الخدمة

### مثال:
```json
{
    "product_id": 2,
    "name": "Social Media Management",
    "yearly_price": 4000.00,
    "duration": "1 year"
}
```

---

## 💰 مقارنة الأسعار

### مثال: إدارة وسائل التواصل الاجتماعي

| المدة | السعر | السعر الشهري | الخصم |
|-------|-------|--------------|-------|
| **3 أشهر** | 1,200 ج.م | 400 ج.م/شهر | - |
| **6 أشهر** | 2,200 ج.م | 367 ج.م/شهر | 8% |
| **سنة** | 4,000 ج.م | 333 ج.م/شهر | 17% |

### الحساب:
```
3 أشهر:  1200 ÷ 3  = 400 ج.م/شهر
6 أشهر:  2200 ÷ 6  = 367 ج.م/شهر (خصم 33 ج.م/شهر)
سنة:     4000 ÷ 12 = 333 ج.م/شهر (خصم 67 ج.م/شهر)
```

---

## 📊 بنية الجدول

### Products Table:
```sql
CREATE TABLE products (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    name_ar VARCHAR(255),
    description TEXT,
    description_ar TEXT,
    type VARCHAR(50) DEFAULT 'general',
    product_role ENUM('one_time', 'strategy') DEFAULT 'one_time',
    price DECIMAL(12,2),                    -- للـ one_time فقط
    monthly_price DECIMAL(12,2) NULL,       -- غير مستخدم (null)
    three_month_price DECIMAL(12,2) NULL,   -- للـ strategy
    six_month_price DECIMAL(12,2) NULL,     -- للـ strategy
    yearly_price DECIMAL(12,2) NULL,        -- للـ strategy
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 🔍 Response Examples

### Strategy Product - Full Response:
```json
{
    "id": 2,
    "name": "Social Media Management",
    "name_ar": "إدارة وسائل التواصل الاجتماعي",
    "description": "Complete social media management service",
    "description_ar": "خدمة إدارة كاملة لوسائل التواصل الاجتماعي",
    "type": "general",
    "product_role": "strategy",
    "price": null,
    "monthly_price": null,
    "three_month_price": 1200.00,
    "six_month_price": 2200.00,
    "yearly_price": 4000.00,
    "created_at": "2026-05-20T10:00:00.000000Z",
    "updated_at": "2026-05-20T10:00:00.000000Z"
}
```

### One-Time Product - للمقارنة:
```json
{
    "id": 1,
    "name": "Website Design",
    "name_ar": "تصميم موقع إلكتروني",
    "product_role": "one_time",
    "price": 5000.00,
    "monthly_price": null,
    "three_month_price": null,
    "six_month_price": null,
    "yearly_price": null
}
```

---

## 📝 ملاحظات مهمة

### للمنتجات الاستراتيجية:
- ✅ يجب أن تكون جميع الأسعار الثلاثة موجودة
- ✅ `monthly_price` يجب أن يكون `null`
- ✅ `price` يجب أن يكون `null`
- ✅ يجب إضافة Strategy Tips

### للمنتجات لمرة واحدة:
- ✅ `price` يجب أن يكون موجود
- ✅ جميع أسعار الاشتراك يجب أن تكون `null`

---

## 🎯 حالات الاستخدام

### السيناريو 1: عميل جديد
```
العميل → يريد تجربة الخدمة
الاختيار → 3 أشهر
السعر → 1,200 ج.م
المدة → 3 أشهر
```

### السيناريو 2: عميل راضي
```
العميل → راضي عن الخدمة
الاختيار → 6 أشهر
السعر → 2,200 ج.م (خصم 8%)
المدة → 6 أشهر
```

### السيناريو 3: عميل دائم
```
العميل → يريد التزام طويل
الاختيار → سنة
السعر → 4,000 ج.م (خصم 17%)
المدة → 12 شهر
```

---

## 🚀 في الـ API

### Get Strategy Products:
```bash
GET /api/admin/content/products?product_role=strategy
```

### Response:
```json
{
    "success": true,
    "data": [
        {
            "id": 2,
            "name": "Social Media Management",
            "product_role": "strategy",
            "three_month_price": 1200.00,
            "six_month_price": 2200.00,
            "yearly_price": 4000.00
        }
    ]
}
```

---

## ✅ Migration

تم إضافة الأعمدة الجديدة في:
```
database/migrations/2026_05_24_100000_add_subscription_periods_to_products_table.php
```

### لتطبيق الـ Migration:
```bash
php artisan migrate
```

---

**الآن النظام يدعم 3 مدد اشتراك! 🎉**
