# Middleware Localization Fix - 401 Errors

## Problem
الـ 401 error messages كانت بالإنجليزي دائماً حتى لو الـ `Accept-Language: ar`

## Root Cause
الـ **Authentication Middleware** كان بيرجع رسائل hardcoded بدون استخدام الترجمة.

## Files Fixed

### 1. ✅ MultiGuard Middleware
**File:** `app/Http/Middleware/MultiGuard.php`

**Before:**
```php
return response()->json(['status' => false, 'message' => 'Unauthenticated'], 401);
```

**After:**
```php
return response()->json(['status' => false, 'message' => __('messages.unauthenticated')], 401);
```

### 2. ✅ Admin Middleware
**File:** `app/Http/Middleware/Admin.php`

**Updated Messages:**
- `'Not authorized'` → `__('messages.not_authorized')`
- `'Token is Invalid'` → `__('messages.token_invalid')`
- `'Token is Expired'` → `__('messages.token_expired')`
- `'Authorization Token not found'` → `__('messages.token_not_found')`

### 3. ✅ Client Middleware
**File:** `app/Http/Middleware/Client.php`

**Updated Messages:**
- `'Not authorized'` → `__('messages.not_authorized')`
- `'Token is Invalid'` → `__('messages.token_invalid')`
- `'Token is Expired'` → `__('messages.token_expired')`
- `'Authorization Token not found'` → `__('messages.token_not_found')`

### 4. ✅ Exception Handler
**File:** `app/Exceptions/Handler.php`

**Updated:**
```php
'message' => 'Unauthenticated.' → 'message' => __('messages.unauthenticated')
```

## Translation Keys Added

### English (`lang/en/messages.php`)
```php
'unauthenticated' => 'Unauthenticated',
'not_authorized' => 'Not authorized',
'token_invalid' => 'Token is Invalid',
'token_expired' => 'Token is Expired',
'token_not_found' => 'Authorization Token not found',
```

### Arabic (`lang/ar/messages.php`)
```php
'unauthenticated' => 'غير مصادق عليه',
'not_authorized' => 'غير مصرح',
'token_invalid' => 'الرمز غير صحيح',
'token_expired' => 'الرمز منتهي الصلاحية',
'token_not_found' => 'رمز التفويض غير موجود',
```

## Testing

### Test 401 Errors with English
```bash
# Request without token
curl -X GET https://user.bareqq.com/api/client/client-invoices \
  -H "Accept-Language: en" \
  -H "API-Password: Nf:upZTg^7A?Hj"

# Response
{
    "status": false,
    "message": "Unauthenticated",
    "code": 401
}
```

### Test 401 Errors with Arabic
```bash
# Request without token
curl -X GET https://user.bareqq.com/api/client/client-invoices \
  -H "Accept-Language: ar" \
  -H "API-Password: Nf:upZTg^7A?Hj"

# Response
{
    "status": false,
    "message": "غير مصادق عليه",
    "code": 401
}
```

### Test Invalid Token with Arabic
```bash
curl -X GET https://user.bareqq.com/api/client/client-invoices \
  -H "Accept-Language: ar" \
  -H "API-Password: Nf:upZTg^7A?Hj" \
  -H "Authorization: Bearer invalid_token"

# Response
{
    "status": false,
    "message": "الرمز غير صحيح"
}
```

### Test Expired Token with Arabic
```bash
# With expired token
{
    "status": false,
    "message": "الرمز منتهي الصلاحية"
}
```

## All Error Scenarios Now Localized

### 1. No Token (401)
- **EN:** "Unauthenticated"
- **AR:** "غير مصادق عليه"

### 2. Invalid Token (400/401)
- **EN:** "Token is Invalid"
- **AR:** "الرمز غير صحيح"

### 3. Expired Token (400/401)
- **EN:** "Token is Expired"
- **AR:** "الرمز منتهي الصلاحية"

### 4. Token Not Found (400/401)
- **EN:** "Authorization Token not found"
- **AR:** "رمز التفويض غير موجود"

### 5. Wrong User Type (400/403)
- **EN:** "Not authorized"
- **AR:** "غير مصرح"

## Postman Testing

في الـ Postman Collection:

1. **Remove or use invalid token**
2. **Set `Accept-Language: ar`**
3. **Send request**
4. **Check response message** - سيكون بالعربي! ✅

### Example in Postman:
```
GET /api/client/client-invoices
Headers:
  Accept-Language: ar
  API-Password: Nf:upZTg^7A?Hj
  Authorization: Bearer invalid_or_no_token

Response:
{
    "status": false,
    "message": "غير مصادق عليه",
    "code": 401
}
```

## Summary

✅ **All authentication errors now respect `Accept-Language` header**
✅ **Middleware updated**
✅ **Exception handler updated**
✅ **Translation keys added**
✅ **Works with all endpoints in Postman collection**

الآن **جميع رسائل الـ 401 و 403 بتترجم تلقائياً** بناءً على لغة الـ request! 🎉
