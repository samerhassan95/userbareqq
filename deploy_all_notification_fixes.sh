#!/bin/bash

echo "🚀 Deploying All Notification Fixes..."
echo ""

echo "📝 Changes Made:"
echo "  1. ✅ Fixed notifyAdmins() - gets ALL admins"
echo "  2. ✅ Fixed AdminProductOrderController->assignTeam() - properly creates team members"
echo "  3. ✅ Fixed AdminPostController->store() - properly notifies team from order"
echo "  4. ✅ Fixed AdminPostController->addTeamMembers() - correct notification type"
echo "  5. ✅ Fixed PostFeedbackController - correct notification type"
echo ""

echo "🧹 Clearing cache..."
php artisan config:clear
php artisan cache:clear  
php artisan route:clear
php artisan view:clear
echo "✅ Cache cleared"
echo ""

echo "🔄 Restarting PHP-FPM..."
systemctl restart php8.2-fpm 2>/dev/null || service php8.2-fpm restart 2>/dev/null
echo "✅ PHP-FPM restarted"
echo ""

echo "🧪 Testing post relationships..."
php test_post_relationships.php
echo ""

echo "📋 Next Steps:"
echo "1. Clear notifications: mysql -u userbareqq -p userbareqq -e \"TRUNCATE TABLE notifications;\""
echo "2. Run full test: sh test_all_notifications.sh"
echo ""
echo "✨ Deployment complete!"
