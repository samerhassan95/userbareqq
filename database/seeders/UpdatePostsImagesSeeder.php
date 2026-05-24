<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;

class UpdatePostsImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Updating posts images with real server images...');

        // الصور الموجودة على السيرفر
        $availableImages = [
            'Screenshot (42).png',
            'Screenshot (43) - Copying.png',
            'Screenshot (43).png',
            'Screenshot (44) - Copying.png',
            'Screenshot (44).png',
            'Screenshot (45).png',
            'Screenshot (46).png',
        ];

        // جلب كل البوستات
        $posts = Post::all();

        if ($posts->isEmpty()) {
            $this->command->warn('No posts found to update.');
            return;
        }

        $imageIndex = 0;
        $totalImages = count($availableImages);

        foreach ($posts as $post) {
            // استخدام الصور بالتناوب
            $imageName = $availableImages[$imageIndex % $totalImages];
            
            $post->update([
                'image' => $imageName
            ]);

            $imageIndex++;
            
            if ($imageIndex % 50 == 0) {
                $this->command->info("Updated {$imageIndex} posts...");
            }
        }

        $this->command->info("Successfully updated {$posts->count()} posts with real images!");
        $this->command->info("Images are now using the files from /public/posts/ folder");
    }
}
