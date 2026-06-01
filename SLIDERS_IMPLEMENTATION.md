# Sliders API Implementation

## Overview
Unified slider endpoints with public read access and admin-only write access. All endpoints use `/api/sliders` path.

## Created Files

### Controllers
- `app/Http/Controllers/SliderController.php` - Unified controller for all slider operations

### Requests
- `app/Http/Requests/Admin/StoreSliderRequest.php` - Validation for creating sliders
- `app/Http/Requests/Admin/UpdateSliderRequest.php` - Validation for updating sliders

### Resources
- `app/Http/Resources/SliderResource.php` - API response formatting

### Updated Files
- `app/Models/Slider.php` - Simplified standalone slider model
- `app/Repositories/SliderRepository.php` - Repository methods
- `app/Repositories/SliderRepositoryInterface.php` - Repository interface
- `routes/api.php` - Added unified slider routes
- `lang/en/messages.php` - Added English translations
- `lang/ar/messages.php` - Added Arabic translations
- `Bareqq_Complete_API.postman_collection.json` - Added unified slider endpoints

## API Endpoints (Unified)

All endpoints use `/api/sliders` - authentication determines access level.

### Public Endpoints (No Authentication)

#### 1. Get All Sliders
```
GET /api/sliders
Accept-Language: en|ar
```

#### 2. Get Slider Details
```
GET /api/sliders/{id}
Accept-Language: en|ar
```

### Admin Endpoints (Requires Admin Authentication)

#### 3. Create Slider
```
POST /api/sliders
Authorization: Bearer {admin_token}
Accept-Language: en|ar
Content-Type: multipart/form-data

Body:
- image: file (required, jpeg/png/jpg/gif, max 2MB)
```

#### 4. Update Slider
```
POST /api/sliders/{id}
Authorization: Bearer {admin_token}
Accept-Language: en|ar
Content-Type: multipart/form-data

Body:
- image: file (required, jpeg/png/jpg/gif, max 2MB)

Note: Use POST (not PUT) for file uploads. No _method field needed.
```

#### 5. Delete Slider
```
DELETE /api/sliders/{id}
Authorization: Bearer {admin_token}
Accept-Language: en|ar
```

## Response Format

### Success Response
```json
{
    "status": true,
    "message": "Sliders retrieved successfully",
    "data": [
        {
            "id": 1,
            "image": "https://user.bareqq.com/sliders/abc123.jpg",
            "created_at": "2026-06-01 12:00:00",
            "updated_at": "2026-06-01 12:00:00"
        }
    ]
}
```

### Error Response
```json
{
    "status": false,
    "message": "Slider not found"
}
```

## Features

### Public Access
- ✅ View all sliders (no authentication)
- ✅ View slider details (no authentication)
- ✅ Localized responses (English/Arabic)

### Admin Access
- ✅ Create sliders with image upload
- ✅ Update slider images
- ✅ Delete sliders (automatic image cleanup)
- ✅ All CRUD operations

## Image Handling
- Images are stored in `public/sliders/`
- Automatic image deletion when slider is deleted or updated
- Maximum file size: 2MB
- Supported formats: JPEG, PNG, JPG, GIF
- Images are served via public URL: `https://user.bareqq.com/sliders/{filename}`

## Translations

### English Messages
- `sliders_retrieved_successfully`
- `slider_retrieved_successfully`
- `slider_created_successfully`
- `slider_updated_successfully`
- `slider_deleted_successfully`
- `slider_not_found`

### Arabic Messages
- `تم استرجاع السلايدرات بنجاح`
- `تم استرجاع السلايدر بنجاح`
- `تم إنشاء السلايدر بنجاح`
- `تم تحديث السلايدر بنجاح`
- `تم حذف السلايدر بنجاح`
- `السلايدر غير موجود`

## Postman Collection

The Postman collection has been updated with:
- **Sliders (Unified)** folder with 5 endpoints
- Public endpoints (no auth required)
- Admin endpoints (admin token required)
- Simplified update endpoint (no `_method` field)
- All using `/api/sliders` path

## Key Changes

### 1. Unified Routes
- **Before**: `/api/admin/sliders` and `/api/client/sliders`
- **After**: `/api/sliders` (token determines access)

### 2. Simplified Update
- **Before**: POST with `_method=PUT` field
- **After**: POST with just the image file
- No need for `_method` field anymore

### 3. Access Control
- Public: GET requests (read-only)
- Admin: POST, DELETE requests (write operations)
- Middleware handles authentication automatically

## Testing

### Public Tests (No Auth)
1. GET /api/sliders - List all
2. GET /api/sliders/1 - View details
3. Test with different languages (en/ar)

### Admin Tests (With Admin Token)
1. POST /api/sliders - Create with image
2. POST /api/sliders/1 - Update image
3. DELETE /api/sliders/1 - Delete slider
4. Verify old image is deleted on update/delete

## Database
Uses existing `sliders` table:
- `id` - Primary key
- `product_id` - Foreign key (nullable, not used)
- `image` - String (file path)
- `created_at` - Timestamp
- `updated_at` - Timestamp
