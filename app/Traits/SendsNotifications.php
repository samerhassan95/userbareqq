<?php

namespace App\Traits;

use App\Services\FirebaseService;
use App\Repositories\NotificationRepository;
use Illuminate\Support\Facades\Log;

trait SendsNotifications
{
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
            if (!$user || !$user->device_token) {
                Log::info("Skipping notification for user without device_token", [
                    'user_id' => $user->id ?? 'unknown',
                    'type' => $type
                ]);
                continue;
            }

            try {
                // Send Firebase push notification
                $firebaseService->sendNotification(
                    $user->device_token,
                    $title,
                    $message,
                    array_merge($data, ['notification_type' => $type])
                );

                // Save to database
                $notificationRepo->createNotification(
                    $user,
                    $title,
                    $message,
                    $user->device_token,
                    $type,
                    $data
                );

                Log::info("Notification sent successfully", [
                    'user_id' => $user->id,
                    'type' => $type,
                    'title' => $title
                ]);
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
        $admins = \App\Models\Admin::whereNotNull('device_token')->get();
        $this->sendNotification($admins, $title, $message, $type, $data);
    }

    /**
     * Get current authenticated user from any guard
     */
    protected function getCurrentUser()
    {
        foreach (['admin', 'client', 'designer', 'marketer'] as $guard) {
            if (auth()->guard($guard)->check()) {
                return auth()->guard($guard)->user();
            }
        }
        return null;
    }
}
