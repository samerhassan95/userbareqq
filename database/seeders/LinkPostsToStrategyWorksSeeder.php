<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\PostFeedback;
use App\Models\StrategyWork;
use App\Models\ProductOrder;
use App\Models\Admin;
use App\Models\Designer;
use App\Models\Marketer;
use Carbon\Carbon;

class LinkPostsToStrategyWorksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all strategy works
        $strategyWorks = StrategyWork::with('productOrder.client')->get();

        if ($strategyWorks->isEmpty()) {
            $this->command->warn('No strategy works found. Run StrategyDataSeeder first.');
            return;
        }

        $admin = Admin::first();
        $designers = Designer::all();
        $marketers = Marketer::all();

        if (!$admin) {
            $this->command->warn('No admin found. Create an admin first.');
            return;
        }

        foreach ($strategyWorks as $work) {
            $client = $work->productOrder->client ?? null;
            
            if (!$client) {
                continue;
            }

            // Create 1-3 posts per work
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
                    $creatorName = 'Designer';
                } else {
                    if ($marketers->isEmpty()) {
                        $createdById = $admin->id;
                        $createdByType = Admin::class;
                        $creatorName = 'Admin';
                    } else {
                        $marketer = $marketers->random();
                        $createdById = $marketer->id;
                        $createdByType = Marketer::class;
                        $creatorName = 'Marketer';
                    }
                }

                $isApproved = rand(1, 10) > 7; // 30% chance of being approved
                
                // Create post with image
                $imageName = $this->createSampleImage($i);
                
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
                    'product_order_id' => $work->product_order_id,
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

                $this->command->info("Created post #{$i} for work #{$work->id}");
            }
        }

        $this->command->info('Posts linked to strategy works successfully!');
    }

    /**
     * Create a sample image name (you can replace this with actual image creation)
     */
    private function createSampleImage($index)
    {
        // Generate a unique filename
        return uniqid() . '_sample_' . $index . '.jpg';
    }
}
