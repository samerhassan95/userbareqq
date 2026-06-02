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
        // Add language column to clients
        Schema::table('clients', function (Blueprint $table) {
            $table->string('language')->default('en')->after('device_token');
        });

        // Add language column to admins
        Schema::table('admins', function (Blueprint $table) {
            $table->string('language')->default('en')->after('device_token');
        });

        // Add language column to designers
        Schema::table('designers', function (Blueprint $table) {
            $table->string('language')->default('en')->after('device_token');
        });

        // Add language column to marketers
        Schema::table('marketers', function (Blueprint $table) {
            $table->string('language')->default('en')->after('device_token');
        });

        // Add language column to employees
        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('language')->default('en')->after('device_token');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('language');
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('language');
        });

        Schema::table('designers', function (Blueprint $table) {
            $table->dropColumn('language');
        });

        Schema::table('marketers', function (Blueprint $table) {
            $table->dropColumn('language');
        });

        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('language');
            });
        }
    }
};
