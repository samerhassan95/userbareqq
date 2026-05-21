<?php

namespace Database\Seeders;

use App\Models\Addon;
use App\Models\Product;
use App\Models\ProductStrategyTip;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ArabicTranslationsSeeder extends Seeder
{
    /**
     * Seed Arabic translations for existing data
     */
    public function run(): void
    {
        // Update Addons with Arabic translations
        $addonTranslations = [
            'Video Production' => [
                'name_ar' => 'إنتاج الفيديو',
                'description_ar' => 'خدمة إنتاج فيديو احترافية',
            ],
            'Social Media Post' => [
                'name_ar' => 'منشور سوشيال ميديا',
                'description_ar' => 'تصميم منشور سوشيال ميديا مخصص',
            ],
            'Logo Design' => [
                'name_ar' => 'تصميم شعار',
                'description_ar' => 'تصميم شعار احترافي',
            ],
            'Banner Design' => [
                'name_ar' => 'تصميم بانر',
                'description_ar' => 'تصميم بانر مخصص لوسائل التواصل الاجتماعي',
            ],
            'Content Writing' => [
                'name_ar' => 'كتابة محتوى',
                'description_ar' => 'خدمة كتابة محتوى احترافي',
            ],
        ];

        foreach ($addonTranslations as $name => $translations) {
            Addon::where('name', $name)->update($translations);
        }

        $this->command->info('Addons Arabic translations updated!');

        // Update Products with Arabic translations
        $products = Product::all();
        foreach ($products as $product) {
            if ($product->name === 'New Product') {
                $product->update([
                    'name_ar' => 'منتج جديد',
                    'description_ar' => 'وصف المنتج',
                    'note_ar' => 'ملاحظة',
                ]);
            } elseif ($product->name === 'Social Media Strategy') {
                $product->update([
                    'name_ar' => 'استراتيجية السوشيال ميديا',
                    'description_ar' => 'إدارة شهرية لوسائل التواصل الاجتماعي',
                    'note_ar' => 'يشمل إنشاء المحتوى والتحليلات',
                ]);
            } elseif ($product->name === 'Website Design') {
                $product->update([
                    'name_ar' => 'تصميم موقع إلكتروني',
                    'description_ar' => 'خدمة تصميم موقع إلكتروني احترافي',
                    'note_ar' => 'تصميم احترافي',
                ]);
            }
        }

        $this->command->info('Products Arabic translations updated!');

        // Update Strategy Tips with Arabic translations
        $tipTranslations = [
            'We will create engaging posts for Facebook, Instagram, and Twitter' => 
                'سنقوم بإنشاء منشورات جذابة لفيسبوك وإنستجرام وتويتر',
            'Daily content calendar with optimal posting times' => 
                'تقويم محتوى يومي مع أوقات النشر المثالية',
            'Monthly performance analytics and insights report' => 
                'تقرير تحليلات الأداء والرؤى الشهرية',
            'Hashtag research and optimization for maximum reach' => 
                'بحث وتحسين الهاشتاجات للوصول الأقصى',
            'Community management and engagement monitoring' => 
                'إدارة المجتمع ومراقبة التفاعل',
            'Competitor analysis and market trends tracking' => 
                'تحليل المنافسين وتتبع اتجاهات السوق',
            'Custom graphics and visual content creation' => 
                'إنشاء رسومات ومحتوى مرئي مخصص',
            'Ad campaign strategy and optimization recommendations' => 
                'استراتيجية الحملات الإعلانية وتوصيات التحسين',
        ];

        foreach ($tipTranslations as $text => $textAr) {
            ProductStrategyTip::where('text', $text)->update(['text_ar' => $textAr]);
        }

        $this->command->info('Strategy tips Arabic translations updated!');

        // Update Categories if any exist
        $categories = Category::all();
        foreach ($categories as $category) {
            // Add generic Arabic translation if not set
            if (!$category->name_ar) {
                $category->update(['name_ar' => $category->name]);
            }
        }

        $this->command->info('All Arabic translations seeded successfully!');
    }
}
