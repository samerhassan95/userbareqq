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
        Schema::create('client_social_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->enum('platform', ['facebook', 'tiktok', 'instagram', 'linkedin', 'twitter']);
            $table->string('username');
            $table->text('password'); // Will be encrypted
            $table->timestamps();
            
            // Unique constraint: one credential per client per platform
            $table->unique(['client_id', 'platform']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_social_credentials');
    }
};
