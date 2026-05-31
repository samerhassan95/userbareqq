#!/bin/bash

# Notification System Deployment Script
# Run this script to deploy the notification system

echo "🚀 Deploying Notification System..."
echo ""

# Step 1: Run migrations
echo "📦 Step 1: Running migrations..."
php artisan migrate
echo "✅ Migrations complete"
echo ""

# Step 2: Seed notification templates
echo "📝 Step 2: Seeding notification templates..."
php artisan db:seed --class=NotificationTemplatesSeeder
echo "✅ Templates seeded"
echo ""

# Step 3: Clear cache
echo "🧹 Step 3: Clearing cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
echo "✅ Cache cleared"
echo ""

# Step 4: Verify templates
echo "🔍 Step 4: Verifying notification templates..."
mysql -u root -p'Nf:upZTg^7A?Hj' userbareqq -e "SELECT type, title FROM notification_templates;"
echo ""

echo "✨ Deployment complete!"
echo ""
echo "📋 Next steps:"
echo "1. Test order creation: POST /client/product-orders"
echo "2. Test post creation: POST /admin/posts"
echo "3. Test feedback: POST /posts/{id}/feedback"
echo "4. Check notifications: GET /notifications"
echo ""
echo "📚 See IMPLEMENTATION_COMPLETE.md for full testing guide"
