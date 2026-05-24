<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('product_order_id')->nullable()->after('client_id')->constrained('product_orders')->nullOnDelete();
            $table->foreignId('strategy_work_id')->nullable()->after('product_order_id')->constrained('strategy_works')->nullOnDelete();
            
            // Index for queries
            $table->index('product_order_id');
            $table->index('strategy_work_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['product_order_id']);
            $table->dropForeign(['strategy_work_id']);
            $table->dropIndex(['product_order_id']);
            $table->dropIndex(['strategy_work_id']);
            $table->dropColumn(['product_order_id', 'strategy_work_id']);
        });
    }
};
