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
        Schema::table('product_orders', function (Blueprint $table) {
            $table->string('duration', 50)->nullable()->change();
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('billing_cycle', 50)->default('monthly')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_orders', function (Blueprint $table) {
            $table->enum('duration', ['month', 'year'])->nullable()->change();
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('billing_cycle', ['monthly', 'yearly', 'once'])->default('monthly')->change();
        });
    }
};
