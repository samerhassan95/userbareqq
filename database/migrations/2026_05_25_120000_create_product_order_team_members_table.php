<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_order_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_order_id')->constrained('product_orders')->onDelete('cascade');
            $table->unsignedBigInteger('member_id');
            $table->string('member_type'); // 'designer' or 'marketer'
            $table->timestamps();

            // Unique constraint to prevent duplicate assignments
            $table->unique(['product_order_id', 'member_id', 'member_type'], 'unique_order_member');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_order_team_members');
    }
};
