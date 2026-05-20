<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('product_role', ['one_time', 'strategy'])->default('one_time')->after('type');
            $table->decimal('monthly_price', 12, 2)->nullable()->after('price');
            $table->decimal('yearly_price', 12, 2)->nullable()->after('monthly_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['product_role', 'monthly_price', 'yearly_price']);
        });
    }
};
