<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Update billing_cycle to include yearly
            $table->dropColumn('billing_cycle');
        });
        
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('billing_cycle', ['monthly', 'yearly', 'once'])->default('monthly')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('billing_cycle');
        });
        
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('billing_cycle', ['monthly', 'once'])->default('monthly')->after('status');
        });
    }
};
