<?php

namespace App\Http\Controllers\Client;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\CreateProductOrderRequest;
use App\Http\Resources\ProductOrderResource;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Subscription;
use App\Repositories\ProductOrderRepositoryInterface;
use Carbon\Carbon;

class ProductOrderController extends Controller
{
    protected $orderRepository;

    public function __construct(ProductOrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Create product order + invoice
     * POST /api/client/product-orders
     */
    public function store(CreateProductOrderRequest $request)
    {
        // Debug: Log request
        \Log::info('Product order request received', [
            'user_id' => auth()->id(),
            'data' => $request->all()
        ]);

        try {
            $client = auth()->user();
            
            if (!$client) {
                \Log::error('No authenticated user found');
                return ResponseHelper::error('Unauthenticated', [], 401);
            }
            
            $validated = $request->validated();

            \Log::info('Request validated', ['validated' => $validated]);

            // Get product
            $product = Product::with(['addons', 'strategyTips'])->find($validated['product_id']);
            
            if (!$product) {
                \Log::error('Product not found', ['product_id' => $validated['product_id']]);
                return ResponseHelper::error('Product not found', [], 404);
            }

            \Log::info('Product found', ['product' => $product->toArray()]);

            // Verify product_role matches
            if ($product->product_role !== $validated['product_role']) {
                \Log::error('Product role mismatch', [
                    'expected' => $product->product_role,
                    'received' => $validated['product_role']
                ]);
                return ResponseHelper::error('Product role mismatch', [], 422);
            }

            // Calculate price server-side
            $calculatedPrice = $this->calculatePrice($product, $validated);
            
            \Log::info('Price calculated', ['price' => $calculatedPrice]);

            // Create order
            $orderData = [
                'client_id' => $client->id,
                'product_id' => $product->id,
                'product_role' => $validated['product_role'],
                'total_price' => $calculatedPrice,
                'status' => 'pending_payment',
            ];

            $featureDetails = null;
            $durationDetails = null;

            if ($validated['product_role'] === 'one_time') {
                $orderData['feature_id'] = $validated['feature_id'];
                $orderData['feature_name'] = $validated['feature_name'] ?? null;
                
                // Get feature details
                $feature = $product->addons()->find($validated['feature_id']);
                if ($feature) {
                    $featureDetails = [
                        'id' => $feature->id,
                        'name' => $feature->name,
                        'price' => (float) $feature->price,
                    ];
                }
            } else {
                $orderData['duration'] = $validated['duration'];
                
                // Get duration details
                $durationDetails = [
                    'duration' => $validated['duration'],
                    'duration_label' => $validated['duration'] === 'month' ? 'Monthly' : 'Yearly',
                    'price' => (float) $calculatedPrice,
                    'starts_at' => Carbon::now()->format('Y-m-d'),
                    'ends_at' => $validated['duration'] === 'month' 
                        ? Carbon::now()->addMonth()->format('Y-m-d')
                        : Carbon::now()->addYear()->format('Y-m-d'),
                ];
            }

            \Log::info('Creating order', ['orderData' => $orderData]);

            $order = $this->orderRepository->create($orderData);
            
            \Log::info('Order created', ['order_id' => $order->id]);

            // Create invoice
            $invoice = Invoice::create([
                'client_id' => $client->id,
                'product_id' => $product->id,
                'amount' => $calculatedPrice,
                'status' => 'unpaid',
                'payment_method' => 'bank_transfer',
                'due_date' => Carbon::now()->addDays(7),
            ]);

            \Log::info('Invoice created', ['invoice_id' => $invoice->id]);

            // Link invoice to order
            $this->orderRepository->attachInvoice($order->id, $invoice->id);

            // Build comprehensive response
            $responseData = [
                'order' => [
                    'id' => $order->id,
                    'order_number' => 'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                    'status' => $order->status,
                    'status_label' => $this->getStatusLabel($order->status),
                    'total_price' => (float) $order->total_price,
                    'currency' => 'EGP',
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                ],
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'product_role' => $product->product_role,
                    'product_role_label' => $product->product_role === 'one_time' ? 'One-Time Service' : 'Strategy Package',
                ],
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => 'INV-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT),
                    'amount' => (float) $invoice->amount,
                    'status' => $invoice->status,
                    'status_label' => ucfirst($invoice->status),
                    'due_date' => $invoice->due_date->format('Y-m-d'),
                    'payment_method' => $invoice->payment_method,
                ],
                'next_steps' => [
                    'step_1' => 'Upload payment proof to the invoice',
                    'step_2' => 'Wait for admin approval',
                    'step_3' => $validated['product_role'] === 'one_time' 
                        ? 'Admin will deliver your selected feature'
                        : 'Your subscription will be activated',
                ],
            ];

            // Add role-specific details
            if ($validated['product_role'] === 'one_time') {
                $responseData['selected_feature'] = $featureDetails;
            } else {
                $responseData['subscription_details'] = $durationDetails;
                $responseData['included_tips_count'] = $product->strategyTips->count();
            }

            \Log::info('Returning success response');

            return ResponseHelper::success(
                $responseData,
                'Order created successfully! Please upload payment proof to complete your order.',
                201
            );
        } catch (\Exception $e) {
            \Log::error('Order creation failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return ResponseHelper::error(
                'Failed to create order: ' . $e->getMessage(),
                [],
                500
            );
        }
    }

    /**
     * Get client orders
     * GET /api/client/my-orders
     */
    public function index()
    {
        $client = auth()->user();
        $orders = $this->orderRepository->getClientOrders($client->id);
        
        return ResponseHelper::success(
            ProductOrderResource::collection($orders),
            __('Orders retrieved successfully')
        );
    }

    /**
     * Get single order
     * GET /api/client/product-orders/{id}
     */
    public function show($id)
    {
        $client = auth()->user();
        $order = $this->orderRepository->findById($id);

        if (!$order || $order->client_id !== $client->id) {
            return ResponseHelper::error(__('Order not found'), [], 404);
        }

        return ResponseHelper::success(
            new ProductOrderResource($order),
            __('Order retrieved successfully')
        );
    }

    /**
     * Calculate price based on product role
     */
    private function calculatePrice(Product $product, array $data)
    {
        if ($data['product_role'] === 'strategy') {
            // Strategy product
            if ($data['duration'] === 'month') {
                return $product->monthly_price ?? $product->price;
            } else {
                return $product->yearly_price ?? $product->price;
            }
        } else {
            // One-time product
            $basePrice = $product->price;
            
            if (isset($data['feature_id'])) {
                $feature = $product->addons()->find($data['feature_id']);
                if ($feature) {
                    $basePrice += $feature->price;
                }
            }
            
            return $basePrice;
        }
    }

    /**
     * Get status label in human-readable format
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'pending_payment' => 'Pending Payment',
            'paid' => 'Paid - Awaiting Delivery',
            'in_progress' => 'In Progress',
            'delivered' => 'Delivered',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }
}
