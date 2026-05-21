# Universal Login Setup Guide

## Overview
The Universal Login system allows all user roles (Admin, Client, Designer, Marketer) to login using a single endpoint `/api/login`.

## Test Users

### Existing Test Users
1. **Admin**: 
   - Username: `testadmin`
   - Phone: `01111111111`
   - Password: `password123`

2. **Client**: 
   - Email: `test@client.com`
   - Password: `password123`

### Create Designer Test User

```sql
-- Insert test designer
INSERT INTO designers (admin_id, username, email, phone, password, role, created_at, updated_at)
VALUES (
    1,
    'testdesigner',
    'test@designer.com',
    '01222222222',
    '$2y$10$wDs60XTB9yEJt3VTrfU6eeVCrpIS8x8GFEtV1qxWFVc3x4Cs5qDSa',
    'designer',
    NOW(),
    NOW()
);
```

### Create Marketer Test User

```sql
-- Insert test marketer
INSERT INTO marketers (admin_id, username, email, phone, password, role, created_at, updated_at)
VALUES (
    1,
    'testmarketer',
    'test@marketer.com',
    '01333333333',
    '$2y$10$wDs60XTB9yEJt3VTrfU6eeVCrpIS8x8GFEtV1qxWFVc3x4Cs5qDSa',
    'marketer',
    NOW(),
    NOW()
);
```

**Note**: All test users use the same password hash for `password123`

## Deployment to Production

### Step 1: Create Test Users in Database

```bash
# Connect to MySQL
mysql -u userbareqq -p userbareqq

# Run the INSERT commands above
```

### Step 2: Pull Latest Code

```bash
cd /www/wwwroot/user.bareqq.com
git pull origin main
```

### Step 3: Clear Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Step 4: Restart Apache

```bash
/etc/init.d/httpd restart
```

## API Usage

### Universal Login Endpoint

**POST** `/api/login`

**Headers:**
- `API-Password: Nf:upZTg^7A?Hj`
- `Content-Type: application/json`

**Request Body:**
```json
{
    "identifier": "testadmin",
    "password": "password123",
    "device_token": "optional_firebase_token",
    "device": "mobile"
}
```

**Response:**
```json
{
    "status": true,
    "code": 200,
    "message": "Login successful",
    "data": {
        "id": 1,
        "username": "testadmin",
        "phone": "01111111111",
        "password": null,
        "device_token": "fcm_device_token_here",
        "created_at": "2026-05-20T10:00:00.000000Z",
        "updated_at": "2026-05-20T10:00:00.000000Z",
        "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
        "type": "admin"
    }
}
```

### Login Examples for All Roles

#### 1. Admin Login
```json
{
    "identifier": "testadmin",
    "password": "password123"
}
```

#### 2. Client Login
```json
{
    "identifier": "test@client.com",
    "password": "password123"
}
```

#### 3. Designer Login
```json
{
    "identifier": "testdesigner",
    "password": "password123"
}
```

#### 4. Marketer Login
```json
{
    "identifier": "testmarketer",
    "password": "password123"
}
```

### Universal Logout Endpoint

**POST** `/api/logout`

**Headers:**
- `API-Password: Nf:upZTg^7A?Hj`
- `Authorization: Bearer {token}`

**Response:**
```json
{
    "status": true,
    "message": "Logout successful"
}
```

## Role Permissions

### Admin
- manage_products
- manage_orders
- manage_users
- manage_invoices
- view_analytics

### Client
- view_products
- create_orders
- view_orders
- manage_profile

### Designer
- view_projects
- upload_designs
- manage_portfolio

### Marketer
- view_campaigns
- manage_content
- view_analytics

## Testing with cURL

### Test Admin Login
```bash
curl -X POST "https://user.bareqq.com/api/login" \
-H "API-Password: Nf:upZTg^7A?Hj" \
-H "Content-Type: application/json" \
-d '{"identifier":"testadmin","password":"password123"}'
```

### Test Client Login
```bash
curl -X POST "https://user.bareqq.com/api/login" \
-H "API-Password: Nf:upZTg^7A?Hj" \
-H "Content-Type: application/json" \
-d '{"identifier":"test@client.com","password":"password123"}'
```

### Test Designer Login
```bash
curl -X POST "https://user.bareqq.com/api/login" \
-H "API-Password: Nf:upZTg^7A?Hj" \
-H "Content-Type: application/json" \
-d '{"identifier":"testdesigner","password":"password123"}'
```

### Test Marketer Login
```bash
curl -X POST "https://user.bareqq.com/api/login" \
-H "API-Password: Nf:upZTg^7A?Hj" \
-H "Content-Type: application/json" \
-d '{"identifier":"testmarketer","password":"password123"}'
```

## Postman Collection

The Postman collection `Bareqq_Complete_API.postman_collection.json` includes:

1. **Universal Login (All Roles)** folder with:
   - Universal Login (Admin example)
   - Universal Login - Client
   - Universal Logout

2. Auto-saves tokens to collection variables:
   - `admin_token`
   - `client_token`
   - `designer_token`
   - `marketer_token`

3. All endpoints use the `API-Password` header automatically

## Notes

- The `identifier` field accepts: username, email, or phone
- The system automatically detects the user role
- Tokens are JWT with configurable TTL (10 years for mobile devices)
- All responses follow the standard format: `{status, message, data}`
