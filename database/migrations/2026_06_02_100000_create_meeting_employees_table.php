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
        if (!Schema::hasTable('meeting_employees')) {
            Schema::create('meeting_employees', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('meeting_id');
                $table->unsignedBigInteger('employee_id');
                $table->timestamps();

                // Only FK to meetings (created via migration with proper InnoDB)
                $table->foreign('meeting_id')->references('id')->on('meetings')->onDelete('cascade');

                // No FK to employees — that table was created manually on server
                $table->index('employee_id');

                $table->unique(['meeting_id', 'employee_id']);
            });
        } else {
            // Table already exists (from partial previous run) — ensure constraints exist
            Schema::table('meeting_employees', function (Blueprint $table) {
                // Add unique index if missing (ignore error if already exists)
                try {
                    $table->unique(['meeting_id', 'employee_id'], 'meeting_employees_meeting_id_employee_id_unique');
                } catch (\Exception $e) {
                    // Already exists — skip
                }
                try {
                    $table->index('employee_id', 'meeting_employees_employee_id_index');
                } catch (\Exception $e) {
                    // Already exists — skip
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_employees');
    }
};
