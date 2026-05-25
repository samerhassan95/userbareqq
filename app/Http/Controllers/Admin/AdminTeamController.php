<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Designer;
use App\Models\Marketer;
use Illuminate\Http\Request;

class AdminTeamController extends Controller
{
    /**
     * Get all designers
     * GET /api/admin/designers
     */
    public function getDesigners(Request $request)
    {
        try {
            $query = Designer::query();

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Pagination
            if ($request->get('pagination') === 'false' || $request->get('pagination') === false) {
                $designers = $query->select('id', 'username', 'email', 'phone', 'created_at')
                    ->get();
            } else {
                $perPage = $request->get('per_page', 15);
                $designers = $query->select('id', 'username', 'email', 'phone', 'created_at')
                    ->paginate($perPage);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.designers_retrieved_successfully'),
                'data' => $designers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_occurred'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all marketers
     * GET /api/admin/marketers
     */
    public function getMarketers(Request $request)
    {
        try {
            $query = Marketer::query();

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Pagination
            if ($request->get('pagination') === 'false' || $request->get('pagination') === false) {
                $marketers = $query->select('id', 'username', 'email', 'phone', 'created_at')
                    ->get();
            } else {
                $perPage = $request->get('per_page', 15);
                $marketers = $query->select('id', 'username', 'email', 'phone', 'created_at')
                    ->paginate($perPage);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.marketers_retrieved_successfully'),
                'data' => $marketers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_occurred'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all team members (designers + marketers)
     * GET /api/admin/team-members
     */
    public function getAllTeamMembers(Request $request)
    {
        try {
            $designers = Designer::select('id', 'username', 'email', 'phone', 'created_at')
                ->get()
                ->map(function ($designer) {
                    return [
                        'id' => $designer->id,
                        'username' => $designer->username,
                        'email' => $designer->email,
                        'phone' => $designer->phone,
                        'type' => 'designer',
                        'created_at' => $designer->created_at->format('Y-m-d H:i:s'),
                    ];
                });

            $marketers = Marketer::select('id', 'username', 'email', 'phone', 'created_at')
                ->get()
                ->map(function ($marketer) {
                    return [
                        'id' => $marketer->id,
                        'username' => $marketer->username,
                        'email' => $marketer->email,
                        'phone' => $marketer->phone,
                        'type' => 'marketer',
                        'created_at' => $marketer->created_at->format('Y-m-d H:i:s'),
                    ];
                });

            $teamMembers = $designers->merge($marketers)->sortBy('username')->values();

            return response()->json([
                'success' => true,
                'message' => __('messages.team_members_retrieved_successfully'),
                'data' => [
                    'designers' => $designers->values(),
                    'marketers' => $marketers->values(),
                    'all' => $teamMembers,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_occurred'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
