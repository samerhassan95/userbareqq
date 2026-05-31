#!/bin/bash

echo "🔍 Checking Notification Issues..."
echo ""

echo "📋 Recent notification-related logs:"
tail -100 /www/wwwroot/user.bareqq.com/storage/logs/laravel.log | grep -i "notification\|feedback\|approved" | tail -20

echo ""
echo "📊 Current notification counts:"
mysql -u userbareqq -p userbareqq -e "
SELECT 
  SUBSTRING_INDEX(notifiable_type, '\\\\', -1) as role,
  COUNT(*) as total
FROM notifications 
GROUP BY notifiable_type;"

echo ""
echo "📝 Latest notifications by type:"
mysql -u userbareqq -p userbareqq -e "
SELECT 
  SUBSTRING_INDEX(notifiable_type, '\\\\', -1) as role,
  title,
  LEFT(message, 40) as message,
  created_at
FROM notifications 
ORDER BY created_at DESC 
LIMIT 15;"

echo ""
echo "🔍 Checking if PostFeedback notifications are being triggered..."
echo "Run: POST /posts/{id}/feedback"
echo "Then check logs: tail -20 /www/wwwroot/user.bareqq.com/storage/logs/laravel.log | grep -i feedback"
