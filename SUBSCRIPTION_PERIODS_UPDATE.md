# ✅ تحديث مدد الاشتراك - Subscription Periods Update

## 🎯 التحديث

تم إضافة دعم **3 مدد اشتراك** للمنتجات الاستراتيجية بدلاً من شهري/سنوي فقط.

---

## 📋 المدد الجديدة

### قبل التحديث:
- ❌ شهري (`monthly_price`)
- ✅ سنوي (`yearly_price`)

### بعد التحديث:
- ✅ 3 أشهر (`three_month_price`)
- ✅ 6 أشهر (`six_month_price`)
- ✅ سنة (`yearly_price`)

---

## 🔧 التغييرات

### 1. Migration جديد
**الملف:** `2026_05_24_100000_add_subscription_periods_to_products_table.php`

```php
Schema::table('products', function (Blueprint $table) {
    $table->decimal('three_month_price', 12, 2)->nullable();
    $table->decimal('six_month_price', 12, 2)->nullable();
});
```

### 2. Controller محدث
**الملف:** `AdminContentController.php`

```php
// تم إضافة الحقول الجديدة
'three_month_price',
'six_month_price',
```

### 3. التوثيق محدث
- ✅ `ADMIN_CLIENTS_CONTENT_ENDPOINTS.md`
- ✅ `PRODUCT_TYPES_EXPLAINED.md`
- ✅ `STRATEGY_SUBSCRIPTION_PERIODS.md` (جديد)

---

## 📊 بنية الأسعار الجديدة

### Strategy Product:
```json
{
    "product_role": "strategy",
    "monthly_price": null,
    "three_month_price": 1200.00,
    "six_month_price": 2200.00,
    "yearly_price": 4000.00
}
```

### One-Time Product (بدون تغيير):
```json
{
    "product_role": "one_time",
    "price": 5000.00,
    "monthly_price": null,
    "three_month_price": null,
    "six_month_price": null,
    "yearly_price": null
}
```

---

## 🚀 خطوات التطبيق

### 1. على الجهاز المحلي:
```bash
# تطبيق الـ migration
php artisan migrate

# التحقق من الأعمدة الجديدة
php artisan tinker
>>> Schema::hasColumn('products', 'three_month_price')
>>> Schema::hasColumn('products', 'six_month_price')
```

### 2. على السيرفر:
```bash
cd /var/www/html/bareqq
git pull origin main
php artisan migrate
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## 📝 تحديث البيانات الموجودة

إذا كان لديك منتجات استراتيجية موجودة، قم بتحديث أسعارها:

```sql
-- مثال: تحديث منتج Social Media Management
UPDATE products 
SET 
    three_month_price = 1200.00,
    six_month_price = 2200.00,
    yearly_price = 4000.00,
    monthly_price = NULL
WHERE product_role = 'strategy' AND id = 2;
```

أو باستخدام Tinker:
```php
php artisan tinker

$product = Product::find(2);
$product->three_month_price = 1200.00;
$product->six_month_price = 2200.00;
$product->yearly_price = 4000.00;
$product->monthly_price = null;
$product->save();
```

---

## 🧪 الاختبار

### في Postman:
```
GET /api/admin/content/products?product_role=strategy
```

### النتيجة المتوقعة:
```json
{
    "success": true,
    "data": [
        {
            "id": 2,
            "name": "Social Media Management",
            "product_role": "strategy",
            "monthly_price": null,
            "three_month_price": 1200.00,
            "six_month_price": 2200.00,
            "yearly_price": 4000.00
        }
    ]
}
```

---

## 💰 مثال على الأسعار

### إدارة وسائل التواصل الاجتماعي:

| المدة | السعر | السعر الشهري | الخصم |
|-------|-------|--------------|-------|
| 3 أشهر | 1,200 ج.م | 400 ج.م | - |
| 6 أشهر | 2,200 ج.م | 367 ج.م | 8% |
| سنة | 4,000 ج.م | 333 ج.م | 17% |

---

## ✅ Checklist

- [x] إنشاء migration جديد
- [x] تحديث AdminContentController
- [x] تحديث التوثيق
- [x] إنشاء STRATEGY_SUBSCRIPTION_PERIODS.md
- [x] تحديث PRODUCT_TYPES_EXPLAINED.md
- [x] تحديث ADMIN_CLIENTS_CONTENT_ENDPOINTS.md

---

## 📚 المراجع

- **التوثيق الكامل:** `STRATEGY_SUBSCRIPTION_PERIODS.md`
- **شرح الأنواع:** `PRODUCT_TYPES_EXPLAINED.md`
- **الـ Endpoints:** `ADMIN_CLIENTS_CONTENT_ENDPOINTS.md`

---

**تم التحديث بنجاح! 🎉**
