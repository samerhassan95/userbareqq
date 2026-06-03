# Client Image Upload Update

## Changes Made

### 1. Update Profile Endpoint Enhancement
**Endpoint:** `POST {{base_url}}/client/update-profile`

#### Changes:
- Request field name: `image` (for uploading profile picture)
- Response field name: `image` (returns full URL)
- Increased max image size from 2MB to 10MB (10240 KB)
- Request now uses `multipart/form-data` instead of JSON
- Database column remains `photo` (no migration needed)

#### Request Body Parameters:
- **Content-Type:** `multipart/form-data`

| Field | Type | Required | Max Size | Description |
|-------|------|----------|----------|-------------|
| image | file | No | 10MB | Image file (jpeg, png, jpg, gif, svg) |
| username | string | No | - | Unique username |
| email | string | No | - | Valid email address |
| phone | string | No | - | Phone number |
| name | string | No | - | Client name |
| company_name | string | No | - | Company name |
| website | string | No | - | Website URL |
| address | string | No | - | Address |
| city | string | No | - | City |
| country | string | No | - | Country |

#### Response:
```json
{
  "status": true,
  "message": "Profile updated successfully.",
  "data": {
    "client": {
      "id": 1,
      "username": "johndoe",
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "1234567890",
      "image": "http://yourapp.com/client_photos/image123.jpg",
      "company_name": "ABC Corp",
      "website": "https://example.com",
      "address": "123 Main St",
      "city": "New York",
      "country": "USA"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJh...",
    "type": "client"
  }
}
```

### 2. Login Response Enhancement
**Endpoint:** `POST {{base_url}}/client/login`

#### Changes:
- Added `image` field to the login response containing the full URL of the client's profile photo
- Image will be `null` if no photo is uploaded

#### Request:
```json
{
  "email": "test@client.com",
  "password": "password123",
  "device_token": "optional_device_token_here"
}
```

#### Response:
```json
{
  "status": true,
  "message": "Login successful.",
  "token": "eyJ0eXAiOiJKV1QiLCJh...",
  "token_type": "bearer",
  "expires_in": 3600,
  "client": {
    "id": 1,
    "name": "John Doe",
    "email": "test@client.com",
    "role": "client",
    "phone": "1234567890",
    "image": "http://yourapp.com/client_photos/image123.jpg"
  },
  "subscriptions": [...]
}
```

### 3. Get Profile Endpoint
**Endpoint:** `GET {{base_url}}/client/profile`

#### Response uses `image` field:
```json
{
  "status": true,
  "message": "Profile retrieved successfully.",
  "data": {
    "client": {
      "id": 1,
      "username": "johndoe",
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "1234567890",
      "image": "http://yourapp.com/client_photos/image123.jpg",
      ...
    },
    "token": "eyJ0eXAiOiJKV1QiLCJh...",
    "type": "client"
  }
}
```

## All Endpoints Updated

The following endpoints now return `image` instead of `photo`:

| Endpoint | Method | Field in Response |
|----------|--------|-------------------|
| `/client/login` | POST | `client.image` |
| `/client/profile` | GET | `data.client.image` |
| `/client/update-profile` | POST | `data.client.image` |
| `/client/change-profile` | POST | `data.client.image` |
| `/client/change-email` | POST | `data.client.image` (after OTP verification) |
| `/client/verify-otp` | POST | `data.client.image` |

## Postman Collection Updates

The `Bareqq_Complete_API.postman_collection.json` has been updated with:

### Client Login Endpoint:
- Added `device_token` to request body example
- Response example shows `image` field (not `photo`)
- Added description explaining the image field

### Client Update Profile Endpoint:
- Changed from JSON to form-data format
- Request uses `image` field for file upload
- Response example shows `image` field (not `photo`)
- All optional fields are disabled by default in the collection
- Added description about max file size (10MB)

## Testing Guide

### 1. Test Update Profile with Image

**Using Postman:**
1. Import the updated collection
2. Open "Client - Profile" → "Update Profile"
3. Select the `image` field
4. Choose an image file (max 10MB)
5. Enable any other fields you want to update
6. Send the request

**Expected Response:**
- Status: 200
- `image` field should contain full URL in `data.client.image`
- Image should be accessible via the returned URL

### 2. Test Login Response

**Using Postman:**
1. Open "Client Auth" → "Client Login"
2. Use valid credentials
3. Send the request

**Expected Response:**
- Status: 200
- `client.image` field should contain full URL or null
- Image URL should be accessible if present

### 3. Test Get Profile

**Using Postman:**
1. Open "Client - Profile" → "Get Profile"
2. Ensure you have a valid token
3. Send the request

**Expected Response:**
- Status: 200
- `data.client.image` field should contain full URL or null

## Deployment Steps

1. Upload updated controller:
```bash
bash deploy_client_image_update.sh
```

Or manually:
```bash
scp app/Http/Controllers/Client/ClientAuthController.php root@147.79.77.238:/var/www/html/app/Http/Controllers/Client/
```

2. Clear cache on server:
```bash
ssh root@147.79.77.238
cd /var/www/html
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

3. Import updated Postman collection:
   - File: `Bareqq_Complete_API.postman_collection.json`
   - Import into Postman to test

## Files Modified

1. **app/Http/Controllers/Client/ClientAuthController.php**
   - Updated `register()` - increased photo max size to 10MB
   - Updated `updateProfile()` - request field `image`, response key `image`, max 10MB
   - Updated `changeProfile()` - increased photo max size to 10MB, response key `image`
   - Updated `getProfile()` - response key `image`
   - Updated `changeEmail()` - response key `image`
   - Updated `login()` - response key `image` 
   - Updated `respondWithToken()` - response key `image`
   - All responses now use `image` key instead of `photo`

2. **Bareqq_Complete_API.postman_collection.json**
   - Updated "Client Login" endpoint response example to use `image` field
   - Updated "Update Profile" endpoint to use form-data with `image` upload
   - Updated response examples to use `image` instead of `photo`
   - Added descriptions and example responses

## Important Notes

- **Database column remains `photo`** (no migration needed)
- **Request body uses `image`** key for file upload
- **All responses use `image`** key with full URL
- Existing photos will still work - no data migration required
- **Maximum file size increased to 10MB** (was 2MB)
- Supported formats: jpeg, png, jpg, gif, svg
- Images are stored in `public/client_photos/` directory
- All endpoints properly handle missing images (returns null)
- Frontend should now use `image` field instead of `photo`

## Image Size Limits

| Endpoint | Request Field | Response Field | Max Size | Previous Size |
|----------|--------------|----------------|----------|---------------|
| Register | photo | image | 10MB | 2MB |
| Update Profile | image | image | 10MB | 2MB |
| Change Profile | photo | image | 10MB | 2MB |
| Login | - | image | - | - |
| Get Profile | - | image | - | - |

## Frontend Integration

Update your frontend code to use `image` instead of `photo`:

```javascript
// Before
const photoUrl = response.data.client.photo;

// After
const imageUrl = response.data.client.image;
```


