# 🧪 اختبار الـ Admin Endpoints

## الخطوات السريعة للاختبار

### 1️⃣ تسجيل الدخول كـ Admin

**في Postman:**
- افتح: `Universal Login - Admin`
- أو: `Admin Login`
- أرسل الطلب واحصل على الـ token

**أو باستخدام cURL:**
```bash
curl -X POST "https://user.bareqq.com/api/login" \
  -H "Content-Type: application/json" \
  -H "api-password: Nf:upZTg^7A?Hj" \
  -d '{
    "email": "admin@bareqq.com",
    "password": "your_password",
    "type": "admin"
  }'
```

**احفظ الـ token من الـ response!**

---

### 2️⃣ اختبار Get All Clients

**في Postman:**
1. افتح: `Admin - Clients` → `Get All Clients`
2. تأكد من:
   - ✅ Authorization: Bearer {{admin_token}}
   - ✅ Accept-Language: en أو ar
3. اضغط Send

**أو باستخدام cURL:**
```bash
curl -X GET "https://user.bareqq.com/api/admin/clients" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept-Language: en"
```

**النتيجة المتوقعة:**
```json
{
    "success": true,
    "message": "Clients retrieved successfully",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "name": "Client Name",
                "email": "client@example.com",
                "phone": "+201234567890",
                "role": "client",
                "subscriptions": [],
                "invoices": []
            }
        ],
        "total": 10,
        "per_page": 15
    }
}
```

---

### 3️⃣ اختبار Get Products Content

**في Postman:**
1. افتح: `Admin - Content Management` → `Get All Products Content`
2. اضغط Send

**أو باستخدام cURL:**
```bash
curl -X GET "https://user.bareqq.com/api/admin/content/products" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept-Language: en"
```

**النتيجة المتوقعة:**
```json
{
    "success": true,
    "message": "Products content retrieved successfully",
    "data": [
        {
            "id": 1,
            "name_en": "Website Design",
            "name_ar": "تصميم موقع إلكتروني",
            "description_en": "Professional website design...",
            "description_ar": "تصميم مواقع احترافية...",
            "type": "service",
            "role": null,
            "price_monthly": null,
            "price_yearly": null
        }
    ]
}
```

---

### 4️⃣ اختبار Get Strategy Tips Content

**في Postman:**
1. افتح: `Admin - Content Management` → `Get All Strategy Tips Content`
2. اضغط Send

**أو باستخدام cURL:**
```bash
curl -X GET "https://user.bareqq.com/api/admin/content/strategy-tips" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept-Language: en"
```

---

## 🔍 اختبارات متقدمة

### البحث عن عميل
```bash
curl -X GET "https://user.bareqq.com/api/admin/clients?search=ahmed" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept-Language: ar"
```

### التصفية حسب الدور
```bash
curl -X GET "https://user.bareqq.com/api/admin/clients?role=designer" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept-Language: en"
```

### تحديد عدد العناصر في الصفحة
```bash
curl -X GET "https://user.bareqq.com/api/admin/clients?per_page=5" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept-Language: en"
```

### التصفية حسب نوع المنتج
```bash
curl -X GET "https://user.bareqq.com/api/admin/content/products?type=subscription" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept-Language: en"
```

### التصفية حسب معرف المنتج للنصائح
```bash
curl -X GET "https://user.bareqq.com/api/admin/content/strategy-tips?product_id=2" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept-Language: en"
```

---

## ❌ الأخطاء المحتملة

### 1. 401 Unauthorized
**السبب:** Token غير صحيح أو منتهي الصلاحية
**الحل:** سجل دخول مرة أخرى واحصل على token جديد

### 2. 403 Forbidden
**السبب:** المستخدم ليس admin
**الحل:** تأكد من تسجيل الدخول بحساب admin

### 3. 404 Not Found
**السبب:** الـ route غير موجود
**الحل:** 
```bash
php artisan route:clear
php artisan route:cache
```

### 4. 500 Internal Server Error
**السبب:** خطأ في السيرفر
**الحل:** راجع `TROUBLESHOOTING_ADMIN_ENDPOINTS.md`

---

## ✅ Checklist للاختبار

- [ ] تسجيل الدخول كـ Admin
- [ ] الحصول على admin_token
- [ ] اختبار Get All Clients
- [ ] اختبار Get Client Details
- [ ] اختبار Get Products Content
- [ ] اختبار Get Single Product Content
- [ ] اختبار Get Strategy Tips Content
- [ ] اختبار Get Single Strategy Tip Content
- [ ] اختبار البحث والتصفية
- [ ] اختبار مع اللغة العربية
- [ ] اختبار مع اللغة الإنجليزية

---

## 📊 النتائج المتوقعة

| Endpoint | Status | Response Time |
|----------|--------|---------------|
| GET /admin/clients | 200 OK | < 500ms |
| GET /admin/clients/{id} | 200 OK | < 300ms |
| GET /admin/content/products | 200 OK | < 400ms |
| GET /admin/content/products/{id} | 200 OK | < 300ms |
| GET /admin/content/strategy-tips | 200 OK | < 400ms |
| GET /admin/content/strategy-tips/{id} | 200 OK | < 300ms |

---

## 🎉 إذا نجحت جميع الاختبارات

تهانينا! الـ endpoints تعمل بشكل صحيح! 🚀

يمكنك الآن:
- ✅ استخدام الـ endpoints في التطبيق
- ✅ عرض جميع العملاء
- ✅ عرض المحتوى بالإنجليزي والعربي
- ✅ التعديل على المحتوى

---

## 📚 المراجع

- **التوثيق الكامل:** `ADMIN_CLIENTS_CONTENT_ENDPOINTS.md`
- **حل المشاكل:** `TROUBLESHOOTING_ADMIN_ENDPOINTS.md`
- **الملخص:** `ADMIN_CLIENTS_CONTENT_SUMMARY.md`
