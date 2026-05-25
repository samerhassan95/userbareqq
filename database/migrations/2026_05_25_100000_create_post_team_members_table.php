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
        Schema::create('post_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            
            // Polymorphic relation to Designer or Marketer
            $table->unsignedBigInteger('member_id');
            $table->string('member_type'); // App\Models\Designer or App\Models\Marketer
            
            $table->string('role')->nullable(); // designer, marketer, etc.
            $table->timestamps();
            
            // Unique constraint to prevent duplicate team members
            $table->unique(['post_id', 'member_id', 'member_type']);
            
            // Index for queries
            $table->index(['post_id']);
            $table->index(['member_id', 'member_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_team_members');
    }
};
