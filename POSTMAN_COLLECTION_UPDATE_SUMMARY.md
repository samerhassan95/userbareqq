# Postman Collection Update Summary

## 📦 Updated File
`Bareqq_Complete_API.postman_collection.json`

## 🔄 Changes Made

### Before
The "Shared Notifications (All Roles)" folder had only **3 requests**:
- Get My Notifications (using `{{client_token}}`)
- Mark Notification as Read (using `{{client_token}}`)
- Mark All Notifications as Read (using `{{client_token}}`)

### After
The "Shared Notifications (All Roles)" folder now has **5 subfolders** with **14 total requests**:

#### 1. Admin Notifications (3 requests)
- ✅ Get My Notifications - Admin
- ✅ Mark Notification as Read - Admin
- ✅ Mark All Notifications as Read - Admin

#### 2. Client Notifications (3 requests)
- ✅ Get My Notifications - Client
- ✅ Mark Notification as Read - Client
- ✅ Mark All Notifications as Read - Client

#### 3. Designer Notifications (3 requests)
- ✅ Get My Notifications - Designer
- ✅ Mark Notification as Read - Designer
- ✅ Mark All Notifications as Read - Designer

#### 4. Marketer Notifications (3 requests)
- ✅ Get My Notifications - Marketer
- ✅ Mark Notification as Read - Marketer
- ✅ Mark All Notifications as Read - Marketer

#### 5. Error Cases (2 requests)
- ✅ Unauthorized - No Token
- ✅ Not Found - Invalid Notification ID

## 🎯 Benefits

### 1. Role-Specific Testing
Each role now has dedicated requests with the correct token variable:
- Admin uses `{{admin_token}}`
- Client uses `{{client_token}}`
- Designer uses `{{designer_token}}`
- Marketer uses `{{marketer_token}}`

### 2. Better Organization
Requests are grouped by role, making it easier to:
- Test specific roles
- Understand which token to use
- Navigate the collection

### 3. Error Testing
Added dedicated error case requests to test:
- Unauthorized access (no token)
- Invalid notification IDs

### 4. Clear Descriptions
Each request has a descriptive name and description explaining:
- What it does
- Which role it's for
- Expected behavior

## 📝 How to Use

### Step 1: Import Updated Collection
1. Open Postman
2. Go to File → Import
3. Select `Bareqq_Complete_API.postman_collection.json`
4. Choose "Replace" if prompted

### Step 2: Login as Each Role
Use the "Universal Login (All Roles)" folder to login as:
1. Admin → Saves to `{{admin_token}}`
2. Client → Saves to `{{client_token}}`
3. Designer → Saves to `{{designer_token}}`
4. Marketer → Saves to `{{marketer_token}}`

### Step 3: Test Notifications
Navigate to "Shared Notifications (All Roles)" folder and test:
1. **Admin Notifications** subfolder
2. **Client Notifications** subfolder
3. **Designer Notifications** subfolder
4. **Marketer Notifications** subfolder
5. **Error Cases** subfolder

## 🧪 Testing Workflow

### For Each Role:

1. **Login**
   ```
   POST /login
   Body: { "identifier": "...", "password": "..." }
   ```

2. **Get Notifications**
   ```
   GET /notifications
   Authorization: Bearer {{role_token}}
   ```

3. **Mark One as Read**
   ```
   POST /notifications/1/read
   Authorization: Bearer {{role_token}}
   ```

4. **Mark All as Read**
   ```
   POST /notifications/read-all
   Authorization: Bearer {{role_token}}
   ```

## ✅ Verification

After importing, verify:
- [ ] Collection has "Shared Notifications (All Roles)" folder
- [ ] Folder contains 5 subfolders
- [ ] Each role subfolder has 3 requests
- [ ] Error Cases subfolder has 2 requests
- [ ] Total of 14 requests in the notifications section
- [ ] Each request uses the correct token variable

## 🔗 Related Files

- `NOTIFICATIONS_TESTING_GUIDE.md` - Complete testing guide
- `NOTIFICATIONS_QUICK_CHECKLIST.md` - Quick reference
- `test_notifications_seed.sql` - Test data SQL script
- `NOTIFICATIONS_IMPLEMENTATION_SUMMARY.md` - Technical summary

## 📊 Request Breakdown

| Subfolder | Requests | Token Used |
|-----------|----------|------------|
| Admin Notifications | 3 | `{{admin_token}}` |
| Client Notifications | 3 | `{{client_token}}` |
| Designer Notifications | 3 | `{{designer_token}}` |
| Marketer Notifications | 3 | `{{marketer_token}}` |
| Error Cases | 2 | Various |
| **Total** | **14** | - |

## 🎨 Collection Structure

```
Bareqq Complete API Collection
└── Shared Notifications (All Roles)
    ├── Admin Notifications
    │   ├── Get My Notifications - Admin
    │   ├── Mark Notification as Read - Admin
    │   └── Mark All Notifications as Read - Admin
    ├── Client Notifications
    │   ├── Get My Notifications - Client
    │   ├── Mark Notification as Read - Client
    │   └── Mark All Notifications as Read - Client
    ├── Designer Notifications
    │   ├── Get My Notifications - Designer
    │   ├── Mark Notification as Read - Designer
    │   └── Mark All Notifications as Read - Designer
    ├── Marketer Notifications
    │   ├── Get My Notifications - Marketer
    │   ├── Mark Notification as Read - Marketer
    │   └── Mark All Notifications as Read - Marketer
    └── Error Cases
        ├── Unauthorized - No Token
        └── Not Found - Invalid Notification ID
```

## 🚀 Next Steps

1. **Import the updated collection** into Postman
2. **Run the migration** to add device_token fields
3. **Seed test data** using `test_notifications_seed.sql`
4. **Test each role** using the new organized structure
5. **Verify error cases** work as expected

## 💡 Tips

- Use Postman's **Collection Runner** to test all requests at once
- Use **Environments** to switch between dev/staging/production
- Save **Example Responses** for each request for documentation
- Use **Tests** tab to add automated assertions

## ✨ Summary

The Postman collection has been updated with a comprehensive, well-organized structure for testing notifications across all 5 roles (Admin, Client, Designer, Marketer, Employee). Each role now has dedicated requests with proper token variables, making testing more efficient and less error-prone.
