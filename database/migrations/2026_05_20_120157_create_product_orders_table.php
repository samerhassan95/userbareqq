<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->enum('product_role', ['one_time', 'strategy']);
            $table->foreignId('feature_id')->nullable()->constrained('addons')->nullOnDelete();
            $table->string('feature_name')->nullable();
            $table->enum('duration', ['month', 'year'])->nullable();
            $table->decimal('total_price', 12, 2);
            $table->enum('status', ['pending_payment', 'paid', 'in_progress', 'delivered', 'cancelled'])->default('pending_payment');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->text('admin_notes')->nullable();
            $table->string('deliverable_url')->nullable(); // For one-time orders
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_orders');
    }
};
