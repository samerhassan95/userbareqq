<?php

use App\Http\Controllers\AddonController;
use App\Http\Controllers\Admin\AdminProductOrderController;
use App\Http\Controllers\Admin\AdminStrategyTipController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductMediaController;
use Illuminate\Support\Facades\Route;

Route::middleware('admin')->group(function () {
    Route::apiResource('addons', AddonController::class);

    Route::apiResource('products', ProductController::class)->names([
        'index' => 'admin.products.index',
        'show' => 'admin.products.show',
        'store' => 'admin.products.store',
        'update' => 'admin.products.update',
        'destroy' => 'admin.products.destroy',
    ]);

    Route::apiResource('product-media', ProductMediaController::class)->names([
        'index' => 'admin.product-media.index',
        'show' => 'admin.product-media.show',
        'store' => 'admin.product-media.store',
        'update' => 'admin.product-media.update',
        'destroy' => 'admin.product-media.destroy',
    ]);
    Route::get('specific-product-media/{productId}', [ProductMediaController::class, 'getAllMediaForProduct']);
    Route::delete('products/{product}/media/{media}', [ProductController::class, 'deleteMedia']);

    // Invoice Management
    Route::get('invoices', [InvoiceController::class, 'getAllInvoices']);
    Route::post('invoices', [InvoiceController::class, 'store']);
    Route::get('invoices/{invoiceId}', [InvoiceController::class, 'getInvoiceDetails']);
    Route::post('invoices/{invoiceId}/pay', [InvoiceController::class, 'initiatePayment']);

    // Product Orders Management
    Route::get('product-orders', [AdminProductOrderController::class, 'index']);
    Route::get('product-orders/statistics', [AdminProductOrderController::class, 'statistics']);
    Route::get('product-orders/{id}', [AdminProductOrderController::class, 'show']);
    Route::post('product-orders/{id}/approve-payment', [AdminProductOrderController::class, 'approvePayment']);
    Route::put('product-orders/{id}/status', [AdminProductOrderController::class, 'updateStatus']);
    Route::post('product-orders/{id}/upload-deliverable', [AdminProductOrderController::class, 'uploadDeliverable']);

    // Strategy Tips Management
    Route::get('products/{productId}/strategy-tips', [AdminStrategyTipController::class, 'index']);
    Route::post('products/{productId}/strategy-tips', [AdminStrategyTipController::class, 'store']);
    Route::put('strategy-tips/{id}', [AdminStrategyTipController::class, 'update']);
    Route::delete('strategy-tips/{id}', [AdminStrategyTipController::class, 'destroy']);
    Route::post('products/{productId}/strategy-tips/reorder', [AdminStrategyTipController::class, 'reorder']);
});
