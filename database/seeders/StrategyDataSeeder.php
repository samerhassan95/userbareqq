<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductOrder;
use App\Models\StrategyTeamMember;
use App\Models\StrategyWork;
use App\Models\Designer;
use App\Models\Marketer;
use Carbon\Carbon;

class StrategyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all strategy orders
        $strategyOrders = ProductOrder::where('product_role', 'strategy')->get();

        if ($strategyOrders->isEmpty()) {
            $this->command->warn('No strategy orders found. Create some strategy orders first.');
            return;
        }

        foreach ($strategyOrders as $order) {
            // Add team members (designers and marketers)
            $this->addTeamMembers($order);
            
            // Add sample works
            $this->addSampleWorks($order);
        }

        $this->command->info('Strategy data seeded successfully!');
    }

    private function addTeamMembers($order)
    {
        // Get random designers and marketers
        $designers = Designer::inRandomOrder()->limit(2)->get();
        $marketers = Marketer::inRandomOrder()->limit(2)->get();

        foreach ($designers as $designer) {
            StrategyTeamMember::updateOrCreate([
                'product_order_id' => $order->id,
                'member_id' => $designer->id,
                'member_type' => Designer::class,
            ], [
                'role' => 'designer',
            ]);
        }

        foreach ($marketers as $marketer) {
            StrategyTeamMember::updateOrCreate([
                'product_order_id' => $order->id,
                'member_id' => $marketer->id,
                'member_type' => Marketer::class,
            ], [
                'role' => 'marketer',
            ]);
        }

        $this->command->info("Added team members for order #" . $order->id);
    }

    private function addSampleWorks($order)
    {
        $platforms = ['facebook', 'instagram', 'twitter', 'linkedin', 'tiktok'];
        $postTypes = ['image', 'video', 'text', 'carousel'];
        $statuses = ['pending', 'in_progress', 'completed'];

        // Create 10 sample works
        for ($i = 0; $i < 10; $i++) {
            $date = Carbon::now()->addDays($i);
            $postNumber = $i + 1;
            
            StrategyWork::create([
                'product_order_id' => $order->id,
                'title' => "Post #$postNumber - " . $date->format('M d'),
                'title_ar' => "منشور #$postNumber - " . $date->format('M d'),
                'description' => "Engaging content for " . $date->format('l'),
                'description_ar' => "محتوى جذاب ليوم " . $date->format('l'),
                'scheduled_date' => $date->format('Y-m-d'),
                'scheduled_time' => '10:00:00',
                'platforms' => array_rand(array_flip($platforms), rand(1, 3)),
                'status' => $statuses[array_rand($statuses)],
                'post_type' => $postTypes[array_rand($postTypes)],
                'attachments' => [],
                'notes' => 'Sample work created by seeder',
            ]);
        }

        $this->command->info("Added sample works for order #" . $order->id);
    }
}
