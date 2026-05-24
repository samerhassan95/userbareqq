# Troubleshooting Guide - Admin Endpoints

## 🔧 حل مشكلة 500 Internal Server Error

إذا واجهت خطأ 500 عند استدعاء الـ endpoints الجديدة، اتبع الخطوات التالية:

---

## ✅ الخطوات المنفذة لحل المشكلة

### 1. تحديث Controllers
تم إضافة:
- ✅ استخدام `filled()` بدلاً من `has()` للتحقق من القيم الفارغة
- ✅ إضافة try-catch blocks لجميع الـ methods
- ✅ إرجاع رسائل خطأ واضحة مع تفاصيل الخطأ

### 2. التحديثات في AdminClientController
```php
// قبل التحديث
if ($request->has('search')) { ... }

// بعد التحديث
if ($request->filled('search')) { ... }

// إضافة try-catch
try {
    // الكود
} catch (\Exception $e) {
    return response()->json([
        'success' => false,
        'message' => __('messages.error_occurred'),
        'error' => $e->getMessage()
    ], 500);
}
```

### 3. التحديثات في AdminContentController
نفس التحديثات تم تطبيقها على جميع الـ methods.

---

## 🔍 خطوات التشخيص

### 1. التحقق من الـ Routes
```bash
php artisan route:list | grep admin/clients
php artisan route:list | grep admin/content
```

يجب أن ترى:
```
GET|HEAD  api/admin/clients ..................... admin › AdminClientController@index
GET|HEAD  api/admin/clients/{id} ................ admin › AdminClientController@show
GET|HEAD  api/admin/content/products ............ admin › AdminContentController@getProductsContent
GET|HEAD  api/admin/content/products/{id} ....... admin › AdminContentController@getProductContent
GET|HEAD  api/admin/content/strategy-tips ....... admin › AdminContentController@getStrategyTipsContent
GET|HEAD  api/admin/content/strategy-tips/{id} .. admin › AdminContentController@getStrategyTipContent
```

### 2. التحقق من الـ Logs
```bash
# على السيرفر
tail -f storage/logs/laravel.log

# أو
cat storage/logs/laravel-$(date +%Y-%m-%d).log
```

### 3. التحقق من الـ Database
```bash
php artisan tinker
```

ثم:
```php
// التحقق من وجود clients
\App\Models\Client::count();

// التحقق من الـ relationships
$client = \App\Models\Client::first();
$client->subscriptions;
$client->invoices;
```

---

## 🛠️ الحلول الشائعة

### المشكلة 1: Route Not Found
**الحل:**
```bash
php artisan route:clear
php artisan route:cache
```

### المشكلة 2: Class Not Found
**الحل:**
```bash
composer dump-autoload
php artisan clear-compiled
php artisan optimize
```

### المشكلة 3: Database Connection Error
**الحل:**
```bash
# التحقق من الـ .env
cat .env | grep DB_

# اختبار الاتصال
php artisan tinker
DB::connection()->getPdo();
```

### المشكلة 4: Middleware Error
**الحل:**
تأكد من أن الـ admin middleware مسجل في `app/Http/Kernel.php`:
```php
protected $middlewareAliases = [
    'admin' => \App\Http\Middleware\Admin::class,
];
```

### المشكلة 5: Translation Missing
**الحل:**
```bash
php artisan config:clear
php artisan cache:clear
```

---

## 🧪 اختبار الـ Endpoints

### 1. اختبار بدون Authentication (يجب أن يفشل)
```bash
curl -X GET "https://user.bareqq.com/api/admin/clients" \
  -H "Accept: application/json"
```

**النتيجة المتوقعة:** 401 Unauthorized

### 2. اختبار مع Admin Token
```bash
# أولاً، احصل على admin token
curl -X POST "https://user.bareqq.com/api/admin/login" \
  -H "Content-Type: application/json" \
  -H "api-password: Nf:upZTg^7A?Hj" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'

# ثم استخدم الـ token
curl -X GET "https://user.bareqq.com/api/admin/clients" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept-Language: en"
```

**النتيجة المتوقعة:** 200 OK مع بيانات العملاء

### 3. اختبار مع Query Parameters
```bash
# البحث عن عميل
curl -X GET "https://user.bareqq.com/api/admin/clients?search=ahmed" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept-Language: ar"

# التصفية حسب الدور
curl -X GET "https://user.bareqq.com/api/admin/clients?role=designer" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept-Language: en"
```

---

## 📋 Checklist للنشر

قبل النشر، تأكد من:

- [ ] تم رفع جميع الملفات للـ repository
- [ ] تم تشغيل `composer install` على السيرفر
- [ ] تم تنفيذ `php artisan route:clear`
- [ ] تم تنفيذ `php artisan config:clear`
- [ ] تم تنفيذ `php artisan cache:clear`
- [ ] تم تنفيذ `php artisan route:cache`
- [ ] تم تنفيذ `php artisan config:cache`
- [ ] الـ permissions صحيحة (755 للـ storage)
- [ ] الـ .env file موجود وصحيح
- [ ] الـ database connection تعمل

---

## 🚀 أوامر النشر السريع

```bash
cd /var/www/html/bareqq
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:cache
php artisan config:cache
php artisan view:cache
chown -R www-data:www-data /var/www/html/bareqq
chmod -R 755 /var/www/html/bareqq/storage
chmod -R 755 /var/www/html/bareqq/bootstrap/cache
```

---

## 📞 الدعم الفني

إذا استمرت المشكلة:

1. **تحقق من الـ logs:**
   ```bash
   tail -100 storage/logs/laravel.log
   ```

2. **فعّل الـ debug mode مؤقتاً:**
   في `.env`:
   ```
   APP_DEBUG=true
   ```
   ⚠️ **تحذير:** لا تترك debug mode مفعل في production!

3. **اختبر الـ endpoint في Postman:**
   - تأكد من الـ Authorization header
   - تأكد من الـ Accept-Language header
   - تأكد من الـ api-password header

4. **راجع التوثيق:**
   - `ADMIN_CLIENTS_CONTENT_ENDPOINTS.md`
   - `ADMIN_CLIENTS_CONTENT_SUMMARY.md`

---

## ✅ التحديثات الأخيرة

تم تحديث الـ Controllers لتشمل:
- ✅ استخدام `filled()` للتحقق من القيم
- ✅ إضافة try-catch blocks
- ✅ رسائل خطأ واضحة
- ✅ إرجاع تفاصيل الخطأ في حالة الفشل

الآن الـ endpoints يجب أن تعمل بشكل صحيح! 🎉
