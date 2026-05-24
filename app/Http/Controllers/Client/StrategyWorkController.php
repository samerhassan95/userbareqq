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
                    'attachments' => $work->attachments ?? [],
                    'notes' => $work->notes,
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
}
