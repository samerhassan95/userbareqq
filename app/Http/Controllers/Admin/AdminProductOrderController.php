<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductOrderResource;
use App\Models\Invoice;
use App\Models\ProductOrder;
use App\Models\Subscription;
use App\Repositories\ProductOrderRepositoryInterface;
use App\Services\ImageService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminProductOrderController extends Controller
{
    protected $orderRepository;

    public function __construct(ProductOrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Get all orders (with filters)
     * GET /api/admin/product-orders
     */
    public function index(Request $request)
    {
        $status = $request->input('status');
        $productRole = $request->input('product_role');
        $clientId = $request->input('client_id');

        $orders = ProductOrder::with(['product', 'feature', 'client', 'invoice', 'subscription'])
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($productRole, function ($q) use ($productRole) {
                $q->where('product_role', $productRole);
            })
            ->when($clientId, function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return ResponseHelper::success([
            'orders' => ProductOrderResource::collection($orders->items()),
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ]
        ], __('Orders retrieved successfully'));
    }

    /**
     * Get single order details
     * GET /api/admin/product-orders/{id}
     */
    public function show($id)
    {
        $order = $this->orderRepository->findById($id);

        if (!$order) {
            return ResponseHelper::error(__('Order not found'), [], 404);
        }

        return ResponseHelper::success(
            new ProductOrderResource($order),
            __('Order retrieved successfully')
        );
    }

    /**
     * Approve invoice payment (after client uploads payment_proof)
     * POST /api/admin/product-orders/{id}/approve-payment
     */
    public function approvePayment(Request $request, $id)
    {
        $order = $this->orderRepository->findById($id);

        if (!$order) {
            return ResponseHelper::error(__('Order not found'), [], 404);
        }

        if (!$order->invoice) {
            return ResponseHelper::error(__('Invoice not found'), [], 404);
        }

        // Check if invoice has payment_proof
        if (!$order->invoice->payment_proof) {
            return ResponseHelper::error(__('No payment proof uploaded'), [], 422);
        }

        // Update invoice status to paid
        $order->invoice->update(['status' => 'paid']);

        // Update order status to paid
        $this->orderRepository->updateStatus($order->id, 'paid');

        // If strategy product, create subscription
        if ($order->product_role === 'strategy') {
            $startsAt = Carbon::now();
            $expiresAt = $order->duration === 'year' 
                ? $startsAt->copy()->addYear() 
                : $startsAt->copy()->addMonth();

            $subscription = Subscription::create([
                'client_id' => $order->client_id,
                'product_id' => $order->product_id,
                'status' => 'active',
                'billing_cycle' => $order->duration === 'year' ? 'yearly' : 'monthly',
                'starts_at' => $startsAt,
                'expires_at' => $expiresAt,
            ]);

            $this->orderRepository->attachSubscription($order->id, $subscription->id);
        }

        return ResponseHelper::success(
            new ProductOrderResource($order->fresh()),
            __('Payment approved successfully')
        );
    }

    /**
     * Update order status
     * PUT /api/admin/product-orders/{id}/status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending_payment,paid,in_progress,delivered,cancelled',
            'admin_notes' => 'nullable|string',
        ]);

        $order = ProductOrder::find($id);

        if (!$order) {
            return ResponseHelper::error(__('Order not found'), [], 404);
        }

        $order->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes ?? $order->admin_notes,
        ]);

        return ResponseHelper::success(
            new ProductOrderResource($order->fresh()),
            __('Order status updated successfully')
        );
    }

    /**
     * Upload deliverable for one-time orders
     * POST /api/admin/product-orders/{id}/upload-deliverable
     */
    public function uploadDeliverable(Request $request, $id)
    {
        $request->validate([
            'deliverable' => 'required|file|max:51200', // 50MB max
        ]);

        $order = ProductOrder::find($id);

        if (!$order) {
            return ResponseHelper::error(__('Order not found'), [], 404);
        }

        if ($order->product_role !== 'one_time') {
            return ResponseHelper::error(__('Only one-time orders can have deliverables'), [], 422);
        }

        // Upload file
        $filePath = ImageService::upload($request->file('deliverable'), 'deliverables');

        // Update order
        $order->update([
            'deliverable_url' => $filePath,
            'status' => 'delivered',
        ]);

        return ResponseHelper::success(
            new ProductOrderResource($order->fresh()),
            __('Deliverable uploaded successfully')
        );
    }

    /**
     * Get orders statistics
     * GET /api/admin/product-orders/statistics
     */
    public function statistics()
    {
        $stats = [
            'total_orders' => ProductOrder::count(),
            'pending_payment' => ProductOrder::where('status', 'pending_payment')->count(),
            'paid' => ProductOrder::where('status', 'paid')->count(),
            'in_progress' => ProductOrder::where('status', 'in_progress')->count(),
            'delivered' => ProductOrder::where('status', 'delivered')->count(),
            'cancelled' => ProductOrder::where('status', 'cancelled')->count(),
            'one_time_orders' => ProductOrder::where('product_role', 'one_time')->count(),
            'strategy_orders' => ProductOrder::where('product_role', 'strategy')->count(),
            'total_revenue' => ProductOrder::where('status', '!=', 'cancelled')->sum('total_price'),
        ];

        return ResponseHelper::success($stats, __('Statistics retrieved successfully'));
    }

    /**
     * Get all posts for a specific order
     * GET /api/admin/product-orders/{orderId}/posts
     */
    public function getPosts($orderId)
    {
        try {
            $order = $this->orderRepository->findById($orderId);

            if (!$order) {
                return ResponseHelper::error('Order not found', [], 404);
            }

            $posts = $order->posts()
                ->with(['createdBy', 'feedbacks.createdBy', 'strategyWork', 'client'])
                ->orderBy('created_at', 'desc')
                ->get();

            return ResponseHelper::success(
                \App\Http\Resources\PostResource::collection($posts),
                __('messages.posts_retrieved')
            );
        } catch (\Exception $e) {
            \Log::error('Failed to fetch order posts: ' . $e->getMessage());
            
            return ResponseHelper::error(
                'Failed to retrieve posts: ' . $e->getMessage(),
                [],
                500
            );
        }
    }
}
