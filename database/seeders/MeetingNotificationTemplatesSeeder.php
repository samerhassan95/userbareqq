<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MeetingNotificationTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            // Meeting Created
            [
                'type' => 'meeting_created',
                'title' => 'New Meeting Scheduled',
                'message' => 'Meeting "{meeting_name}" has been scheduled for {date} at {time}',
                'title_ar' => 'اجتماع جديد مجدول',
                'message_ar' => 'تم جدولة الاجتماع "{meeting_name}" لتاريخ {date} الساعة {time}',
            ],

            // Meeting Status Updated
            [
                'type' => 'meeting_status_updated',
                'title' => 'Meeting Status Updated',
                'message' => 'Meeting "{meeting_name}" status changed to: {status}',
                'title_ar' => 'تحديث حالة الاجتماع',
                'message_ar' => 'تم تغيير حالة الاجتماع "{meeting_name}" إلى: {status}',
            ],

            // Meeting Confirmed
            [
                'type' => 'meeting_confirmed',
                'title' => 'Meeting Confirmed',
                'message' => 'Your meeting "{meeting_name}" has been confirmed for {date} at {time}',
                'title_ar' => 'تأكيد الاجتماع',
                'message_ar' => 'تم تأكيد اجتماعك "{meeting_name}" في {date} الساعة {time}',
            ],

            // Meeting Completed
            [
                'type' => 'meeting_completed',
                'title' => 'Meeting Completed',
                'message' => 'Meeting "{meeting_name}" has been marked as completed',
                'title_ar' => 'اكتمال الاجتماع',
                'message_ar' => 'تم وضع علامة على الاجتماع "{meeting_name}" كمكتمل',
            ],

            // Meeting Canceled
            [
                'type' => 'meeting_canceled',
                'title' => 'Meeting Canceled',
                'message' => 'Meeting "{meeting_name}" has been canceled',
                'title_ar' => 'إلغاء الاجتماع',
                'message_ar' => 'تم إلغاء الاجتماع "{meeting_name}"',
            ],

            // Team Member Added to Meeting
            [
                'type' => 'meeting_team_member_added',
                'title' => 'Assigned to Meeting',
                'message' => 'You have been assigned to meeting "{meeting_name}" on {date}',
                'title_ar' => 'تعيينك في اجتماع',
                'message_ar' => 'تم تعيينك في الاجتماع "{meeting_name}" في {date}',
            ],

            // Team Member Removed from Meeting
            [
                'type' => 'meeting_team_member_removed',
                'title' => 'Removed from Meeting',
                'message' => 'You have been removed from meeting "{meeting_name}"',
                'title_ar' => 'إزالتك من اجتماع',
                'message_ar' => 'تم إزالتك من الاجتماع "{meeting_name}"',
            ],

            // Team Auto-Synced
            [
                'type' => 'meeting_team_synced',
                'title' => 'Team Auto-Synced',
                'message' => 'Team members have been automatically synced for meeting "{meeting_name}"',
                'title_ar' => 'مزامنة الفريق التلقائية',
                'message_ar' => 'تم مزامنة أعضاء الفريق تلقائياً للاجتماع "{meeting_name}"',
            ],

            // Meeting Reminder
            [
                'type' => 'meeting_reminder',
                'title' => 'Meeting Reminder',
                'message' => 'Your meeting "{meeting_name}" starts at {time}',
                'title_ar' => 'تذكير الاجتماع',
                'message_ar' => 'اجتماعك "{meeting_name}" يبدأ الساعة {time}',
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
