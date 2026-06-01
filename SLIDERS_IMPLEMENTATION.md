# Sliders API Implementation

## Overview
Complete implementation of standalone Sliders management system with Admin CRUD operations and public Client access. Sliders are simple image-only entities without product associations.

## Created Files

### Controllers
- `app/Http/Controllers/Admin/SliderController.php` - Admin CRUD operations
- `app/Http/Controllers/Client/SliderController.php` - Public read-only access

### Requests
- `app/Http/Requests/Admin/StoreSliderRequest.php` - Validation for creating sliders
- `app/Http/Requests/Admin/UpdateSliderRequest.php` - Validation for updating sliders

### Resources
- `app/Http/Resources/SliderResource.php` - API response formatting

### Updated Files
- `app/Models/Slider.php` - Simplified to standalone slider (removed product relationship)
- `app/Repositories/SliderRepository.php` - Updated methods for standalone sliders
- `app/Repositories/SliderRepositoryInterface.php` - Added update method signature
- `routes/admin.php` - Added admin slider routes
- `routes/client.php` - Added public slider routes
- `lang/en/messages.php` - Added English translations
- `lang/ar/messages.php` - Added Arabic translations
- `Bareqq_Complete_API.postman_collection.json` - Added slider endpoints

## API Endpoints

### Admin Endpoints (Requires Authentication)

#### 1. Get All Sliders
```
GET /api/admin/sliders
Authorization: Bearer {admin_token}
Accept-Language: en|ar
```

#### 2. Get Slider Details
```
GET /api/admin/sliders/{id}
Authorization: Bearer {admin_token}
Accept-Language: en|ar
```

#### 3. Create Slider
```
POST /api/admin/sliders
Authorization: Bearer {admin_token}
Accept-Language: en|ar
Content-Type: multipart/form-data

Body:
- image: file (required, jpeg/png/jpg/gif, max 2MB)
```

#### 4. Update Slider
```
POST /api/admin/sliders/{id}
Authorization: Bearer {admin_token}
Accept-Language: en|ar
Content-Type: multipart/form-data

Body:
- _method: PUT (required for file upload)
- image: file (optional, jpeg/png/jpg/gif, max 2MB)
```

#### 5. Delete Slider
```
DELETE /api/admin/sliders/{id}
Authorization: Bearer {admin_token}
Accept-Language: en|ar
```

### Client Endpoints (Public - No Authentication)

#### 1. Get All Sliders
```
GET /api/client/sliders
Accept-Language: en|ar
```

#### 2. Get Slider Details
```
GET /api/client/sliders/{id}
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
            "image": "https://user.bareqq.com/storage/sliders/image.jpg",
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

### Admin Features
- ✅ Create standalone sliders (image only)
- ✅ Upload slider images (automatic storage management)
- ✅ Update slider images
- ✅ Delete sliders (automatic image cleanup)
- ✅ View all sliders
- ✅ View individual slider details

### Client Features
- ✅ Public access to all sliders
- ✅ View slider details
- ✅ Localized responses (English/Arabic)
- ✅ No authentication required

## Image Handling
- Images are stored in `storage/app/public/sliders/`
- Automatic image deletion when slider is deleted or updated
- Maximum file size: 2MB
- Supported formats: JPEG, PNG, JPG, GIF
- Images are served via public URL: `storage/sliders/{filename}`

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
- **Admin - Sliders** folder with 5 endpoints
- **Client - Sliders** folder with 2 endpoints
- Pre-configured headers and authentication
- Example request bodies
- Detailed descriptions

## Testing

### Admin Tests
1. Create a slider with image
2. Get all sliders
3. Get specific slider details
4. Update slider image
5. Delete slider

### Client Tests
1. Get all sliders (no auth required)
2. Get specific slider details (no auth required)
3. Verify localization works (test with en and ar)

## Database
Uses existing `sliders` table:
- `id` - Primary key
- `product_id` - Foreign key (nullable, not used in current implementation)
- `image` - String (file path)
- `created_at` - Timestamp
- `updated_at` - Timestamp

**Note:** The `product_id` column exists in the database but is not used. Sliders are standalone entities.
