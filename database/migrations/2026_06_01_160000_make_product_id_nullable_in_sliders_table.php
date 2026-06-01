<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['product_id']);
            
            // Make product_id nullable
            $table->foreignId('product_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable(false)->constrained()->cascadeOnDelete()->change();
        });
    }
};
