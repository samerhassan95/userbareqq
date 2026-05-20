<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductStrategyTipResource;
use App\Models\Product;
use App\Models\ProductStrategyTip;
use Illuminate\Http\Request;

class AdminStrategyTipController extends Controller
{
    /**
     * Get all tips for a product
     * GET /api/admin/products/{productId}/strategy-tips
     */
    public function index($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return ResponseHelper::error(__('Product not found'), [], 404);
        }

        $tips = $product->strategyTips;

        return ResponseHelper::success(
            ProductStrategyTipResource::collection($tips),
            __('Strategy tips retrieved successfully')
        );
    }

    /**
     * Create new tip
     * POST /api/admin/products/{productId}/strategy-tips
     */
    public function store(Request $request, $productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return ResponseHelper::error(__('Product not found'), [], 404);
        }

        if ($product->product_role !== 'strategy') {
            return ResponseHelper::error(__('Only strategy products can have tips'), [], 422);
        }

        $validated = $request->validate([
            'text' => 'required|string',
            'platforms' => 'nullable|array',
            'platforms.*' => 'in:facebook,instagram,twitter,tiktok,linkedin',
            'sort_order' => 'nullable|integer',
        ]);

        $tip = ProductStrategyTip::create([
            'product_id' => $productId,
            'text' => $validated['text'],
            'platforms' => $validated['platforms'] ?? [],
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return ResponseHelper::success(
            new ProductStrategyTipResource($tip),
            __('Strategy tip created successfully'),
            201
        );
    }

    /**
     * Update tip
     * PUT /api/admin/strategy-tips/{id}
     */
    public function update(Request $request, $id)
    {
        $tip = ProductStrategyTip::find($id);

        if (!$tip) {
            return ResponseHelper::error(__('Strategy tip not found'), [], 404);
        }

        $validated = $request->validate([
            'text' => 'nullable|string',
            'platforms' => 'nullable|array',
            'platforms.*' => 'in:facebook,instagram,twitter,tiktok,linkedin',
            'sort_order' => 'nullable|integer',
        ]);

        $tip->update($validated);

        return ResponseHelper::success(
            new ProductStrategyTipResource($tip->fresh()),
            __('Strategy tip updated successfully')
        );
    }

    /**
     * Delete tip
     * DELETE /api/admin/strategy-tips/{id}
     */
    public function destroy($id)
    {
        $tip = ProductStrategyTip::find($id);

        if (!$tip) {
            return ResponseHelper::error(__('Strategy tip not found'), [], 404);
        }

        $tip->delete();

        return ResponseHelper::success(
            null,
            __('Strategy tip deleted successfully')
        );
    }

    /**
     * Reorder tips
     * POST /api/admin/products/{productId}/strategy-tips/reorder
     */
    public function reorder(Request $request, $productId)
    {
        $validated = $request->validate([
            'tips' => 'required|array',
            'tips.*.id' => 'required|exists:product_strategy_tips,id',
            'tips.*.sort_order' => 'required|integer',
        ]);

        foreach ($validated['tips'] as $tipData) {
            ProductStrategyTip::where('id', $tipData['id'])
                ->update(['sort_order' => $tipData['sort_order']]);
        }

        return ResponseHelper::success(
            null,
            __('Tips reordered successfully')
        );
    }
}
