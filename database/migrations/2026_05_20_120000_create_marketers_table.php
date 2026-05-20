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
        Schema::create('marketers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->string('password');
            $table->string('photo')->nullable();
            $table->text('bio')->nullable();
            $table->enum('role', ['user', 'admin', 'designer', 'marketer'])->default('marketer');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketers');
    }
};
