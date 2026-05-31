#!/bin/bash

echo "🔧 Deploying Notification Fixes..."
echo ""

echo "📝 Changes:"
echo "  1. Fixed notifyAdmins() to get ALL admins (not just with device_token)"
echo "  2. Fixed AdminPostController to properly get designers/marketers from order"
echo "  3. Fixed PostFeedbackController notification type (feedback_added -> post_feedback_received)"
echo ""

echo "🧹 Clearing cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "✅ Cache cleared"
echo ""

echo "🔄 Restarting PHP-FPM..."
systemctl restart php8.2-fpm 2>/dev/null || service php8.2-fpm restart 2>/dev/null || echo "⚠️  Please manually restart PHP-FPM"

echo "✅ PHP-FPM restarted"
echo ""

echo "🧪 Testing..."
echo ""
echo "Please run these tests:"
echo ""
echo "1. Clear notifications table:"
echo "   mysql -u userbareqq -p userbareqq -e \"TRUNCATE TABLE notifications;\""
echo ""
echo "2. Create a new order (Client):"
echo "   POST /client/product-orders"
echo ""
echo "3. Check notifications:"
echo "   mysql -u userbareqq -p userbareqq -e \"SELECT notifiable_type, title FROM notifications;\""
echo ""
echo "Expected: Both Client AND Admin should have notifications now!"
echo ""
echo "4. Assign team to order (Admin):"
echo "   POST /admin/product-orders/{id}/team"
echo ""
echo "5. Create post (Admin):"
echo "   POST /admin/posts (with product_order_id)"
echo ""
echo "6. Check notifications again - Designer/Marketer should get 'New Post' notification"
echo ""
echo "7. Add feedback:"
echo "   POST /posts/{id}/feedback"
echo ""
echo "8. Check notifications - All roles should get 'New Feedback' notification"
echo ""
echo "✨ Deployment complete!"
