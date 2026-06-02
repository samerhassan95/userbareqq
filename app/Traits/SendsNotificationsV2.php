<?php

namespace App\Traits;

use App\Services\FirebaseService;
use App\Repositories\NotificationRepository;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Log;

trait SendsNotificationsV2
{
    /**
     * Send notification to a single user or multiple users (language-aware)
     *
     * @param mixed $users Single user or collection of users
     * @param string $templateType Notification template type (e.g., 'meeting_created')
     * @param array $replacements Replacements for template placeholders
     * @param array $data Additional Firebase data payload
     */
    protected function sendNotificationV2($users, string $templateType, array $replacements = [], array $data = [])
    {
        // Convert single user to array
        if (!is_array($users) && !($users instanceof \Illuminate\Support\Collection)) {
            $users = [$users];
        }

        $firebaseService = app(FirebaseService::class);
        $notificationRepo = app(NotificationRepository::class);

        foreach ($users as $user) {
            if (!$user) {
                Log::info("Skipping notification for null user", ['type' => $templateType]);
                continue;
            }

            try {
                // Get the template
                $template = NotificationTemplate::where('type', $templateType)->first();

                if (!$template) {
                    Log::warning("Notification template not found", ['type' => $templateType]);
                    continue;
                }

                // Get user's preferred language (default to 'en')
                $language = $user->language ?? 'en';

                // Select title and message based on language
                $titleField = $language === 'ar' ? 'title_ar' : 'title';
                $messageField = $language === 'ar' ? 'message_ar' : 'message';

                $title = $template->$titleField ?? $template->title;
                $message = $template->$messageField ?? $template->message;

                // Replace placeholders in title and message
                foreach ($replacements as $key => $value) {
                    $title = str_replace('{' . $key . '}', $value, $title);
                    $message = str_replace('{' . $key . '}', $value, $message);
                }

                // Always save to database
                $notificationRepo->createNotification(
                    $user,
                    $title,
                    $message,
                    $user->device_token ?? null,
                    $templateType,
                    $data
                );

                // Send Firebase push notification only if device_token exists
                if ($user->device_token) {
                    $firebaseService->sendNotification(
                        $user->device_token,
                        $title,
                        $message,
                        array_merge($data, [
                            'notification_type' => $templateType,
                            'language' => $language
                        ])
                    );

                    Log::info("Notification sent successfully (v2)", [
                        'user_id' => $user->id,
                        'type' => $templateType,
                        'language' => $language,
                        'has_device_token' => true
                    ]);
                } else {
                    Log::info("Notification saved to database (no device_token for push) (v2)", [
                        'user_id' => $user->id,
                        'type' => $templateType,
                        'language' => $language,
                        'has_device_token' => false
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Failed to send notification (v2)", [
                    'user_id' => $user->id,
                    'type' => $templateType,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Send notification to all admins (language-aware)
     */
    protected function notifyAdminsV2(string $templateType, array $replacements = [], array $data = [])
    {
        $admins = \App\Models\Admin::all();
        $this->sendNotificationV2($admins, $templateType, $replacements, $data);
    }

    // Legacy methods for backward compatibility
    /**
     * Send notification to a single user or multiple users
     */
    protected function sendNotification($users, string $title, string $message, string $type, array $data = [])
    {
        // Convert single user to array
        if (!is_array($users) && !($users instanceof \Illuminate\Support\Collection)) {
            $users = [$users];
        }

        $firebaseService = app(FirebaseService::class);
        $notificationRepo = app(NotificationRepository::class);

        foreach ($users as $user) {
            if (!$user) {
                Log::info("Skipping notification for null user", ['type' => $type]);
                continue;
            }

            try {
                // Always save to database
                $notificationRepo->createNotification(
                    $user,
                    $title,
                    $message,
                    $user->device_token ?? null,
                    $type,
                    $data
                );

                // Send Firebase push notification only if device_token exists
                if ($user->device_token) {
                    $firebaseService->sendNotification(
                        $user->device_token,
                        $title,
                        $message,
                        array_merge($data, ['notification_type' => $type])
                    );

                    Log::info("Notification sent successfully", [
                        'user_id' => $user->id,
                        'type' => $type,
                        'title' => $title,
                        'has_device_token' => true
                    ]);
                } else {
                    Log::info("Notification saved to database (no device_token for push)", [
                        'user_id' => $user->id,
                        'type' => $type,
                        'title' => $title,
                        'has_device_token' => false
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Failed to send notification", [
                    'user_id' => $user->id,
                    'type' => $type,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Send notification to all admins
     */
    protected function notifyAdmins(string $title, string $message, string $type, array $data = [])
    {
        $admins = \App\Models\Admin::all();
        $this->sendNotification($admins, $title, $message, $type, $data);
    }

    /**
     * Get current authenticated user from any guard
     */
    protected function getCurrentUser()
    {
        foreach (['admin', 'client', 'designer', 'marketer', 'employee'] as $guard) {
            if (auth()->guard($guard)->check()) {
                return auth()->guard($guard)->user();
            }
        }
        return null;
    }
}
