<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            // Order notifications
            [
                'type' => 'order_created',
                'title' => 'New Order Received',
                'message' => 'New order #{order_id} from {client_name}',
                'title_ar' => 'طلب جديد',
                'message_ar' => 'طلب جديد #{order_id} من {client_name}',
            ],
            [
                'type' => 'order_confirmed',
                'title' => 'Order Confirmed',
                'message' => 'Your order #{order_id} has been received and is being processed',
                'title_ar' => 'تأكيد الطلب',
                'message_ar' => 'تم استلام طلبك #{order_id} وجاري معالجته',
            ],
            [
                'type' => 'payment_approved',
                'title' => 'Payment Approved',
                'message' => 'Your payment for order #{order_id} has been approved. Work will begin soon!',
                'title_ar' => 'تمت الموافقة على الدفع',
                'message_ar' => 'تمت الموافقة على دفعتك للطلب #{order_id}. سيبدأ العمل قريباً!',
            ],
            
            // Team assignment
            [
                'type' => 'team_assigned_to_order',
                'title' => 'New Project Assignment',
                'message' => 'You have been assigned to {client_name}\'s project',
                'title_ar' => 'تعيين مشروع جديد',
                'message_ar' => 'تم تعيينك لمشروع {client_name}',
            ],
            [
                'type' => 'team_assigned_notification_client',
                'title' => 'Team Assigned',
                'message' => 'Your team has been assigned to your project',
                'title_ar' => 'تم تعيين الفريق',
                'message_ar' => 'تم تعيين فريقك لمشروعك',
            ],
            
            // Post notifications
            [
                'type' => 'post_created',
                'title' => 'New Post Created',
                'message' => 'New post "{title}" created for your review',
                'title_ar' => 'منشور جديد',
                'message_ar' => 'تم إنشاء منشور جديد "{title}" للمراجعة',
            ],
            [
                'type' => 'post_assigned',
                'title' => 'Post Assignment',
                'message' => 'You have been assigned to work on post: {title}',
                'title_ar' => 'تعيين منشور',
                'message_ar' => 'تم تعيينك للعمل على منشور: {title}',
            ],
            [
                'type' => 'post_updated',
                'title' => 'Post Updated',
                'message' => 'Post "{title}" has been updated',
                'title_ar' => 'تحديث المنشور',
                'message_ar' => 'تم تحديث المنشور "{title}"',
            ],
            
            // Feedback notifications
            [
                'type' => 'feedback_added',
                'title' => 'New Feedback',
                'message' => '{user_name} added feedback on post: {title}',
                'title_ar' => 'ملاحظات جديدة',
                'message_ar' => '{user_name} أضاف ملاحظات على المنشور: {title}',
            ],
            
            // Approval notifications
            [
                'type' => 'post_approved',
                'title' => 'Post Approved! 🎉',
                'message' => 'Post "{title}" has been approved',
                'title_ar' => 'تمت الموافقة على المنشور',
                'message_ar' => 'تمت الموافقة على المنشور "{title}"',
            ],
            [
                'type' => 'post_published',
                'title' => 'Post Published 🚀',
                'message' => 'Your post "{title}" has been published successfully',
                'title_ar' => 'تم نشر المنشور',
                'message_ar' => 'تم نشر منشورك "{title}" بنجاح',
            ],
            
            // Order completion
            [
                'type' => 'order_completed',
                'title' => 'Order Completed',
                'message' => 'Your order #{order_id} has been completed. Thank you!',
                'title_ar' => 'اكتمل الطلب',
                'message_ar' => 'اكتمل طلبك #{order_id}. شكراً لك!',
            ],
            [
                'type' => 'order_status_changed',
                'title' => 'Order Status Updated',
                'message' => 'Your order #{order_id} status: {status}',
                'title_ar' => 'تحديث حالة الطلب',
                'message_ar' => 'حالة طلبك #{order_id}: {status}',
            ],
        ];

        foreach ($templates as $template) {
            DB::table('notification_templates')->updateOrInsert(
                ['type' => $template['type']],
                array_merge($template, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
