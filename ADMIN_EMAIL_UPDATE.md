# Admin Email Field Update - تحديث حقل الإيميل للأدمن

## Overview - نظرة عامة

تم إضافة حقل `email` لجدول الـ admins لتوحيد تجربة تسجيل الدخول عبر جميع أنواع المستخدمين.

---

## What Changed - ما الذي تغير

### 1. Database Schema
- ✅ إضافة عمود `email` (nullable) لجدول `admins`
- ✅ الحقل اختياري (nullable) للحفاظ على التوافق مع البيانات الموجودة

### 2. Admin Model
- ✅ إضافة `email` للـ `$fillable` array
- ✅ إضافة `$hidden` array لإخفاء password و remember_token

### 3. Universal Login
- ✅ الآن يدعم تسجيل دخول الـ Admin بـ:
  - Username
  - Phone
  - Email ✨ NEW

### 4. Response
- ✅ الآن الـ `email` يظهر في response تسجيل الدخول للـ admins
- ✅ لن يكون `null` بعد الآن

---

## Migration Details

### File: `2026_05_21_130000_add_email_to_admins_table.php`

```php
Schema::table('admins', function (Blueprint $table) {
    $table->string('email')->nullable()->after('phone');
});
```

**Why nullable?**
- للحفاظ على التوافق مع الـ admins الموجودين
- يمكن تحديث الإيميلات لاحقاً

---

## Seeder Details

### File: `AddEmailToAdminsSeeder.php`

يقوم بـ:
1. البحث عن جميع الـ admins الذين ليس لديهم email
2. إنشاء email تلقائي بناءً على username: `{username}@bareqq.com`
3. تحديث السجلات

**مثال:**
- Username: `testadmin`
- Generated Email: `testadmin@bareqq.com`

---

## Usage Examples - أمثلة الاستخدام

### 1. Login with Username (كما كان)
```json
{
    "identifier": "testadmin",
    "password": "password123"
}
```

### 2. Login with Phone (كما كان)
```json
{
    "identifier": "01111111111",
    "password": "password123"
}
```

### 3. Login with Email ✨ NEW
```json
{
    "identifier": "admin@bareqq.com",
    "password": "password123"
}
```

---

## Response Example

### Before (قبل):
```json
{
    "status": true,
    "message": "Login successful",
    "data": {
        "id": 1,
        "username": "testadmin",
        "phone": "01111111111",
        "email": null,  ❌ كان null
        "token": "...",
        "type": "admin"
    }
}
```

### After (بعد):
```json
{
    "status": true,
    "message": "Login successful",
    "data": {
        "id": 1,
        "username": "testadmin",
        "phone": "01111111111",
        "email": "testadmin@bareqq.com",  ✅ الآن يظهر
        "token": "...",
        "type": "admin"
    }
}
```

---

## Deployment Steps - خطوات النشر

### Option 1: Using Script (الطريقة السريعة)
```bash
bash deploy_localization.sh
```

### Option 2: Manual (يدوياً)
```bash
cd /www/wwwroot/user.bareqq.com
git pull origin main

# Run migration
php artisan migrate --force

# Add emails to existing admins
php artisan db:seed --class=AddEmailToAdminsSeeder

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Restart Apache
/etc/init.d/httpd restart
```

---

## Testing - الاختبار

### Test 1: Login with Email
```bash
curl -X POST "https://user.bareqq.com/api/login" \
  -H "Content-Type: application/json" \
  -H "API-Password: Nf:upZTg^7A?Hj" \
  -d '{
    "identifier": "testadmin@bareqq.com",
    "password": "password123"
  }'
```

### Test 2: Verify Email in Response
```bash
# Check that email field is not null
# Expected: "email": "testadmin@bareqq.com"
```

---

## Database Changes

### Before:
```
admins table:
- id
- username
- phone
- password
- remember_token
- created_at
- updated_at
- deleted_at
- device_token
```

### After:
```
admins table:
- id
- username
- phone
- email          ✨ NEW
- password
- remember_token
- created_at
- updated_at
- deleted_at
- device_token
```

---

## Important Notes - ملاحظات مهمة

1. ✅ الحقل `email` اختياري (nullable)
2. ✅ الـ seeder يضيف emails تلقائية للـ admins الموجودين
3. ✅ يمكن تحديث الإيميلات يدوياً من الـ database
4. ✅ تسجيل الدخول بـ username/phone لا يزال يعمل
5. ✅ الآن يمكن تسجيل الدخول بالإيميل أيضاً

---

## Rollback (في حالة الحاجة)

إذا احتجت للتراجع عن التغيير:

```bash
php artisan migrate:rollback --step=1
```

هذا سيحذف عمود `email` من جدول `admins`.

---

## Support - الدعم

للمشاكل أو الأسئلة، تحقق من:
- Logs: `tail -f /www/wwwroot/user.bareqq.com/storage/logs/laravel.log`
- Database: تأكد من وجود عمود `email` في جدول `admins`
- Seeder: تأكد من تشغيل `AddEmailToAdminsSeeder`

---

## ✅ Done!

الآن جميع المستخدمين (Admins, Clients, Designers, Marketers) يمكنهم تسجيل الدخول بالإيميل! 🎉
