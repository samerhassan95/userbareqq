<?php

namespace App\Http\Controllers;

use App\Models\ProductOrder;
use App\Models\StrategyWork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StrategyDayController extends Controller
{
    /**
     * Get all strategies with posts for a specific day
     * Accessible by Admin, Client, Marketer, Designer
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStrategiesByDay(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'date' => 'required|date_format:Y-m-d',
                'client_id' => 'nullable|integer|exists:clients,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.validation_error'),
                    'errors' => $validator->errors()
                ], 422);
            }

            $date = $request->query('date');
            $clientId = $request->query('client_id');

            // Determine user type and access level
            $user = null;
            $userType = null;

            if (auth()->guard('admin')->check()) {
                $userType = 'admin';
            } elseif (auth()->guard('marketer')->check()) {
                $userType = 'marketer';
            } elseif (auth()->guard('designer')->check()) {
                $userType = 'designer';
            } elseif (auth()->guard('client')->check()) {
                $userType = 'client';
                $clientId = auth()->guard('client')->id(); // Force client's own ID
            } else {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthorized')
                ], 401);
            }

            // For non-client roles, client_id is required
            if ($userType !== 'client' && !$clientId) {
                return response()->json([
                    'success' => false,
                    'message' => 'client_id is required for ' . $userType . ' users'
                ], 400);
            }

            // Get product orders for the client with strategy role
            $query = ProductOrder::where('product_role', 'strategy');

            if ($userType === 'client') {
                $query->where('client_id', $clientId);
            } else {
                // Admin, Marketer, Designer can view any client's data if client_id is specified
                $query->where('client_id', $clientId);
            }

            $productOrders = $query->with([
                'client',
                'strategyWorks' => function ($q) use ($date) {
                    $q->where('scheduled_date', $date)
                      ->orderBy('scheduled_time', 'asc');
                },
                'strategyWorks.posts' => function ($q) {
                    $q->with([
                        'feedbacks.user',
                        'teamMembers',
                        'createdBy',
                        'updatedBy',
                    ])->orderBy('scheduled_time', 'asc');
                }
            ])->get();

            // Format response
            $strategies = [];
            $totalPosts = 0;

            foreach ($productOrders as $order) {
                foreach ($order->strategyWorks as $strategyWork) {
                    $postsData = [];

                    foreach ($strategyWork->posts as $post) {
                        $totalPosts++;

                        $postsData[] = [
                            'id' => $post->id,
                            'title' => $post->title,
                            'title_ar' => $post->title_ar,
                            'description' => $post->description,
                            'description_ar' => $post->description_ar,
                            'image' => $post->image ? asset('posts/' . $post->image) : null,
                            'status' => $post->status,
                            'scheduled_date' => $post->scheduled_date,
                            'scheduled_time' => $post->scheduled_time,
                            'approval_status' => [
                                'is_approved' => $post->is_approved,
                                'id_approved' => $post->id_approved,
                                'client_approved' => $post->client_approved,
                                'client_approved_at' => $post->client_approved_at,
                                'admin_approved' => $post->admin_approved,
                                'admin_approved_at' => $post->admin_approved_at,
                                'marketer_approved' => $post->marketer_approved,
                                'marketer_approved_at' => $post->marketer_approved_at,
                            ],
                            'feedbacks' => $post->feedbacks->map(function ($feedback) {
                                return [
                                    'id' => $feedback->id,
                                    'comment' => $feedback->comment,
                                    'user' => [
                                        'id' => $feedback->user->id ?? null,
                                        'name' => $feedback->user->name ?? null,
                                        'email' => $feedback->user->email ?? null,
                                        'type' => $feedback->user_type,
                                    ],
                                    'created_at' => $feedback->created_at->format('Y-m-d H:i:s'),
                                ];
                            })->values()->all(),
                            'team_members' => $post->teamMembers->map(function ($member) {
                                return [
                                    'id' => $member->id,
                                    'name' => $member->name ?? null,
                                    'email' => $member->email ?? null,
                                    'type' => $member->type,
                                ];
                            })->values()->all(),
                            'created_by' => $post->createdBy ? [
                                'id' => $post->createdBy->id,
                                'name' => $post->createdBy->name,
                                'type' => $post->created_by_type,
                            ] : null,
                            'updated_by' => $post->updatedBy ? [
                                'id' => $post->updatedBy->id,
                                'name' => $post->updatedBy->name,
                                'type' => $post->updated_by_type,
                            ] : null,
                        ];
                    }

                    $strategies[] = [
                        'id' => $strategyWork->id,
                        'title' => $strategyWork->title,
                        'title_ar' => $strategyWork->title_ar,
                        'description' => $strategyWork->description,
                        'description_ar' => $strategyWork->description_ar,
                        'scheduled_date' => $strategyWork->scheduled_date,
                        'scheduled_time' => $strategyWork->scheduled_time,
                        'platforms' => $strategyWork->platforms,
                        'status' => $strategyWork->status,
                        'post_type' => $strategyWork->post_type,
                        'attachments' => $strategyWork->attachments,
                        'notes' => $strategyWork->notes,
                        'posts' => $postsData,
                        'posts_count' => count($postsData),
                    ];
                }
            }

            // Sort strategies by time
            usort($strategies, function ($a, $b) {
                $timeA = strtotime($a['scheduled_time'] ?? '00:00:00');
                $timeB = strtotime($b['scheduled_time'] ?? '00:00:00');
                return $timeA - $timeB;
            });

            $responseData = [];

            if ($userType !== 'client') {
                $responseData['client'] = [
                    'id' => $clientId,
                    'name' => $productOrders->first()?->client->name ?? null,
                    'email' => $productOrders->first()?->client->email ?? null,
                ];
            }

            $responseData = array_merge($responseData, [
                'date' => $date,
                'user_type' => $userType,
                'strategies' => $strategies,
                'total_strategies' => count($strategies),
                'total_posts' => $totalPosts,
            ]);

            return response()->json([
                'success' => true,
                'message' => __('messages.strategies_retrieved_successfully'),
                'data' => $responseData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_occurred'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
