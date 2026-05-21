# Product Order Response Examples

## Create One-Time Order Response

**Request:**
```json
{
    "product_id": 1,
    "product_role": "one_time",
    "total_price": "600",
    "feature_id": 5,
    "feature_name": "Premium Feature"
}
```

**Response (201 Created):**
```json
{
    "status": true,
    "code": 201,
    "message": "Order created successfully! Please upload payment proof to complete your order.",
    "data": {
        "order": {
            "id": 101,
            "order_number": "ORD-000101",
            "status": "pending_payment",
            "status_label": "Pending Payment",
            "total_price": 600,
            "currency": "EGP",
            "created_at": "2026-05-20 15:30:00"
        },
        "product": {
            "id": 1,
            "name": "Social Media Marketing",
            "product_role": "one_time",
            "product_role_label": "One-Time Service"
        },
        "selected_feature": {
            "id": 5,
            "name": "Premium Feature",
            "price": 100
        },
        "invoice": {
            "id": 550,
            "invoice_number": "INV-000550",
            "amount": 600,
            "status": "unpaid",
            "status_label": "Unpaid",
            "due_date": "2026-05-27",
            "payment_method": "bank_transfer"
        },
        "next_steps": {
            "step_1": "Upload payment proof to the invoice",
            "step_2": "Wait for admin approval",
            "step_3": "Admin will deliver your selected feature"
        }
    }
}
```

---

## Create Strategy Order (Monthly) Response

**Request:**
```json
{
    "product_id": 2,
    "product_role": "strategy",
    "total_price": "2000",
    "duration": "month"
}
```

**Response (201 Created):**
```json
{
    "status": true,
    "code": 201,
    "message": "Order created successfully! Please upload payment proof to complete your order.",
    "data": {
        "order": {
            "id": 102,
            "order_number": "ORD-000102",
            "status": "pending_payment",
            "status_label": "Pending Payment",
            "total_price": 2000,
            "currency": "EGP",
            "created_at": "2026-05-20 15:35:00"
        },
        "product": {
            "id": 2,
            "name": "Full Social Strategy",
            "product_role": "strategy",
            "product_role_label": "Strategy Package"
        },
        "subscription_details": {
            "duration": "month",
            "duration_label": "Monthly",
            "price": 2000,
            "starts_at": "2026-05-20",
            "ends_at": "2026-06-20"
        },
        "included_tips_count": 5,
        "invoice": {
            "id": 551,
            "invoice_number": "INV-000551",
            "amount": 2000,
            "status": "unpaid",
            "status_label": "Unpaid",
            "due_date": "2026-05-27",
            "payment_method": "bank_transfer"
        },
        "next_steps": {
            "step_1": "Upload payment proof to the invoice",
            "step_2": "Wait for admin approval",
            "step_3": "Your subscription will be activated"
        }
    }
}
```

---

## Create Strategy Order (Yearly) Response

**Request:**
```json
{
    "product_id": 2,
    "product_role": "strategy",
    "total_price": "20000",
    "duration": "year"
}
```

**Response (201 Created):**
```json
{
    "status": true,
    "code": 201,
    "message": "Order created successfully! Please upload payment proof to complete your order.",
    "data": {
        "order": {
            "id": 103,
            "order_number": "ORD-000103",
            "status": "pending_payment",
            "status_label": "Pending Payment",
            "total_price": 20000,
            "currency": "EGP",
            "created_at": "2026-05-20 15:40:00"
        },
        "product": {
            "id": 2,
            "name": "Full Social Strategy",
            "product_role": "strategy",
            "product_role_label": "Strategy Package"
        },
        "subscription_details": {
            "duration": "year",
            "duration_label": "Yearly",
            "price": 20000,
            "starts_at": "2026-05-20",
            "ends_at": "2027-05-20"
        },
        "included_tips_count": 5,
        "invoice": {
            "id": 552,
            "invoice_number": "INV-000552",
            "amount": 20000,
            "status": "unpaid",
            "status_label": "Unpaid",
            "due_date": "2026-05-27",
            "payment_method": "bank_transfer"
        },
        "next_steps": {
            "step_1": "Upload payment proof to the invoice",
            "step_2": "Wait for admin approval",
            "step_3": "Your subscription will be activated"
        }
    }
}
```

---

## Response Fields Explanation

### Order Object
- `id`: Unique order ID
- `order_number`: Formatted order number (ORD-XXXXXX)
- `status`: Current order status (pending_payment, paid, in_progress, delivered, completed, cancelled)
- `status_label`: Human-readable status
- `total_price`: Final calculated price
- `currency`: Currency code (EGP)
- `created_at`: Order creation timestamp

### Product Object
- `id`: Product ID
- `name`: Product name
- `product_role`: Product type (one_time or strategy)
- `product_role_label`: Human-readable product type

### Selected Feature (One-Time Only)
- `id`: Feature/addon ID
- `name`: Feature name
- `price`: Feature price

### Subscription Details (Strategy Only)
- `duration`: Selected duration (month or year)
- `duration_label`: Human-readable duration
- `price`: Subscription price
- `starts_at`: Subscription start date
- `ends_at`: Subscription end date

### Invoice Object
- `id`: Invoice ID
- `invoice_number`: Formatted invoice number (INV-XXXXXX)
- `amount`: Invoice amount
- `status`: Payment status (unpaid, paid)
- `status_label`: Human-readable payment status
- `due_date`: Payment due date
- `payment_method`: Payment method (bank_transfer)

### Next Steps
- Clear instructions for the client on what to do next
- Different steps for one-time vs strategy orders

---

## Error Responses

### Product Not Found (404)
```json
{
    "status": false,
    "message": "Product not found"
}
```

### Product Role Mismatch (422)
```json
{
    "status": false,
    "message": "Product role mismatch"
}
```

### Validation Error (422)
```json
{
    "status": false,
    "message": "Validation error",
    "errors": {
        "product_id": ["The product id field is required."],
        "feature_id": ["Required for one-time products."]
    }
}
```
