# Image Key Standardization - Complete Summary

## What Changed

All client authentication endpoints now use **`image`** as the key name in both requests and responses.

### Key Changes:
- **Request field:** `image` (for file uploads)
- **Response field:** `image` (for URLs)
- **Database field:** `photo` (unchanged - no migration needed)
- **Max file size:** 10MB (increased from 2MB)

---

## Affected Endpoints

### 1. POST `/client/login`
**Response includes:**
```json
{
  "client": {
    "image": "http://yourapp.com/client_photos/image123.jpg"
  }
}
```

### 2. GET `/client/profile`
**Response includes:**
```json
{
  "data": {
    "client": {
      "image": "http://yourapp.com/client_photos/image123.jpg"
    }
  }
}
```

### 3. POST `/client/update-profile`
**Request field:** `image` (file upload, max 10MB)  
**Response includes:**
```json
{
  "data": {
    "client": {
      "image": "http://yourapp.com/client_photos/image123.jpg"
    }
  }
}
```

### 4. POST `/client/change-profile`
**Response includes:**
```json
{
  "data": {
    "client": {
      "image": "http://yourapp.com/client_photos/image123.jpg"
    }
  }
}
```

### 5. POST `/client/change-email` (after OTP verification)
**Response includes:**
```json
{
  "data": {
    "client": {
      "image": "http://yourapp.com/client_photos/image123.jpg"
    }
  }
}
```

### 6. POST `/client/verify-otp`
**Response includes:**
```json
{
  "data": {
    "client": {
      "image": "http://yourapp.com/client_photos/image123.jpg"
    }
  }
}
```

---

## Controller Methods Updated

| Method | Change |
|--------|--------|
| `login()` | Returns `image` key |
| `getProfile()` | Returns `image` key |
| `updateProfile()` | Accepts `image` field, returns `image` key |
| `changeProfile()` | Returns `image` key |
| `changeEmail()` | Returns `image` key |
| `respondWithToken()` | Returns `image` key |

---

## Implementation Details

### How it works:
1. Database column remains `photo`
2. When returning responses, we:
   - Convert the model to array
   - Add `image` key with full URL
   - Remove `photo` key
   - Return the modified array

### Code pattern:
```php
$clientData = $client->makeHidden(['password', 'remember_token'])->toArray();
$clientData['image'] = $client->photo ? asset($client->photo) : null;
unset($clientData['photo']);
```

---

## Testing Checklist

- [x] Update controller to use `image` in all responses
- [x] Update Postman collection response examples
- [x] Increase max file size to 10MB
- [x] Verify JSON syntax
- [ ] Deploy to server
- [ ] Test all endpoints
- [ ] Update frontend to use `image` instead of `photo`

---

## Files Modified

1. ✅ `app/Http/Controllers/Client/ClientAuthController.php`
2. ✅ `Bareqq_Complete_API.postman_collection.json`
3. ✅ `CLIENT_IMAGE_UPLOAD_UPDATE.md`
4. ✅ `deploy_client_image_update.sh`

---

## Frontend Migration

**Before:**
```javascript
const photoUrl = user.photo;
```

**After:**
```javascript
const imageUrl = user.image;
```

---

## Deployment

```bash
bash deploy_client_image_update.sh
```

Then import the updated Postman collection and test all endpoints.

---

## Notes

- ✅ No database migration needed
- ✅ Backward compatible (existing photos still work)
- ✅ All responses return full URLs
- ✅ Null returned if no image uploaded
- ✅ Max size: 10MB
- ✅ Formats: jpeg, png, jpg, gif, svg
