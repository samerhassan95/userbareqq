<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Client;
use App\Models\Designer;
use App\Models\Marketer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UniversalAuthController extends Controller
{
    /**
     * Universal Login for all roles
     * POST /api/login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string', // username, email, or phone
            'password' => 'required|string|min:6',
            'device_token' => 'nullable|string',
            'device' => 'nullable|string',
        ], [
            'identifier.required' => 'Username, email, or phone is required.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => __('messages.validation_error'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $identifier = $request->identifier;
        $password = $request->password;
        $deviceToken = $request->device_token;
        $device = $request->device;

        // Try to find user in all tables
        $user = null;
        $role = null;
        $guard = null;

        // Check Client (email or username)
        $client = Client::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();
        
        if ($client) {
            $user = $client;
            $role = 'client';
            $guard = 'client';
        }

        // Check Admin (username, phone, or email)
        if (!$user) {
            $admin = Admin::where('username', $identifier)
                ->orWhere('phone', $identifier)
                ->orWhere('email', $identifier)
                ->first();
            
            if ($admin) {
                $user = $admin;
                $role = 'admin';
                $guard = 'admin';
            }
        }

        // Check Designer (email or username)
        if (!$user) {
            $designer = Designer::where('email', $identifier)
                ->orWhere('username', $identifier)
                ->first();
            
            if ($designer) {
                $user = $designer;
                $role = 'designer';
                $guard = 'client'; // Designers use client guard
            }
        }

        // Check Marketer (email or username)
        if (!$user) {
            $marketer = Marketer::where('email', $identifier)
                ->orWhere('username', $identifier)
                ->first();
            
            if ($marketer) {
                $user = $marketer;
                $role = 'marketer';
                $guard = 'client'; // Marketers use client guard
            }
        }

        // User not found
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => __('messages.invalid_credentials'),
            ], 401);
        }

        // Attempt authentication
        try {
            // Set TTL for mobile devices
            if ($device === 'mobile') {
                auth($guard)->factory()->setTTL(525600 * 10); // 10 years
            }

            // Prepare credentials based on user type
            $credentials = ['password' => $password];
            
            if ($role === 'client') {
                $credentials['email'] = $user->email;
            } elseif ($role === 'admin') {
                $credentials['username'] = $user->username;
            } elseif ($role === 'designer') {
                $credentials['email'] = $user->email;
            } elseif ($role === 'marketer') {
                $credentials['email'] = $user->email;
            }

            // Attempt login
            if (!$token = auth($guard)->attempt($credentials)) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.invalid_credentials'),
                ], 401);
            }

            // Update device token
            if ($deviceToken) {
                $user->update(['device_token' => $deviceToken]);
                $user->refresh(); // Reload to get updated device_token
            }

            // Prepare response - all user fields at root level
            $userData = $user->toArray();
            
            // Add full photo URL if exists
            if (isset($userData['photo']) && $userData['photo']) {
                $userData['photo'] = asset($userData['photo']);
            } else {
                $userData['photo'] = null;
            }
            
            // Ensure all nullable fields are present
            $userData['password'] = null; // Hide password
            $userData['name'] = $userData['name'] ?? null;
            $userData['email'] = $userData['email'] ?? null;
            $userData['phone'] = $userData['phone'] ?? null;
            $userData['company_name'] = $userData['company_name'] ?? null;
            $userData['website'] = $userData['website'] ?? null;
            $userData['address'] = $userData['address'] ?? null;
            $userData['city'] = $userData['city'] ?? null;
            $userData['country'] = $userData['country'] ?? null;
            
            // Add token and type to user data
            $userData['token'] = $token;
            $userData['type'] = $role;

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => __('messages.login_successful'),
                'data' => $userData,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => __('messages.server_error'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Universal Logout
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        try {
            // Try all guards
            if (auth('admin')->check()) {
                auth('admin')->logout();
            } elseif (auth('client')->check()) {
                auth('client')->logout();
            }

            return response()->json([
                'status' => true,
                'message' => __('messages.logout_successful'),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => __('messages.logout_failed'),
            ], 500);
        }
    }
}
