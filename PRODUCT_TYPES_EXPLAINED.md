# 📋 شرح أنواع المنتجات - Product Types

## 🎯 نظام المنتجات في Bareqq

يوجد نوعان رئيسيان من المنتجات:

---

## 1️⃣ One-Time Products (خدمات لمرة واحدة)

### الوصف:
خدمات تُقدم مرة واحدة ويتم الدفع مقابلها مرة واحدة.

### الخصائص:
- `product_role` = `one_time`
- سعر ثابت واحد (`price`)
- `monthly_price` = `null`
- `yearly_price` = `null`
- يمكن إضافة **Addons** (خدمات إضافية)

### أمثلة:
- ✅ تصميم موقع إلكتروني
- ✅ تصميم تطبيق موبايل
- ✅ تصميم شعار
- ✅ تصميم هوية بصرية
- ✅ تطوير نظام ERP

### Addons المتاحة:
- Video Production
- Social Media Post
- Logo Design
- Banner Design
- Content Writing

### Response Example:
```json
{
    "id": 1,
    "name": "Website Design",
    "name_ar": "تصميم موقع إلكتروني",
    "description": "Professional website design",
    "description_ar": "تصميم مواقع احترافية",
    "type": "general",
    "product_role": "one_time",
    "monthly_price": null,
    "yearly_price": null,
    "price": 5000.00
}
```

---

## 2️⃣ Strategy Products (خدمات استراتيجية مستمرة)

### الوصف:
خدمات مستمرة تُقدم على أساس شهري أو سنوي.

### الخصائص:
- `product_role` = `strategy`
- أسعار حسب المدة:
  - 3 أشهر (`three_month_price`)
  - 6 أشهر (`six_month_price`)
  - سنة (`yearly_price`)
- `monthly_price` = `null` (لا يوجد اشتراك شهري)
- يحتوي على **Strategy Tips** (نصائح استراتيجية)
- يمكن تحديد **Platforms** (المنصات المستهدفة)

### أمثلة:
- ✅ إدارة وسائل التواصل الاجتماعي
- ✅ استشارات تسويقية
- ✅ إدارة الحملات الإعلانية
- ✅ تحسين محركات البحث (SEO)
- ✅ إدارة المحتوى

### Strategy Tips:
كل منتج استراتيجي يحتوي على نصائح مثل:
- "We will create engaging posts for Facebook, Instagram, and Twitter"
- "Daily content calendar with optimal posting times"
- "Monthly performance analytics and insights report"
- "Hashtag research and optimization"
- "Community management and engagement monitoring"

### Response Example:
```json
{
    "id": 2,
    "name": "Social Media Management",
    "name_ar": "إدارة وسائل التواصل الاجتماعي",
    "description": "Complete social media management",
    "description_ar": "إدارة كاملة لوسائل التواصل",
    "type": "general",
    "product_role": "strategy",
    "monthly_price": null,
    "three_month_price": 1200.00,
    "six_month_price": 2200.00,
    "yearly_price": 4000.00
}
```

---

## 📊 المقارنة

| الميزة | One-Time | Strategy |
|--------|----------|----------|
| **النوع** | خدمة لمرة واحدة | خدمة مستمرة |
| **الدفع** | مرة واحدة | 3 شهور / 6 شهور / سنة |
| **السعر** | `price` | `three_month_price`, `six_month_price`, `yearly_price` |
| **Addons** | ✅ نعم | ❌ لا |
| **Strategy Tips** | ❌ لا | ✅ نعم |
| **Platforms** | ❌ لا | ✅ نعم |

---

## 🔍 كيفية التصفية

### في الـ API:

#### عرض جميع المنتجات:
```
GET /api/admin/content/products
```

#### عرض One-Time Products فقط:
```
GET /api/admin/content/products?product_role=one_time
```

#### عرض Strategy Products فقط:
```
GET /api/admin/content/products?product_role=strategy
```

---

## 💡 Strategy Tips Structure

### الحقول:
```json
{
    "id": 1,
    "product_id": 2,
    "text": "We will create engaging posts...",
    "text_ar": "سننشئ منشورات جذابة...",
    "platforms": ["facebook", "instagram", "twitter"],
    "sort_order": 1
}
```

### Platforms المتاحة:
- `facebook`
- `instagram`
- `twitter`
- `linkedin`
- `tiktok`
- `youtube`
- `snapchat`

---

## 🎯 حالات الاستخدام

### One-Time Products:
```
العميل يريد → تصميم موقع
الطلب → يتم مرة واحدة
الدفع → مبلغ ثابت
الإضافات → يمكن إضافة خدمات إضافية
النتيجة → موقع جاهز
```

### Strategy Products:
```
العميل يريد → إدارة حساباته على السوشيال ميديا
الطلب → اشتراك شهري أو سنوي
الدفع → شهري أو سنوي
الخدمة → مستمرة (منشورات، تحليلات، إدارة)
النتيجة → خدمة مستمرة
```

---

## 📝 ملاحظات مهمة

### للـ One-Time:
- ✅ يجب أن يكون `price` موجود
- ✅ `monthly_price` و `yearly_price` يجب أن يكونا `null`
- ✅ يمكن ربط Addons

### للـ Strategy:
- ✅ يجب أن تكون الأسعار موجودة:
  - `three_month_price` (3 أشهر)
  - `six_month_price` (6 أشهر)
  - `yearly_price` (سنة)
- ✅ `monthly_price` يجب أن يكون `null`
- ✅ يجب إضافة Strategy Tips
- ✅ يمكن تحديد Platforms لكل Tip

---

**الآن أصبح واضحاً! 🎉**
