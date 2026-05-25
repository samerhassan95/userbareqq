# Product Order Team Members System

## Overview
Team members (Designers and Marketers) are now assigned to **Product Orders** (entire strategy) instead of individual posts. This means when you add a team member to an order, they automatically have access to all posts within that strategy.

## Key Changes

### Before:
- Team members were assigned to **individual posts**
- Had to add team members separately for each post
- Used `post_team_members` table

### After:
- Team members are assigned to **Product Orders** (entire strategy)
- Add team members once, they're available for all posts in the strategy
- Uses `product_order_team_members` table

## API Endpoints

### 1. Add Team Members to Order
**Endpoint:** `POST /api/admin/product-orders/{orderId}/team`

**Headers:**
```
Authorization: Bearer {admin_token}
Content-Type: application/json
Accept-Language: en|ar
```

**Request Body:**
```json
{
  "team_members": [
    {
      "member_id": 1,
      "member_type": "designer"
    },
    {
      "member_id": 2,
      "member_type": "marketer"
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Team members added successfully",
  "data": {
    "added": [
      {
        "id": 1,
        "member_id": 1,
        "member_type": "designer",
        "username": "designer1",
        "email": "designer@example.com"
      },
      {
        "id": 2,
        "member_id": 2,
        "member_type": "marketer",
        "username": "marketer1",
        "email": "marketer@example.com"
      }
    ],
    "errors": []
  }
}
```

---

### 2. Get Team Members for Order
**Endpoint:** `GET /api/admin/product-orders/{orderId}/team`

**Headers:**
```
Authorization: Bearer {admin_token}
Accept-Language: en|ar
```

**Response:**
```json
{
  "success": true,
  "message": "Team members retrieved successfully",
  "data": [
    {
      "id": 1,
      "member_id": 1,
      "member_type": "designer",
      "username": "designer1",
      "email": "designer@example.com",
      "phone": "+1234567890",
      "created_at": "2026-05-25 12:00:00"
    },
    {
      "id": 2,
      "member_id": 2,
      "member_type": "marketer",
      "username": "marketer1",
      "email": "marketer@example.com",
      "phone": "+0987654321",
      "created_at": "2026-05-25 12:05:00"
    }
  ]
}
```

---

### 3. Remove Team Member from Order
**Endpoint:** `DELETE /api/admin/product-orders/{orderId}/team/{teamMemberId}`

**Headers:**
```
Authorization: Bearer {admin_token}
Accept-Language: en|ar
```

**Response:**
```json
{
  "success": true,
  "message": "Team member removed successfully"
}
```

---

## Workflow

### Step 1: Create Strategy Order
Client creates a strategy order (e.g., Order #7)

### Step 2: Admin Adds Team Members
Admin assigns designers and marketers to the entire order:
```bash
POST /api/admin/product-orders/7/team
{
  "team_members": [
    {"member_id": 1, "member_type": "designer"},
    {"member_id": 2, "member_type": "marketer"}
  ]
}
```

### Step 3: Create Posts
When creating posts for this order, the team members are automatically available:
- Designer can create/edit posts
- Marketer can create/edit posts
- All posts in the order share the same team

### Step 4: View Team Members
Check who's assigned to the order:
```bash
GET /api/admin/product-orders/7/team
```

### Step 5: Remove Team Member (if needed)
```bash
DELETE /api/admin/product-orders/7/team/1
```

---

## Benefits

1. **Efficiency:** Add team members once for the entire strategy
2. **Consistency:** Same team works on all posts in the strategy
3. **Flexibility:** Can add/remove team members at the order level
4. **Clarity:** Easy to see who's working on which strategy

---

## Database Structure

### Table: `product_order_team_members`
```sql
- id (primary key)
- product_order_id (foreign key to product_orders)
- member_id (ID of designer or marketer)
- member_type (enum: 'designer', 'marketer')
- created_at
- updated_at
- UNIQUE constraint on (product_order_id, member_id, member_type)
```

---

## Validation Rules

1. **Only Strategy Orders:** Team members can only be added to orders with `product_role = 'strategy'`
2. **Valid Member Types:** Only `designer` or `marketer` allowed
3. **Member Must Exist:** The designer/marketer ID must exist in the database
4. **No Duplicates:** Cannot add the same member twice to the same order

---

## Error Handling

### Invalid Order Type
```json
{
  "success": false,
  "message": "Only strategy orders can have team members"
}
```

### Member Not Found
```json
{
  "success": false,
  "message": "Team members added successfully",
  "data": {
    "added": [],
    "errors": ["Member ID 999 of type designer not found"]
  }
}
```

### Duplicate Assignment
```json
{
  "success": false,
  "message": "Team members added successfully",
  "data": {
    "added": [],
    "errors": ["Member already assigned to this order"]
  }
}
```

---

## Migration from Old System

If you have existing `post_team_members` data, you can migrate it to the new system:

1. Identify all posts belonging to each order
2. Get unique team members from those posts
3. Add them to the order using the new endpoint
4. (Optional) Clean up old `post_team_members` records

---

## Testing in Postman

1. Import the updated collection
2. Login as Admin
3. Navigate to "Admin - Product Orders"
4. Use the three new endpoints:
   - "Add Team Members to Order"
   - "Get Team Members for Order"
   - "Remove Team Member from Order"

---

## Notes

- Team members are assigned at the **order level**, not post level
- All posts in the order automatically have access to these team members
- Removing a team member from the order affects all posts in that order
- This system is only for **strategy orders** (not one-time products)
