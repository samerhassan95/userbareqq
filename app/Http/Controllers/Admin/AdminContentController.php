<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStrategyTip;
use Illuminate\Http\Request;

class AdminContentController extends Controller
{
    /**
     * Get all products with English and Arabic descriptions for editing
     */
    public function getProductsContent(Request $request)
    {
        try {
            $query = Product::query();

            // Filter by type if provided
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            $products = $query->select([
                'id',
                'name',
                'name_ar',
                'description',
                'description_ar',
                'type',
                'role',
                'price_monthly',
                'price_yearly',
                'created_at',
                'updated_at'
            ])->get();

            return response()->json([
                'success' => true,
                'message' => __('messages.products_content_retrieved_successfully'),
                'data' => $products
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
     * Get single product content for editing
     */
    public function getProductContent($id)
    {
        try {
            $product = Product::select([
                'id',
                'name',
                'name_ar',
                'description',
                'description_ar',
                'type',
                'role',
                'price_monthly',
                'price_yearly',
                'created_at',
                'updated_at'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => __('messages.product_content_retrieved_successfully'),
                'data' => $product
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
     * Get all strategy tips with English and Arabic content for editing
     */
    public function getStrategyTipsContent(Request $request)
    {
        try {
            $query = ProductStrategyTip::with('product:id,name,name_ar');

            // Filter by product if provided
            if ($request->filled('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            $tips = $query->select([
                'id',
                'product_id',
                'text',
                'text_ar',
                'platforms',
                'sort_order',
                'created_at',
                'updated_at'
            ])->orderBy('product_id')->orderBy('sort_order')->get();

            return response()->json([
                'success' => true,
                'message' => __('messages.strategy_tips_content_retrieved_successfully'),
                'data' => $tips
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
     * Get single strategy tip content for editing
     */
    public function getStrategyTipContent($id)
    {
        try {
            $tip = ProductStrategyTip::with('product:id,name,name_ar')
                ->select([
                    'id',
                    'product_id',
                    'text',
                    'text_ar',
                    'platforms',
                    'sort_order',
                    'created_at',
                    'updated_at'
                ])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => __('messages.strategy_tip_content_retrieved_successfully'),
                'data' => $tip
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
