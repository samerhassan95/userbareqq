# Invoice PDF System - Complete Sequence

## 📋 Overview

This document explains the complete flow of the invoice PDF system from order creation to PDF download.

---

## 🔄 Complete Flow Sequence

### Step 1: Client Creates Order
```
Client → POST /api/client/product-orders
{
  "product_id": 2,
  "duration": "month",
  "payment_method": "bank_transfer"
}

Response:
{
  "status": true,
  "data": {
    "order": {
      "id": 1,
      "status": "pending_payment"
    },
    "invoice": {
      "id": 46,
      "amount": "200.00",
      "status": "unpaid",
      "payment_proof": null,
      "pdf_path": null  ← No PDF yet
    }
  }
}
```

### Step 2: Client Uploads Payment Proof
```
Client → POST /api/client/invoices/{invoiceId}/upload-payment-proof
Content-Type: multipart/form-data

Form Data:
- payment_proof: [image/pdf file]

Response:
{
  "status": true,
  "message": "Payment proof uploaded successfully",
  "data": {
    "invoice": {
      "id": 46,
      "payment_proof": "attachments/xyz.jpg",
      "status": "unpaid"  ← Still unpaid, waiting admin approval
    }
  }
}
```

### Step 3: Admin Approves Payment
```
Admin → POST /api/admin/product-orders/{orderId}/approve-payment

What happens:
1. Invoice status → "paid"
2. PDF is automatically generated
3. PDF path saved to invoice.pdf_path
4. Order status → "paid"
5. If strategy product → subscription created
6. Client receives notification

Response:
{
  "status": true,
  "message": "Payment approved successfully",
  "data": {
    "order": {
      "id": 1,
      "status": "paid"
    },
    "invoice": {
      "id": 46,
      "status": "paid",
      "pdf_path": "invoices/invoice_46_1234567890.pdf"  ← PDF generated!
    }
  }
}
```

### Step 4: Client Lists Invoices
```
Client → GET /api/client/invoices?per_page=20

Response:
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

### Step 5: Client Gets Invoice Details
```
Client → GET /api/client/invoices/{id}

Response:
{
  "status": true,
  "data": {
    "id": 46,
    "reference": null,
    "amount": "200.00",
    "status": "paid",
    "payment_method": "bank_transfer",
    "gateway": "opay",
    "payment_proof": "attachments/xyz.jpg",
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

### Step 6: Client Downloads PDF
```
Client → GET /api/client/invoices/{id}/download

Response: PDF file download
Content-Type: application/pdf
Content-Disposition: attachment; filename="invoice_000046.pdf"

[Binary PDF data]
```

### Step 7: Client Views PDF in Browser
```
Client → GET /api/client/invoices/{id}/view

Response: PDF displayed inline
Content-Type: application/pdf
Content-Disposition: inline; filename="invoice_46.pdf"

[Binary PDF data - opens in browser]
```

---

## 🎯 Key Points

### When is PDF Generated?
- **Automatically** when admin approves payment
- **On-demand** if PDF doesn't exist when client tries to download/view

### PDF Storage Location
- Path: `storage/app/public/invoices/invoice_{id}_{timestamp}.pdf`
- Accessible via: `public/storage/invoices/invoice_{id}_{timestamp}.pdf`

### Invoice Status Flow
```
unpaid → (client uploads proof) → unpaid (waiting approval)
       → (admin approves) → paid (PDF generated)
```

### Order Status Flow
```
pending_payment → (admin approves) → paid → in_progress → delivered
```

---

## 📱 API Endpoints Summary

### Client Endpoints
| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/client/invoices` | List all invoices |
| GET | `/api/client/invoices/{id}` | Get invoice details |
| GET | `/api/client/invoices/{id}/download` | Download PDF |
| GET | `/api/client/invoices/{id}/view` | View PDF in browser |
| POST | `/api/client/invoices/{id}/upload-payment-proof` | Upload payment proof |

### Admin Endpoints
| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/api/admin/product-orders/{id}/approve-payment` | Approve payment & generate PDF |
| PUT | `/api/admin/product-orders/{id}/status` | Update order status |

---

## 🔍 Database Changes

### invoices table
```sql
-- New column added
pdf_path VARCHAR(255) NULL

-- Example data
id: 46
client_id: 2
amount: 200.00
status: paid
payment_proof: attachments/xyz.jpg
pdf_path: invoices/invoice_46_1234567890.pdf  ← New!
```

---

## 🧪 Testing Sequence

### Test 1: Complete Flow
```bash
# 1. Client creates order
curl -X POST "https://user.bareqq.com/api/client/product-orders" \
  -H "Authorization: Bearer CLIENT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"product_id": 2, "duration": "month", "payment_method": "bank_transfer"}'

# 2. Client uploads payment proof
curl -X POST "https://user.bareqq.com/api/client/invoices/46/upload-payment-proof" \
  -H "Authorization: Bearer CLIENT_TOKEN" \
  -F "payment_proof=@payment.jpg"

# 3. Admin approves payment (PDF generated here)
curl -X POST "https://user.bareqq.com/api/admin/product-orders/1/approve-payment" \
  -H "Authorization: Bearer ADMIN_TOKEN"

# 4. Client lists invoices
curl -X GET "https://user.bareqq.com/api/client/invoices" \
  -H "Authorization: Bearer CLIENT_TOKEN"

# 5. Client downloads PDF
curl -X GET "https://user.bareqq.com/api/client/invoices/46/download" \
  -H "Authorization: Bearer CLIENT_TOKEN" \
  --output invoice.pdf
```

### Test 2: Verify PDF Generation
```sql
-- Check if PDF was generated
SELECT id, client_id, amount, status, pdf_path 
FROM invoices 
WHERE id = 46;

-- Expected result:
-- pdf_path should NOT be NULL after admin approval
```

### Test 3: Check Storage
```bash
# Check if PDF file exists
ls -la /www/wwwroot/user.bareqq.com/storage/app/public/invoices/

# Should see files like:
# invoice_46_1234567890.pdf
```

---

## ⚠️ Important Notes

1. **PDF is NOT generated when order is created** - only after admin approval
2. **Client cannot download PDF until payment is approved**
3. **If PDF generation fails, order is still approved** (logged as error)
4. **PDF is regenerated if file is missing** when client tries to download
5. **Only the invoice owner (client) can download their PDF**

---

## 🐛 Troubleshooting

### Issue: PDF not generated after approval
```bash
# Check logs
tail -100 /www/wwwroot/user.bareqq.com/storage/logs/laravel.log | grep -i "pdf\|invoice"

# Check if DomPDF is installed
composer show | grep dompdf

# Check storage permissions
ls -la storage/app/public/invoices/
```

### Issue: Download returns 404
```bash
# Check if storage link exists
ls -la public/storage

# If not, create it
php artisan storage:link
```

### Issue: PDF shows wrong data
```bash
# Check invoice relationships
mysql -u userbareqq -p userbareqq -e "
SELECT i.id, i.client_id, i.product_id, c.name as client_name, p.name as product_name
FROM invoices i
LEFT JOIN clients c ON i.client_id = c.id
LEFT JOIN products p ON i.product_id = p.id
WHERE i.id = 46;"
```

---

## ✅ Success Checklist

- [ ] Client can create order
- [ ] Client can upload payment proof
- [ ] Admin can approve payment
- [ ] PDF is automatically generated on approval
- [ ] Client can list invoices with PDF URLs
- [ ] Client can download PDF
- [ ] Client can view PDF in browser
- [ ] PDF shows correct invoice data
- [ ] Only invoice owner can access PDF
- [ ] Logs show successful PDF generation

---

**System Flow Complete!** 🎉
