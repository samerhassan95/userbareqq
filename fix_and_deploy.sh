#!/bin/bash

echo "🔧 Fixing and deploying notification system..."
echo ""

# Step 1: Run new migration
echo "📦 Step 1: Adding Arabic columns to notification_templates..."
php artisan migrate
echo "✅ Migration complete"
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

# Step 4: Verify templates (without password in command)
echo "🔍 Step 4: Verifying notification templates..."
echo "Run this command manually to verify:"
echo "mysql -u root -p userbareqq -e \"SELECT type, title, title_ar FROM notification_templates;\""
echo ""

echo "✨ Deployment complete!"
echo ""
echo "📋 Next steps:"
echo "1. Verify templates were created"
echo "2. Test order creation: POST /client/product-orders"
echo "3. Test post creation: POST /admin/posts"
echo "4. Check notifications: GET /notifications"
echo ""
echo "📚 See IMPLEMENTATION_COMPLETE.md for full testing guide"
