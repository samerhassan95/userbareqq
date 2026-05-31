#!/bin/bash

# Update SendsNotifications.php on server with correct code
cat > /www/wwwroot/user.bareqq.com/app/Traits/SendsNotifications.php << 'EOF'
<?php

namespace App\Traits;

use App\Services\FirebaseService;
use App\Repositories\NotificationRepository;
use Illuminate\Support\Facades\Log;

trait SendsNotifications
{
    /**
     * Send notification to a single user or multiple users
     */
    protected function sendNotification($users, string $title, string $message, string $type, array $data = [])
    {
        // Convert single user to array
        if (!is_array($users) && !($users instanceof \Illuminate\Support\Collection)) {
            $users = [$users];
        }

        $firebaseService = app(FirebaseService::class);
        $notificationRepo = app(NotificationRepository::class);

        foreach ($users as $user) {
            if (!$user) {
                Log::info("Skipping notification for null user", ['type' => $type]);
                continue;
            }

            try {
                // Always save to database
                $notificationRepo->createNotification(
                    $user,
                    $title,
                    $message,
                    $user->device_token ?? null,
                    $type,
                    $data
                );

                // Send Firebase push notification only if device_token exists
                if ($user->device_token) {
                    $firebaseService->sendNotification(
                        $user->device_token,
                        $title,
                        $message,
                        array_merge($data, ['notification_type' => $type])
                    );
                    
                    Log::info("Notification sent successfully", [
                        'user_id' => $user->id,
                        'type' => $type,
                        'title' => $title,
                        'has_device_token' => true
                    ]);
                } else {
                    Log::info("Notification saved to database (no device_token for push)", [
                        'user_id' => $user->id,
                        'type' => $type,
                        'title' => $title,
                        'has_device_token' => false
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Failed to send notification", [
                    'user_id' => $user->id,
                    'type' => $type,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Send notification to all admins
     */
    protected function notifyAdmins(string $title, string $message, string $type, array $data = [])
    {
        $admins = \App\Models\Admin::whereNotNull('device_token')->get();
        $this->sendNotification($admins, $title, $message, $type, $data);
    }

    /**
     * Get current authenticated user from any guard
     */
    protected function getCurrentUser()
    {
        foreach (['admin', 'client', 'designer', 'marketer'] as $guard) {
            if (auth()->guard($guard)->check()) {
                return auth()->guard($guard)->user();
            }
        }
        return null;
    }
}
EOF

echo "✅ SendsNotifications.php updated"

# Clear all caches
php /www/wwwroot/user.bareqq.com/artisan config:clear
php /www/wwwroot/user.bareqq.com/artisan cache:clear
php /www/wwwroot/user.bareqq.com/artisan route:clear
php /www/wwwroot/user.bareqq.com/artisan view:clear

echo "✅ Cache cleared"

# Restart PHP-FPM
systemctl restart php8.2-fpm 2>/dev/null || service php8.2-fpm restart 2>/dev/null || echo "⚠️  Please manually restart PHP-FPM"

echo "✅ PHP-FPM restart attempted"

echo ""
echo "🧪 Now test by creating an order and check:"
echo "1. Logs should show: 'Notification saved to database (no device_token for push)'"
echo "2. Database should have notifications:"
echo "   mysql -u userbareqq -p userbareqq -e \"SELECT id, notifiable_type, notifiable_id, title, is_read, created_at FROM notifications ORDER BY created_at DESC LIMIT 5;\""
