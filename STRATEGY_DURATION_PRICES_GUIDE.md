# Strategy Product Duration Prices Guide

## Overview
Strategy products now support 4 different duration options with separate pricing:
- **month** - 1 month subscription
- **3_months** - 3 months subscription
- **6_months** - 6 months subscription  
- **year** - 1 year subscription

## Admin: Create Strategy Product

### Endpoint
```
POST {{base_url}}/admin/products
```

### Headers
```
Authorization: Bearer {{admin_token}}
Accept-Language: ar
Content-Type: application/json
```

### Request Body Example
```json
{
    "name": "Digital Marketing Strategy",
    "name_ar": "استراتيجية التسويق الرقمي",
    "description": "Complete digital marketing strategy with social media management",
    "description_ar": "استراتيجية تسويق رقمي كاملة مع إدارة وسائل التواصل الاجتماعي",
    "note": "Includes monthly reports and analytics",
    "note_ar": "يتضمن تقارير شهرية وتحليلات",
    "type": "subscription",
    "product_role": "strategy",
    "monthly_price": 500,
    "three_months_price": 1350,
    "six_months_price": 2550,
    "yearly_price": 4800,
    "category_id": 1
}
```

### Response Example
```json
{
    "status": true,
    "message": "Product created successfully",
    "data": {
        "id": 15,
        "name": "استراتيجية التسويق الرقمي",
        "name_ar": "استراتيجية التسويق الرقمي",
        "description": "Complete digital marketing strategy with social media management",
        "description_ar": "استراتيجية تسويق رقمي كاملة مع إدارة وسائل التواصل الاجتماعي",
        "type": "subscription",
        "product_role": "strategy",
        "monthly_price": 500.00,
        "three_months_price": 1350.00,
        "six_months_price": 2550.00,
        "yearly_price": 4800.00,
        "image": "https://user.bareqq.com/product_images/xyz.jpg",
        "category": {
            "id": 1,
            "name": "Marketing"
        },
        "strategy_tips": [],
        "created_at": "2026-05-25T12:00:00.000000Z"
    }
}
```

## Client: Create Product Order with Duration

### Endpoint
```
POST {{base_url}}/client/product-orders
```

### Headers
```
Authorization: Bearer {{client_token}}
Accept-Language: ar
Content-Type: application/json
```

### Request Examples

#### 1 Month Duration
```json
{
    "product_id": 15,
    "duration": "month",
    "payment_method": "opay"
}
```
**Price:** 500 EGP

#### 3 Months Duration
```json
{
    "product_id": 15,
    "duration": "3_months",
    "payment_method": "opay"
}
```
**Price:** 1350 EGP (خصم 10%)

#### 6 Months Duration
```json
{
    "product_id": 15,
    "duration": "6_months",
    "payment_method": "opay"
}
```
**Price:** 2550 EGP (خصم 15%)

#### 1 Year Duration
```json
{
    "product_id": 15,
    "duration": "year",
    "payment_method": "opay"
}
```
**Price:** 4800 EGP (خصم 20%)

### Response Example
```json
{
    "status": true,
    "message": "Order created successfully",
    "data": {
        "id": 123,
        "product_id": 15,
        "product_name": "استراتيجية التسويق الرقمي",
        "client_id": 5,
        "duration": "3_months",
        "duration_label": "3 أشهر",
        "price": 1350.00,
        "payment_method": "opay",
        "payment_status": "pending",
        "order_status": "pending",
        "start_date": null,
        "expiry_date": null,
        "created_at": "2026-05-25T12:00:00.000000Z"
    }
}
```

## Admin: Approve Payment

When admin approves payment, the system automatically calculates the expiry date based on duration:

### Endpoint
```
POST {{base_url}}/admin/product-orders/{id}/approve-payment
```

### Duration Calculations
- **month**: +1 month from approval date
- **3_months**: +3 months from approval date
- **6_months**: +6 months from approval date
- **year**: +1 year from approval date

### Example
```json
{
    "status": true,
    "message": "Payment approved successfully",
    "data": {
        "id": 123,
        "payment_status": "paid",
        "order_status": "active",
        "start_date": "2026-05-25",
        "expiry_date": "2026-08-25"
    }
}
```

## Database Fields

### products table
- `monthly_price` - DECIMAL(10,2) - Price for 1 month
- `three_months_price` - DECIMAL(10,2) - Price for 3 months
- `six_months_price` - DECIMAL(10,2) - Price for 6 months
- `yearly_price` - DECIMAL(10,2) - Price for 1 year

### product_orders table
- `duration` - VARCHAR(20) - Values: 'month', '3_months', '6_months', 'year'
- `price` - DECIMAL(10,2) - Final calculated price
- `start_date` - DATE - When subscription starts (after payment approval)
- `expiry_date` - DATE - When subscription expires

## Pricing Strategy Example

For a base monthly price of 500 EGP:

| Duration | Price | Discount | Monthly Cost |
|----------|-------|----------|--------------|
| 1 month | 500 | 0% | 500 |
| 3 months | 1350 | 10% | 450 |
| 6 months | 2550 | 15% | 425 |
| 1 year | 4800 | 20% | 400 |

## Updated Files
- `database/migrations/2026_05_25_110000_add_duration_prices_to_products_table.php`
- `app/Models/Product.php`
- `app/Http/Controllers/ProductController.php`
- `app/Http/Requests/Client/CreateProductOrderRequest.php`
- `app/Http/Controllers/Client/ProductOrderController.php`
- `app/Http/Controllers/Admin/AdminProductOrderController.php`
- `Bareqq_Complete_API.postman_collection.json`
