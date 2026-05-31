<?php

namespace App\Http\Controllers;

use App\Models\Post;
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

            // Get posts scheduled on this date under strategy orders
            $query = Post::query();

            if ($clientId) {
                $query->where('client_id', $clientId);
            }

            $query->where(function ($q) use ($date) {
                $q->whereHas('strategyWork', function ($sq) use ($date) {
                    $sq->whereDate('scheduled_date', $date);
                })
                ->orWhereDate('scheduled_date', $date);
            })
            ->whereHas('productOrder', function ($q) {
                $q->where('product_role', 'strategy');
            });

            $posts = $query->with([
                'feedbacks.user',
                'teamMembers',
                'createdBy',
                'updatedBy',
                'strategyWork',
                'client'
            ])->orderBy('scheduled_time', 'asc')->get();

            // Format response
            $postsData = [];

            foreach ($posts as $post) {
                $postData = [
                    'id' => $post->id,
                    'title' => $post->title,
                    'title_ar' => $post->title_ar,
                    'description' => $post->description,
                    'description_ar' => $post->description_ar,
                    'image' => $post->image ? asset('posts/' . $post->image) : null,
                    'status' => $post->status,
                    'scheduled_date' => $post->scheduled_date ? $post->scheduled_date->format('Y-m-d') : null,
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
                    'strategy_work' => $post->strategyWork ? [
                        'id' => $post->strategyWork->id,
                        'title' => $post->strategyWork->title,
                        'title_ar' => $post->strategyWork->title_ar,
                        'description' => $post->strategyWork->description,
                        'description_ar' => $post->strategyWork->description_ar,
                        'platforms' => $post->strategyWork->platforms,
                        'status' => $post->strategyWork->status,
                        'post_type' => $post->strategyWork->post_type,
                        'attachments' => $post->strategyWork->attachments,
                        'notes' => $post->strategyWork->notes,
                    ] : null,
                ];

                if ($userType !== 'client') {
                    $postData['client'] = $post->client ? [
                        'id' => $post->client->id,
                        'name' => $post->client->name,
                        'email' => $post->client->email,
                        'image' => $post->client->photo ? asset($post->client->photo) : null,
                    ] : null;
                }

                $postsData[] = $postData;
            }

            $responseData = [
                'date' => $date,
                'user_type' => $userType,
                'posts' => $postsData,
                'total_posts' => count($postsData),
            ];

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
