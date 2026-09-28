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
        // 1. Add member_price to waste_categories
        Schema::table('waste_categories', function (Blueprint $table) {
            $table->decimal('member_price', 15, 2)->default(0)->after('unit');
        });

        // 2. Remove collector_id from deposits
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropForeign(['collector_id']);
            $table->dropColumn('collector_id');
        });

        // 3. Drop obsolete columns from waste_prices
        Schema::table('waste_prices', function (Blueprint $table) {
            $table->dropColumn(['member_price', 'price_per_unit']);
        });

        // 4. Add member_price and margin to sale_details
        Schema::table('sale_details', function (Blueprint $table) {
            $table->decimal('member_price', 15, 2)->default(0)->after('price_per_unit');
            $table->decimal('margin', 15, 2)->default(0)->after('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropColumn(['member_price', 'margin']);
        });

        Schema::table('waste_prices', function (Blueprint $table) {
            $table->decimal('member_price', 15, 2)->nullable()->after('collector_id');
            $table->decimal('price_per_unit', 15, 2)->nullable()->after('collector_id');
        });

        Schema::table('deposits', function (Blueprint $table) {
            $table->foreignId('collector_id')->nullable()->after('waste_customer_id')->constrained('collectors');
        });

        Schema::table('waste_categories', function (Blueprint $table) {
            $table->dropColumn('member_price');
        });
    }
};
