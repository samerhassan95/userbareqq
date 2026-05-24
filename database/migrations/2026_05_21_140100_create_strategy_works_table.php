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
        Schema::create('strategy_works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_order_id')->constrained('product_orders')->cascadeOnDelete();
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();
            $table->json('platforms')->nullable(); // ['facebook', 'instagram', 'twitter']
            $table->string('status')->default('pending'); // pending, in_progress, completed, cancelled
            $table->string('post_type')->nullable(); // image, video, text, carousel
            $table->json('attachments')->nullable(); // URLs to images/videos
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Index for date queries
            $table->index(['product_order_id', 'scheduled_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strategy_works');
    }
};
