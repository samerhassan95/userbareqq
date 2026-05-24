# 🔄 تحديث Post Workflow

## ✅ التغييرات الرئيسية - COMPLETE:

### 1. بنية البوست:
- ✅ `title` + `title_ar`
- ✅ `description` + `description_ar` (بدلاً من content)
- ✅ `image`
- ✅ `status`: pending, approved, rejected
- ✅ `is_approved`: boolean
- ✅ `client_id`: العميل اللي هيشوف ويعمل approve

### 2. Feedbacks System:
- ✅ جدول `post_feedbacks`
- ✅ الـ Client يقدر يضيف feedbacks (تعليقات)
- ✅ لما يعمل approve، مينفعش يضيف feedbacks تاني

### 3. Workflow:
```
1. Admin/Marketer → ينشئ البوست
2. Client → يشوف البوست ويضيف feedbacks
3. Admin/Marketer/Designer → يعدلوا البوست حسب الـ feedbacks
4. Client → يضيف feedbacks تانية
5. Client → يعمل Approve
6. البوست يتقفل → مينفعش تعديل أو feedbacks
```

---

## الملفات المحدثة:

### 1. Migration:
- ✅ `create_posts_table.php` - محدث
- ✅ `create_post_feedbacks_table.php` - جديد

### 2. Models:
- ✅ `Post.php` - محدث
- ✅ `PostFeedback.php` - جديد

### 3. Controllers:
- ✅ `AdminPostController.php` - محدث
- ✅ `MarketerPostController.php` - محدث
- ✅ `DesignerPostController.php` - محدث
- ✅ `ClientPostController.php` - محدث بالكامل

### 4. Routes:
- ✅ `routes/posts.php` - محدث

### 5. Translations:
- ✅ `lang/en/messages.php` - محدث
- ✅ `lang/ar/messages.php` - محدث

### 6. Postman Collection:
- ✅ `Bareqq_Complete_API.postman_collection.json` - محدث

---

## الـ Endpoints المطلوبة:

### Admin/Marketer:
```
✅ POST /api/admin/posts
  - title, description, image, client_id

✅ PUT /api/admin/posts/{id}
  - يتحقق: !is_approved
```

### Client:
```
✅ GET /api/client/posts
  - يشوف البوستات الخاصة بيه

✅ POST /api/client/posts/{id}/feedback
  - يضيف feedback
  - يتحقق: !is_approved

✅ POST /api/client/posts/{id}/approve
  - يعمل approve
  - is_approved = true
  - approved_at = now()

✅ GET /api/client/posts/{id}/feedbacks
  - يشوف كل الـ feedbacks
```

---

## الخطوات التالية:

1. ✅ تحديث Migration
2. ✅ تحديث Models
3. ✅ تحديث Controllers
4. ✅ إضافة Feedback endpoints
5. ✅ إضافة Approve endpoint
6. ✅ تحديث Postman Collection
7. ✅ تحديث Documentation

---

**Status:** ✅ COMPLETE - Ready for Testing 🚀

**Date:** May 24, 2026

---

## 🧪 للتجربة:

```bash
# Run migrations
php artisan migrate

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Test with Postman
# Import: Bareqq_Complete_API.postman_collection.json
```

---

## 📚 الوثائق:

- `POSTS_SYSTEM_SUMMARY.md` - ملخص شامل للنظام
- `POSTS_SYSTEM_COMPLETE.md` - دليل التنفيذ الكامل
- `POST_WORKFLOW_UPDATE.md` - هذا الملف

---

**كل حاجة جاهزة! 🎉**

