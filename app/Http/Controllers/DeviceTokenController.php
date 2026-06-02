<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceTokenController extends Controller
{
    /**
     * Update device token for authenticated user
     * POST /api/profile/device-token
     * Body: { "device_token": "firebase_fcm_token_here" }
     */
    public function updateDeviceToken(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string|min:5|max:500',
        ]);

        $user = $this->getAuthenticatedUser();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized - no authenticated user found',
            ], 401);
        }

        $oldToken = $user->device_token;
        $user->update(['device_token' => $request->device_token]);

        return response()->json([
            'status'  => true,
            'message' => 'Device token updated successfully',
            'data'    => [
                'user_id'    => $user->id,
                'type'       => get_class($user),
                'old_token'  => substr($oldToken ?? 'none', 0, 20) . '...',
                'new_token'  => substr($request->device_token, 0, 20) . '...',
            ]
        ]);
    }

    /**
     * Update language preference for authenticated user
     * POST /api/profile/language
     * Body: { "language": "en" | "ar" }
     */
    public function updateLanguage(Request $request)
    {
        $request->validate([
            'language' => 'required|in:en,ar',
        ]);

        $user = $this->getAuthenticatedUser();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized - no authenticated user found',
            ], 401);
        }

        $oldLanguage = $user->language ?? 'en';
        $user->update(['language' => $request->language]);

        return response()->json([
            'status'  => true,
            'message' => 'Language preference updated successfully',
            'data'    => [
                'user_id'      => $user->id,
                'type'         => get_class($user),
                'old_language' => $oldLanguage,
                'new_language' => $request->language,
            ]
        ]);
    }

    /**
     * Get current device token and language settings
     * GET /api/profile/notification-settings
     */
    public function getNotificationSettings()
    {
        $user = $this->getAuthenticatedUser();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized - no authenticated user found',
            ], 401);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Notification settings retrieved',
            'data'    => [
                'user_id'      => $user->id,
                'type'         => get_class($user),
                'device_token' => $user->device_token ? substr($user->device_token, 0, 20) . '...' : null,
                'has_token'    => (bool) $user->device_token,
                'language'     => $user->language ?? 'en',
            ]
        ]);
    }

    /**
     * Get authenticated user from any guard
     */
    private function getAuthenticatedUser()
    {
        foreach (['admin', 'client', 'designer', 'marketer', 'employee'] as $guard) {
            if (Auth::guard($guard)->check()) {
                return Auth::guard($guard)->user();
            }
        }
        return null;
    }
}
