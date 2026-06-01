#!/bin/bash

echo "🚀 Notification System Deployment"
echo "======================================"
echo ""

# Step 1: Add missing notification templates
echo "📝 Step 1: Adding missing notification templates..."
mysql -u userbareqq -p userbareqq << 'EOF'
INSERT INTO notification_templates (type, title, message, title_ar, message_ar, created_at, updated_at) VALUES
('post_team_assigned', 'Post Assignment', 'You have been assigned to work on post: {title}', 'تعيين منشور', 'تم تعيينك للعمل على المنشور: {title}', NOW(), NOW()),
('post_feedback_received', 'New Feedback', 'New feedback on post: {title}', 'ملاحظات جديدة', 'ملاحظات جديدة على المنشور: {title}', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
  title = VALUES(title),
  message = VALUES(message),
  title_ar = VALUES(title_ar),
  message_ar = VALUES(message_ar),
  updated_at = NOW();
EOF

if [ $? -eq 0 ]; then
    echo "✅ Templates added successfully"
else
    echo "❌ Failed to add templates"
    exit 1
fi

echo ""

# Step 2: Check Firebase credentials
echo "🔥 Step 2: Checking Firebase credentials..."

FIREBASE_FILE="/www/wwwroot/user.bareqq.com/storage/firebase/bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json"

if [ -f "$FIREBASE_FILE" ]; then
    echo "✅ Firebase credentials file exists"
    chmod 600 "$FIREBASE_FILE"
    echo "✅ File permissions set (600)"
else
    echo "❌ Firebase credentials file NOT found at: $FIREBASE_FILE"
    echo ""
    echo "⚠️  IMPORTANT: You need to manually copy the Firebase credentials file:"
    echo "   1. Get the file: bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json"
    echo "   2. Copy to: /www/wwwroot/user.bareqq.com/storage/firebase/"
    echo "   3. Set permissions: chmod 600 /www/wwwroot/user.bareqq.com/storage/firebase/bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json"
    echo ""
    echo "   OR use: bash deploy_firebase_credentials.sh"
    echo ""
fi

echo ""

# Step 3: Clear cache
echo "🧹 Step 3: Clearing cache..."
cd /www/wwwroot/user.bareqq.com
php artisan config:clear
php artisan cache:clear
php artisan route:clear
echo "✅ Cache cleared"

echo ""

# Step 4: Restart PHP-FPM
echo "🔄 Step 4: Restarting PHP-FPM..."
systemctl restart php-fpm-82 2>/dev/null || systemctl restart php8.2-fpm 2>/dev/null || service php8.2-fpm restart 2>/dev/null
if [ $? -eq 0 ]; then
    echo "✅ PHP-FPM restarted"
else
    echo "⚠️  Please manually restart PHP-FPM"
fi

echo ""
echo "======================================"
echo "✨ Deployment Complete!"
echo "======================================"
echo ""

# Step 5: Verify setup
echo "🔍 Verification:"
echo ""

echo "1. Notification Templates:"
mysql -u userbareqq -p userbareqq -e "SELECT type, title_en FROM notification_templates WHERE type IN ('post_team_assigned', 'post_feedback_received');"

echo ""
echo "2. Firebase Credentials:"
if [ -f "$FIREBASE_FILE" ]; then
    echo "   ✅ File exists"
    ls -lh "$FIREBASE_FILE"
else
    echo "   ❌ File not found - Firebase push notifications will NOT work"
fi

echo ""
echo "======================================"
echo "📋 Next Steps:"
echo "======================================"
echo ""
echo "1. If Firebase credentials are missing, deploy them:"
echo "   bash deploy_firebase_credentials.sh"
echo ""
echo "2. Test the fixed scenarios:"
echo "   bash test_fixed_scenarios.sh"
echo ""
echo "3. Check notifications:"
echo "   mysql -u userbareqq -p userbareqq -e \"SELECT id, notifiable_type, title, created_at FROM notifications ORDER BY created_at DESC LIMIT 10;\""
echo ""
echo "4. Check logs:"
echo "   tail -20 /www/wwwroot/user.bareqq.com/storage/logs/laravel.log"
echo ""
