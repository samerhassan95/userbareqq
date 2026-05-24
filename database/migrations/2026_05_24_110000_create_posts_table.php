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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->text('description');
            $table->text('description_ar')->nullable();
            $table->string('image')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('is_approved')->default(false); // لما الـ client يعمل approve
            $table->timestamp('approved_at')->nullable();
            
            // Client info (اللي هيشوف البوست ويعمل approve)
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            
            // Creator info (polymorphic - Admin or Marketer)
            $table->morphs('created_by');
            
            // Last editor info (polymorphic)
            $table->morphs('updated_by');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
