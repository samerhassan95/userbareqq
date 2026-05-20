# Products API — One-Time & Strategy

This document describes backend changes for the new **two product roles** in the Codgoo mobile app.

## Overview

| Role | API value | Client flow |
|------|-----------|-------------|
| **One-time product** | `product` | Select product → pick **one** feature (video/post) → create order + invoice → pay → admin team delivers that feature |
| **Strategy product** | `strategy` | Select product → pick **duration** (month/year) → see included tips → create order + invoice → pay → team runs ongoing strategy |

---

## Base URL & auth

```
https://back.codgoo.com/codgoo/public/api/
```

Same headers as existing client APIs: `Authorization: Bearer {token}`, `API-Password`, `Accept-Language`.

---

## 1. Product list — `GET client/our-products`

### New / updated fields per product

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `product_role` | string | **Yes** | `product` or `strategy` |
| `type` | string | No | Legacy display type (keep for backward compatibility) |
| `monthly_price` | string/decimal | For strategy | Price when duration = month |
| `yearly_price` | string/decimal | For strategy | Price when duration = year |

### Optional query param

| Param | Values | Description |
|-------|--------|-------------|
| `product_role` | `product`, `strategy` | Filter list server-side (recommended) |
| `device` | `mobile` | Already used by app |

### Example item

```json
{
  "id": 12,
  "name": "Social Post Package",
  "price": "500",
  "description": "...",
  "image": "...",
  "background_image": "...",
  "category_name": "Marketing",
  "type": "service",
  "product_role": "one_time"
}
```

```json
{
  "id": 15,
  "name": "Full Social Strategy",
  "price": "2000",
  "monthly_price": "2000",
  "yearly_price": "20000",
  "product_role": "strategy"
}
```

### Response shape (unchanged wrapper)

```json
{
  "status": true,
  "products": [ /* ... */ ],
  "sliders": [ /* ... */ ]
}
```

**Important:** Until `product_role` is sent, the app defaults products to `product`. Backend should set the correct role for each product.

---

## 2. Product details — `GET client/products/{id}`

### Additional fields on `data` object

| Field | Type | Role | Description |
|-------|------|------|-------------|
| `product_role` | string | Both | `product` \| `strategy` |
| `monthly_price` | string | Strategy | Monthly price |
| `yearly_price` | string | Strategy | Yearly price |
| `duration_prices` | array | Strategy | Alternative: `[{ "duration": "month", "price": "2000" }, { "duration": "year", "price": "20000" }]` |
| `strategy_tips` | array | Strategy | Included deliverables — **read-only** in app (not selectable) |
| `features` | array | One-time | Selectable features (user picks **exactly one**) |

### `strategy_tips[]` item (read-only — client does not send selected tip IDs)

Tips are **bundled with the strategy product**. The user only chooses **duration** (month/year); all tips for that product are included automatically.

| Field | Type | Description |
|-------|------|-------------|
| `id` | int | Optional (for admin only; not sent on order) |
| `text` | string | Human-readable tip, e.g. *"We will create posts for Facebook, Instagram and Twitter"* |
| `platforms` | string[] | Optional: `facebook`, `instagram`, `twitter`, `tiktok`, `linkedin` |

### Example strategy tips

```json
"strategy_tips": [
  {
    "id": 1,
    "text": "We will create posts for Facebook, Instagram and Twitter",
    "platforms": ["facebook", "instagram", "twitter"]
  },
  {
    "id": 2,
    "text": "Monthly performance report",
    "platforms": []
  }
]
```

### `features[]` for one-time products

| Field | Type | Description |
|-------|------|-------------|
| `id` | int | Feature ID |
| `name` | string | e.g. `"Video"`, `"Post"` |
| `feature_type` | string | Recommended: `video`, `post` |
| `price` | string | Added to base product price if > 0 |
| `description` | string | Optional |

```json
"features": [
  { "id": 3, "name": "Video", "feature_type": "video", "price": "0", "description": "One promotional video" },
  { "id": 4, "name": "Post", "feature_type": "post", "price": "100", "description": "One social post" }
]
```

**Note:** Do not use `addons` — the mobile app reads `features` only.

---

## 3. Create product order + invoice — `POST client/product-orders`

Called when the user taps **Start product & Pay** or **Create invoice & Pay**.

### Request body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `product_id` | int | Yes | Product ID |
| `product_role` | string | Yes | `one_time` or `strategy` |
| `total_price` | string/decimal | Yes | Final price calculated on client |
| `feature_id` | int | One-time | Selected feature ID |
| `feature_name` | string | One-time | Display name (optional) |
| `duration` | string | Strategy | `month` or `year` |

### One-time example

```json
{
  "product_id": 12,
  "product_role": "one_time",
  "total_price": "600",
  "feature_id": 4,
  "feature_name": "Post"
}
```

### Strategy example

```json
{
  "product_id": 15,
  "product_role": "strategy",
  "total_price": "2000",
  "duration": "month"
}
```

All `strategy_tips` linked to the product on the server are included — no tip selection from the client.

### Expected behavior (backend)

1. Validate product exists and `product_role` matches.
2. Validate `feature_id` belongs to product (one-time) or `duration` is allowed (strategy).
3. Recalculate price server-side (do not trust client `total_price` alone).
4. Create **order** record (status: `pending_payment`).
5. Create **invoice** linked to order.
6. For one-time: after payment → assign to admin/team queue with `feature_id` for fulfillment.
7. For strategy: after payment → activate subscription until `end_date` (month +1 / year +1).

### Success response (200/201)

```json
{
  "status": true,
  "message": "Order created successfully",
  "data": {
    "order_id": 101,
    "invoice_id": 550,
    "payment_url": "https://..."
  }
}
```

| Field | Description |
|-------|-------------|
| `order_id` | Internal order reference |
| `invoice_id` | For existing invoices flow in app |
| `payment_url` | Optional direct payment link |

### Error (422)

```json
{
  "status": false,
  "message": "Validation failed",
  "errors": {
    "feature_id": ["Required for one-time products."]
  }
}
```

---

## 4. Pay order (optional) — `POST client/product-orders/{order_id}/pay`

If payment is separate from invoice creation, expose pay endpoint. Otherwise paying via existing `client/view-invoice/{id}` is enough.

---

## 5. Admin / team fulfillment

### One-time orders (after invoice paid)

| Status | Description |
|--------|-------------|
| `pending_payment` | Invoice not paid |
| `paid` | Paid, waiting for team |
| `in_progress` | Team creating feature (video/post) |
| `delivered` | Feature completed |
| `cancelled` | Cancelled |

Admin needs:
- List of paid one-time orders with `feature_id` / `feature_type`
- Ability to upload deliverable or mark complete

### Strategy orders (after invoice paid)

| Field | Description |
|-------|-------------|
| `duration` | `month` / `year` |
| `starts_at` | Subscription start |
| `ends_at` | Subscription end |
| `strategy_tips` | All tips for the product (from DB; not client-selected) |

---

## 6. Suggested database tables

### `products` (alter)

```sql
ALTER TABLE products ADD COLUMN product_role ENUM('one_time', 'strategy') NOT NULL DEFAULT 'one_time';
ALTER TABLE products ADD COLUMN monthly_price DECIMAL(12,2) NULL;
ALTER TABLE products ADD COLUMN yearly_price DECIMAL(12,2) NULL;
```

### `product_strategy_tips`

```sql
CREATE TABLE product_strategy_tips (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  product_id BIGINT NOT NULL,
  text TEXT NOT NULL,
  platforms JSON NULL,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

### `product_orders`

```sql
CREATE TABLE product_orders (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  client_id BIGINT NOT NULL,
  product_id BIGINT NOT NULL,
  product_role ENUM('one_time', 'strategy') NOT NULL,
  feature_id BIGINT NULL,
  duration ENUM('month', 'year') NULL,
  total_price DECIMAL(12,2) NOT NULL,
  status VARCHAR(32) NOT NULL DEFAULT 'pending_payment',
  invoice_id BIGINT NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

Link to existing `invoices` table.

---

## 7. Mobile app mapping

| UI | API |
|----|-----|
| Switch One-Time / Strategy | Filter by `product_role` |
| One-time: radio features | `features` + single `feature_id` on order |
| Strategy: month/year chips | `duration` + `monthly_price` / `yearly_price` |
| Strategy tips list | `strategy_tips` (read-only, no selection) |
| Checkout | `POST client/product-orders` |
| After success | Navigate to Invoices list |

**Flutter files:**
- `lib/features/codgooSoftware/Products/presentation/products_view.dart`
- `lib/features/codgooSoftware/Products/presentation/product_details_view.dart`
- `lib/features/codgooSoftware/Products/presentation/product_checkout_view.dart`
- `lib/features/codgooSoftware/Products/data/dataSource/product_order_data_source.dart`

---

## 8. Open questions for backend

1. **Payment:** Is invoice payment the same as existing `client/view-invoice/{id}` flow?
2. **Pricing:** Should strategy price always come from `monthly_price`/`yearly_price`, or only `duration_prices` array?
3. **One-time features:** Use `features[]` on product details (not `addons`).
4. **Credentials:** Should strategy orders use stored social credentials (`client/credentials`) automatically?
5. **Partial pay / deposits:** Supported or full payment only?

Please confirm endpoint paths and sample JSON before production so the app can be tested end-to-end.
