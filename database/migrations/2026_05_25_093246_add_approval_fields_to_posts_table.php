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
        Schema::table('posts', function (Blueprint $table) {
            // Approval tracking fields
            $table->boolean('client_approved')->default(false)->after('is_approved');
            $table->timestamp('client_approved_at')->nullable()->after('client_approved');
            $table->boolean('admin_approved')->default(false)->after('client_approved_at');
            $table->timestamp('admin_approved_at')->nullable()->after('admin_approved');
            $table->boolean('marketer_approved')->default(false)->after('admin_approved_at');
            $table->timestamp('marketer_approved_at')->nullable()->after('marketer_approved');
            
            // Track who approved
            $table->unsignedBigInteger('approved_by_client_id')->nullable()->after('marketer_approved_at');
            $table->unsignedBigInteger('approved_by_admin_id')->nullable()->after('approved_by_client_id');
            $table->unsignedBigInteger('approved_by_marketer_id')->nullable()->after('approved_by_admin_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'client_approved',
                'client_approved_at',
                'admin_approved',
                'admin_approved_at',
                'marketer_approved',
                'marketer_approved_at',
                'approved_by_client_id',
                'approved_by_admin_id',
                'approved_by_marketer_id',
            ]);
        });
    }
};
