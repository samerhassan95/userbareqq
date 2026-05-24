# ✅ Quick Deploy Checklist

## المشكلة الحالية
الكود محدث محلياً ✅ لكن **مش على السيرفر** ❌

## الحل السريع (5 دقائق)

### □ Step 1: Upload Files
ارفع الملفات دي على السيرفر:

**Must Upload (الأهم):**
- [ ] `lang/en/messages.php`
- [ ] `lang/ar/messages.php`
- [ ] `app/Http/Middleware/SetLocale.php`

**Should Upload:**
- [ ] `app/Http/Middleware/Admin.php`
- [ ] `app/Http/Middleware/Client.php`
- [ ] `app/Http/Middleware/MultiGuard.php`
- [ ] `app/Http/Controllers/UniversalAuthController.php`
- [ ] `app/Http/Controllers/TicketController.php`
- [ ] `app/Exceptions/Handler.php`

### □ Step 2: Clear Cache (على السيرفر)
```bash
ssh user@bareqq.com
cd /path/to/project
php artisan cache:clear
php artisan config:clear
php artisan config:cache
```

### □ Step 3: Test في Postman
1. Set `Accept-Language: ar`
2. Send login request
3. Check if message is in Arabic ✅

---

## Quick Test Command

على السيرفر، جرب:
```bash
php artisan tinker
>>> app()->setLocale('ar');
>>> __('messages.login_successful');
```

**Expected:** `"تم تسجيل الدخول بنجاح"`

---

## إذا لسه مش شغال

1. تأكد إن الملفات اتنسخت:
```bash
ls -la lang/ar/messages.php
cat lang/ar/messages.php | head -20
```

2. Clear cache تاني:
```bash
php artisan cache:clear
php artisan config:clear
php artisan optimize:clear
```

3. Restart PHP-FPM (إذا لزم):
```bash
sudo systemctl restart php8.2-fpm
```

---

## 🎯 النتيجة المتوقعة

**Before Deploy:**
```json
{
    "message": "Login successful"  // Always English
}
```

**After Deploy:**
```json
// With Accept-Language: ar
{
    "message": "تم تسجيل الدخول بنجاح"  // Arabic! ✅
}

// With Accept-Language: en
{
    "message": "Login successful"  // English ✅
}
```
