<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\PostFeedback;
use App\Models\StrategyWork;
use App\Models\ProductOrder;
use App\Models\Product;
use App\Models\Client;
use App\Models\Admin;
use App\Models\Designer;
use App\Models\Marketer;
use App\Models\Invoice;
use App\Models\Subscription;
use Carbon\Carbon;

class CompletePostsStrategySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting complete posts and strategy seeding...');

        // Get required data
        $admin = Admin::first();
        $clients = Client::limit(3)->get();
        $strategyProducts = Product::where('product_role', 'strategy')->get();

        if (!$admin) {
            $this->command->error('No admin found. Please create an admin first.');
            return;
        }

        if ($clients->isEmpty()) {
            $this->command->error('No clients found. Please create clients first.');
            return;
        }

        if ($strategyProducts->isEmpty()) {
            $this->command->error('No strategy products found. Please create strategy products first.');
            return;
        }

        // Get designers and marketers
        $designers = Designer::all();
        $marketers = Marketer::all();

        // Create strategy orders for each client
        foreach ($clients as $client) {
            $product = $strategyProducts->random();
            
            // Create order
            $order = ProductOrder::create([
                'client_id' => $client->id,
                'product_id' => $product->id,
                'product_role' => 'strategy',
                'duration' => rand(0, 1) ? 'month' : 'year',
                'total_price' => rand(0, 1) ? $product->monthly_price : $product->yearly_price,
                'status' => 'paid',
            ]);

            // Create invoice
            $invoice = Invoice::create([
                'client_id' => $client->id,
                'product_id' => $product->id,
                'amount' => $order->total_price,
                'status' => 'paid',
                'payment_method' => 'bank_transfer',
                'due_date' => Carbon::now()->addDays(7),
            ]);

            $order->update(['invoice_id' => $invoice->id]);

            // Create subscription
            $startsAt = Carbon::now();
            $expiresAt = $order->duration === 'year' 
                ? $startsAt->copy()->addYear() 
                : $startsAt->copy()->addMonth();

            $subscription = Subscription::create([
                'client_id' => $client->id,
                'product_id' => $product->id,
                'status' => 'active',
                'billing_cycle' => $order->duration === 'year' ? 'yearly' : 'monthly',
                'starts_at' => $startsAt,
                'expires_at' => $expiresAt,
            ]);

            $order->update(['subscription_id' => $subscription->id]);

            $this->command->info("Created strategy order #{$order->id} for client {$client->name}");

            // Create strategy works
            $this->createStrategyWorks($order, $client, $admin, $designers, $marketers);
        }

        $this->command->info('Complete posts and strategy seeding completed successfully!');
    }

    private function createStrategyWorks($order, $client, $admin, $designers, $marketers)
    {
        $platforms = ['facebook', 'instagram', 'twitter', 'linkedin', 'tiktok'];
        $postTypes = ['image', 'video', 'text', 'carousel', 'reel', 'story'];
        $statuses = ['pending', 'in_progress', 'completed'];

        // Create 10 strategy works
        for ($i = 0; $i < 10; $i++) {
            $date = Carbon::now()->addDays($i);
            $postNumber = $i + 1;
            
            // Randomly select 1-3 platforms
            $selectedPlatforms = [];
            $platformCount = rand(1, 3);
            $shuffledPlatforms = $platforms;
            shuffle($shuffledPlatforms);
            for ($j = 0; $j < $platformCount; $j++) {
                $selectedPlatforms[] = $shuffledPlatforms[$j];
            }
            
            $work = StrategyWork::create([
                'product_order_id' => $order->id,
                'title' => "Post #$postNumber - " . $date->format('M d'),
                'title_ar' => "منشور #$postNumber - " . $date->format('M d'),
                'description' => "Engaging content for " . $date->format('l'),
                'description_ar' => "محتوى جذاب ليوم " . $date->format('l'),
                'scheduled_date' => $date->format('Y-m-d'),
                'scheduled_time' => sprintf('%02d:00:00', rand(9, 18)),
                'platforms' => $selectedPlatforms,
                'status' => $statuses[array_rand($statuses)],
                'post_type' => $postTypes[array_rand($postTypes)],
                'attachments' => [],
                'notes' => 'Sample work created by seeder',
            ]);

            // Create 1-3 posts for this work
            $this->createPostsForWork($work, $order, $client, $admin, $designers, $marketers);
        }
    }

    private function createPostsForWork($work, $order, $client, $admin, $designers, $marketers)
    {
        $postsCount = rand(1, 3);
        
        for ($i = 1; $i <= $postsCount; $i++) {
            // Randomly choose creator (Admin, Designer, or Marketer)
            $creatorType = rand(1, 3);
            
            if ($creatorType == 1 && $admin) {
                $createdById = $admin->id;
                $createdByType = Admin::class;
                $creatorName = 'Admin';
            } elseif ($creatorType == 2 && $designers->isNotEmpty()) {
                $designer = $designers->random();
                $createdById = $designer->id;
                $createdByType = Designer::class;
                $creatorName = $designer->name ?? 'Designer';
            } else {
                if ($marketers->isEmpty()) {
                    $createdById = $admin->id;
                    $createdByType = Admin::class;
                    $creatorName = 'Admin';
                } else {
                    $marketer = $marketers->random();
                    $createdById = $marketer->id;
                    $createdByType = Marketer::class;
                    $creatorName = $marketer->name ?? 'Marketer';
                }
            }

            $isApproved = rand(1, 10) > 7; // 30% chance of being approved
            
            // Create post with image
            $imageName = uniqid() . '_sample_' . $i . '.jpg';
            
            $post = Post::create([
                'title' => "Post #{$i} for " . $work->title,
                'title_ar' => "منشور #{$i} لـ " . ($work->title_ar ?? $work->title),
                'description' => "This is post #{$i} for the work: {$work->title}. Created by {$creatorName}.",
                'description_ar' => "هذا منشور #{$i} للعمل: " . ($work->title_ar ?? $work->title) . ". تم إنشاؤه بواسطة {$creatorName}.",
                'image' => $imageName,
                'status' => $isApproved ? 'approved' : 'pending',
                'is_approved' => $isApproved,
                'approved_at' => $isApproved ? Carbon::now() : null,
                'client_id' => $client->id,
                'product_order_id' => $order->id,
                'strategy_work_id' => $work->id,
                'created_by_id' => $createdById,
                'created_by_type' => $createdByType,
                'updated_by_id' => $createdById,
                'updated_by_type' => $createdByType,
            ]);

            // Add feedbacks for non-approved posts
            if (!$isApproved && rand(1, 10) > 5) {
                PostFeedback::create([
                    'post_id' => $post->id,
                    'client_id' => $client->id,
                    'comment' => 'Please adjust the colors to match our brand guidelines.',
                ]);
            }

            $this->command->info("  - Created post #{$i} for work #{$work->id}");
        }
    }
}
