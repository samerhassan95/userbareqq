<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Testing Post Relationships\n\n";

// Get the latest post
$post = App\Models\Post::latest()->first();

if (!$post) {
    echo "❌ No posts found\n";
    exit(1);
}

echo "✅ Post found: {$post->title} (ID: {$post->id})\n\n";

// Check team members
echo "📊 Team Members:\n";
$teamMembers = $post->teamMembers;

if ($teamMembers->count() === 0) {
    echo "   ⚠️  No team members assigned to this post\n\n";
} else {
    echo "   Found {$teamMembers->count()} team members\n\n";
    
    foreach ($teamMembers as $teamMember) {
        echo "   Team Member #{$teamMember->id}:\n";
        echo "   - member_id: {$teamMember->member_id}\n";
        echo "   - member_type: {$teamMember->member_type}\n";
        echo "   - role: {$teamMember->role}\n";
        
        // Try to load the member
        try {
            if ($teamMember->member_type === 'App\\Models\\Designer') {
                $member = App\Models\Designer::find($teamMember->member_id);
                if ($member) {
                    echo "   - member name: {$member->name}\n";
                    echo "   - member email: {$member->email}\n";
                    echo "   ✅ Designer loaded successfully\n";
                } else {
                    echo "   ❌ Designer not found\n";
                }
            } elseif ($teamMember->member_type === 'App\\Models\\Marketer') {
                $member = App\Models\Marketer::find($teamMember->member_id);
                if ($member) {
                    echo "   - member name: {$member->name}\n";
                    echo "   - member email: {$member->email}\n";
                    echo "   ✅ Marketer loaded successfully\n";
                } else {
                    echo "   ❌ Marketer not found\n";
                }
            } else {
                echo "   ⚠️  Unknown member_type: {$teamMember->member_type}\n";
            }
        } catch (\Exception $e) {
            echo "   ❌ Error loading member: {$e->getMessage()}\n";
        }
        
        echo "   ---\n";
    }
}

// Check client
echo "\n📊 Client:\n";
if ($post->client) {
    echo "   ✅ Client: {$post->client->name} (ID: {$post->client->id})\n";
} else {
    echo "   ❌ No client found\n";
}

// Check product order
echo "\n📊 Product Order:\n";
if ($post->product_order_id) {
    $order = App\Models\ProductOrder::with('orderTeamMembers')->find($post->product_order_id);
    if ($order) {
        echo "   ✅ Order ID: {$order->id}\n";
        echo "   Order Team Members: {$order->orderTeamMembers->count()}\n";
        
        foreach ($order->orderTeamMembers as $teamMember) {
            echo "   - {$teamMember->member_type} ID: {$teamMember->member_id}\n";
        }
    } else {
        echo "   ❌ Order not found\n";
    }
} else {
    echo "   ⚠️  No product_order_id set\n";
}

echo "\n✅ Test complete!\n";
