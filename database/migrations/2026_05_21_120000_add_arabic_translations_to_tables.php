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
        // Products table
        Schema::table('products', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->text('description_ar')->nullable()->after('description');
            $table->text('note_ar')->nullable()->after('note');
        });

        // Addons table
        Schema::table('addons', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->text('description_ar')->nullable()->after('description');
        });

        // Categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
        });

        // Product Strategy Tips table
        Schema::table('product_strategy_tips', function (Blueprint $table) {
            $table->text('text_ar')->nullable()->after('text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'description_ar', 'note_ar']);
        });

        Schema::table('addons', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'description_ar']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('name_ar');
        });

        Schema::table('product_strategy_tips', function (Blueprint $table) {
            $table->dropColumn('text_ar');
        });
    }
};
