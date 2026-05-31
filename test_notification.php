<?php

// Test notification creation script
// Run with: php test_notification.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Client;
use App\Repositories\NotificationRepository;

echo "Testing notification creation...\n\n";

// Get a client
$client = Client::first();

if (!$client) {
    echo "❌ No client found in database\n";
    exit(1);
}

echo "✅ Found client: {$client->name} (ID: {$client->id})\n";
echo "   Device token: " . ($client->device_token ?? 'NULL') . "\n\n";

// Create notification
try {
    $notificationRepo = app(NotificationRepository::class);
    
    $notification = $notificationRepo->createNotification(
        $client,
        'Test Notification',
        'This is a test notification created manually',
        $client->device_token,
        'test_notification',
        ['test' => true]
    );
    
    echo "✅ Notification created successfully!\n";
    echo "   Notification ID: {$notification->id}\n";
    echo "   Title: {$notification->title}\n";
    echo "   Message: {$notification->message}\n\n";
    
    // Verify in database
    $count = \App\Models\Notification::count();
    echo "✅ Total notifications in database: {$count}\n";
    
} catch (\Exception $e) {
    echo "❌ Error creating notification: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}
