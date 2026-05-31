<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test with client ID 2 (who has notifications)
$client = App\Models\Client::find(2);

if (!$client) {
    echo "❌ Client 2 not found\n";
    exit(1);
}

echo "✅ Client found: {$client->name} (ID: {$client->id})\n";
echo "   Class: " . get_class($client) . "\n\n";

// Check notifications
$notifications = App\Models\Notification::where('notifiable_id', $client->id)
    ->where('notifiable_type', get_class($client))
    ->latest()
    ->get();

echo "📊 Notifications found: " . $notifications->count() . "\n\n";

if ($notifications->count() > 0) {
    echo "📋 Notification details:\n";
    foreach ($notifications as $notification) {
        echo "   ID: {$notification->id}\n";
        echo "   Title: {$notification->title}\n";
        echo "   Message: {$notification->message}\n";
        echo "   Notifiable Type: {$notification->notifiable_type}\n";
        echo "   Notifiable ID: {$notification->notifiable_id}\n";
        echo "   Is Read: " . ($notification->is_read ? 'Yes' : 'No') . "\n";
        echo "   Created: {$notification->created_at}\n";
        echo "   ---\n";
    }
} else {
    echo "❌ No notifications found for this client\n";
    echo "   Searching for notifiable_id: {$client->id}\n";
    echo "   Searching for notifiable_type: " . get_class($client) . "\n\n";
    
    // Check all notifications
    $allNotifications = App\Models\Notification::all();
    echo "📊 Total notifications in database: " . $allNotifications->count() . "\n";
    
    if ($allNotifications->count() > 0) {
        echo "   Sample notification:\n";
        $sample = $allNotifications->first();
        echo "   - notifiable_type: {$sample->notifiable_type}\n";
        echo "   - notifiable_id: {$sample->notifiable_id}\n";
    }
}
