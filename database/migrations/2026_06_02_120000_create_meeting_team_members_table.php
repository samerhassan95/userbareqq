<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Replaces the non-existent `meeting_employees` pivot with a polymorphic
     * `meeting_team_members` table that supports both designers and marketers.
     */
    public function up(): void
    {
        Schema::create('meeting_team_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_id');
            $table->string('employee_type');   // 'designer' or 'marketer'
            $table->unsignedBigInteger('employee_id');
            $table->timestamps();

            $table->foreign('meeting_id')
                  ->references('id')
                  ->on('meetings')
                  ->onDelete('cascade');

            // Composite unique: one person can only be in the same meeting once
            $table->unique(['meeting_id', 'employee_type', 'employee_id'], 'uniq_meeting_team_member');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_team_members');
    }
};
