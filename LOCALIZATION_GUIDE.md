# Localization Guide - دليل الترجمة

## Overview - نظرة عامة

The API now supports **Arabic** and **English** localization for all responses.

يدعم الـ API الآن الترجمة إلى **العربية** و **الإنجليزية** لجميع الاستجابات.

---

## How to Use - كيفية الاستخدام

### 1. Set Language in Request Header

Add the `Accept-Language` header to your request:

```http
Accept-Language: ar    # For Arabic - للعربية
Accept-Language: en    # For English - للإنجليزية
```

### 2. Alternative: Query Parameter

You can also use the `lang` query parameter:

```
GET /api/client/products?lang=ar
GET /api/client/products?lang=en
```

---

## What Gets Translated - ما الذي يتم ترجمته

### 1. **Product Fields - حقول المنتج**
- `name` → `name_ar`
- `description` → `description_ar`
- `note` → `note_ar`

### 2. **Addon Fields - حقول الإضافات**
- `name` → `name_ar`
- `description` → `description_ar`

### 3. **Category Fields - حقول الفئات**
- `name` → `name_ar`

### 4. **Strategy Tip Fields - حقول النصائح الاستراتيجية**
- `text` → `text_ar`

### 5. **System Messages - رسائل النظام**
All success/error messages are translated using `lang/en/messages.php` and `lang/ar/messages.php`

---

## Creating Content - إنشاء المحتوى

### When Creating Products

**English Request:**
```json
{
    "name": "Website Design",
    "name_ar": "تصميم موقع إلكتروني",
    "description": "Professional website design",
    "description_ar": "تصميم موقع إلكتروني احترافي",
    "note": "Includes 5 pages",
    "note_ar": "يشمل 5 صفحات",
    "price": 5000,
    "product_role": "one_time"
}
```

### When Creating Addons

```json
{
    "name": "Extra Page",
    "name_ar": "صفحة إضافية",
    "description": "Add one more page to your website",
    "description_ar": "أضف صفحة إضافية لموقعك",
    "price": 500
}
```

### When Creating Strategy Tips

```json
{
    "text": "Post consistently at peak times",
    "text_ar": "انشر بانتظام في أوقات الذروة",
    "platforms": ["facebook", "instagram"],
    "sort_order": 1
}
```

---

## Response Examples - أمثلة الاستجابات

### English Response (Accept-Language: en)

```json
{
    "status": true,
    "message": "Products retrieved successfully",
    "data": {
        "id": 1,
        "name": "Website Design",
        "description": "Professional website design",
        "note": "Includes 5 pages",
        "price": 5000
    }
}
```

### Arabic Response (Accept-Language: ar)

```json
{
    "status": true,
    "message": "تم استرجاع المنتجات بنجاح",
    "data": {
        "id": 1,
        "name": "تصميم موقع إلكتروني",
        "description": "تصميم موقع إلكتروني احترافي",
        "note": "يشمل 5 صفحات",
        "price": 5000
    }
}
```

---

## Validation Rules - قواعد التحقق

### Required Fields - الحقول المطلوبة

When creating/updating content, **both English and Arabic fields are required**:

- `name` ✅ Required
- `name_ar` ✅ Required
- `description` ⚠️ Optional
- `description_ar` ⚠️ Optional (but recommended)

---

## Postman Collection Updates - تحديثات مجموعة Postman

### 1. Added Language Variable

```
{{lang}} = "en" or "ar"
```

### 2. All Requests Now Include

```http
Accept-Language: {{lang}}
```

### 3. Updated Examples

- ✅ Universal Login uses **email** for clients (not username)
- ✅ Create Product includes `name_ar`, `description_ar`, `note_ar`
- ✅ Create Addon includes `name_ar`, `description_ar`
- ✅ Create Strategy Tip includes `text_ar`
- ✅ One-Time Orders **no longer** include `feature_id` or `feature_name`

---

## Migration & Seeder - الترحيل والبذر

### Run Migration

```bash
php artisan migrate
```

This adds `_ar` columns to:
- `products` table
- `addons` table
- `categories` table
- `product_strategy_tips` table

### Run Seeder (Optional)

```bash
php artisan db:seed --class=ArabicTranslationsSeeder
```

This populates existing data with sample Arabic translations.

---

## Technical Implementation - التنفيذ التقني

### Middleware: `SetLocale`

Located at: `app/Http/Middleware/SetLocale.php`

Automatically detects language from:
1. `Accept-Language` header
2. `?lang=` query parameter
3. Defaults to `en`

### Helper: `TranslationHelper`

Located at: `app/Helpers/TranslationHelper.php`

Usage:
```php
use App\Helpers\TranslationHelper;

$translatedName = TranslationHelper::getTranslatedField($product, 'name');
// Returns: $product->name_ar if locale is 'ar', otherwise $product->name
```

---

## Deployment - النشر

Use the deployment script:

```bash
bash deploy_localization.sh
```

This will:
1. Upload migration file
2. Upload seeder file
3. Upload middleware
4. Upload helper
5. Run migrations
6. Clear all caches

---

## Testing - الاختبار

### Test English Response

```bash
curl -X GET "https://user.bareqq.com/api/client/our-products" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept-Language: en"
```

### Test Arabic Response

```bash
curl -X GET "https://user.bareqq.com/api/client/our-products" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept-Language: ar"
```

---

## Notes - ملاحظات

1. ✅ All system messages are translated
2. ✅ All product/addon/category/tip content is translated
3. ✅ Fallback to English if Arabic is missing
4. ✅ Works for both Client and Admin endpoints
5. ⚠️ User-generated content (client names, emails) is NOT translated

---

## Support - الدعم

For issues or questions, contact the development team.

للمشاكل أو الأسئلة، اتصل بفريق التطوير.
