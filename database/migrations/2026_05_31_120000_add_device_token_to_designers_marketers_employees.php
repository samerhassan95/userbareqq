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
        // Check and add device_token to designers table
        if (!Schema::hasColumn('designers', 'device_token')) {
            Schema::table('designers', function (Blueprint $table) {
                $table->string('device_token')->nullable()->after('photo');
            });
        }
        
        // Check and add device_token to marketers table
        if (!Schema::hasColumn('marketers', 'device_token')) {
            Schema::table('marketers', function (Blueprint $table) {
                $table->string('device_token')->nullable()->after('photo');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('designers', 'device_token')) {
            Schema::table('designers', function (Blueprint $table) {
                $table->dropColumn('device_token');
            });
        }
        
        if (Schema::hasColumn('marketers', 'device_token')) {
            Schema::table('marketers', function (Blueprint $table) {
                $table->dropColumn('device_token');
            });
        }
    }
};
