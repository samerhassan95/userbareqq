# Admin Edit Product API Guide

## Overview
هذا الـ API مخصص للأدمن لجلب بيانات المنتج للتعديل، حيث يرجع كل الحقول منفصلة (English و Arabic) بدون ترجمة تلقائية.

## Endpoint

### Get Single Product for Editing
```
GET {{base_url}}/admin/content/products/{id}
```

### Headers
```
Authorization: Bearer {{admin_token}}
Accept-Language: ar
```

### Response Example - Strategy Product
```json
{
    "success": true,
    "message": "Product content retrieved successfully",
    "data": {
        "id": 1,
        "name": "Digital Marketing Strategy",
        "name_ar": "استراتيجية التسويق الرقمي",
        "description": "Complete digital marketing strategy with social media management and content creation",
        "description_ar": "استراتيجية تسويق رقمي كاملة مع إدارة وسائل التواصل الاجتماعي وإنشاء المحتوى",
        "note": "Includes monthly reports and analytics dashboard",
        "note_ar": "يتضمن تقارير شهرية ولوحة تحليلات",
        "price": null,
        "type": "subscription",
        "product_role": "strategy",
        "category_id": 1,
        "image": "https://user.bareqq.com/product_images/strategy-marketing.jpg",
        "background_image": "https://user.bareqq.com/product_images/bg-marketing.jpg",
        "monthly_price": 500.00,
        "three_months_price": 1350.00,
        "six_months_price": 2550.00,
        "yearly_price": 4800.00,
        "category": {
            "id": 1,
            "name": "Marketing",
            "name_ar": "التسويق"
        },
        "created_at": "2026-05-20T10:00:00.000000Z",
        "updated_at": "2026-05-25T12:00:00.000000Z"
    }
}
```

### Response Example - One-Time Product
```json
{
    "success": true,
    "message": "Product content retrieved successfully",
    "data": {
        "id": 5,
        "name": "Website Development",
        "name_ar": "تطوير موقع إلكتروني",
        "description": "Professional website development with modern design",
        "description_ar": "تطوير موقع إلكتروني احترافي بتصميم عصري",
        "note": "Includes hosting for 1 year",
        "note_ar": "يتضمن استضافة لمدة سنة",
        "price": 5000.00,
        "type": "one_time",
        "product_role": "one_time",
        "category_id": 2,
        "image": "https://user.bareqq.com/product_images/website-dev.jpg",
        "background_image": null,
        "monthly_price": null,
        "three_months_price": null,
        "six_months_price": null,
        "yearly_price": null,
        "category": {
            "id": 2,
            "name": "Development",
            "name_ar": "التطوير"
        },
        "created_at": "2026-05-15T10:00:00.000000Z",
        "updated_at": "2026-05-20T12:00:00.000000Z"
    }
}
```

## Fields Explanation

### Basic Fields (All Products)
| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Product ID |
| `name` | string | Product name in English |
| `name_ar` | string | Product name in Arabic |
| `description` | string | Product description in English |
| `description_ar` | string | Product description in Arabic |
| `note` | string | Additional note in English |
| `note_ar` | string | Additional note in Arabic |
| `type` | string | `subscription` or `one_time` |
| `product_role` | string | `strategy` or `one_time` |
| `category_id` | integer | Category ID |
| `image` | string | Full URL to product image |
| `background_image` | string | Full URL to background image |

### Strategy Product Fields
| Field | Type | Description |
|-------|------|-------------|
| `monthly_price` | decimal | Price for 1 month (شهر واحد) |
| `three_months_price` | decimal | Price for 3 months (3 شهور) |
| `six_months_price` | decimal | Price for 6 months (6 شهور) |
| `yearly_price` | decimal | Price for 1 year (سنة) |
| `price` | null | Not used for strategy products |

### One-Time Product Fields
| Field | Type | Description |
|-------|------|-------------|
| `price` | decimal | Product price |
| `monthly_price` | null | Not used |
| `three_months_price` | null | Not used |
| `six_months_price` | null | Not used |
| `yearly_price` | null | Not used |

## Get All Products for Editing

### Endpoint
```
GET {{base_url}}/admin/content/products
```

### Query Parameters
| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `product_role` | string | Filter by role | `strategy` or `one_time` |
| `pagination` | boolean | Enable pagination | `true` or `false` |
| `per_page` | integer | Items per page | `15` |

### Example Request
```
GET {{base_url}}/admin/content/products?product_role=strategy&pagination=true&per_page=10
```

### Response Example
```json
{
    "success": true,
    "message": "Products content retrieved successfully",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "name": "Digital Marketing Strategy",
                "name_ar": "استراتيجية التسويق الرقمي",
                "description": "Complete digital marketing strategy",
                "description_ar": "استراتيجية تسويق رقمي كاملة",
                "note": "Includes monthly reports",
                "note_ar": "يتضمن تقارير شهرية",
                "price": null,
                "type": "subscription",
                "product_role": "strategy",
                "category_id": 1,
                "image": "https://user.bareqq.com/product_images/xyz.jpg",
                "background_image": null,
                "monthly_price": 500.00,
                "three_months_price": 1350.00,
                "six_months_price": 2550.00,
                "yearly_price": 4800.00,
                "created_at": "2026-05-20T10:00:00.000000Z",
                "updated_at": "2026-05-25T12:00:00.000000Z"
            }
        ],
        "per_page": 10,
        "total": 1
    }
}
```

## Usage in Admin Panel

### 1. Load Product for Editing
```javascript
// Fetch product data
const response = await fetch(`${baseUrl}/admin/content/products/${productId}`, {
    headers: {
        'Authorization': `Bearer ${adminToken}`,
        'Accept-Language': 'ar'
    }
});

const { data } = await response.json();

// Populate form fields
document.getElementById('name').value = data.name;
document.getElementById('name_ar').value = data.name_ar;
document.getElementById('description').value = data.description;
document.getElementById('description_ar').value = data.description_ar;
document.getElementById('note').value = data.note;
document.getElementById('note_ar').value = data.note_ar;

// For strategy products
if (data.product_role === 'strategy') {
    document.getElementById('monthly_price').value = data.monthly_price;
    document.getElementById('three_months_price').value = data.three_months_price;
    document.getElementById('six_months_price').value = data.six_months_price;
    document.getElementById('yearly_price').value = data.yearly_price;
} else {
    document.getElementById('price').value = data.price;
}
```

### 2. Update Product
After editing, use the standard update endpoint:
```
PUT {{base_url}}/admin/products/{id}
```

## Related Endpoints

### Strategy Tips Content
```
GET {{base_url}}/admin/content/strategy-tips/{id}
```
Returns strategy tip with separate `text` and `text_ar` fields.

### All Strategy Tips for Product
```
GET {{base_url}}/admin/content/strategy-tips?product_id=1
```

## Key Differences from Regular Show Endpoint

| Feature | `/admin/products/{id}` | `/admin/content/products/{id}` |
|---------|------------------------|--------------------------------|
| Purpose | Display to users | Edit by admin |
| Translation | Auto-translated based on language | Separate fields (name & name_ar) |
| Fields | Translated single field | All language fields |
| Images | May be device-specific | Full URLs |
| Use Case | Client/Public view | Admin editing |

## Notes
- هذا الـ API يرجع **كل** الحقول منفصلة بدون ترجمة
- الصور ترجع كـ full URLs جاهزة للعرض
- مناسب للاستخدام في forms التعديل في الـ Admin Panel
- يدعم pagination للقوائم الكبيرة
- يمكن الفلترة حسب `product_role` (strategy أو one_time)

## Updated Files
- `app/Http/Controllers/Admin/AdminContentController.php`
- `routes/admin.php`
- `Bareqq_Complete_API.postman_collection.json`
