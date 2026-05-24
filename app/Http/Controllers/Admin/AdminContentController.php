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
        $query = Product::query();

        // Filter by type if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $products = $query->select([
            'id',
            'name_en',
            'name_ar',
            'description_en',
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
    }

    /**
     * Get single product content for editing
     */
    public function getProductContent($id)
    {
        $product = Product::select([
            'id',
            'name_en',
            'name_ar',
            'description_en',
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
    }

    /**
     * Get all strategy tips with English and Arabic content for editing
     */
    public function getStrategyTipsContent(Request $request)
    {
        $query = ProductStrategyTip::with('product:id,name_en,name_ar');

        // Filter by product if provided
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $tips = $query->select([
            'id',
            'product_id',
            'title_en',
            'title_ar',
            'description_en',
            'description_ar',
            'order',
            'created_at',
            'updated_at'
        ])->orderBy('product_id')->orderBy('order')->get();

        return response()->json([
            'success' => true,
            'message' => __('messages.strategy_tips_content_retrieved_successfully'),
            'data' => $tips
        ]);
    }

    /**
     * Get single strategy tip content for editing
     */
    public function getStrategyTipContent($id)
    {
        $tip = ProductStrategyTip::with('product:id,name_en,name_ar')
            ->select([
                'id',
                'product_id',
                'title_en',
                'title_ar',
                'description_en',
                'description_ar',
                'order',
                'created_at',
                'updated_at'
            ])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => __('messages.strategy_tip_content_retrieved_successfully'),
            'data' => $tip
        ]);
    }
}
