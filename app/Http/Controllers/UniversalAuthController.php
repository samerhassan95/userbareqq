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
                'message' => 'Validation error',
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

        // Check Admin (username or phone)
        if (!$user) {
            $admin = Admin::where('username', $identifier)
                ->orWhere('phone', $identifier)
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
                'message' => 'Invalid credentials',
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
                    'message' => 'Invalid credentials',
                ], 401);
            }

            // Update device token
            if ($deviceToken) {
                $user->update(['device_token' => $deviceToken]);
            }

            // Prepare response
            $userData = $user->toArray();
            
            // Add photo for clients
            if ($role === 'client' && $user->photo) {
                $userData['photo'] = asset($user->photo);
            }

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $userData,
                    'token' => $token,
                    'role' => $role,
                    'permissions' => $this->getRolePermissions($role),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Server error, please try again later',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get role-specific permissions
     */
    private function getRolePermissions($role)
    {
        $permissions = [
            'admin' => [
                'manage_products',
                'manage_orders',
                'manage_users',
                'manage_invoices',
                'view_analytics',
            ],
            'client' => [
                'view_products',
                'create_orders',
                'view_orders',
                'manage_profile',
            ],
            'designer' => [
                'view_projects',
                'upload_designs',
                'manage_portfolio',
            ],
            'marketer' => [
                'view_campaigns',
                'manage_content',
                'view_analytics',
            ],
        ];

        return $permissions[$role] ?? [];
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
                'message' => 'Logout successful',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Logout failed',
            ], 500);
        }
    }
}
