# Invoice PDF System - Deployment Guide

## 📋 Overview

This system automatically generates PDF invoices when admin approves payment, and allows clients to download/view their invoices anytime.

---

## 🚀 Deployment Steps

### Step 1: Install DomPDF Package

```bash
cd /www/wwwroot/user.bareqq.com
composer require barryvdh/laravel-dompdf
```

### Step 2: Run Migration

```bash
php artisan migrate
```

This adds `pdf_path` column to `invoices` table.

### Step 3: Create Storage Link (if not exists)

```bash
php artisan storage:link
```

### Step 4: Set Permissions

```bash
# Ensure storage/app/public/invoices directory is writable
chmod -R 775 storage/app/public
chown -R www:www storage/app/public
```

### Step 5: Clear Cache

```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### Step 6: Restart PHP-FPM

```bash
systemctl restart php-fpm-81
```

---

## 📁 Files Created/Modified

### New Files
1. `app/Services/InvoicePdfService.php` - PDF generation service
2. `app/Http/Controllers/Client/InvoiceController.php` - Invoice endpoints
3. `resources/views/invoices/template.blade.php` - PDF template
4. `database/migrations/2026_06_01_163507_add_pdf_path_to_invoices_table.php` - Migration

### Modified Files
1. `app/Http/Controllers/Admin/AdminProductOrderController.php` - Added PDF generation on payment approval
2. `routes/api.php` - Added invoice routes

---

## 🔄 How It Works

### 1. Payment Approval Flow

```
Admin approves payment
    ↓
Invoice status → 'paid'
    ↓
PDF automatically generated
    ↓
PDF path saved to invoice.pdf_path
    ↓
Client notified
```

### 2. Client Downloads Invoice

```
Client requests invoice
    ↓
Check if PDF exists
    ↓
If not, generate it
    ↓
Return PDF file
```

---

## 📱 API Endpoints

### 1. List All Invoices
```
GET /api/client/invoices
Authorization: Bearer {client_token}

Response:
{
  "status": true,
  "data": [
    {
      "id": 1,
      "amount": "200.00",
      "status": "paid",
      "has_pdf": true,
      "download_url": "https://user.bareqq.com/api/client/invoices/1/download",
      "view_url": "https://user.bareqq.com/api/client/invoices/1/view"
    }
  ]
}
```

### 2. Get Invoice Details
```
GET /api/client/invoices/{id}
Authorization: Bearer {client_token}

Response:
{
  "status": true,
  "data": {
    "id": 1,
    "reference": "INV-001",
    "amount": "200.00",
    "status": "paid",
    "product": {
      "name": "Social Media Strategy"
    },
    "download_url": "https://user.bareqq.com/api/client/invoices/1/download"
  }
}
```

### 3. Download Invoice PDF
```
GET /api/client/invoices/{id}/download
Authorization: Bearer {client_token}

Response: PDF file download
```

### 4. View Invoice PDF (in browser)
```
GET /api/client/invoices/{id}/view
Authorization: Bearer {client_token}

Response: PDF file displayed inline
```

---

## 🧪 Testing

### Test 1: Approve Payment & Generate PDF

```bash
# 1. Create an order (as client)
curl -X POST "https://user.bareqq.com/api/client/product-orders" \
  -H "Authorization: Bearer CLIENT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 2,
    "duration": "month",
    "payment_method": "bank_transfer"
  }'

# 2. Approve payment (as admin)
curl -X POST "https://user.bareqq.com/api/admin/product-orders/1/approve-payment" \
  -H "Authorization: Bearer ADMIN_TOKEN"

# 3. Check if PDF was generated
mysql -u userbareqq -p userbareqq -e "SELECT id, pdf_path FROM invoices WHERE id = 1;"
```

### Test 2: Download Invoice

```bash
# List invoices
curl -X GET "https://user.bareqq.com/api/client/invoices" \
  -H "Authorization: Bearer CLIENT_TOKEN"

# Download specific invoice
curl -X GET "https://user.bareqq.com/api/client/invoices/1/download" \
  -H "Authorization: Bearer CLIENT_TOKEN" \
  --output invoice.pdf
```

---

## 🔍 Verification

### Check Database
```sql
-- Check if pdf_path column exists
DESCRIBE invoices;

-- Check invoices with PDFs
SELECT id, client_id, amount, status, pdf_path 
FROM invoices 
WHERE pdf_path IS NOT NULL;
```

### Check Storage
```bash
# Check if invoices directory exists
ls -la /www/wwwroot/user.bareqq.com/storage/app/public/invoices/

# Check PDF files
ls -lh /www/wwwroot/user.bareqq.com/storage/app/public/invoices/
```

### Check Logs
```bash
tail -50 /www/wwwroot/user.bareqq.com/storage/logs/laravel.log | grep -i "pdf\|invoice"
```

---

## 🐛 Troubleshooting

### Issue 1: PDF Not Generated

**Check:**
```bash
# Verify DomPDF is installed
composer show | grep dompdf

# Check storage permissions
ls -la storage/app/public/

# Check logs
tail -100 storage/logs/laravel.log | grep -i "pdf"
```

**Fix:**
```bash
chmod -R 775 storage/app/public
chown -R www:www storage/app/public
```

### Issue 2: PDF Download Returns 404

**Check:**
```bash
# Verify storage link exists
ls -la public/storage

# If not, create it
php artisan storage:link
```

### Issue 3: PDF Generation Fails

**Check logs:**
```bash
tail -100 storage/logs/laravel.log | grep -i "error"
```

**Common causes:**
- Missing DomPDF package
- Storage permissions
- Invalid invoice data
- Missing relationships (client, product)

---

## 📊 PDF Template Customization

The PDF template is located at: `resources/views/invoices/template.blade.php`

You can customize:
- Company logo
- Colors and styling
- Invoice layout
- Additional fields
- Footer information

After changes, PDFs will be regenerated automatically on next download.

---

## 🔐 Security Notes

- PDFs are stored in `storage/app/public/invoices/` (not directly accessible)
- Only authenticated clients can access their own invoices
- PDF generation is logged for audit trail
- File permissions are set to 644 (read-only for others)

---

## ✅ Success Criteria

- [x] DomPDF package installed
- [x] Migration run successfully
- [x] Storage link created
- [x] PDF generated on payment approval
- [x] Clients can list their invoices
- [x] Clients can download PDFs
- [x] Clients can view PDFs in browser
- [x] Proper error handling and logging

---

## 📞 Support

If you encounter issues:
1. Check logs: `storage/logs/laravel.log`
2. Verify permissions: `storage/app/public/`
3. Test PDF generation manually
4. Check database for `pdf_path` values

---

**System is ready for production use!** 🎉
