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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('slot_id')->nullable();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('strategy_id')->nullable();
            $table->string('meeting_name');
            $table->text('description')->nullable();
            $table->date('date')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('jitsi_url')->nullable();
            $table->string('status')->default('request_sent');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('strategy_id')->references('id')->on('product_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
