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
        $client = auth()->user();
        $validated = $request->validated();

        // Get product
        $product = Product::find($validated['product_id']);
        
        if (!$product) {
            return ResponseHelper::error(__('Product not found'), [], 404);
        }

        // Verify product_role matches
        if ($product->product_role !== $validated['product_role']) {
            return ResponseHelper::error(__('Product role mismatch'), [], 422);
        }

        // Calculate price server-side
        $calculatedPrice = $this->calculatePrice($product, $validated);

        // Create order
        $orderData = [
            'client_id' => $client->id,
            'product_id' => $product->id,
            'product_role' => $validated['product_role'],
            'total_price' => $calculatedPrice,
            'status' => 'pending_payment',
        ];

        if ($validated['product_role'] === 'one_time') {
            $orderData['feature_id'] = $validated['feature_id'];
            $orderData['feature_name'] = $validated['feature_name'] ?? null;
        } else {
            $orderData['duration'] = $validated['duration'];
        }

        $order = $this->orderRepository->create($orderData);

        // Create invoice
        $invoice = Invoice::create([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'amount' => $calculatedPrice,
            'status' => 'unpaid',
            'payment_method' => 'bank_transfer',
            'due_date' => Carbon::now()->addDays(7),
        ]);

        // Link invoice to order
        $this->orderRepository->attachInvoice($order->id, $invoice->id);

        return ResponseHelper::success([
            'order_id' => $order->id,
            'invoice_id' => $invoice->id,
            'payment_url' => null, // Offline payment
        ], __('Order created successfully. Please upload payment proof.'), 201);
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
}
