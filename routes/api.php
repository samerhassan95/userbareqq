<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Client\ClientAuthController;
use App\Http\Controllers\Employee\EmployeeAuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StrategyDayController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DeviceTokenController;
use App\Http\Controllers\UniversalAuthController;
use Illuminate\Support\Facades\Route;

// Universal Login & Logout (All Roles)
Route::post('login', [UniversalAuthController::class, 'login']);
Route::post('logout', [UniversalAuthController::class, 'logout']);

Route::prefix('admin')->group(function () {
    Route::post('register', [AdminAuthController::class, 'register']);
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::post('logout', [AdminAuthController::class, 'logout']);
    Route::post('forgot-password', [AdminAuthController::class, 'forgotPassword']);
});

Route::prefix('client')->group(function () {
    Route::post('register', [ClientAuthController::class, 'register']);
    Route::post('login', [ClientAuthController::class, 'login'])->name('login');
    Route::post('logout', [ClientAuthController::class, 'logout']);
    Route::post('verify-otp', [ClientAuthController::class, 'verifyOtpAndCreateClient']);
    Route::post('forgot-password', [ClientAuthController::class, 'forgotPasswordRequest']);
    Route::post('verify-otp-and-reset-password', [ClientAuthController::class, 'verifyOtp']);
    Route::post('reset-password', [ClientAuthController::class, 'resetPassword']);
});

Route::prefix('employee')->group(function () {
    Route::post('register', [EmployeeAuthController::class, 'register']);
    Route::post('login', [EmployeeAuthController::class, 'login']);
    Route::post('logout', [EmployeeAuthController::class, 'logout']);
    Route::post('verify-otp', [EmployeeAuthController::class, 'verifyOtpAndCreateEmployee']);
    Route::post('forgot-password', [EmployeeAuthController::class, 'forgotPasswordRequest']);
    Route::post('verify-otp-and-reset-password', [EmployeeAuthController::class, 'verifyOtp']);
    Route::post('reset-password', [EmployeeAuthController::class, 'resetPassword']);
});

Route::post('opay/callback', [PaymentController::class, 'opayCallback'])->name('opay.callback');

// Shared Routes - Strategies by Day (All Authenticated Roles: Admin, Client, Marketer, Designer)
Route::middleware(['auth:admin,client,marketer,designer'])->group(function () {
    Route::get('strategies/day', [StrategyDayController::class, 'getStrategiesByDay']);
});

// Shared Routes - Notifications (All Authenticated Roles)
Route::middleware(['auth:admin,client,designer,marketer'])->group(function () {
    Route::get('notifications', [NotificationController::class, 'getNotifications']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markNotificationAsRead']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllNotificationsAsRead']);
});

// Shared Routes - Profile/Device Settings (All Authenticated Roles)
Route::middleware(['auth:admin,client,designer,marketer,employee'])->prefix('profile')->group(function () {
    Route::post('device-token', [DeviceTokenController::class, 'updateDeviceToken']);
    Route::post('language', [DeviceTokenController::class, 'updateLanguage']);
    Route::get('notification-settings', [DeviceTokenController::class, 'getNotificationSettings']);
});

// Client Invoice Routes (New PDF System)
Route::middleware(['auth:client'])->prefix('client')->group(function () {
    Route::get('invoices', [\App\Http\Controllers\Client\InvoiceController::class, 'index'])->name('client.invoices.index');
    Route::get('invoices/{id}', [\App\Http\Controllers\Client\InvoiceController::class, 'show'])->name('client.invoices.show');
    Route::get('invoices/{id}/download', [\App\Http\Controllers\Client\InvoiceController::class, 'download'])->name('client.invoices.download');
    Route::get('invoices/{id}/view', [\App\Http\Controllers\Client\InvoiceController::class, 'view'])->name('client.invoices.view');
});

// Unified Sliders Routes (Public read, Admin write)
Route::get('sliders', [\App\Http\Controllers\SliderController::class, 'index']);
Route::get('sliders/{id}', [\App\Http\Controllers\SliderController::class, 'show'])->where('id', '[0-9]+');

Route::middleware(['admin'])->group(function () {
    Route::post('sliders', [\App\Http\Controllers\SliderController::class, 'store']);
    Route::post('sliders/{id}', [\App\Http\Controllers\SliderController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('sliders/{id}', [\App\Http\Controllers\SliderController::class, 'destroy'])->where('id', '[0-9]+');
});
