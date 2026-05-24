<?php

use App\Http\Controllers\Admin\AdminPostController;
use App\Http\Controllers\Client\ClientPostController;
use App\Http\Controllers\Client\DesignerPostController;
use App\Http\Controllers\Client\MarketerPostController;
use Illuminate\Support\Facades\Route;

// Admin Routes (Create & Edit)
Route::middleware('admin')->prefix('admin')->group(function () {
    Route::get('posts', [AdminPostController::class, 'index']);
    Route::get('posts/{id}', [AdminPostController::class, 'show']);
    Route::post('posts', [AdminPostController::class, 'store']);
    Route::put('posts/{id}', [AdminPostController::class, 'update']);
    Route::post('posts/{id}', [AdminPostController::class, 'update']); // For form-data with image
    Route::delete('posts/{id}', [AdminPostController::class, 'destroy']);
});

// Marketer Routes (Create & Edit) - Client with role=marketer
Route::middleware(['auth:api', 'check.role:marketer'])->prefix('marketer')->group(function () {
    Route::get('posts', [MarketerPostController::class, 'index']);
    Route::post('posts', [MarketerPostController::class, 'store']);
    Route::put('posts/{id}', [MarketerPostController::class, 'update']);
    Route::post('posts/{id}', [MarketerPostController::class, 'update']); // For form-data
});

// Designer Routes (Edit only) - Client with role=designer
Route::middleware(['auth:api', 'check.role:designer'])->prefix('designer')->group(function () {
    Route::get('posts', [DesignerPostController::class, 'index']);
    Route::put('posts/{id}', [DesignerPostController::class, 'update']);
    Route::post('posts/{id}', [DesignerPostController::class, 'update']); // For form-data
});

// Client Routes (View, Feedback, Approve) - Regular clients
Route::middleware(['auth:api', 'check.role:client'])->prefix('client')->group(function () {
    Route::get('posts', [ClientPostController::class, 'index']);
    Route::get('posts/{id}', [ClientPostController::class, 'show']);
    Route::post('posts/{id}/feedback', [ClientPostController::class, 'addFeedback']);
    Route::post('posts/{id}/approve', [ClientPostController::class, 'approve']);
    Route::get('posts/{id}/feedbacks', [ClientPostController::class, 'getFeedbacks']);
});
