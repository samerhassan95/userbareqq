<?php

use App\Http\Controllers\Client\ClientAuthController;
use App\Http\Controllers\Client\ClientSocialCredentialController;
use App\Http\Controllers\Client\ProductOrderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::apiResource('products', ProductController::class)->only(['index', 'show'])->names([
    'index' => 'client.products.index',
    'show' => 'client.products.show',
]);
Route::get('our-products', [ProductController::class, 'ourProducts']);

Route::middleware(['client'])->group(function () {
    Route::get('profile', [ClientAuthController::class, 'getProfile']);
    Route::post('update-profile', [ClientAuthController::class, 'updateProfile']);
    Route::post('change-password', [ClientAuthController::class, 'changePassword']);
    Route::delete('delete', [ClientAuthController::class, 'deleteAccount']);

    Route::get('client-invoices', [InvoiceController::class, 'getInvoicesForClient']);
    Route::get('invoice-details/{invoiceId}', [InvoiceController::class, 'getInvoiceDetails']);
    Route::get('all-client-invoices', [InvoiceController::class, 'getUserInvoices']);
    Route::post('pay-invoice/{invoiceId}', [PaymentController::class, 'payInvoice']);
    Route::post('invoices/{invoiceId}/upload-payment-proof', [InvoiceController::class, 'uploadPaymentProof']);

    // Social Media Credentials Routes
    Route::get('credentials', [ClientSocialCredentialController::class, 'index']);
    Route::post('credentials', [ClientSocialCredentialController::class, 'store']);
    Route::put('credentials/{platform}', [ClientSocialCredentialController::class, 'update']);
    Route::delete('credentials/{platform}', [ClientSocialCredentialController::class, 'destroy']);

    // Product Orders Routes
    Route::post('product-orders', [ProductOrderController::class, 'store']);
    Route::get('my-orders', [ProductOrderController::class, 'index']);
    Route::get('product-orders/{id}', [ProductOrderController::class, 'show']);
});
