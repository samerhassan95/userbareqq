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
        Schema::table('products', function (Blueprint $table) {
            // إضافة أسعار المدد الجديدة
            $table->decimal('three_month_price', 12, 2)->nullable()->after('monthly_price');
            $table->decimal('six_month_price', 12, 2)->nullable()->after('three_month_price');
            
            // إعادة تسمية yearly_price ليكون بعد six_month_price
            // (لا حاجة لإعادة الترتيب، فقط للتوضيح)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['three_month_price', 'six_month_price']);
        });
    }
};
