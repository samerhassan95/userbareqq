#!/bin/bash

# Invoice PDF System Deployment Script
# This script deploys the invoice PDF generation system to the server

echo "=========================================="
echo "Invoice PDF System Deployment"
echo "=========================================="
echo ""

# Step 1: Install DomPDF package
echo "Step 1: Installing DomPDF package..."
composer require barryvdh/laravel-dompdf
if [ $? -eq 0 ]; then
    echo "✓ DomPDF installed successfully"
else
    echo "✗ Failed to install DomPDF"
    exit 1
fi
echo ""

# Step 2: Run migration
echo "Step 2: Running migration..."
php artisan migrate --force
if [ $? -eq 0 ]; then
    echo "✓ Migration completed successfully"
else
    echo "✗ Migration failed"
    exit 1
fi
echo ""

# Step 3: Create storage link
echo "Step 3: Creating storage link..."
php artisan storage:link
echo "✓ Storage link created"
echo ""

# Step 4: Create invoices directory
echo "Step 4: Creating invoices directory..."
mkdir -p storage/app/public/invoices
echo "✓ Invoices directory created"
echo ""

# Step 5: Set permissions
echo "Step 5: Setting permissions..."
chmod -R 775 storage/app/public
chown -R www:www storage/app/public
echo "✓ Permissions set"
echo ""

# Step 6: Clear cache
echo "Step 6: Clearing cache..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
echo "✓ Cache cleared"
echo ""

# Step 7: Restart PHP-FPM
echo "Step 7: Restarting PHP-FPM..."
systemctl restart php-fpm-81
if [ $? -eq 0 ]; then
    echo "✓ PHP-FPM restarted successfully"
else
    echo "⚠ Could not restart PHP-FPM (may need sudo)"
fi
echo ""

# Verification
echo "=========================================="
echo "Verification"
echo "=========================================="
echo ""

# Check if pdf_path column exists
echo "Checking database..."
mysql -u userbareqq -p userbareqq -e "DESCRIBE invoices;" | grep pdf_path
if [ $? -eq 0 ]; then
    echo "✓ pdf_path column exists in invoices table"
else
    echo "✗ pdf_path column not found"
fi
echo ""

# Check storage directory
echo "Checking storage directory..."
if [ -d "storage/app/public/invoices" ]; then
    echo "✓ Invoices directory exists"
    ls -la storage/app/public/invoices/
else
    echo "✗ Invoices directory not found"
fi
echo ""

# Check if DomPDF is installed
echo "Checking DomPDF installation..."
composer show | grep dompdf
if [ $? -eq 0 ]; then
    echo "✓ DomPDF package is installed"
else
    echo "✗ DomPDF package not found"
fi
echo ""

echo "=========================================="
echo "Deployment Complete!"
echo "=========================================="
echo ""
echo "Next Steps:"
echo "1. Test payment approval to generate PDF"
echo "2. Test invoice download endpoint"
echo "3. Check logs: tail -50 storage/logs/laravel.log"
echo ""
echo "Test Commands:"
echo "# Approve a payment (as admin)"
echo "curl -X POST 'https://user.bareqq.com/api/admin/product-orders/{id}/approve-payment' \\"
echo "  -H 'Authorization: Bearer ADMIN_TOKEN'"
echo ""
echo "# List invoices (as client)"
echo "curl -X GET 'https://user.bareqq.com/api/client/invoices' \\"
echo "  -H 'Authorization: Bearer CLIENT_TOKEN'"
echo ""
echo "# Download invoice (as client)"
echo "curl -X GET 'https://user.bareqq.com/api/client/invoices/{id}/download' \\"
echo "  -H 'Authorization: Bearer CLIENT_TOKEN' --output invoice.pdf"
echo ""
