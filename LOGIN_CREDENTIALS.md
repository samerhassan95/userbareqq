# 🔐 Login Credentials - حسابات الدخول

## 📋 جميع الحسابات المتاحة

---

## 👨‍💼 Admin
```
Email: admin@bareqq.com
Password: [your admin password]
```

**Login Endpoint:**
```
POST {{base_url}}/admin/login
Body:
{
  "email": "admin@bareqq.com",
  "password": "your_password"
}
```

---

## 👤 Clients

### Client 1
```
Email: test_123@marketplace.com
Password: password
```

### Client 2
```
Email: newclient@example.com
Password: [check database]
```

**Login Endpoint:**
```
POST {{base_url}}/login
Body:
{
  "email": "test_123@marketplace.com",
  "password": "password"
}
```

---

## 🎨 Designers

### Designer 1
```
Email: designer1@bareqq.com
Password: 123456
Username: ahmed_designer
```

### Designer 2
```
Email: designer2@bareqq.com
Password: 123456
Username: sara_designer
```

**Login Endpoint:**
```
POST {{base_url}}/login
Body:
{
  "email": "designer1@bareqq.com",
  "password": "123456"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "username": "ahmed_designer",
      "email": "designer1@bareqq.com",
      "role": "designer"
    }
  }
}
```

---

## 📢 Marketers

### Marketer 1
```
Email: marketer1@bareqq.com
Password: 123456
Username: mohamed_marketer
```

### Marketer 2
```
Email: marketer2@bareqq.com
Password: 123456
Username: fatima_marketer
```

**Login Endpoint:**
```
POST {{base_url}}/login
Body:
{
  "email": "marketer1@bareqq.com",
  "password": "123456"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "username": "mohamed_marketer",
      "email": "marketer1@bareqq.com",
      "role": "marketer"
    }
  }
}
```

---

## 🔧 كيفية الاستخدام في Postman

### 1. Login
استخدم الـ Universal Login endpoint:
```
POST {{base_url}}/login
```

### 2. احفظ الـ Token
بعد الـ login الناجح، انسخ الـ token من الـ response

### 3. ضع الـ Token في Variables
في Postman:
- اضغط على Environment
- ضيف/عدل المتغيرات:
  - `designer_token` = [token من response]
  - `marketer_token` = [token من response]

### 4. استخدم الـ Endpoints
دلوقتي تقدر تستخدم:
- **Designer Posts**: `/api/designer/posts`
- **Marketer Posts**: `/api/marketer/posts`

---

## 📝 ملاحظات مهمة

### للـ Designer:
- يقدر يعدل Posts فقط
- مينفعش ينشئ Posts جديدة
- يقدر يضيف/يعدل الصور

### للـ Marketer:
- يقدر ينشئ Posts جديدة
- يقدر يعدل Posts
- لازم يحدد `client_id` عند الإنشاء

### للـ Client:
- يشوف Posts الخاصة بيه فقط
- يقدر يضيف Feedbacks
- يقدر يعمل Approve للـ Posts

---

## 🧪 اختبار سريع

### 1. Login كـ Marketer
```bash
POST {{base_url}}/login
{
  "email": "marketer1@bareqq.com",
  "password": "123456"
}
```

### 2. انشئ Post
```bash
POST {{base_url}}/marketer/posts
Headers:
  Authorization: Bearer {{marketer_token}}
  Accept-Language: en

Body (form-data):
  title: "New Marketing Campaign"
  description: "Campaign description"
  client_id: 1
  image: [file]
```

### 3. Login كـ Client
```bash
POST {{base_url}}/login
{
  "email": "test_123@marketplace.com",
  "password": "password"
}
```

### 4. شوف الـ Posts
```bash
GET {{base_url}}/client/posts
Headers:
  Authorization: Bearer {{client_token}}
```

### 5. ضيف Feedback
```bash
POST {{base_url}}/client/posts/1/feedback
Headers:
  Authorization: Bearer {{client_token}}
  Content-Type: application/json

Body:
{
  "comment": "Please change the color to blue"
}
```

---

## ✅ Checklist

- [ ] Login كـ Designer
- [ ] Login كـ Marketer
- [ ] Login كـ Client
- [ ] احفظ كل الـ tokens في Postman
- [ ] جرب Designer endpoints
- [ ] جرب Marketer endpoints
- [ ] جرب Client endpoints
- [ ] جرب الـ Feedback workflow
- [ ] جرب الـ Approve workflow

---

**تاريخ التحديث**: 24 مايو 2026
**الحالة**: ✅ جاهز للاستخدام

---

## 🆘 في حالة مشكلة

### "401 Unauthorized"
- تأكد من الـ email والـ password
- تأكد إن الـ seeder اشتغل: `php artisan db:seed --class=DesignerMarketerSeeder`

### "403 Forbidden"
- تأكد إن الـ token صحيح
- تأكد إن الـ role صحيح (designer/marketer/client)

### "500 Internal Server Error"
- شوف الـ logs: `tail -f storage/logs/laravel.log`
- تأكد إن الـ routes محملة: `php artisan route:list | grep posts`

---

**كل حاجة جاهزة! 🚀**
