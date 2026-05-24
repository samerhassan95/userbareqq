<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\PostFeedback;
use App\Models\Admin;
use App\Models\Client;
use Carbon\Carbon;

class PostsDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first admin and clients
        $admin = Admin::first();
        $clients = Client::limit(3)->get();

        if (!$admin) {
            $this->command->warn('No admin found. Create an admin first.');
            return;
        }

        if ($clients->isEmpty()) {
            $this->command->warn('No clients found. Create some clients first.');
            return;
        }

        foreach ($clients as $index => $client) {
            // Create 3 posts per client
            for ($i = 1; $i <= 3; $i++) {
                $isApproved = $i == 3; // Last post is approved
                
                $post = Post::create([
                    'title' => "Marketing Campaign #{$i} for {$client->name}",
                    'title_ar' => "حملة تسويقية #{$i} لـ {$client->name}",
                    'description' => "This is a marketing post for {$client->name}. It includes engaging content and creative design.",
                    'description_ar' => "هذا منشور تسويقي لـ {$client->name}. يتضمن محتوى جذاب وتصميم إبداعي.",
                    'image' => null,
                    'status' => $isApproved ? 'approved' : 'pending',
                    'is_approved' => $isApproved,
                    'approved_at' => $isApproved ? Carbon::now() : null,
                    'client_id' => $client->id,
                    'created_by_id' => $admin->id,
                    'created_by_type' => Admin::class,
                    'updated_by_id' => $admin->id,
                    'updated_by_type' => Admin::class,
                ]);

                // Add feedbacks for non-approved posts
                if (!$isApproved) {
                    PostFeedback::create([
                        'post_id' => $post->id,
                        'client_id' => $client->id,
                        'comment' => 'Can you change the color scheme to match our brand?',
                    ]);

                    PostFeedback::create([
                        'post_id' => $post->id,
                        'client_id' => $client->id,
                        'comment' => 'The text looks good, but please add our logo.',
                    ]);
                }

                $this->command->info("Created post #{$i} for client {$client->name}");
            }
        }

        $this->command->info('Posts data seeded successfully!');
    }
}
