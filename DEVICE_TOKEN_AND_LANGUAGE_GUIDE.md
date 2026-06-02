# Device Token & Language Support + All Meeting Notifications

## ✅ What Was Implemented

### 1. **Device Token Management Endpoint**
After login, users can update their Firebase device token anytime:

```bash
POST /api/profile/device-token
Authorization: Bearer {your_token}
Content-Type: application/json

{
  "device_token": "fcm_device_token_here"
}
```

**Response:**
```json
{
  "status": true,
  "message": "Device token updated successfully",
  "data": {
    "user_id": 1,
    "type": "App\\Models\\Client",
    "old_token": "token_starts_...",
    "new_token": "new_token_starts_..."
  }
}
```

**Use Cases:**
- User reinstalls app
- App loses notification permissions
- Device token expires
- Switching devices
- Testing Firebase notifications

---

### 2. **Language Preference Endpoint**
Users can set their notification language (English or Arabic):

```bash
POST /api/profile/language
Authorization: Bearer {your_token}
Content-Type: application/json

{
  "language": "ar"  // or "en"
}
```

**Response:**
```json
{
  "status": true,
  "message": "Language preference updated successfully",
  "data": {
    "user_id": 1,
    "type": "App\\Models\\Client",
    "old_language": "en",
    "new_language": "ar"
  }
}
```

---

### 3. **Get Notification Settings**
Check current device token and language settings:

```bash
GET /api/profile/notification-settings
Authorization: Bearer {your_token}
```

**Response:**
```json
{
  "status": true,
  "message": "Notification settings retrieved",
  "data": {
    "user_id": 1,
    "type": "App\\Models\\Client",
    "device_token": "token_first_20_chars...",
    "has_token": true,
    "language": "ar"
  }
}
```

---

## 📢 All Meeting Notifications (Now Implemented)

### **For All Roles: Admin, Client, Designer, Marketer**

#### **1. Meeting Created** ✅
- **Trigger:** Admin creates a new meeting
- **Recipients:** Client
- **Template:** `meeting_created`
- **EN:** "New Meeting Scheduled" → "Meeting {meeting_name} has been scheduled for {date} at {time}"
- **AR:** "اجتماع جديد مجدول" → "تم جدولة الاجتماع {meeting_name} لتاريخ {date} الساعة {time}"

#### **2. Meeting Status Updated (Generic)** ✅
- **Trigger:** Admin changes any status
- **Recipients:** Client
- **Template:** `meeting_status_updated`
- **EN:** "Meeting Status Updated" → "Meeting {meeting_name} status changed to: {status}"
- **AR:** "تحديث حالة الاجتماع" → "تم تغيير حالة الاجتماع {meeting_name} إلى: {status}"

#### **3. Meeting Confirmed** ✅
- **Trigger:** Admin sets status to "confirmed"
- **Recipients:** Client
- **Template:** `meeting_confirmed`
- **EN:** "Meeting Confirmed" → "Your meeting {meeting_name} has been confirmed for {date} at {time}"
- **AR:** "تأكيد الاجتماع" → "تم تأكيد اجتماعك {meeting_name} في {date} الساعة {time}"

#### **4. Meeting Completed** ✅
- **Trigger:** Admin sets status to "completed"
- **Recipients:** Client
- **Template:** `meeting_completed`
- **EN:** "Meeting Completed" → "Meeting {meeting_name} has been marked as completed"
- **AR:** "اكتمال الاجتماع" → "تم وضع علامة على الاجتماع {meeting_name} كمكتمل"

#### **5. Meeting Canceled** ✅
- **Trigger:** Admin sets status to "canceled"
- **Recipients:** Client
- **Template:** `meeting_canceled`
- **EN:** "Meeting Canceled" → "Meeting {meeting_name} has been canceled"
- **AR:** "إلغاء الاجتماع" → "تم إلغاء الاجتماع {meeting_name}"

#### **6. Team Member Added to Meeting** ✅
- **Trigger:** Admin adds a designer/marketer to meeting
- **Recipients:** The added team member (designer/marketer)
- **Template:** `meeting_team_member_added`
- **EN:** "Assigned to Meeting" → "You have been assigned to meeting {meeting_name} on {date}"
- **AR:** "تعيينك في اجتماع" → "تم تعيينك في الاجتماع {meeting_name} في {date}"

#### **7. Team Member Removed from Meeting** ✅
- **Trigger:** Admin removes a designer/marketer from meeting
- **Recipients:** The removed team member (designer/marketer)
- **Template:** `meeting_team_member_removed`
- **EN:** "Removed from Meeting" → "You have been removed from meeting {meeting_name}"
- **AR:** "إزالتك من اجتماع" → "تم إزالتك من الاجتماع {meeting_name}"

#### **8. Team Auto-Synced from Strategy** ✅
- **Trigger:** Admin calls sync endpoint OR strategy posts are assigned to meeting
- **Recipients:** All team members synced from posts (designers/marketers)
- **Template:** `meeting_team_synced`
- **EN:** "Team Auto-Synced" → "Team members have been automatically synced for meeting {meeting_name}"
- **AR:** "مزامنة الفريق التلقائية" → "تم مزامنة أعضاء الفريق تلقائياً للاجتماع {meeting_name}"

#### **9. Meeting Reminder** (Available for future use) ✅
- **Template:** `meeting_reminder`
- **EN:** "Meeting Reminder" → "Your meeting {meeting_name} starts at {time}"
- **AR:** "تذكير الاجتماع" → "اجتماعك {meeting_name} يبدأ الساعة {time}"

---

## 🌍 Language Support in Firebase

**How it works:**

1. When a client updates their language: `POST /profile/language`
2. The language preference is stored in the database
3. When any notification is sent:
   - System checks user's `language` field
   - If `ar` (Arabic) → sends `title_ar` + `message_ar`
   - If `en` or not set → sends `title` + `message` (English)
   - Firebase push includes `language` field in data payload
4. Client app can use this to display notifications in the right language

---

## 📋 Available Endpoints Summary

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/profile/device-token` | Update Firebase device token | All roles |
| POST | `/api/profile/language` | Update notification language (en/ar) | All roles |
| GET | `/api/profile/notification-settings` | View current settings | All roles |
| PUT | `/admin/meetings/{id}/status` | Change meeting status (triggers notifications) | Admin |
| POST | `/admin/meetings/{id}/team` | Add team members (triggers notifications) | Admin |
| DELETE | `/admin/meetings/{id}/team/{teamMemberId}` | Remove team member (triggers notifications) | Admin |
| POST | `/admin/meetings/{id}/team/sync-from-strategy` | Auto-sync team from posts (triggers notifications) | Admin |

---

## 💾 Database Changes

**Added to tables:**
- `clients.language` (VARCHAR, default='en')
- `admins.language` (VARCHAR, default='en')
- `designers.language` (VARCHAR, default='en')
- `marketers.language` (VARCHAR, default='en')
- `employees.language` (VARCHAR, default='en')

**New notification templates** (9 templates with EN + AR):
```
meeting_created
meeting_status_updated
meeting_confirmed
meeting_completed
meeting_canceled
meeting_team_member_added
meeting_team_member_removed
meeting_team_synced
meeting_reminder
```

---

## 🔧 Testing Examples

### Update Device Token
```bash
curl -X POST http://localhost:8000/api/profile/device-token \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Content-Type: application/json" \
  -d '{
    "device_token": "d1Lxd3tAbc123XyZ..."
  }'
```

### Switch to Arabic
```bash
curl -X POST http://localhost:8000/api/profile/language \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Content-Type: application/json" \
  -d '{
    "language": "ar"
  }'
```

### Check Settings
```bash
curl -X GET http://localhost:8000/api/profile/notification-settings \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
```

### Add Team Member to Meeting (triggers notification)
```bash
curl -X POST http://localhost:8000/api/admin/meetings/5/team \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Content-Type: application/json" \
  -d '{
    "members": [
      {"type": "designer", "id": 3},
      {"type": "marketer", "id": 7}
    ]
  }'
```

### Change Meeting Status (triggers notification)
```bash
curl -X PUT http://localhost:8000/api/admin/meetings/5/status \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Content-Type: application/json" \
  -d '{
    "status": "confirmed"
  }'
```

---

## 📝 Firebase Notification Payload

When Firebase notification is sent, it includes:

```json
{
  "notification": {
    "title": "اجتماع جديد مجدول",  // or English title
    "body": "تم جدولة الاجتماع 'Website Design' لتاريخ 2026-06-05 الساعة 14:00"
  },
  "data": {
    "meeting_id": "5",
    "notification_type": "meeting_created",
    "language": "ar",
    "click_action": "FLUTTER_NOTIFICATION_CLICK"
  }
}
```

---

## ✨ Key Features

✅ **Multi-Role Support:** Works for admin, client, designer, marketer, employee  
✅ **Bilingual:** English + Arabic notifications automatically  
✅ **Comprehensive Meeting Coverage:** All 8 meeting scenarios covered  
✅ **Easy Updates:** Can change device token and language anytime  
✅ **Database Persistent:** Both device token and language stored  
✅ **Firebase Ready:** Automatic push notifications with language awareness  
✅ **Template-Based:** Easy to update messages in NotificationTemplate table  
✅ **Backward Compatible:** Legacy notification methods still work  

---

## 🚀 Next Steps

1. **Update your mobile app** to:
   - Call `POST /profile/device-token` on app startup with FCM token
   - Call `POST /profile/language` when user changes language
   - Listen for notifications and display based on `language` field in payload

2. **Test all scenarios:**
   - Add team members → designers/marketers receive notifications in their language
   - Change meeting status → client receives status-specific notification
   - Use auto-sync → all synced team members get notified

3. **Monitor:** Check database for `language` field and `device_token` updates in user tables

---

