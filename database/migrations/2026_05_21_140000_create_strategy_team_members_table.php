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
        Schema::create('strategy_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_order_id')->constrained('product_orders')->cascadeOnDelete();
            $table->morphs('member'); // member_id, member_type (Designer, Marketer, Employee, Admin)
            $table->string('role')->nullable(); // designer, marketer, manager, etc.
            $table->timestamps();
            
            // Prevent duplicate assignments
            $table->unique(['product_order_id', 'member_id', 'member_type'], 'unique_team_member');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strategy_team_members');
    }
};
