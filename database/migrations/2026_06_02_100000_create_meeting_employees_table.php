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
        Schema::create('meeting_employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_id');
            $table->unsignedBigInteger('employee_id');
            $table->timestamps();

            // Only FK to meetings (which was created via migration and has proper InnoDB)
            $table->foreign('meeting_id')->references('id')->on('meetings')->onDelete('cascade');

            // No FK to employees — that table was created manually on server
            $table->index('employee_id');

            $table->unique(['meeting_id', 'employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_employees');
    }
};
