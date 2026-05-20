# Social Media Credentials API

This document describes the backend API required by the mobile app **Credentials** feature. Clients can store and retrieve login credentials (username/email + password) per social platform.

## Base URL

```
https://back.codgoo.com/codgoo/public/api/
```

## Authentication

All endpoints require the same headers used by other client APIs:

| Header | Value |
|--------|--------|
| `Authorization` | `Bearer {client_token}` |
| `API-Password` | `Nf:upZTg^7A?Hj` |
| `Accept-Language` | `ar` or `en` (optional) |

The authenticated user is the **client** who owns the credentials.

---

## Supported platforms

| Platform key (`platform`) | Display name |
|---------------------------|--------------|
| `facebook` | Facebook |
| `tiktok` | TikTok |
| `instagram` | Instagram |
| `linkedin` | LinkedIn |
| `twitter` | Twitter (X) |

Platform keys are **lowercase** and must match exactly.

---

## Data model

### Credential object

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `id` | integer | No (response only) | Database ID |
| `platform` | string | Yes | One of: `facebook`, `tiktok`, `instagram`, `linkedin`, `twitter` |
| `username` | string | Yes | Email or username for the platform account |
| `password` | string | Yes | Account password (must be encrypted at rest on the server) |
| `updated_at` | string (ISO 8601) | No (response only) | Last update timestamp |

**Security requirements (backend):**

- Encrypt `password` before storing (e.g. Laravel `encrypt()` or dedicated secrets vault).
- Never log plain-text passwords.
- Return decrypted password only to the authenticated owner over HTTPS (same as other sensitive client data in this app).
- Consider masking password in list responses and exposing full value only on detail/edit if needed later.

**Uniqueness:** One credential record per `(client_id, platform)`.

---

## Endpoints

### 1. List all credentials

**GET** `client/credentials`

**Success response (200):**

```json
{
  "status": true,
  "message": "Credentials retrieved successfully",
  "data": [
    {
      "id": 1,
      "platform": "facebook",
      "username": "client@example.com",
      "password": "encrypted-or-plain-for-owner",
      "updated_at": "2026-05-20T10:30:00.000000Z"
    },
    {
      "id": 2,
      "platform": "instagram",
      "username": "my_instagram_handle",
      "password": "********",
      "updated_at": "2026-05-19T08:00:00.000000Z"
    }
  ]
}
```

**Empty list:** Return `"data": []` with `status: true`.

**Alternative shape (also supported by the app):**

```json
{
  "status": true,
  "data": {
    "credentials": [ /* same array as above */ ]
  }
}
```

Or keyed by platform:

```json
{
  "status": true,
  "data": {
    "facebook": { "id": 1, "username": "...", "password": "..." },
    "instagram": { "id": 2, "username": "...", "password": "..." }
  }
}
```

---

### 2. Create or upsert a single platform credential

**POST** `client/credentials`

Used when the client saves one platform from the edit screen.

**Request body (form-data or JSON — app sends `FormData`):**

| Field | Type | Required |
|-------|------|----------|
| `platform` | string | Yes |
| `username` | string | Yes |
| `password` | string | Yes |

**Example:**

```json
{
  "platform": "tiktok",
  "username": "user@mail.com",
  "password": "MySecurePass123"
}
```

**Behavior:**

- If a record exists for this client + `platform`, **update** it.
- Otherwise **create** a new record.

**Success response (200 or 201):**

```json
{
  "status": true,
  "message": "Credential saved successfully",
  "data": {
    "id": 3,
    "platform": "tiktok",
    "username": "user@mail.com",
    "password": "MySecurePass123",
    "updated_at": "2026-05-20T12:00:00.000000Z"
  }
}
```

**Validation errors (422):**

```json
{
  "status": false,
  "message": "Validation failed",
  "errors": {
    "platform": ["The platform field is required."],
    "username": ["The username field is required."]
  }
}
```

---

### 3. Bulk save (optional)

**POST** `client/credentials`

**Request body:**

```json
{
  "credentials": [
    {
      "platform": "facebook",
      "username": "fb@user.com",
      "password": "pass1"
    },
    {
      "platform": "linkedin",
      "username": "linkedin_user",
      "password": "pass2"
    }
  ]
}
```

Upsert each item in the array. The mobile app currently saves **one platform at a time**, but this bulk format is implemented on the client for future use.

---

### 4. Update a platform credential

**PUT** `client/credentials/{platform}`

**Example:** `PUT client/credentials/facebook`

**Request body:** Same as POST single credential.

```json
{
  "platform": "facebook",
  "username": "new@email.com",
  "password": "NewPassword456"
}
```

**Success response (200):** Same structure as POST success.

**404:** If no credential exists for that platform (app may fall back to POST on next save).

---

### 5. Delete a platform credential

**DELETE** `client/credentials/{platform}`

**Example:** `DELETE client/credentials/twitter`

**Request body:** Empty `{}` (app sends empty form map).

**Success response (200):**

```json
{
  "status": true,
  "message": "Credential deleted successfully"
}
```

**404:** No credential for that platform (optional; can still return success).

---

## Error responses

Follow existing API conventions:

| HTTP code | When |
|-----------|------|
| 401 | Invalid or missing token |
| 403 | Client not allowed |
| 404 | Resource not found |
| 422 | Validation error |
| 500 | Server error |

**Example error:**

```json
{
  "status": false,
  "message": "Unauthenticated."
}
```

---

## Suggested database schema (Laravel)

```sql
CREATE TABLE client_social_credentials (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id BIGINT UNSIGNED NOT NULL,
  platform VARCHAR(20) NOT NULL,
  username VARCHAR(255) NOT NULL,
  password TEXT NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY client_platform_unique (client_id, platform),
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);
```

**`platform` enum/check:** `facebook`, `tiktok`, `instagram`, `linkedin`, `twitter`

---

## Mobile app integration summary

| App action | HTTP call |
|------------|-----------|
| Open Credentials screen | `GET client/credentials` |
| Save platform form | `POST client/credentials` (new) or `PUT client/credentials/{platform}` (if `id` was returned earlier) |
| Clear platform | `DELETE client/credentials/{platform}` |

**Flutter files:**

- Endpoints: `lib/core/network/remote/end_points.dart`
- Data source: `lib/features/Credentials/data/dataSource/credentials_data_source.dart`
- UI entry: Menu + bottom navigation tab (replaced Products)

---

## Questions for backend

1. Should passwords be returned in GET responses, or only a placeholder with a separate “reveal” endpoint?
2. Is audit logging required when credentials are viewed or updated?
3. Should admins have read access to client credentials, or client-only?

Please confirm endpoint paths and response shapes before production deploy so the app can be tested end-to-end.
