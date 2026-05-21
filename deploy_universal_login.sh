#!/bin/bash

echo "=========================================="
echo "Deploying Universal Login Updates"
echo "=========================================="

# Navigate to project directory
cd /www/wwwroot/user.bareqq.com

# Pull latest changes
echo "Pulling latest code from GitHub..."
git pull origin main

# Clear Laravel caches
echo "Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Restart Apache
echo "Restarting Apache..."
/etc/init.d/httpd restart

echo ""
echo "=========================================="
echo "Deployment Complete!"
echo "=========================================="
echo ""
echo "Testing Universal Login..."
echo ""

# Test Admin Login
echo "1. Testing Admin Login..."
curl -X POST "https://user.bareqq.com/api/login" \
-H "API-Password: Nf:upZTg^7A?Hj" \
-H "Content-Type: application/json" \
-d '{"identifier":"testadmin","password":"password123"}' \
-s | python3 -m json.tool

echo ""
echo "2. Testing Client Login..."
curl -X POST "https://user.bareqq.com/api/login" \
-H "API-Password: Nf:upZTg^7A?Hj" \
-H "Content-Type: application/json" \
-d '{"identifier":"test@client.com","password":"password123"}' \
-s | python3 -m json.tool

echo ""
echo "=========================================="
echo "All tests completed!"
echo "=========================================="
