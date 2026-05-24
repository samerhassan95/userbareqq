# Strategy Details (from Product Orders API)

This document explains what backend should provide so `lib/features/codgooSoftware/Strategy/presentation/strategy_details_view.dart` can be fully powered by order APIs.

Current order endpoint sample:

- `GET /client/product-orders/{orderId}`

---

## What the screen needs

`strategy_details_view.dart` (through `StrategyDetailsCubit` + `StrategyDetailsModel`) currently needs:

1. Strategy basic info
   - `id`
   - `product_name`
   - `status`
   - `starts_at`
   - `ends_at`
2. Team discussion block
   - `team_discussion.discussion_id`
   - `team_discussion.team_count`
   - `team_discussion.title`
   - `team_discussion.team[]`
3. Strategy tips list
   - `strategy_tips[]` (or `tips[]`)
4. Scheduled works/posts by date
   - separate endpoint currently: `GET /client/strategies/{strategyId}/works?date=YYYY-MM-DD`

---

## Gap vs current `client/product-orders/{id}` response

From your sample order response, available:

- order + product + duration + payment + status

Missing for details screen:

- `starts_at`, `ends_at`
- `team_discussion` object
- `strategy_tips` list
- a direct `strategy_id` used for works/discussion endpoints

---

## Backend asks (required)

For strategy orders (`product.role == "strategy"`), please add these fields in:

- `GET /client/product-orders/{orderId}`

### Required additions

1. `strategy_id`
   - numeric ID to use with strategy works/discussion APIs
2. `starts_at`
3. `ends_at`
4. `strategy_tips` (or `tips`)
5. `team_discussion`

### Suggested response shape

```json
{
  "status": true,
  "message": "Order retrieved successfully",
  "data": {
    "id": 7,
    "order_number": "ORD-000007",
    "product": {
      "id": 2,
      "name": "Social Media Strategy",
      "role": "strategy",
      "role_label": "Strategy Subscription"
    },
    "strategy_id": 12,
    "status": "pending_payment",
    "status_label": "Pending payment",
    "duration": "year",
    "duration_label": "Year",
    "total_price": 20000,
    "currency": "EGP",
    "starts_at": "2026-05-21",
    "ends_at": "2027-05-20",
    "strategy_tips": [
      {
        "id": 19,
        "text": "We will create engaging posts...",
        "platforms": ["facebook", "instagram", "twitter"]
      }
    ],
    "team_discussion": {
      "discussion_id": 45,
      "team_count": 5,
      "title": "Strategy team chat",
      "team": [
        {
          "id": 1,
          "name": "Ahmed",
          "avatar": "https://...",
          "type": "employee",
          "role": "designer"
        }
      ]
    },
    "payment": {
      "invoice_id": 9,
      "payment_status": "unpaid",
      "payment_proof": "https://..."
    }
  }
}
```

---

## Optional but helpful

1. Include `works_preview` (first 3 posts) in order details to reduce first paint requests.
2. Add endpoint by order ID:
   - `GET /client/product-orders/{orderId}/works?date=YYYY-MM-DD`
   This avoids needing `strategy_id` on client side.
3. Provide stable date format (`YYYY-MM-DD`) for `starts_at` and `ends_at`.

---

## Notes for frontend mapping

- If `product.role == "one_time"`:
  - strategy details UI should not be opened.
- If `product.role == "strategy"`:
  - frontend can map order details directly to `StrategyDetailsModel` once required fields above are present.
