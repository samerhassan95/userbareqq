<?php

namespace Database\Seeders;

use App\Models\Addon;
use App\Models\Product;
use App\Models\ProductStrategyTip;
use Illuminate\Database\Seeder;

class ProductDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Addons (Features for one-time products)
        $addons = [
            [
                'name' => 'Video Production',
                'description' => 'Professional video production service',
                'price' => 500,
                'icon' => null,
            ],
            [
                'name' => 'Social Media Post',
                'description' => 'Custom social media post design',
                'price' => 100,
                'icon' => null,
            ],
            [
                'name' => 'Logo Design',
                'description' => 'Professional logo design',
                'price' => 300,
                'icon' => null,
            ],
            [
                'name' => 'Banner Design',
                'description' => 'Custom banner design for social media',
                'price' => 150,
                'icon' => null,
            ],
            [
                'name' => 'Content Writing',
                'description' => 'Professional content writing service',
                'price' => 200,
                'icon' => null,
            ],
        ];

        foreach ($addons as $addonData) {
            Addon::firstOrCreate(
                ['name' => $addonData['name']],
                $addonData
            );
        }

        $this->command->info('Addons seeded successfully!');

        // Attach addons to one-time products
        $oneTimeProducts = Product::where('product_role', 'one_time')->get();
        $addonIds = Addon::pluck('id')->toArray();

        foreach ($oneTimeProducts as $product) {
            if ($product->addons()->count() === 0) {
                $product->addons()->attach($addonIds);
            }
        }

        $this->command->info('Addons attached to one-time products!');

        // Create Strategy Tips for strategy products
        $strategyProducts = Product::where('product_role', 'strategy')->get();

        $tips = [
            [
                'text' => 'We will create engaging posts for Facebook, Instagram, and Twitter',
                'platforms' => ['facebook', 'instagram', 'twitter'],
                'sort_order' => 1,
            ],
            [
                'text' => 'Daily content calendar with optimal posting times',
                'platforms' => ['facebook', 'instagram', 'twitter', 'linkedin'],
                'sort_order' => 2,
            ],
            [
                'text' => 'Monthly performance analytics and insights report',
                'platforms' => [],
                'sort_order' => 3,
            ],
            [
                'text' => 'Hashtag research and optimization for maximum reach',
                'platforms' => ['instagram', 'twitter', 'tiktok'],
                'sort_order' => 4,
            ],
            [
                'text' => 'Community management and engagement monitoring',
                'platforms' => ['facebook', 'instagram', 'twitter', 'linkedin'],
                'sort_order' => 5,
            ],
            [
                'text' => 'Competitor analysis and market trends tracking',
                'platforms' => [],
                'sort_order' => 6,
            ],
            [
                'text' => 'Custom graphics and visual content creation',
                'platforms' => ['facebook', 'instagram', 'linkedin'],
                'sort_order' => 7,
            ],
            [
                'text' => 'Ad campaign strategy and optimization recommendations',
                'platforms' => ['facebook', 'instagram', 'linkedin'],
                'sort_order' => 8,
            ],
        ];

        foreach ($strategyProducts as $product) {
            foreach ($tips as $tipData) {
                ProductStrategyTip::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'text' => $tipData['text'],
                    ],
                    [
                        'platforms' => $tipData['platforms'],
                        'sort_order' => $tipData['sort_order'],
                    ]
                );
            }
        }

        $this->command->info('Strategy tips seeded successfully!');
        $this->command->info('All product data seeded!');
    }
}
