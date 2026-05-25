<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\ProductOrder;
use App\Models\StrategyWork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminStrategyWorkController extends Controller
{
    /**
     * Get strategy works for an order
     * GET /api/admin/product-orders/{orderId}/works
     */
    public function index(Request $request, $orderId)
    {
        try {
            $order = ProductOrder::find($orderId);

            if (!$order) {
                return ResponseHelper::error(__('Order not found'), [], 404);
            }

            // Verify it's a strategy order
            if ($order->product_role !== 'strategy') {
                return ResponseHelper::error(__('This endpoint is only for strategy orders'), [], 422);
            }

            // Get date filter (optional)
            $date = $request->query('date');
            
            $query = StrategyWork::where('product_order_id', $orderId);

            if ($date) {
                // Validate date format
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    return ResponseHelper::error(__('Invalid date format. Use YYYY-MM-DD'), [], 422);
                }
                $query->whereDate('scheduled_date', $date);
            }

            $works = $query->orderBy('scheduled_date')
                ->orderBy('scheduled_time')
                ->get();

            $data = $works->map(function ($work) {
                // Get posts for this work with feedbacks
                $posts = $work->posts()->with(['createdBy', 'client', 'feedbacks.client'])->get();
                
                return [
                    'id' => $work->id,
                    'title' => \App\Helpers\TranslationHelper::getTranslatedField($work, 'title'),
                    'description' => \App\Helpers\TranslationHelper::getTranslatedField($work, 'description'),
                    'scheduled_date' => $work->scheduled_date->format('Y-m-d'),
                    'scheduled_time' => $work->scheduled_time,
                    'platforms' => $work->platforms ?? [],
                    'status' => $work->status,
                    'status_label' => ucfirst($work->status),
                    'post_type' => $work->post_type,
                    'notes' => $work->notes,
                    'posts' => $posts->map(function ($post) {
                        return [
                            'id' => $post->id,
                            'title' => \App\Helpers\TranslationHelper::getTranslatedField($post, 'title'),
                            'description' => \App\Helpers\TranslationHelper::getTranslatedField($post, 'description'),
                            'image' => $post->image ? asset('posts/' . $post->image) : null,
                            'status' => $post->status,
                            'is_approved' => $post->is_approved,
                            'client_approved' => $post->client_approved,
                            'admin_approved' => $post->admin_approved,
                            'marketer_approved' => $post->marketer_approved,
                            'approved_at' => $post->approved_at ? $post->approved_at->format('Y-m-d H:i:s') : null,
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
                    })->toArray(),
                    'posts_count' => $posts->count(),
                    'created_at' => $work->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return ResponseHelper::success(
                $data,
                __('Strategy works retrieved successfully')
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

    /**
     * Create new strategy work
     * POST /api/admin/product-orders/{orderId}/works
     */
    public function store(Request $request, $orderId)
    {
        try {
            $order = ProductOrder::find($orderId);

            if (!$order) {
                return ResponseHelper::error(__('Order not found'), [], 404);
            }

            if ($order->product_role !== 'strategy') {
                return ResponseHelper::error(__('This endpoint is only for strategy orders'), [], 422);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'title_ar' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'description_ar' => 'nullable|string',
                'scheduled_date' => 'required|date',
                'scheduled_time' => 'nullable|date_format:H:i',
                'platforms' => 'nullable|array',
                'platforms.*' => 'string',
                'status' => 'nullable|in:pending,in_progress,completed,cancelled',
                'post_type' => 'nullable|string',
                'attachments' => 'nullable|array',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error(__('Validation error'), $validator->errors(), 422);
            }

            $work = StrategyWork::create([
                'product_order_id' => $orderId,
                'title' => $request->title,
                'title_ar' => $request->title_ar,
                'description' => $request->description,
                'description_ar' => $request->description_ar,
                'scheduled_date' => $request->scheduled_date,
                'scheduled_time' => $request->scheduled_time,
                'platforms' => $request->platforms,
                'status' => $request->status ?? 'pending',
                'post_type' => $request->post_type,
                'attachments' => $request->attachments,
                'notes' => $request->notes,
            ]);

            return ResponseHelper::success(
                $work,
                __('Strategy work created successfully'),
                201
            );
        } catch (\Exception $e) {
            \Log::error('Failed to create strategy work: ' . $e->getMessage());
            
            return ResponseHelper::error(
                'Failed to create strategy work: ' . $e->getMessage(),
                [],
                500
            );
        }
    }

    /**
     * Update strategy work
     * PUT /api/admin/strategy-works/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $work = StrategyWork::find($id);

            if (!$work) {
                return ResponseHelper::error(__('Strategy work not found'), [], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'title_ar' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'description_ar' => 'nullable|string',
                'scheduled_date' => 'sometimes|required|date',
                'scheduled_time' => 'nullable|date_format:H:i',
                'platforms' => 'nullable|array',
                'platforms.*' => 'string',
                'status' => 'nullable|in:pending,in_progress,completed,cancelled',
                'post_type' => 'nullable|string',
                'attachments' => 'nullable|array',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error(__('Validation error'), $validator->errors(), 422);
            }

            $work->update($request->only([
                'title',
                'title_ar',
                'description',
                'description_ar',
                'scheduled_date',
                'scheduled_time',
                'platforms',
                'status',
                'post_type',
                'attachments',
                'notes',
            ]));

            return ResponseHelper::success(
                $work->fresh(),
                __('Strategy work updated successfully')
            );
        } catch (\Exception $e) {
            \Log::error('Failed to update strategy work: ' . $e->getMessage());
            
            return ResponseHelper::error(
                'Failed to update strategy work: ' . $e->getMessage(),
                [],
                500
            );
        }
    }

    /**
     * Delete strategy work
     * DELETE /api/admin/strategy-works/{id}
     */
    public function destroy($id)
    {
        try {
            $work = StrategyWork::find($id);

            if (!$work) {
                return ResponseHelper::error(__('Strategy work not found'), [], 404);
            }

            $work->delete();

            return ResponseHelper::success(
                null,
                __('Strategy work deleted successfully')
            );
        } catch (\Exception $e) {
            \Log::error('Failed to delete strategy work: ' . $e->getMessage());
            
            return ResponseHelper::error(
                'Failed to delete strategy work: ' . $e->getMessage(),
                [],
                500
            );
        }
    }
}
