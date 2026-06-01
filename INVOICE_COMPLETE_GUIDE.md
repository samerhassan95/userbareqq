# Invoice System - Complete Guide

## 📋 System Overview

The Bareqq platform has **TWO invoice systems**:

### 1. Old Invoice System (InvoiceController)
- Located at: `app/Http/Controllers/InvoiceController.php`
- Used for: Basic invoice management
- Endpoints: `/api/client/client-invoices`, `/api/client/invoice-details/{id}`

### 2. New PDF Invoice System (Client\InvoiceController)
- Located at: `app/Http/Controllers/Client/InvoiceController.php`
- Used for: PDF generation and download
- Endpoints: `/api/client/invoices`, `/api/client/invoices/{id}/download`

---

## 🔄 Complete Flow (Step by Step)

### Step 1: Client Creates Product Order

**Endpoint:** `POST /api/client/product-orders`

**Request:**
```json
{
  "product_id": 2,
  "duration": "month",
  "payment_method": "bank_transfer"
}
```

**Response:**
```json
{
  "status": true,
  "message": "Order created successfully",
  "data": {
    "order": {
      "id": 1,
      "client_id": 2,
      "product_id": 2,
      "status": "pending_payment",
      "total_price": "200.00",
      "duration": "month"
    },
    "invoice": {
      "id": 46,
      "client_id": 2,
      "product_id": 2,
      "amount": "200.00",
      "status": "unpaid",
      "payment_method": "bank_transfer",
      "gateway": "opay",
      "due_date": "2026-06-08",
      "payment_proof": null,
      "pdf_path": null
    }
  }
}
```

**What happens:**
- Order is created with status `pending_payment`
- Invoice is automatically created with status `unpaid`
- No PDF yet (`pdf_path` is `null`)

---

### Step 2: Client Uploads Payment Proof

**Endpoint:** `POST /api/client/invoices/{invoiceId}/upload-payment-proof`

**Request:**
```
Content-Type: multipart/form-data

Form Data:
- payment_proof: [file] (image or PDF, max 5MB)
```

**Response:**
```json
{
  "status": true,
  "message": "Payment proof uploaded successfully. Waiting for admin approval.",
  "data": {
    "invoice_id": 46,
    "payment_proof": "https://user.bareqq.com/payment_proofs/xyz.jpg"
  }
}
```

**What happens:**
- Payment proof file is uploaded to `storage/app/public/payment_proofs/`
- Invoice `payment_proof` field is updated
- Invoice status remains `unpaid` (waiting for admin approval)
- Admin receives notification (if implemented)

---

### Step 3: Admin Approves Payment

**Endpoint:** `POST /api/admin/product-orders/{orderId}/approve-payment`

**Request:** No body required

**Response:**
```json
{
  "status": true,
  "message": "Payment approved successfully",
  "data": {
    "order": {
      "id": 1,
      "status": "paid",
      "invoice": {
        "id": 46,
        "status": "paid",
        "pdf_path": "invoices/invoice_46_1717234567.pdf"
      }
    }
  }
}
```

**What happens:**
1. Invoice status changes to `paid`
2. **PDF is automatically generated** using `InvoicePdfService`
3. PDF is saved to `storage/app/public/invoices/invoice_46_timestamp.pdf`
4. Invoice `pdf_path` field is updated
5. Order status changes to `paid`
6. If strategy product → subscription is created
7. Client receives notification

---

### Step 4: Client Lists Invoices (NEW ENDPOINT)

**Endpoint:** `GET /api/client/invoices?per_page=20`

**Headers:**
```
Authorization: Bearer {client_token}
```

**Response:**
```json
{
  "status": true,
  "message": "Invoices retrieved successfully",
  "data": [
    {
      "id": 46,
      "reference": null,
      "amount": "200.00",
      "status": "paid",
      "payment_method": "bank_transfer",
      "gateway": "opay",
      "due_date": "2026-06-08",
      "created_at": "2026-06-01T13:51:57.000000Z",
      "product": {
        "id": 2,
        "name": "Social Media Strategy"
      },
      "has_pdf": true,
      "download_url": "https://user.bareqq.com/api/client/invoices/46/download",
      "view_url": "https://user.bareqq.com/api/client/invoices/46/view"
    }
  ],
  "pagination": {
    "total": 7,
    "per_page": 20,
    "current_page": 1,
    "last_page": 1
  }
}
```

**What happens:**
- Returns all invoices for authenticated client
- Includes `has_pdf` flag
- Includes `download_url` and `view_url` if PDF exists
- Supports pagination

---

### Step 5: Client Gets Invoice Details (NEW ENDPOINT)

**Endpoint:** `GET /api/client/invoices/{id}`

**Headers:**
```
Authorization: Bearer {client_token}
```

**Response:**
```json
{
  "status": true,
  "data": {
    "id": 46,
    "reference": null,
    "amount": "200.00",
    "status": "paid",
    "payment_method": "bank_transfer",
    "gateway": "opay",
    "payment_proof": "payment_proofs/xyz.jpg",
    "due_date": "2026-06-08",
    "created_at": "2026-06-01T13:51:57.000000Z",
    "product": {
      "id": 2,
      "name": "Social Media Strategy",
      "description": "Monthly social media management"
    },
    "has_pdf": true,
    "download_url": "https://user.bareqq.com/api/client/invoices/46/download",
    "view_url": "https://user.bareqq.com/api/client/invoices/46/view"
  }
}
```

---

### Step 6: Client Downloads PDF

**Endpoint:** `GET /api/client/invoices/{id}/download`

**Headers:**
```
Authorization: Bearer {client_token}
```

**Response:**
```
Content-Type: application/pdf
Content-Disposition: attachment; filename="invoice_000046.pdf"

[Binary PDF data - file downloads]
```

**What happens:**
- Checks if PDF exists
- If not, generates it automatically
- Returns PDF file for download
- Browser prompts to save file

---

### Step 7: Client Views PDF in Browser

**Endpoint:** `GET /api/client/invoices/{id}/view`

**Headers:**
```
Authorization: Bearer {client_token}
```

**Response:**
```
Content-Type: application/pdf
Content-Disposition: inline; filename="invoice_46.pdf"

[Binary PDF data - opens in browser]
```

**What happens:**
- Checks if PDF exists
- If not, generates it automatically
- Returns PDF file to display inline
- Browser opens PDF viewer

---

## 📱 All Available Endpoints

### Client Endpoints (Old System)
| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/client/client-invoices` | List invoices (old format) |
| GET | `/api/client/invoice-details/{id}` | Get invoice details (old format) |
| GET | `/api/client/all-client-invoices` | Get all invoices with cards |
| POST | `/api/client/invoices/{id}/upload-payment-proof` | Upload payment proof |

### Client Endpoints (New PDF System)
| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/client/invoices` | List invoices with PDF URLs |
| GET | `/api/client/invoices/{id}` | Get invoice details with PDF URLs |
| GET | `/api/client/invoices/{id}/download` | Download PDF file |
| GET | `/api/client/invoices/{id}/view` | View PDF in browser |

### Admin Endpoints
| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/admin/invoices` | List all invoices |
| POST | `/api/admin/invoices` | Create invoice |
| GET | `/api/admin/invoices/{id}` | Get invoice details |
| PUT | `/api/admin/invoices/{id}` | Update invoice |
| DELETE | `/api/admin/invoices/{id}` | Delete invoice |
| POST | `/api/admin/product-orders/{id}/approve-payment` | **Approve payment & generate PDF** |

---

## 🎯 Key Differences Between Old and New Systems

### Old System (`InvoiceController`)
- Returns invoice data as JSON
- No PDF generation
- Uses `/api/client/client-invoices` endpoint
- Response format: Simple invoice list

### New System (`Client\InvoiceController`)
- Generates PDF automatically on payment approval
- Provides download and view endpoints
- Uses `/api/client/invoices` endpoint
- Response format: Includes `download_url` and `view_url`

---

## 🔍 When is PDF Generated?

### Automatic Generation
1. **When admin approves payment** - PDF is generated immediately
2. **When client tries to download** - If PDF doesn't exist, it's generated on-demand

### Manual Regeneration
- Not currently implemented
- Can be added if needed

---

## 📂 File Storage

### Payment Proofs
- Location: `storage/app/public/payment_proofs/`
- Accessible via: `public/storage/payment_proofs/`
- URL: `https://user.bareqq.com/payment_proofs/filename.jpg`

### Invoice PDFs
- Location: `storage/app/public/invoices/`
- Accessible via: `public/storage/invoices/`
- Filename format: `invoice_{id}_{timestamp}.pdf`
- Example: `invoice_46_1717234567.pdf`

---

## 🧪 Testing Sequence

### Test 1: Complete Flow
```bash
# 1. Login as client
curl -X POST "https://user.bareqq.com/api/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"client@example.com","password":"password","role":"client"}'

# Save the token from response
CLIENT_TOKEN="eyJ0eXAiOiJKV1QiLCJhbGc..."

# 2. Create order
curl -X POST "https://user.bareqq.com/api/client/product-orders" \
  -H "Authorization: Bearer $CLIENT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"product_id":2,"duration":"month","payment_method":"bank_transfer"}'

# Note the invoice_id from response (e.g., 46)

# 3. Upload payment proof
curl -X POST "https://user.bareqq.com/api/client/invoices/46/upload-payment-proof" \
  -H "Authorization: Bearer $CLIENT_TOKEN" \
  -F "payment_proof=@payment.jpg"

# 4. Login as admin
curl -X POST "https://user.bareqq.com/api/admin/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Save admin token
ADMIN_TOKEN="eyJ0eXAiOiJKV1QiLCJhbGc..."

# 5. Approve payment (PDF generated here!)
curl -X POST "https://user.bareqq.com/api/admin/product-orders/1/approve-payment" \
  -H "Authorization: Bearer $ADMIN_TOKEN"

# 6. List invoices (as client)
curl -X GET "https://user.bareqq.com/api/client/invoices" \
  -H "Authorization: Bearer $CLIENT_TOKEN"

# 7. Download PDF
curl -X GET "https://user.bareqq.com/api/client/invoices/46/download" \
  -H "Authorization: Bearer $CLIENT_TOKEN" \
  --output invoice.pdf

# 8. View PDF (open in browser)
# Just visit: https://user.bareqq.com/api/client/invoices/46/view
# with Authorization header
```

### Test 2: Verify Database
```sql
-- Check invoice with PDF
SELECT id, client_id, amount, status, payment_proof, pdf_path 
FROM invoices 
WHERE id = 46;

-- Expected result:
-- status: 'paid'
-- payment_proof: 'payment_proofs/xyz.jpg'
-- pdf_path: 'invoices/invoice_46_1717234567.pdf'
```

### Test 3: Verify Files
```bash
# Check payment proof exists
ls -la /www/wwwroot/user.bareqq.com/storage/app/public/payment_proofs/

# Check PDF exists
ls -la /www/wwwroot/user.bareqq.com/storage/app/public/invoices/

# Check storage link
ls -la /www/wwwroot/user.bareqq.com/public/storage
```

---

## ⚠️ Important Notes

1. **PDF is NOT generated when order is created** - only after admin approval
2. **Client must upload payment proof before admin can approve**
3. **Only paid invoices have PDFs**
4. **Client can only access their own invoices**
5. **PDF is regenerated if file is missing**
6. **Old invoice endpoints still work** - they don't include PDF URLs

---

## 🐛 Common Issues

### Issue 1: "Method Not Allowed" on Login
**Problem:** Using GET instead of POST
**Solution:** Use POST method for `/api/login`

### Issue 2: Wrong URL in Response
**Problem:** URLs point to wrong domain
**Solution:** Fixed by using `route()` helper instead of `url()`

### Issue 3: PDF Not Generated
**Check:**
```bash
# Verify DomPDF installed
composer show | grep dompdf

# Check logs
tail -100 storage/logs/laravel.log | grep -i "pdf"

# Check permissions
ls -la storage/app/public/invoices/
```

### Issue 4: Download Returns 404
**Check:**
```bash
# Verify storage link exists
ls -la public/storage

# If not, create it
php artisan storage:link
```

---

## ✅ Deployment Checklist

- [ ] Install DomPDF: `composer require barryvdh/laravel-dompdf`
- [ ] Run migration: `php artisan migrate`
- [ ] Create storage link: `php artisan storage:link`
- [ ] Set permissions: `chmod -R 775 storage/app/public`
- [ ] Clear cache: `php artisan config:clear && php artisan route:clear`
- [ ] Restart PHP-FPM: `systemctl restart php-fpm-81`
- [ ] Test payment approval
- [ ] Test PDF download
- [ ] Verify PDF content
- [ ] Check logs for errors

---

**System Ready!** 🎉
