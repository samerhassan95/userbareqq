# Post Team Discussion API

## Overview
API endpoints للـ Admin لإضافة وإدارة فريق العمل (Designers & Marketers) على البوستات.

## Database Structure

### Table: `post_team_members`
```sql
- id
- post_id (foreign key to posts)
- member_id (polymorphic)
- member_type (App\Models\Designer or App\Models\Marketer)
- role (designer, marketer)
- created_at
- updated_at
```

## Endpoints

### 1. Add Team Members to Post
**POST** `/api/admin/posts/{id}/team`

إضافة designers و marketers للبوست عشان يشتغلوا عليه.

#### Request Body:
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

#### Response (201):
```json
{
  "success": true,
  "message": "Team members added successfully",
  "data": {
    "post_id": 5,
    "added_members": [
      {
        "id": 1,
        "member_id": 1,
        "member_type": "designer",
        "member_name": "Ahmed Designer"
      },
      {
        "id": 2,
        "member_id": 2,
        "member_type": "marketer",
        "member_name": "Sara Marketer"
      }
    ],
    "total_team_members": 2
  }
}
```

### 2. Get Team Members for Post
**GET** `/api/admin/posts/{id}/team`

جلب كل الفريق المسند للبوست.

#### Response (200):
```json
{
  "success": true,
  "message": "Team members retrieved successfully",
  "data": {
    "post_id": 5,
    "team_members": [
      {
        "id": 1,
        "member_id": 1,
        "member_type": "designer",
        "member_name": "Ahmed Designer",
        "member_email": "ahmed@example.com",
        "member_photo": "http://domain.com/designers/photo.jpg",
        "added_at": "2026-05-25 10:00:00"
      },
      {
        "id": 2,
        "member_id": 2,
        "member_type": "marketer",
        "member_name": "Sara Marketer",
        "member_email": "sara@example.com",
        "member_photo": "http://domain.com/marketers/photo.jpg",
        "added_at": "2026-05-25 10:05:00"
      }
    ],
    "total_count": 2
  }
}
```

### 3. Remove Team Member from Post
**DELETE** `/api/admin/posts/{postId}/team/{teamMemberId}`

إزالة عضو من فريق البوست.

#### Response (200):
```json
{
  "success": true,
  "message": "Team member removed successfully"
}
```

## Usage Examples

### Example 1: إضافة فريق للبوست

```bash
POST /api/admin/posts/5/team
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "team_members": [
    {
      "member_id": 1,
      "member_type": "designer"
    },
    {
      "member_id": 3,
      "member_type": "designer"
    },
    {
      "member_id": 2,
      "member_type": "marketer"
    }
  ]
}
```

### Example 2: جلب فريق البوست

```bash
GET /api/admin/posts/5/team
Authorization: Bearer {admin_token}
```

### Example 3: إزالة عضو من الفريق

```bash
DELETE /api/admin/posts/5/team/1
Authorization: Bearer {admin_token}
```

## Validation Rules

### Add Team Members:
- `team_members` - required, array
- `team_members.*.member_id` - required, integer
- `team_members.*.member_type` - required, in:designer,marketer

## Notes

1. **Unique Constraint**: نفس العضو مش هيتضاف مرتين للبوست
2. **Cascade Delete**: لو البوست اتمسح، كل الفريق بتاعه هيتمسح
3. **Polymorphic Relation**: الـ member ممكن يكون Designer أو Marketer
4. **Auto Skip**: لو العضو مش موجود أو متكرر، هيتخطى تلقائياً

## Integration with Posts

دلوقتي البوست فيه relationship جديد:

```php
$post = Post::with('teamMembers.member')->find(1);

foreach ($post->teamMembers as $teamMember) {
    echo $teamMember->member->name; // Designer or Marketer name
}
```

## Future Enhancements

- إضافة notifications للفريق لما يتضافوا
- Team chat/discussion system
- Task assignment لكل عضو
- Progress tracking
