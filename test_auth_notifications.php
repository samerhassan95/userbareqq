<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "🔐 Testing Authentication Guards\n\n";

// Get the token from your Postman request
echo "📝 To test, you need to provide the Bearer token from Postman\n";
echo "   Run this command with your token:\n";
echo "   php test_auth_notifications.php YOUR_TOKEN_HERE\n\n";

if ($argc < 2) {
    echo "❌ No token provided. Exiting.\n";
    exit(1);
}

$token = $argv[1];

echo "🔍 Testing token: " . substr($token, 0, 20) . "...\n\n";

// Try to authenticate with each guard
$guards = ['admin', 'client', 'designer', 'marketer'];

foreach ($guards as $guardName) {
    echo "Testing guard: {$guardName}\n";
    
    try {
        $guard = auth()->guard($guardName);
        
        // Try to get user from token
        $user = $guard->setToken($token)->user();
        
        if ($user) {
            echo "   ✅ Authenticated as: {$user->name} (ID: {$user->id})\n";
            echo "   Class: " . get_class($user) . "\n";
            
            // Check notifications for this user
            $notifications = App\Models\Notification::where('notifiable_id', $user->id)
                ->where('notifiable_type', get_class($user))
                ->latest()
                ->get();
            
            echo "   📊 Notifications: {$notifications->count()}\n";
            
            if ($notifications->count() > 0) {
                echo "   📋 Sample notification:\n";
                $first = $notifications->first();
                echo "      - Title: {$first->title}\n";
                echo "      - Created: {$first->created_at}\n";
            }
            
            echo "\n";
            break;
        } else {
            echo "   ❌ Not authenticated\n\n";
        }
    } catch (\Exception $e) {
        echo "   ⚠️  Error: {$e->getMessage()}\n\n";
    }
}

echo "\n💡 If no guard authenticated, check:\n";
echo "   1. Token is valid and not expired\n";
echo "   2. Token belongs to the correct guard (client/admin/designer/marketer)\n";
echo "   3. JWT configuration in config/jwt.php\n";
