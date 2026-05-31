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
        Schema::table('post_feedbacks', function (Blueprint $table) {
            // Drop old client_id foreign key if it exists
            if (Schema::hasColumn('post_feedbacks', 'client_id')) {
                $table->dropForeign(['client_id']);
                $table->dropColumn('client_id');
            }

            // Add polymorphic columns for any user type
            $table->unsignedBigInteger('user_id')->nullable()->after('post_id');
            $table->string('user_type')->nullable()->after('user_id'); // Admin, Client, Marketer, Designer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_feedbacks', function (Blueprint $table) {
            // Restore old client_id column
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();

            // Drop polymorphic columns
            if (Schema::hasColumn('post_feedbacks', 'user_id')) {
                $table->dropColumn(['user_id', 'user_type']);
            }
        });
    }
};
