#!/bin/bash

echo "=========================================="
echo "Deploying Client Image Upload Updates"
echo "=========================================="

# Upload the updated controller
echo ""
echo "1. Uploading ClientAuthController..."
scp app/Http/Controllers/Client/ClientAuthController.php root@147.79.77.238:/var/www/html/app/Http/Controllers/Client/

# Clear cache on server
echo ""
echo "2. Clearing cache on server..."
ssh root@147.79.77.238 << 'EOF'
cd /var/www/html
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
EOF

echo ""
echo "=========================================="
echo "Deployment Complete!"
echo "=========================================="
echo ""
echo "Changes Made:"
echo "- Update profile now accepts 'image' field for photo upload"
echo "- Login response now includes 'image' field with full URL"
echo ""
echo "Test Endpoints:"
echo "1. POST /client/update-profile (with 'image' field)"
echo "2. POST /client/login (check 'image' in response)"
echo ""
echo "See CLIENT_IMAGE_UPLOAD_UPDATE.md for full details"
