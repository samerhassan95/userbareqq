<?php

namespace App\Http\Controllers\Client;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\ProductOrder;
use App\Models\StrategyWork;
use Illuminate\Http\Request;

class StrategyWorkController extends Controller
{
    /**
     * Get strategy works by date
     * GET /api/client/product-orders/{orderId}/works?date=YYYY-MM-DD
     */
    public function index(Request $request, $orderId)
    {
        try {
            $client = auth()->user();
            
            // Verify order belongs to client
            $order = ProductOrder::where('id', $orderId)
                ->where('client_id', $client->id)
                ->first();

            if (!$order) {
                return ResponseHelper::error(__('Order not found'), [], 404);
            }

            // Verify it's a strategy order
            if ($order->product_role !== 'strategy') {
                return ResponseHelper::error(__('This endpoint is only for strategy orders'), [], 422);
            }

            // Get date filter (optional)
            $date = $request->query('date');
            
            $query = \App\Models\Post::with(['createdBy', 'client', 'feedbacks.client', 'strategyWork'])
                ->where('product_order_id', $orderId)
                ->whereNotNull('strategy_work_id');

            if ($date) {
                // Validate date format
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    return ResponseHelper::error(__('Invalid date format. Use YYYY-MM-DD'), [], 422);
                }
                $query->whereDate('scheduled_date', $date);
            }

            $posts = $query->orderBy('scheduled_date')
                ->orderBy('scheduled_time')
                ->get();

            $data = $posts->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => \App\Helpers\TranslationHelper::getTranslatedField($post, 'title'),
                    'description' => \App\Helpers\TranslationHelper::getTranslatedField($post, 'description'),
                    'image' => $post->image ? asset('posts/' . $post->image) : null,
                    'status' => $post->status,
                    'scheduled_date' => $post->scheduled_date ? $post->scheduled_date->format('Y-m-d') : null,
                    'scheduled_time' => $post->scheduled_time,
                    'is_approved' => $post->is_approved,
                    'client_approved' => $post->client_approved,
                    'admin_approved' => $post->admin_approved,
                    'marketer_approved' => $post->marketer_approved,
                    'approved_at' => $post->approved_at ? $post->approved_at->format('Y-m-d H:i:s') : null,
                    'work' => $post->strategyWork ? [
                        'id' => $post->strategyWork->id,
                        'title' => \App\Helpers\TranslationHelper::getTranslatedField($post->strategyWork, 'title'),
                        'platforms' => $post->strategyWork->platforms ?? [],
                        'post_type' => $post->strategyWork->post_type,
                        'notes' => $post->strategyWork->notes,
                    ] : null,
                    'created_by' => $post->createdBy ? [
                        'id' => $post->createdBy->id,
                        'name' => $post->createdBy->name ?? $post->createdBy->username ?? 'N/A',
                        'type' => class_basename($post->created_by_type),
                    ] : null,
                    'client' => $post->client ? [
                        'id' => $post->client->id,
                        'name' => $post->client->name,
                        'email' => $post->client->email,
                    ] : null,
                    'feedbacks' => $post->feedbacks->map(function ($feedback) {
                        return [
                            'id' => $feedback->id,
                            'comment' => $feedback->comment,
                            'created_at' => $feedback->created_at->format('Y-m-d H:i:s'),
                            'client' => $feedback->client ? [
                                'id' => $feedback->client->id,
                                'name' => $feedback->client->name,
                                'email' => $feedback->client->email,
                            ] : null,
                        ];
                    })->toArray(),
                ];
            });

            return ResponseHelper::success(
                $data,
                __('messages.posts_retrieved_successfully') ?? 'Posts retrieved successfully'
            );
        } catch (\Exception $e) {
            \Log::error('Failed to fetch strategy works: ' . $e->getMessage());
            
            return ResponseHelper::error(
                'Failed to retrieve strategy works: ' . $e->getMessage(),
                [],
                500
            );
        }
    }
}
